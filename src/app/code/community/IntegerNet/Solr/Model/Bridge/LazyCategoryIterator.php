<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCategories\Implementor\CategoryIterator;
use IntegerNet\SolrCategories\Implementor\Category;

/**
 * Category iterator implementation with lazy loading of multiple collections (chunking).
 * Collections are prepared to be used by the indexer.
 */
class IntegerNet_Solr_Model_Bridge_LazyCategoryIterator implements CategoryIterator, OuterIterator
{
    protected $_bridgeFactory;
    /**
     * @var int
     */
    protected $_storeId;
    /**
     * @var null|int[]
     */
    protected $_categoryIdFilter;
    /**
     * @var int
     */
    protected $_categorySize;
    /**
     * @var int
     */
    protected $_currentCategory;
    /**
     * @var Mage_Catalog_Model_Resource_Category_Collection
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
     * @param int[]|null $_categoryIdFilter array of category ids to be loaded, or null for all category ids
     * @param int $_categorySize Number of categorys per loaded collection (chunk)
     */
    public function __construct($_storeId, $_categoryIdFilter, $_categorySize)
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
        $this->_storeId = $_storeId;
        $this->_categoryIdFilter = $_categoryIdFilter;
        $this->_categorySize = $_categorySize;
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
        } elseif ($this->_currentCategory < $this->_collection->getLastPageNumber()) {
            $this->_currentCategory++;
            $this->_collection = self::getCategoryCollection($this->_storeId, $this->_categoryIdFilter, $this->_categorySize, $this->_currentCategory);
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
        $this->_currentCategory = 1;
        $this->_collection = self::getCategoryCollection($this->_storeId, $this->_categoryIdFilter, $this->_categorySize, $this->_currentCategory);
        $this->_dbResource->disconnectMysql();
        $this->_collectionIterator = $this->_collection->getIterator();
        $this->_collectionIterator->rewind();
    }

    /**
     * @return Category
     */
    public function current()
    {
        $category = $this->getInnerIterator()->current();
        $category->setStoreId($this->_storeId);
        return $this->_bridgeFactory->createCategory($category);
    }

    /**
     * @param int $storeId
     * @param int[]|null $categoryIds
     * @param int $pageSize
     * @param int $pageNumber
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    private static function getCategoryCollection($storeId, $categoryIds = null, $pageSize = null, $pageNumber = 0)
    {
        /** @var $categoryCollection Mage_Catalog_Model_Resource_Category_Collection */
        $categoryCollection = Mage::getResourceModel('catalog/category_collection')
            ->setStoreId($storeId)
            ->addAttributeToSelect('*');

        if (is_array($categoryIds)) {
            $categoryCollection->addAttributeToFilter('entity_id', array('in' => $categoryIds));
        }
        $baseCategoryId = Mage::app()->getStore($storeId)->getGroup()->getRootCategoryId();
        $categoryCollection->addAttributeToFilter('path', array('like' => '1/' . $baseCategoryId . '/%'));

        if (!is_null($pageSize)) {
            $categoryCollection->setPageSize($pageSize);
            $categoryCollection->setCurPage($pageNumber);
        }

        Mage::dispatchEvent('integernet_solr_category_collection_load_before', array(
            'collection' => $categoryCollection
        ));

        $event = new Varien_Event();
        $event->setCollection($categoryCollection);
        $observer = new Varien_Event_Observer();
        $observer->setEvent($event);

        $categoryCollection->load();

        Mage::dispatchEvent('integernet_solr_category_collection_load_after', array(
            'collection' => $categoryCollection
        ));

        return $categoryCollection;
    }
}
