<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

/** @var $attributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */
$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
    ->addFieldToFilter('attribute_code', array('in' => array('name', 'sku', 'manufacturer', 'brand')));

foreach($attributes as $attribute) {
    switch ($attribute->getAttributeCode()) {
        case 'name':
        case 'sku':
            $attribute->setSolrBoost(5)->save();
            break;
            
        default:
            $attribute->setSolrBoost(2)->save();
    }
}

$installer->endSetup();