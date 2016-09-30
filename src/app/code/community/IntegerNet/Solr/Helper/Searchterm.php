<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

use IntegerNet\Solr\Implementor\HasUserQuery;

class IntegerNet_Solr_Helper_Searchterm implements HasUserQuery
{
    /**
     * Returns query as entered by user
     *
     * @return string
     */
    public function getUserQueryText()
    {
        return Mage::helper('catalogsearch')->getQuery()->getQueryText();
    }

}