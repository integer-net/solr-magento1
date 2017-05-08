<?php
use IntegerNet\Solr\Config\CategoryConfig;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Result_Layer_View extends Mage_Core_Block_Template
{
    protected $_filters = null;
    protected $_currentCategory = null;

    protected function _construct()
    {
        IntegerNet_Solr_Helper_Autoloader::createAndRegister();
        parent::_construct();
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        switch ($this->getNameInLayout()) {
            case 'catalogsearch.solr.leftnav':
                return Mage::getStoreConfig('integernet_solr/results/filter_position') == CategoryConfig::FILTER_POSITION_LEFT;
            case 'catalogsearch.solr.topnav':
                return Mage::getStoreConfig('integernet_solr/results/filter_position') == CategoryConfig::FILTER_POSITION_TOP;
            case 'catalog.solr.leftnav':
                switch ($this->_getCurrentCategory()->getData('filter_position')) {
                    case CategoryConfig::FILTER_POSITION_DEFAULT:
                        return Mage::getStoreConfig('integernet_solr/category/filter_position') == CategoryConfig::FILTER_POSITION_LEFT;
                    case CategoryConfig::FILTER_POSITION_LEFT:
                        return true;
                    case CategoryConfig::FILTER_POSITION_TOP:
                        return false;
                }
            case 'catalog.solr.topnav':
                switch ($this->_getCurrentCategory()->getData('filter_position')) {
                    case CategoryConfig::FILTER_POSITION_DEFAULT:
                        return Mage::getStoreConfig('integernet_solr/category/filter_position') == CategoryConfig::FILTER_POSITION_TOP;
                    case CategoryConfig::FILTER_POSITION_LEFT:
                        return false;
                    case CategoryConfig::FILTER_POSITION_TOP:
                        return true;
                }
        }
        return true;
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowOptions()
    {
        return (bool)sizeof($this->getFilters());
    }

    public function getStateHtml()
    {
        return $this->getChildHtml('state');
    }

    public function getFilters()
    {
        if (is_null($this->_filters)) {
            $this->_filters = array();
            $facetName = 'category';
            if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$facetName})) {

                $categoryFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$facetName};
                $categoryFilter = $this->_getCategoryFilter($categoryFacets);
                if ($categoryFilter->getItemsCount() && trim($categoryFilter->getHtml())) {
                    $this->_filters[] = $categoryFilter;
                }
            }
            foreach (Mage::getModel('integernet_solr/bridge_factory')->getAttributeRepository()->getFilterableAttributes(Mage::app()->getStore()->getId(), false) as $attribute) {
                /** @var Mage_Catalog_Model_Entity_Attribute $attribute */

                /** @var Mage_Catalog_Model_Category $currentCategory */
                $currentCategory = $this->_getCurrentCategory();
                if ($currentCategory) {
                    $removedFilterAttributeCodes = $currentCategory->getData('solr_remove_filters');

                    if (is_array($removedFilterAttributeCodes) && in_array($attribute->getAttributeCode(), $removedFilterAttributeCodes)) {
                        continue;
                    }
                }

                $attributeCodeFacetName = $attribute->getAttributeCode() . '_facet';
                if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName})) {

                    $attributeFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName};
                    $filter = $this->_getFilter($attribute, $attributeFacets);
                    if ($filter->getItemsCount() && trim($filter->getHtml())) {
                        $this->_filters[] = $filter;
                    }
                }
                $attributeCodeFacetRangeName = Mage::helper('integernet_solr')->attribute()->getFieldName($attribute);
                if (isset($this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName})) {

                    $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName};
                    $filter = $this->_getIntervalFilter($attribute, $attributeFacetData);
                    if ($filter->getItemsCount() && trim($filter->getHtml())) {
                        $this->_filters[] = $filter;
                    }
                } elseif (isset($this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName})) {

                    $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName};
                    $filter = $this->_getRangeFilter($attribute, $attributeFacetData);
                    if ($filter->getItemsCount() && trim($filter->getHtml())) {
                        $this->_filters[] = $filter;
                    }
                }
            }
        }
        return $this->_filters;
    }

    /**
     * @param Mage_Catalog_Model_Entity_Attribute $attribute
     * @param int[] $attributeFacets
     * @return Varien_Object
     */
    protected function _getFilter($attribute, $attributeFacets)
    {
        $filter = new Varien_Object();
        $filter->setName($attribute->getStoreLabel());
        $filter->setItemsCount(sizeof(array_filter($attributeFacets)));
        $filter->setIdentifier($attribute->getAttributeCode());
        $filter->setHtml(
            $this->_getFilterBlock()
                ->setData('is_category', false)
                ->setData('is_range', false)
                ->setData('attribute', $attribute)
                ->toHtml()
        );
        return $filter;
    }

    /**
     * @param Mage_Catalog_Model_Entity_Attribute $attribute
     * @param array $attributeFacetData
     * @return Varien_Object
     */
    protected function _getIntervalFilter($attribute, $attributeFacetData)
    {
        $filter = new Varien_Object();
        $filter->setName($attribute->getStoreLabel());
        $filter->setItemsCount(sizeof(array_filter($attributeFacetData)));
        $filter->setIdentifier($attribute->getAttributeCode());
        $filter->setHtml(
            $this->_getFilterBlock()
                ->setData('is_category', false)
                ->setData('is_range', true)
                ->setData('attribute', $attribute)
                ->toHtml()
        );
        return $filter;
    }

    /**
     * @param Mage_Catalog_Model_Entity_Attribute $attribute
     * @param array $attributeFacetData
     * @return Varien_Object
     */
    protected function _getRangeFilter($attribute, $attributeFacetData)
    {
        $filter = new Varien_Object();
        $filter->setName($attribute->getStoreLabel());
        $filter->setItemsCount(sizeof(array_filter($attributeFacetData['counts'])));
        $filter->setIdentifier($attribute->getAttributeCode());
        $filter->setHtml(
            $this->_getFilterBlock()
                ->setData('is_category', false)
                ->setData('is_range', true)
                ->setData('attribute', $attribute)
                ->toHtml()
        );
        return $filter;
    }

    /**
     * @param int[] $categoryFacets
     * @return Varien_Object
     */
    protected function _getCategoryFilter($categoryFacets)
    {
        $filter = new Varien_Object();
        $filter->setName(Mage::helper('catalog')->__('Category'));
        $filter->setItemsCount(sizeof(array_filter($categoryFacets)));
        $filter->setIdentifier('category');

        /** @var IntegerNet_Solr_Block_Result_Layer_Filter $filterBlock */
        $filterBlock = $this->_getFilterBlock()
            ->setData('is_category', true);
        if (sizeof($filterBlock->getItems())) {
            $filter->setHtml(
                $filterBlock->toHtml()
            );
        } else {
            $filter->setHtml('');
        }
        return $filter;
    }

    /**
     * @return Apache_Solr_Response
     */
    protected function _getSolrResult()
    {
        return Mage::getSingleton('integernet_solr/result')->getSolrResult();
    }

    /**
     * @return IntegerNet_Solr_Block_Result_Layer
     */
    public function getLayer()
    {
        return $this->getLayout()->createBlock('integernet_solr/result_layer');
    }

    /**
     * @return Mage_Catalog_Model_Category|false
     */
    protected function _getCurrentCategory()
    {
        if (is_null($this->_currentCategory)) {
            /** @var Mage_Catalog_Model_Category $currentCategory */
            $this->_currentCategory = Mage::registry('current_category');
            if (is_null($this->_currentCategory)) {
                $this->_currentCategory = false;
            }
        }

        return $this->_currentCategory;
    }

    /**
     * @return IntegerNet_Solr_Block_Result_Layer_Filter
     */
    protected function _getFilterBlock()
    {
        return $this->getChild('filter')->reset();
    }
}
