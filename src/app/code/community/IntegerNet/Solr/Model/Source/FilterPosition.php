<?php
use IntegerNet\Solr\Config\CategoryConfig;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Source_FilterPosition
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
                'value' => CategoryConfig::FILTER_POSITION_LEFT,
                'label' => Mage::helper('integernet_solr')->__('Left column (Magento default)')
            ),
            array(
                'value' => CategoryConfig::FILTER_POSITION_TOP,
                'label' => Mage::helper('integernet_solr')->__('Content column (above products)')
            ),
        );
    }
}