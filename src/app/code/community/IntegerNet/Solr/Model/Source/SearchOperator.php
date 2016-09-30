<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Source_SearchOperator
{
    const SEARCH_OPERATOR_AND = 'AND';
    const SEARCH_OPERATOR_OR = 'OR';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::SEARCH_OPERATOR_AND,
                'label' => Mage::helper('integernet_solr')->__('AND')
            ),
            array(
                'value' => self::SEARCH_OPERATOR_OR,
                'label' => Mage::helper('integernet_solr')->__('OR')
            ),
        );
    }
}