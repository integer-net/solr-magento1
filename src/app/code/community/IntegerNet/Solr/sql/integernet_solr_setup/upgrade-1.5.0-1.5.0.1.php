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

$autoloader = new IntegerNet_Solr_Helper_Autoloader();
$autoloader->createAndRegister();

$installer->addAttribute('catalog_category', 'filter_position', array(
    'type'              => 'int',
    'input'             => 'select',
    'source'            => 'integernet_solr/eav_source_filterPosition',
    'label'             => 'Position of Filters',
    'required'          => 0,
    'user_defined'      => 0,
    'group'             => 'Solr',
    'global'            => 0,
    'visible'           => 1,
));



$installer->endSetup();