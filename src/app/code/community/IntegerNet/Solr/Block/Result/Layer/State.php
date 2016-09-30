<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Result_Layer_State extends Mage_Core_Block_Template
{
    protected $_activeFilters = null;
    
    public function getActiveFilters()
    {
        if (is_null($this->_activeFilters)) {
            $store = Mage::app()->getStore();
            $this->_activeFilters = array();

            if ($categoryIds = Mage::app()->getRequest()->getParam('cat')) {
                foreach(explode(',', $categoryIds) as $categoryId) {
                    $optionLabel = Mage::getResourceSingleton('catalog/category')->getAttributeRawValue($categoryId, 'name', Mage::app()->getStore());
                    $filter = new Varien_Object();
                    $filter->setIsCategory(true);
                    $filter->setName(Mage::helper('catalog')->__('Category'));
                    $filter->setLabel($optionLabel);
                    $filter->setRemoveUrl($this->_getRemoveUrl('cat', $categoryId));
                    $this->_activeFilters[] = $filter;
                }
            }

            foreach (Mage::getModel('integernet_solr/bridge_factory')->getAttributeRepository()->getFilterableAttributes($store->getId(), false) as $attribute) {
                /** @var Mage_Catalog_Model_Entity_Attribute $attribute */

                $optionLabel = '';
                if ($optionIds = Mage::app()->getRequest()->getParam($attribute->getAttributeCode())) {
                    foreach(explode(',', $optionIds) as $optionId) {
                        if ($attribute->getFrontendInput() == 'price') {
                            if (strpos($optionId, '-') !== false) {
                                list($fromPrice, $toPrice) = explode('-', $optionId);
                                if ($toPrice <= 0) {
                                    $optionLabel = Mage::helper('integernet_solr')->__('from %s', $store->formatPrice($fromPrice));
                                } else {
                                    $optionLabel = Mage::helper('catalog')->__('%s - %s', $store->formatPrice($fromPrice), $store->formatPrice($toPrice));
                                }
                            } else {
                                list($index, $stepSize) = explode(',', $optionId);
                                if (Mage::getStoreConfigFlag('integernet_solr/results/use_custom_price_intervals')
                                    && $customPriceIntervals = Mage::getStoreConfig('integernet_solr/results/custom_price_intervals')
                                ) {
                                    $lowerBorder = 0;
                                    $i = 1;
                                    foreach (explode(',', $customPriceIntervals) as $upperBorder) {
                                        if ($i == $index) {
                                            $optionLabel = Mage::helper('catalog')->__('%s - %s', $store->formatPrice($lowerBorder), $store->formatPrice($upperBorder));
                                            break;
                                        }

                                        $i++;
                                        $lowerBorder = $upperBorder;
                                    }
                                    if (!$optionLabel) {
                                        $optionLabel = Mage::helper('integernet_solr')->__('from %s', $store->formatPrice($lowerBorder));
                                    }
                                } else {
                                    $lowerBorder = ($index - 1) * $stepSize;
                                    $upperBorder = ($index) * $stepSize;
                                    $optionLabel = Mage::helper('catalog')->__('%s - %s', $store->formatPrice($lowerBorder), $store->formatPrice($upperBorder));
                                }
                            }
                        } else {
                            $optionLabel = $attribute->getSource()->getOptionText($optionId);
                        }
                        $filter = new Varien_Object();
                        $filter->setAttribute($attribute);
                        $filter->setName($attribute->getStoreLabel());
                        $filter->setLabel($optionLabel);
                        $filter->setOptionId($optionId);
                        $filter->setRemoveUrl($this->_getRemoveUrl($attribute->getAttributeCode(), $optionId));
                        $this->_activeFilters[] = $filter;
                    }
                }
            }
        }
        
        return $this->_activeFilters;
    }

    /**
     * Get url for remove item from filter
     *
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    protected function _getRemoveUrl($attributeCode, $optionId)
    {
        $currentParamValue = $this->_getCurrentParamValue($attributeCode);
        if (strlen($currentParamValue)) {
            $selectedOptionIds = explode(',', $currentParamValue);
        } else {
            $selectedOptionIds = array();
        }
        $newParamValues = array_diff($selectedOptionIds, array($optionId));
        if (sizeof($newParamValues)) {
            $newParamValues = implode(',', $newParamValues);
        } else {
            $newParamValues = null;
        }

        $query = array($attributeCode => $newParamValues);
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = true;
        return Mage::getUrl($this->_getRoute(), $params);
    }

    /**
     * @param $identifier
     * @return mixed
     */
    protected function _getCurrentParamValue($identifier)
    {
        return Mage::app()->getRequest()->getParam($identifier);
    }

    /**
     * @return Apache_Solr_Response
     */
    protected function _getSolrResult()
    {
        return Mage::getSingleton('integernet_solr/result')->getSolrResult();
    }
    
    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        $filterState = array();
        foreach ($this->getActiveFilters() as $item) {
            if ($item->getIsCategory()) {
                $filterState['cat'] = null;
                continue;
            }
            $filterState[$item->getAttribute()->getAttributeCode()] = null;
        }
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $filterState;
        $params['_escape']      = true;
        return Mage::getUrl($this->_getRoute(), $params);
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
}