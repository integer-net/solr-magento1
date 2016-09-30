<?php
use IntegerNet\Solr\Implementor\Pagination;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class IntegerNet_Solr_Model_Bridge_Pagination_Toolbar implements Pagination
{
    /**
     * @var Varien_Object|Mage_Catalog_Block_Product_List_Toolbar
     */
    protected $_toolbarBlock;

    /**
     * @param Varien_Object|Mage_Catalog_Block_Product_List_Toolbar $toolbarBlock
     */
    public function __construct(Varien_Object $toolbarBlock)
    {
        $this->_toolbarBlock = $toolbarBlock;
    }
    /**
     * Returns page size
     *
     * @return int
     */
    public function getPageSize()
    {
        $limit = $this->_toolbarBlock->getLimit();
        if ($limit == 'all') {
            return 10000;
        }
        return $limit;
    }

    /**
     * Returns current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_toolbarBlock->getCurrentPage();
    }

    /**
     * Returns sort order
     *
     * @return string {'asc', 'desc'}
     */
    public function getCurrentDirection()
    {
        return $this->_toolbarBlock->getCurrentDirection();
    }

    /**
     * Returns sort criterion (attribute)
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        return $this->_toolbarBlock->getCurrentOrder();
    }

}