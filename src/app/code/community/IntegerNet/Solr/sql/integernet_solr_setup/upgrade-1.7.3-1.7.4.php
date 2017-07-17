<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->setConfigData('integernet_solr/results/show_outofstock', Mage::getStoreConfig('cataloginventory/options/show_out_of_stock'));
$installer->setConfigData('integernet_solr/autosuggest/show_outofstock', Mage::getStoreConfig('cataloginventory/options/show_out_of_stock'));
$installer->setConfigData('integernet_solr/category/show_outofstock', Mage::getStoreConfig('cataloginventory/options/show_out_of_stock'));

Mage::getModel('index/process')
    ->load('integernet_solr', 'indexer_code')
    ->setStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)
    ->save();

$installer->endSetup();