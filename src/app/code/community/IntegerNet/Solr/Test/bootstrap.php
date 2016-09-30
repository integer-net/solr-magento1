<?php
/**
 * integer_net Magento Module
 *
 * This file can be included with the environment variable ECOMDEV_PHPUNIT_CUSTOM_BOOTSTRAP if
 * autoloader is not instantiated early in tests.
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
include 'IntegerNet/Solr/Helper/Autoloader.php';
IntegerNet_Solr_Helper_Autoloader::createAndRegister();