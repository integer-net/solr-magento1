<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Implementor\IndexCategoryRepository;
use IntegerNet\SolrSuggest\Implementor\SuggestCategoryRepository;
use IntegerNet\SolrCategories\Implementor\CategoryRepository;
use IntegerNet\SolrCategories\Implementor\CategoryIterator;

class IntegerNet_Solr_Model_Bridge_IndexCategoryRepository implements IndexCategoryRepository
{
    protected $_pathCategoryIds = array();
    protected $_excludedCategoryIds = array();

    protected $_categoryNames = array();
    /**
     * @var IntegerNet_Solr_Model_Bridge_Factory
     */
    protected $_bridgeFactory;

    public function __construct()
    {
        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
    }

    /**
     * @var int
     */
    protected $_pageSize;

    /**
     * @param $categoryIds
     * @param $storeId
     * @return array
     */
    public function getCategoryNames($categoryIds, $storeId)
    {
        $categoryNames = array();

        /** @var Mage_Catalog_Model_Resource_Category $categoryResource */
        $categoryResource = Mage::getResourceModel('catalog/category');
        foreach($categoryIds as $key => $categoryId) {
            if (!isset($this->_categoryNames[$storeId][$categoryId])) {
                $this->_categoryNames[$storeId][$categoryId] = $categoryResource->getAttributeRawValue($categoryId, 'name', $storeId);
            }
            $categoryNames[] = $this->_categoryNames[$storeId][$categoryId];
        }
        return $categoryNames;
    }

    /**
     * Get category ids of assigned categories and all parents
     *
     * @param Product $product
     * @return int[]
     */
    public function getCategoryIds($product)
    {
        $categoryIds = $product->getCategoryIds();

        if (!sizeof($categoryIds)) {
            return array();
        }

        $storeId = $product->getStoreId();
        if (!isset($this->_pathCategoryIds[$storeId])) {
            $this->_pathCategoryIds[$storeId] = array();
        }
        $lookupCategoryIds = array_diff($categoryIds, array_keys($this->_pathCategoryIds[$storeId]));
        $this->_lookupCategoryIdPaths($lookupCategoryIds, $storeId);

        $foundCategoryIds = array();
        foreach($categoryIds as $categoryId) {
            if (isset($this->_pathCategoryIds[$storeId][$categoryId])) {
                $categoryPathIds = $this->_pathCategoryIds[$storeId][$categoryId];
                $foundCategoryIds = array_merge($foundCategoryIds, $categoryPathIds);
            }
        }

        $foundCategoryIds = array_unique($foundCategoryIds);

        $foundCategoryIds = array_diff($foundCategoryIds, $this->_getExcludedCategoryIds($storeId));

        return $foundCategoryIds;
    }

    /**
     * Lookup and store all parent category ids and its own id of given category ids
     *
     * @param int[] $categoryIds
     * @param int $storeId
     */
    protected function _lookupCategoryIdPaths($categoryIds, $storeId)
    {
        if (!sizeof($categoryIds)) {
            return;
        }

        /** @var $categories Mage_Catalog_Model_Resource_Category_Collection */
        $categories = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToFilter('entity_id', array('in' => $categoryIds))
            ->addAttributeToSelect(array('is_active', 'include_in_menu'));

        foreach ($categories as $category) {
            /** @var Mage_Catalog_Model_Category $category */
            if (!$category->getIsActive() || !$category->getIncludeInMenu()) {
                $this->_pathCategoryIds[$storeId][$category->getId()] = array();
                continue;
            }

            $categoryPathIds = explode('/', $category->getPath());
            if (!in_array(Mage::app()->getStore($storeId)->getGroup()->getRootCategoryId(), $categoryPathIds)) {
                $this->_pathCategoryIds[$storeId][$category->getId()] = array();
                continue;
            }

            array_shift($categoryPathIds);
            array_shift($categoryPathIds);
            $this->_pathCategoryIds[$storeId][$category->getId()] = $categoryPathIds;
        }
    }


    /**
     * @param int $storeId
     * @return array
     */
    protected function _getExcludedCategoryIds($storeId)
    {
        if (!isset($this->_excludedCategoryIds[$storeId])) {

            // exclude categories which are configured as excluded
            /** @var $excludedCategories Mage_Catalog_Model_Resource_Category_Collection */
            $excludedCategories = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('solr_exclude', 1);

            $this->_excludedCategoryIds[$storeId] = $excludedCategories->getAllIds();

            // exclude children of categories which are configured as "children excluded"
            /** @var $categoriesWithChildrenExcluded Mage_Catalog_Model_Resource_Category_Collection */
            $categoriesWithChildrenExcluded = Mage::getResourceModel('catalog/category_collection')
                ->setStoreId($storeId)
                ->addFieldToFilter('solr_exclude_children', 1);
            $excludePaths = $categoriesWithChildrenExcluded->getColumnValues('path');

            /** @var $excludedChildrenCategories Mage_Catalog_Model_Resource_Category_Collection */
            $excludedChildrenCategories = Mage::getResourceModel('catalog/category_collection')
                ->setStoreId($storeId);

            $excludePathConditions = array();
            foreach($excludePaths as $excludePath) {
                $excludePathConditions[] = array('like' => $excludePath . '/%');
            }
            if (sizeof($excludePathConditions)) {
                $excludedChildrenCategories->addAttributeToFilter('path', $excludePathConditions);
                $this->_excludedCategoryIds[$storeId] = array_merge($this->_excludedCategoryIds[$storeId], $excludedChildrenCategories->getAllIds());
            }
        }

        return $this->_excludedCategoryIds[$storeId];
    }

    /**
     * Retrieve product category identifiers
     *
     * @param Product $product
     * @return array
     */
    public function getCategoryPositions($product)
    {
        /** @var $setup Mage_Catalog_Model_Resource_Setup */
        $setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
        $adapter = Mage::getSingleton('core/resource')->getConnection('catalog_read');

        $select = $adapter->select()
            ->from($setup->getTable('catalog/category_product_index'), array('category_id', 'position'))
            ->where('product_id = ?', (int)$product->getId())
            ->where('store_id = ?', $product->getStoreId());

        return $adapter->fetchAll($select);
    }
}