<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCms\Implementor\Page;
use IntegerNet\SolrCms\Implementor\PageIterator;

class IntegerNet_Solr_Model_Bridge_PageIterator extends IteratorIterator implements PageIterator
{
    protected $_bridgeFactory;

    /**
     * @param Mage_Cms_Model_Resource_Page_Collection $_collection
     */
    public function __construct(Mage_Cms_Model_Resource_Page_Collection $_collection)
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
        parent::__construct($_collection->getIterator());
    }

    /**
     * @return Page
     */
    public function current()
    {
        return $this->_bridgeFactory->createPage($this->getInnerIterator()->current());
    }

}