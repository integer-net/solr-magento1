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

$installer->addAttribute('catalog_product', 'solr_boost', array(
    'type' => 'decimal',
    'input' => 'text',
    'label' => Mage::helper('integernet_solr')->__('Solr Priority'),
    'frontend_class' => 'validate-number',
    'required' => 0,
    'user_defined' => 1,
    'default' => 1,
    'unique' => 0,
    'note' => Mage::helper('integernet_solr')->__('1 is default, use higher numbers for higher priority.'),
    'group' => 'Solr',
    'global' => 0,
    'visible' => 1,
));

$installer->endSetup();