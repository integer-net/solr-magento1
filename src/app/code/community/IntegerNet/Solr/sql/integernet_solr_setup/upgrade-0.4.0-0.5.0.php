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

$installer->addAttribute('catalog_product', 'solr_exclude', array(
    'type'              => 'int',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'label'             => Mage::helper('integernet_solr')->__('Exclude this Product from Solr Index'),
    'required'          => 0,
    'user_defined'      => 0,
    'group'             => 'Solr',
    'global'            => 0,
    'visible'           => 1,
    'default'           => 0,
    'unique'            => 0,
));


$installer->endSetup();
