<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Sandro Wagner <sw@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Source_LoaderStyle
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'solr-system',
                'label' => 'Solr-System',
            ),
            array(
                'value' => 'modern',
                'label' => 'Modern/RWD',
            ),
        );
    }
}