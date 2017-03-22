<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Resource_Catalog_Product_Indexing_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    public function isEnabledFlat()
    {
        return false;
    }

    /**
     * Join Product Price Table
     * Join left by default in order to include products without price index entry
     *
     * @return IntegerNet_Solr_Model_Resource_Catalog_Product_Indexing_Collection
     */
    protected function _productLimitationJoinPrice()
    {
        return $this->_productLimitationPrice(true);
    }
}