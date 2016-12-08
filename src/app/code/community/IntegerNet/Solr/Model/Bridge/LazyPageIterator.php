<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCms\Implementor\PageIterator;
use IntegerNet\SolrCms\Implementor\Page;

/**
 * Page iterator implementation with lazy loading of multiple collections (chunking).
 * Collections are prepared to be used by the indexer.
 */
class IntegerNet_Solr_Model_Bridge_LazyPageIterator implements PageIterator, OuterIterator
{
    protected $_bridgeFactory;
    /**
     * @var int
     */
    protected $_storeId;
    /**
     * @var null|int[]
     */
    protected $_pageIdFilter;
    /**
     * @var int
     */
    protected $_pageSize;
    /**
     * @var int
     */
    protected $_currentPage;
    /**
     * @var Mage_Cms_Model_Resource_Page_Collection
     */
    protected $_collection;
    /**
     * @var ArrayIterator
     */
    protected $_collectionIterator;

    /**
     * @var IntegerNet_Solr_Model_Resource_Db
     */
    protected $_dbResource;

    /**
     * @link http://php.net/manual/en/outeriterator.getinneriterator.php
     * @return Iterator The inner iterator for the current entry.
     */
    public function getInnerIterator()
    {
        return $this->_collectionIterator;
    }


    /**
     * @param int $_storeId store id for the collections
     * @param int[]|null $_pageIdFilter array of page ids to be loaded, or null for all page ids
     * @param int $_pageSize Number of pages per loaded collection (chunk)
     */
    public function __construct($_storeId, $_pageIdFilter, $_pageSize)
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
        $this->_storeId = $_storeId;
        $this->_pageIdFilter = $_pageIdFilter;
        $this->_pageSize = $_pageSize;
        $this->_dbResource = Mage::getResourceModel('integernet_solr/db');
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->getInnerIterator()->next();
    }

    /**
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->getInnerIterator()->key();
    }

    /**
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->getInnerIterator()->valid()) {
            return true;
        } elseif ($this->_currentPage < $this->_collection->getLastPageNumber()) {
            $this->_currentPage++;
            $this->_collection = self::getPageCollection($this->_storeId, $this->_pageIdFilter, $this->_pageSize, $this->_currentPage);
            $this->_dbResource->disconnectMysql();
            $this->_collectionIterator = $this->_collection->getIterator();
            $this->getInnerIterator()->rewind();
            return $this->getInnerIterator()->valid();
        }
        return false;
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_currentPage = 1;
        $this->_collection = self::getPageCollection($this->_storeId, $this->_pageIdFilter, $this->_pageSize, $this->_currentPage);
        $this->_dbResource->disconnectMysql();
        $this->_collectionIterator = $this->_collection->getIterator();
        $this->_collectionIterator->rewind();
    }

    /**
     * @return Page
     */
    public function current()
    {
        $page = $this->getInnerIterator()->current();
        $page->setStoreId($this->_storeId);
        return $this->_bridgeFactory->createPage($page);
    }

    /**
     * @param int $storeId
     * @param int[]|null $pageIds
     * @param int $pageSize
     * @param int $pageNumber
     * @return Mage_Cms_Model_Resource_Page_Collection
     */
    private static function getPageCollection($storeId, $pageIds = null, $pageSize = null, $pageNumber = 0)
    {
        /** @var $pageCollection Mage_Cms_Model_Resource_Page_Collection */
        $pageCollection = Mage::getResourceModel('cms/page_collection')
            ->addStoreFilter($storeId);

        if (is_array($pageIds)) {
            $pageCollection->addFieldToFilter('page_id', array('in' => $pageIds));
        }

        if (!is_null($pageSize)) {
            $pageCollection->setPageSize($pageSize);
            $pageCollection->setCurPage($pageNumber);
        }

        Mage::dispatchEvent('integernet_solr_page_collection_load_before', array(
            'collection' => $pageCollection
        ));

        $event = new Varien_Event();
        $event->setCollection($pageCollection);
        $observer = new Varien_Event_Observer();
        $observer->setEvent($event);

        $pageCollection->load();

        Mage::dispatchEvent('integernet_solr_page_collection_load_after', array(
            'collection' => $pageCollection
        ));

        return $pageCollection;
    }
}
