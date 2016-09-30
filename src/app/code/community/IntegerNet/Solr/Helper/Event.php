<?php
use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\AttributeRepository;
use IntegerNet\Solr\Implementor\EventDispatcher;
use IntegerNet\Solr\Implementor\HasUserQuery;
use IntegerNet\SolrSuggest\Implementor\SearchUrl;
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class IntegerNet_Solr_Helper_Event implements EventDispatcher
{
    /**
     * Dispatch event
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function dispatch($eventName, array $data = array())
    {
        Mage::dispatchEvent($eventName, $data);
    }
}