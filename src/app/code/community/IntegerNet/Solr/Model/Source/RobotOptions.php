<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Source_RobotOptions
{
    const ROBOT_OPTION_SEARCH_RESULTS_ALL = 'search_results_all';
    const ROBOT_OPTION_SEARCH_RESULTS_FILTERED = 'search_results_filtered';
    const ROBOT_OPTION_CATEGORIES_FILTERED = 'categories_filtered';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '',
                'label' => ''
            ),
            array(
                'value' => self::ROBOT_OPTION_SEARCH_RESULTS_ALL,
                'label' => Mage::helper('integernet_solr')->__('Search Result Page (always)'),
            ),
            array(
                'value' => self::ROBOT_OPTION_SEARCH_RESULTS_FILTERED,
                'label' => Mage::helper('integernet_solr')->__('Search Result Page with active Filters'),
            ),
            array(
                'value' => self::ROBOT_OPTION_CATEGORIES_FILTERED,
                'label' => Mage::helper('integernet_solr')->__('Category Page with active Filters'),
            ),
        );
    }
}