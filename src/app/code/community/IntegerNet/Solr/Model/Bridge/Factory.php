<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Indexer\Data\ProductIdChunk;

/**
 * Classes that implement interfaces from the library are instantiated here to ensure
 * that our autoloader is initialized. Also this factory allows to rewrite bridge classes that are
 * not instantiated with Mage::getModel().
 */
class IntegerNet_Solr_Model_Bridge_Factory
{
    public function __construct()
    {
        IntegerNet_Solr_Helper_Autoloader::createAndRegister();
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_AttributeRepository
     */
    public function createAttributeRepository()
    {
        return Mage::getModel('integernet_solr/bridge_attributeRepository');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_AttributeRepository
     */
    public function getAttributeRepository()
    {
        return Mage::getSingleton('integernet_solr/bridge_attributeRepository');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_ProductRepository
     */
    public function createProductRepository()
    {
        return Mage::getModel('integernet_solr/bridge_productRepository');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_ProductRenderer
     */
    public function createProductRenderer()
    {
        return Mage::getModel('integernet_solr/bridge_productRenderer');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_CategoryRepository
     */
    public function createCategoryRepository()
    {
        return Mage::getModel('integernet_solr/bridge_categoryRepository');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_IndexCategoryRepository
     */
    public function getIndexCategoryRepository()
    {
        return Mage::getSingleton('integernet_solr/bridge_indexCategoryRepository');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_CategoryRepository
     */
    public function getCategoryRepository()
    {
        return Mage::getSingleton('integernet_solr/bridge_categoryRepository');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_CategoryRenderer
     */
    public function createCategoryRenderer()
    {
        return Mage::getModel('integernet_solr/bridge_categoryRenderer');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_PageRepository
     */
    public function createPageRepository()
    {
        return Mage::getModel('integernet_solr/bridge_pageRepository');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_PageRenderer
     */
    public function createPageRenderer()
    {
        return Mage::getModel('integernet_solr/bridge_pageRenderer');
    }

    /**
     * @return IntegerNet_Solr_Model_Bridge_StoreEmulation
     */
    public function createStoreEmulation()
    {
        return Mage::getModel('integernet_solr/bridge_storeEmulation');
    }

    /**
     * @param Varien_Object|Mage_Catalog_Block_Product_List_Toolbar $block
     * @return IntegerNet_Solr_Model_Bridge_Pagination_Toolbar
     */
    public function createPaginationToolbar($block)
    {
        return Mage::getModel('integernet_solr/bridge_pagination_toolbar', $block);
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return IntegerNet_Solr_Model_Bridge_Attribute
     */
    public function createAttribute(Mage_Catalog_Model_Resource_Eav_Attribute $attribute)
    {
        return new IntegerNet_Solr_Model_Bridge_Attribute($attribute);
    }

    /**
     * @param Mage_Eav_Model_Entity_Attribute_Source_Interface $source
     * @return IntegerNet_Solr_Model_Bridge_Source
     */
    public function createAttributeSource(Mage_Eav_Model_Entity_Attribute_Source_Interface $source)
    {
        return new IntegerNet_Solr_Model_Bridge_Source($source);
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param string[]|null $categoryPathNames
     * @return IntegerNet_Solr_Model_Bridge_Category
     */
    public function createCategory(Mage_Catalog_Model_Category $category, array $categoryPathNames = null)
    {
        return new IntegerNet_Solr_Model_Bridge_Category($category, $categoryPathNames);
    }

    /**
     * @param int $storeId store id for the collections
     * @param int[]|null $categoryIds array of category ids to be loaded, or null for all category ids
     * @param int $pageSize Number of categorys per loaded collection (chunk)
     * @return IntegerNet_Solr_Model_Bridge_LazyCategoryIterator
     */
    public function createLazyCategoryIterator($storeId, $categoryIds, $pageSize)
    {
        return new IntegerNet_Solr_Model_Bridge_LazyCategoryIterator($storeId, $categoryIds, $pageSize);
    }

    /**
     * @param Mage_Cms_Model_Page $page
     * @return IntegerNet_Solr_Model_Bridge_Page
     */
    public function createPage(Mage_Cms_Model_Page $page)
    {
        return new IntegerNet_Solr_Model_Bridge_Page($page);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return IntegerNet_Solr_Model_Bridge_Product
     */
    public function createProduct(Mage_Catalog_Model_Product $product)
    {
        return new IntegerNet_Solr_Model_Bridge_Product($product);
    }

    /**
     * @param int $storeId store id for the collections
     * @param int[]|null $pageIds array of page ids to be loaded, or null for all page ids
     * @param int $pageSize Number of pages per loaded collection (chunk)
     * @return IntegerNet_Solr_Model_Bridge_LazyPageIterator
     */
    public function createLazyPageIterator($storeId, $pageIds, $pageSize)
    {
        return new IntegerNet_Solr_Model_Bridge_LazyPageIterator($storeId, $pageIds, $pageSize);
    }

    /**
     * @param Varien_Data_Collection $childProductCollection
     * @return IntegerNet_Solr_Model_Bridge_ProductIterator
     */
    public function createProductIterator(Varien_Data_Collection $childProductCollection)
    {
        return new IntegerNet_Solr_Model_Bridge_ProductIterator($childProductCollection);
    }

    /**
     * @param int $storeId store id for the collections
     * @param \IntegerNet\Solr\Indexer\Data\ProductIdChunks $productIdChunks parent and children product ids to be loaded
     * @return IntegerNet_Solr_Model_Bridge_LazyProductIterator
     */
    public function createLazyProductIterator($storeId, \IntegerNet\Solr\Indexer\Data\ProductIdChunks $productIdChunks)
    {
        return new IntegerNet_Solr_Model_Bridge_LazyProductIterator($storeId, $productIdChunks);
    }
}