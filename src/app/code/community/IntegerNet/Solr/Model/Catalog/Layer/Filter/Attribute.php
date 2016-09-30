<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        foreach (explode(',', $filter) as $optionValue) {
            $text = $this->_getOptionText($optionValue);
            if ($filter && strlen($text)) {
                $this->_getResource()->applyFilterToCollection($this, $optionValue);
                $this->getLayer()->getState()->addFilter($this->_createItem($text, $optionValue));
                $this->_items = array();
            }
        }
        return $this;
    }
}