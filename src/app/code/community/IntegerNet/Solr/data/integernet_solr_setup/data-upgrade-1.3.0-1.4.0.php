<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

Mage::getModel('index/process')
    ->load('integernet_solr', 'indexer_code')
    ->setStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)
    ->save();

$installer->endSetup();