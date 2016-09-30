<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Source_AutosuggestMethod
{
    const AUTOSUGGEST_METHOD_MAGENTO_CONTROLLER = 0;
    const AUTOSUGGEST_METHOD_PHP = 1;
    const AUTOSUGGEST_METHOD_MAGENTO_DIRECT = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::AUTOSUGGEST_METHOD_MAGENTO_CONTROLLER,
                'label' => Mage::helper('integernet_solr')->__('Magento Controller')
            ),
            array(
                'value' => self::AUTOSUGGEST_METHOD_MAGENTO_DIRECT,
                'label' => Mage::helper('integernet_solr')->__('Magento with separate PHP file')
            ),
            array(
                'value' => self::AUTOSUGGEST_METHOD_PHP,
                'label' => Mage::helper('integernet_solr')->__('PHP without Magento instantiation')
            ),
        );
    }
}