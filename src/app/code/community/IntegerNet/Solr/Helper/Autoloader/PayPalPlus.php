<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

/**
 * This rewrite fixes the Iways_PayPalPlus autoloader to work nicely together with other third party autoloaders,
 * i.e. without warnings.
 */
class IntegerNet_Solr_Helper_Autoloader_PayPalPlus extends Iways_PayPalPlus_Model_Autoloader
{
    /**
     * Autoload
     *
     * @param string $class
     */
    public function autoload($class)
    {
        $classFile = str_replace('\\', DS, $class) . '.php';
        // Actually check if file exists in include path, do nothing otherwise
        if (stream_resolve_include_path($classFile) !== false) {
            include $classFile;
        }
    }
}