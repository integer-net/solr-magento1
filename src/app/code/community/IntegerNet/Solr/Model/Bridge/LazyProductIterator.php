<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\PagedProductIterator;
use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Indexer\Data\ProductIdChunks;

/**
 * Product iterator implementation with lazy loading of multiple collections (chunking).
 * Collections are prepared to be used by the indexer.
 */
class IntegerNet_Solr_Model_Bridge_LazyProductIterator implements PagedProductIterator, OuterIterator
{
    protected $_bridgeFactory;
    /**
     * @var ProductIdChunks
     */
    protected $_productIdChunks;
    /**
     * @var int
     */
    protected $_storeId;
    /**
     * @var int
     */
    protected $_currentChunkId;
    /**
     * @var callable
     */
    protected $_pageCallback;
    /**
     * @var Mage_Catalog_Model_Resource_Product_Collection
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
     * @param ProductIdChunks $productIdChunks parent and children product ids to be loaded     */
    public function __construct($_storeId, ProductIdChunks $productIdChunks)
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
        $this->_storeId = $_storeId;
        $this->_productIdChunks = $productIdChunks;
        $this->_dbResource = Mage::getResourceModel('integernet_solr/db');
    }

    /**
     * Define a callback that is called after each "page" iteration (i.e. finished inner iterator)
     *
     * @param callable $callback
     */
    public function setPageCallback($callback)
    {
        $this->_pageCallback = $callback;
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
        if ($this->validInner()) {
            return true;
        } elseif ($this->_currentChunkId < sizeof($this->_productIdChunks) - 1) {
            $this->_currentChunkId++;
            $this->_collection = self::getProductCollection($this->_storeId, $this->_productIdChunks, $this->_currentChunkId);
            $this->_dbResource->disconnectMysql();
            $this->_collectionIterator = $this->_collection->getIterator();
            $this->getInnerIterator()->rewind();
            return $this->validInner();
        }
        return false;
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_currentChunkId = 0;
        $this->_collection = self::getProductCollection($this->_storeId, $this->_productIdChunks, $this->_currentChunkId);
        $this->_dbResource->disconnectMysql();
        $this->_collectionIterator = $this->_collection->getIterator();
        $this->_collectionIterator->rewind();
    }

    /**
     * @return Product
     */
    public function current()
    {
        $product = $this->getInnerIterator()->current();
        $product->setStoreId($this->_storeId);
        return $this->_bridgeFactory->createProduct($product);
    }

    /**
     * @param int $storeId
     * @param ProductIdChunks $productIdChunks
     * @param int $chunkId
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private static function getProductCollection($storeId, $productIdChunks, $chunkId = 0)
    {
        $productAttributes = array_unique(array_merge(
            Mage::getSingleton('catalog/config')->getProductAttributes(),
            array('visibility', 'status', 'url_key', 'solr_boost', 'solr_exclude'),
            Mage::getModel('integernet_solr/bridge_factory')->getAttributeRepository()->getAttributeCodesToIndex()
        ));
        
        /** @var $productCollection IntegerNet_Solr_Model_Resource_Catalog_Product_Indexing_Collection */
        $productCollection = Mage::getResourceModel('integernet_solr/catalog_product_indexing_collection')
            ->setStoreId($storeId)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->addAttributeToSelect($productAttributes);
        
        $productIdChunk = $productIdChunks[$chunkId];
        $productIds = $productIdChunk->getAllIds();
        $productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));

        Mage::dispatchEvent('integernet_solr_product_collection_load_before', array(
            'collection' => $productCollection
        ));

        $event = new Varien_Event();
        $event->setCollection($productCollection);
        $observer = new Varien_Event_Observer();
        $observer->setEvent($event);

        Mage::getModel('tax/observer')->addTaxPercentToProductCollection($observer);

        $productCollection->load();

        Mage::dispatchEvent('integernet_solr_product_collection_load_after', array(
            'collection' => $productCollection
        ));

        return $productCollection;
    }

    /**
     * @return bool
     */
    private function validInner()
    {
        $valid = $this->getInnerIterator()->valid();
        if (! $valid) {
            $this->_dbResource->disconnectMysql();
            call_user_func($this->_pageCallback, $this);
        }
        return $valid;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getDataSource()
    {
        return $this->_collection;
    }

    /**
     * @return \IntegerNet\Solr\Indexer\Data\ProductIdChunk
     */
    public function currentChunk()
    {
        return $this->_productIdChunks[$this->_currentChunkId];
    }

    /**
     * Returns an iterator for a subset of products. The ids must be part of the current chunk, otherwise an
     * OutOfBoundsException will be thrown
     *
     * @param int[] $ids
     * @return \IntegerNet\Solr\Implementor\ProductIterator
     * @throws \OutOfBoundsException
     */
    public function subset($ids)
    {
        $dataCollection = new Varien_Data_Collection();
        foreach($ids as $productId) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $this->getDataSource()->getItemById($productId);
            if (is_null($product)) {
                throw new OutOfBoundsException('Associated product with ID ' . $productId . ' was not loaded in current chunk');
            }
            if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                $dataCollection->addItem($product);
            }
        }

        return $this->_bridgeFactory->createProductIterator($dataCollection);
    }

}
