<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Implementor\ProductIterator;

class IntegerNet_Solr_Model_Bridge_ProductIterator extends IteratorIterator implements ProductIterator
{
    protected $_bridgeFactory;

    /**
     * @param Varien_Data_Collection $_collection
     */
    public function __construct(Varien_Data_Collection $_collection)
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
        parent::__construct($_collection->getIterator());
    }

    /**
     * @return Product
     */
    public function current()
    {
        return $this->_bridgeFactory->createProduct($this->getInnerIterator()->current());
    }

}