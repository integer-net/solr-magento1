<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Milan Hacker
 */
use IntegerNet\Solr\Query\SearchString;

class IntegerNet_Solr_Helper_Query extends Mage_Core_Helper_Abstract {

    /**
     * Quote and escape search strings
     *
     * @param string $string String to escape
     * @return string The escaped/quoted string
     * @deprecated use SearchString value object instead
     */
    public function escape ($string)
    {
        return SearchString::escape($string);
    }
}