<?php
use IntegerNet\Solr\Config\AutosuggestConfig;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Source_CategoryLinkType
{
    public function __construct()
    {
        IntegerNet_Solr_Helper_Autoloader::createAndRegister();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => AutosuggestConfig::CATEGORY_LINK_TYPE_FILTER,
                'label' => Mage::helper('integernet_solr')->__('Search result page with set category filter')
            ),
            array(
                'value' => AutosuggestConfig::CATEGORY_LINK_TYPE_DIRECT,
                'label' => Mage::helper('integernet_solr')->__('Category page')
            ),
        );
    }
}