<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Result_Layer_Renderers extends Mage_Core_Block_Abstract
{
    /**
     * Dummy method to force old (pre-rwd) behavior of filters
     *
     * @return array
     */
    public function getSortedChildren()
    {
        return array();
    }
}