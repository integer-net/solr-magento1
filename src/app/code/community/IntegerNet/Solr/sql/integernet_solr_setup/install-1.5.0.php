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

$installer->addAttribute('catalog_category', 'solr_exclude', array(
    'type'              => 'int',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'label'             => Mage::helper('integernet_solr')->__('Exclude this Category from Solr Index'),
    'note'              => Mage::helper('integernet_solr')->__('Exclude only Categories, not included Products'),
    'required'          => 0,
    'user_defined'      => 0,
    'group'             => 'Solr',
    'global'            => 0,
    'visible'           => 1,
));

$installer->addAttribute('catalog_category', 'solr_exclude_children', array(
    'type'              => 'int',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'label'             => Mage::helper('integernet_solr')->__('Exclude Child Categories from Solr Index'),
    'note'              => Mage::helper('integernet_solr')->__('Exclude only Categories, not included Products'),
    'required'          => 0,
    'user_defined'      => 0,
    'group'             => 'Solr',
    'global'            => 0,
    'visible'           => 1,
));

$installer->addAttribute('catalog_category', 'solr_remove_filters', array(
    'type'              => 'text',
    'input'             => 'multiselect',
    'source'            => 'integernet_solr/eav_source_filterableAttribute',
    'backend'           => 'integernet_solr/eav_backend_filterableAttribute',
    'label'             => Mage::helper('integernet_solr')->__('Remove Filters'),
    'note'              => Mage::helper('integernet_solr')->__('Hold the CTRL key to select multiple filters'),
    'required'          => 0,
    'user_defined'      => 0,
    'group'             => 'Solr',
    'global'            => 0,
    'visible'           => 1,
));

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

$installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'), 'solr_boost', 'float( 12,4 ) UNSIGNED NOT NULL DEFAULT 1');

$installer->setConfigData('integernet_solr/general/install_date', time());

$installer->endSetup();