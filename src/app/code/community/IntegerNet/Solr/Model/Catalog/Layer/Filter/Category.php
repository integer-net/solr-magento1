<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class IntegerNet_Solr_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{


    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        foreach(explode(',', $filter) as $subFilter) {

            $this->_categoryId = $subFilter;

            Mage::register('current_category_filter', $this->getCategory(), true);

            $this->_appliedCategory = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($subFilter);

            if ($this->_isValidCategory($this->_appliedCategory)) {
                $this->getLayer()->getProductCollection()
                    ->addCategoryFilter($this->_appliedCategory);

                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($this->_appliedCategory->getName(), $filter)
                );
            }
        }

        return $this;
    }
}