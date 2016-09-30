<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Indexer\IndexField;

/**
 * @deprecated
 */
class IntegerNet_Solr_Helper_Attribute
{
    protected $_bridgeFactory;

    public function __construct()
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
    }

    /**
     * @deprecated use IndexField directly
     * @param Attribute $attribute
     * @param bool $forSorting
     * @return string
     */
    public function getFieldName($attribute, $forSorting = false)
    {
        if (! $attribute instanceof Attribute) {
            $attribute = $this->_bridgeFactory->createAttribute($attribute);
        }
        $indexField = new IndexField($attribute, $this->_getEventDispatcher(), $forSorting);
        return $indexField->getFieldName();
    }

    /**
     * @return IntegerNet_Solr_Helper_Event
     */
    protected function _getEventDispatcher()
    {
        return Mage::helper('integernet_solr/event');
    }
}