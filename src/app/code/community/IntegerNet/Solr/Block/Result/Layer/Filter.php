<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Result_Layer_Filter extends Mage_Core_Block_Template
{
    /**
     * Whether to display product count for layer navigation items
     * @var bool
     */
    protected $_displayProductCount = null;

    protected $_categoryFilterItems = null;

    protected $_currentCategory = null;

    protected $_numberFilterOptionsDisplayed = 0;

    /**
     * @return Mage_Catalog_Model_Entity_Attribute
     */
    public function getAttribute()
    {
        return $this->getData('attribute');
    }

    /**
     * @return bool
     */
    public function isCategory()
    {
        return (boolean)$this->getData('is_category');
    }

    /**
     * @return bool
     */
    public function isRange()
    {
        return (boolean)$this->getData('is_range');
    }

    /**
     * @return Varien_Object[]
     * @throws Mage_Core_Exception
     */
    public function getItems()
    {
        if ($this->isCategory()) {
            return $this->_getCategoryFilterItems();
        }

        if ($this->isRange()) {
            return $this->_getRangeFilterItems();
        }

        return $this->_getAttributeFilterItems();
    }

    /**
     * Get filter item url
     *
     * @param int $optionId
     * @return string
     */
    protected function _getUrl($optionId)
    {
        if (!Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')) {
            if ($this->isCategory()) {
                $query = array(
                    'cat' => $optionId,
                    Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
                );
            } else {
                $query = array(
                    $this->getAttribute()->getAttributeCode() => $optionId,
                    Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
                );
            }
            return Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true, '_query' => $query));
        }

        if ($this->isCategory()) {
            $identifier = 'cat';
        } else {
            $identifier = $this->getAttribute()->getAttributeCode();
        }
        $query = $this->_getQuery($identifier, $optionId);
        return Mage::getUrl($this->_getRoute(), array('_current' => true, '_use_rewrite' => true, '_query' => $query));
    }

    /**
     * Get filter item url
     *
     * @param int $rangeStart
     * @param int $rangeEnd
     * @return string
     */
    protected function _getRangeUrl($rangeStart, $rangeEnd)
    {
        $identifier = 'price';
        $query = $this->_getQuery($identifier, floatval($rangeStart) . '-' . floatval($rangeEnd));
        return Mage::getUrl($this->_getRoute(), array('_current' => true, '_use_rewrite' => true, '_query' => $query));
    }

    /**
     * @return Apache_Solr_Response
     */
    protected function _getSolrResult()
    {
        return Mage::getSingleton('integernet_solr/result')->getSolrResult();
    }

    /**
     * Getter for $_displayProductCount
     * @return bool
     */
    public function shouldDisplayProductCount()
    {
        if ($this->_displayProductCount === null) {
            $this->_displayProductCount = Mage::helper('catalog')->shouldDisplayProductCountOnLayer();
        }
        return $this->_displayProductCount;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     * @throws Mage_Core_Exception
     */
    protected function _getCurrentChildrenCategories()
    {
        $currentCategory = $this->_getCurrentCategory();

        $childrenCategories = Mage::getResourceModel('catalog/category_collection')
            ->setStore(Mage::app()->getStore())
            ->addAttributeToSelect('name', 'url_key')
            ->addAttributeToFilter('level', $currentCategory->getLevel() + 1)
            ->addAttributeToFilter('path', array('like' => $currentCategory->getPath() . '/%'))
            ->setOrder('position', 'asc');

        return $childrenCategories;
    }

    /**
     * @return Varien_Object[]
     */
    protected function _getCategoryFilterItems()
    {
        if (is_null($this->_categoryFilterItems)) {

            $this->_categoryFilterItems = array();

            $facetName = 'category';
            if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$facetName})) {

                $categoryFacets = $this->_getSolrResult()->facet_counts->facet_fields->{$facetName};

                if (Mage::helper('integernet_solr')->page()->isCategoryPage()) {

                    $childrenCategories = $this->_getCurrentChildrenCategories();

                    foreach ($childrenCategories as $childCategory) {
                        
                        $childCategoryId = $childCategory->getId();
                        if (isset($categoryFacets->{$childCategoryId})) {
                            $item = new Varien_Object();
                            $item->setCount($categoryFacets->{$childCategoryId});
                            $optionLabel = $childCategory->getName();
                            $item->setLabel($this->_getCheckboxHtml('cat', $childCategoryId) . ' ' . $optionLabel);
                            $item->setUrl($this->_getUrl($childCategoryId));
                            $item->setIsChecked($this->_isSelected('cat', $childCategoryId));
                            $item->setType('category');
                            $item->setOptionId($childCategoryId);

                            Mage::dispatchEvent('integernet_solr_filter_item_create', array(
                                'item' => $item,
                                'solr_result' => $this->_getSolrResult(),
                                'type' => 'category',
                                'entity_id' => $childCategoryId,
                                'entity' => $childCategory,
                            ));

                            if (!$item->getIsDisabled()) {
                                $this->_categoryFilterItems[$optionLabel] = $item;
                            }
                        }
                    }

                } else {

                    foreach ((array)$categoryFacets as $optionId => $optionCount) {

                        $item = new Varien_Object();
                        $item->setCount($optionCount);
                        $optionLabel = Mage::getResourceSingleton('catalog/category')->getAttributeRawValue($optionId, 'name', Mage::app()->getStore());
                        $item->setLabel($this->_getCheckboxHtml('cat', $optionId) . ' ' . $optionLabel);
                        $item->setUrl($this->_getUrl($optionId));
                        $item->setIsChecked($this->_isSelected('cat', $optionId));
                        $item->setType('category');
                        $item->setOptionId($optionId);

                        Mage::dispatchEvent('integernet_solr_filter_item_create', array(
                            'item' => $item,
                            'solr_result' => $this->_getSolrResult(),
                            'type' => 'category',
                            'entity_id' => $optionId,
                        ));

                        if (!$item->getIsDisabled()) {
                            if ($this->_isMaxNumberFilterOptionsExceeded()) {
                                break;
                            }
    
                            $this->_categoryFilterItems[$optionLabel] = $item;
                        }
                    }

                }
                if (Mage::getStoreConfigFlag('integernet_solr/results/sort_filter_options_alphabetically')) {
                    ksort($this->_categoryFilterItems);
                }
            }
        }

        return $this->_categoryFilterItems;
    }

    /**
     * @return Varien_Object[]
     */
    protected function _getRangeFilterItems()
    {
        $items = array();

        $store = Mage::app()->getStore();
        $attributeCodeFacetRangeName = Mage::helper('integernet_solr')->attribute()->getFieldName($this->getAttribute());
        if (isset($this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName})) {

            $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName};

            foreach ($attributeFacetData as $range => $rangeCount) {

                if (!$rangeCount) {
                    continue;
                }

                $item = new Varien_Object();
                $item->setCount($rangeCount);

                $commaPos = strpos($range, ',');
                $rangeStart = floatval(substr($range, 1, $commaPos - 1));
                $rangeEnd = floatval(substr($range, $commaPos + 1, -1));
                if ($rangeEnd == 0) {
                    $label = Mage::helper('integernet_solr')->__('from %s', $store->formatPrice($rangeStart));
                } else {
                    $label = Mage::helper('catalog')->__('%s - %s', $store->formatPrice($rangeStart), $store->formatPrice($rangeEnd));
                }

                $item->setLabel($this->_getCheckboxHtml('price', floatval($rangeStart) . '-' . floatval($rangeEnd)) . ' ' . $label);
                $item->setUrl($this->_getRangeUrl($rangeStart, $rangeEnd));
                $item->setIsChecked($this->_isSelected('price', floatval($rangeStart) . '-' . floatval($rangeEnd)));
                $item->setType('range');
                $item->setOptionId(floatval($rangeStart) . '-' . floatval($rangeEnd));

                Mage::dispatchEvent('integernet_solr_filter_item_create', array(
                    'item' => $item,
                    'solr_result' => $this->_getSolrResult(),
                    'type' => 'range',
                    'entity_id' => floatval($rangeStart) . '-' . floatval($rangeEnd),
                    'entity' => $this->getAttribute(),
                ));

                if (!$item->getIsDisabled()) {
                    if ($this->_isMaxNumberFilterOptionsExceeded()) {
                        break;
                    }

                    $items[] = $item;
                }
            }
        } elseif (isset($this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName})) {

            $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName};

            foreach ($attributeFacetData['counts'] as $rangeStart => $rangeCount) {

                $item = new Varien_Object();
                $item->setCount($rangeCount);
                $rangeEnd = $rangeStart + $attributeFacetData['gap'];
                $item->setLabel($this->_getCheckboxHtml('price', floatval($rangeStart) . '-' . floatval($rangeEnd)) . ' ' . Mage::helper('catalog')->__(
                        '%s - %s',
                        $store->formatPrice($rangeStart),
                        $store->formatPrice($rangeEnd)
                    ));
                $item->setUrl($this->_getRangeUrl($rangeStart, $rangeEnd));
                $item->setIsChecked($this->_isSelected('price', floatval($rangeStart) . '-' . floatval($rangeEnd)));
                $item->setType('range');
                $item->setOptionId(floatval($rangeStart) . '-' . floatval($rangeEnd));

                Mage::dispatchEvent('integernet_solr_filter_item_create', array(
                    'item' => $item,
                    'solr_result' => $this->_getSolrResult(),
                    'type' => 'range',
                    'entity_id' => floatval($rangeStart) . '-' . floatval($rangeEnd),
                    'entity' => $this->getAttribute(),
                ));

                if (!$item->getIsDisabled()) {
                    if ($this->_isMaxNumberFilterOptionsExceeded()) {
                        break;
                    }

                    $items[] = $item;
                }
            }
        }
        return $items;
    }

    /**
     * @return Varien_Object[]
     * @throws Mage_Core_Exception
     */
    protected function _getAttributeFilterItems()
    {
        $items = array();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeCodeFacetName = $attributeCode . '_facet';
        if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName})) {

            $attributeFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName};

            foreach ($attributeFacets as $optionId => $optionCount) {

                if (!$optionCount) {
                    continue;
                }
                /** @var Mage_Catalog_Model_Category $currentCategory */
                $currentCategory = $this->_getCurrentCategory();
                if ($currentCategory) {
                    $removedFilterAttributeCodes = $currentCategory->getData('solr_remove_filters');
                    if (is_array($removedFilterAttributeCodes) && in_array($attributeCode, $removedFilterAttributeCodes)) {
                        continue;
                    }
                }
                $item = new Varien_Object();
                $item->setCount($optionCount);
                $optionLabel = $this->getAttribute()->getSource()->getOptionText($optionId);
                $item->setLabel($this->_getCheckboxHtml($attributeCode, $optionId) . ' ' . $optionLabel);
                $item->setUrl($this->_getUrl($optionId));
                $item->setIsChecked($this->_isSelected($attributeCode, $optionId));
                $item->setType('attribute');
                $item->setOptionId($optionId);

                Mage::dispatchEvent('integernet_solr_filter_item_create', array(
                    'item' => $item,
                    'solr_result' => $this->_getSolrResult(),
                    'type' => 'attribute',
                    'entity_id' => $optionId,
                    'entity' => $this->getAttribute(),
                ));

                if (!$item->getIsDisabled()) {
                    if ($this->_isMaxNumberFilterOptionsExceeded()) {
                        break;
                    }
    
                    $items[$optionLabel] = $item;
                }
            }
            if (Mage::getStoreConfigFlag('integernet_solr/results/sort_filter_options_alphabetically')) {
                ksort($items);
            }
        }

        return $items;
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

    protected function _getCheckboxHtml($attributeCode, $optionId)
    {
        if (!Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')) {
            return '';
        }
        /** @var $checkboxBlock IntegerNet_Solr_Block_Result_Layer_Checkbox */
        $checkboxBlock = $this->getLayout()->createBlock('integernet_solr/result_layer_checkbox');
        $checkboxBlock
            ->setIsChecked($this->_isSelected($attributeCode, $optionId))
            ->setOptionId($optionId)
            ->setAttributeCode($attributeCode)
            ->setIsTopNav(strpos($this->getNameInLayout(), 'topnav') !== false);
        return $checkboxBlock->toHtml();
    }

    /**
     * @param string $identifier
     * @param int $optionId
     * @return bool
     */
    protected function _isSelected($identifier, $optionId)
    {
        $selectedOptionIds = explode(',', $this->getCurrentParamValue($identifier));
        if (in_array($optionId, $selectedOptionIds)) {
            return true;
        }
        return false;
    }

    /**
     * Get updated query params, depending on previously selected filters
     *
     * @param string $identifier
     * @param int $optionId
     * @return array
     */
    protected function _getQuery($identifier, $optionId)
    {
        $currentParamValue = $this->getCurrentParamValue($identifier);
        if (strlen($currentParamValue)) {
            $selectedOptionIds = explode(',', $currentParamValue);
        } else {
            $selectedOptionIds = array();
        }
        if (in_array($optionId, $selectedOptionIds)) {
            $newParamValues = array_diff($selectedOptionIds, array($optionId));
        } else {
            $newParamValues = $selectedOptionIds;
            $newParamValues[] = $optionId;
        }
        if (sizeof($newParamValues)) {
            $newParamValues = implode(',', $newParamValues);
        } else {
            $newParamValues = null;
        }
        return array(
            $identifier => $newParamValues,
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function getCurrentParamValue($identifier)
    {
        return Mage::app()->getRequest()->getParam($identifier);
    }

    /**
     * @return string
     */
    protected function _getRoute()
    {
        if (Mage::helper('integernet_solr')->page()->isCategoryPage()) {

            return 'catalog/category/view';
        }
        return 'catalogsearch/result/*';
    }

    /**
     * @return bool
     */
    protected function _isMaxNumberFilterOptionsExceeded()
    {
        $maxNumberFilterOptions = intval(Mage::getStoreConfig('integernet_solr/results/max_number_filter_options'));
        if ($maxNumberFilterOptions == 0) {
            return false;
        }
        if (++$this->_numberFilterOptionsDisplayed > $maxNumberFilterOptions) {
            return true;
        }
        return false;
    }

    /**
     * @return IntegerNet_Solr_Block_Result_Layer_Filter
     */
    public function reset()
    {
        $this->_numberFilterOptionsDisplayed = 0;
        return $this;
    }
}