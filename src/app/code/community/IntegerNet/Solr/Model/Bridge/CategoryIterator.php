<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCategories\Implementor\Category;
use IntegerNet\SolrCategories\Implementor\CategoryIterator;

class IntegerNet_Solr_Model_Bridge_CategoryIterator extends IteratorIterator implements CategoryIterator
{
    protected $_bridgeFactory;

    /**
     * @param Mage_Catalog_Model_Resource_Category_Collection $_collection
     */
    public function __construct(Mage_Catalog_Model_Resource_Category_Collection $_collection)
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
        parent::__construct($_collection->getIterator());
    }

    /**
     * @return Category
     */
    public function current()
    {
        return $this->_bridgeFactory->createCategory($this->getInnerIterator()->current());
    }

}