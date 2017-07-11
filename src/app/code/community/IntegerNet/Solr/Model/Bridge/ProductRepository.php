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
use IntegerNet\Solr\Implementor\ProductRepository;
use IntegerNet\Solr\Implementor\ProductIterator;
use IntegerNet\Solr\Indexer\Data\ProductIdChunk;
use IntegerNet\Solr\Indexer\Data\ProductIdChunks;

class IntegerNet_Solr_Model_Bridge_ProductRepository implements ProductRepository
{
    protected $_bridgeFactory;

    public function __construct()
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
    }

    /**
     * Return product iterator which may implement lazy loading but must ensure that given chunks are loaded together
     *
     * @param int $storeId
     * @param ProductIdChunks $chunks
     * @return PagedProductIterator
     */
    public function getProductsInChunks($storeId, ProductIdChunks $chunks)
    {
        Mage::app()->getStore($storeId)->setConfig('catalog/frontend/flat_catalog_product', 0);
        return $this->_bridgeFactory->createLazyProductIterator($storeId, $chunks);
    }

    /**
     * @param null|int[] $productIds
     * @return \IntegerNet\Solr\Indexer\Data\ProductAssociation[] An array with parent_id as key and association metadata as value
     */
    public function getProductAssociations($productIds)
    {
        /** @var $configurableResourceTypeModel IntegerNet_Solr_Model_Resource_Catalog_Product_Type_Configurable */
        $configurableResourceTypeModel = Mage::getResourceModel('integernet_solr/catalog_product_type_configurable');
        $associations = $configurableResourceTypeModel->getChildrenIdsForMultipleParents($productIds);

        /** @var $groupedResourceTypeModel IntegerNet_Solr_Model_Resource_Catalog_Product_Type_Grouped */
        $groupedResourceTypeModel = Mage::getResourceModel('integernet_solr/catalog_product_type_grouped');
        // Don't use array_merge here due to performance reasons
        foreach ($groupedResourceTypeModel->getChildrenIdsForMultipleParents($productIds) as $parentId => $childrenIds) {
            $associations[$parentId] = $childrenIds;
        }
        
        /** @var $bundleResourceTypeModel IntegerNet_Solr_Model_Resource_Catalog_Product_Type_Bundle */
        $bundleResourceTypeModel = Mage::getResourceModel('integernet_solr/catalog_product_type_bundle');
        // Don't use array_merge here due to performance reasons
        foreach ($bundleResourceTypeModel->getChildrenIdsForMultipleParents($productIds) as $parentId => $childrenIds) {
            $associations[$parentId] = $childrenIds;
        }
        return array_combine(array_keys($associations), array_map(
            function($parentId, $childrenIds) {
                return new \IntegerNet\Solr\Indexer\Data\ProductAssociation($parentId, $childrenIds);
            }, array_keys($associations), $associations));
    }

    /**
     * @param null|int $sliceId
     * @param null|int $totalNumberSlices
     * @return int[]
     */
    public function getAllProductIds($sliceId = null, $totalNumberSlices = null)
    {
        // Fixes a bug with flat product collections called for different stores, see https://magento.stackexchange.com/q/30956/2207
        Mage::unregister('_resource_singleton/catalog/product_flat');

        /** @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */
        $productCollection = Mage::getResourceModel('catalog/product_collection');

        if ((!is_null($sliceId)) && (!is_null($totalNumberSlices))) {
            if ($sliceId == $totalNumberSlices) {
                $sliceId = 0;
            }
            $productCollection->getSelect()->where('e.entity_id % ' . intval($totalNumberSlices) . ' = ' . intval($sliceId));
        }

        return $productCollection->getAllIds();
    }

}