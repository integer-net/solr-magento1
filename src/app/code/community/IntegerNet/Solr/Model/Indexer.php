<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\SolrCategories\Indexer\CategoryIndexer;
use IntegerNet\SolrCms\Indexer\PageIndexer;

/**
 * Class IntegerNet_Solr_Model_Indexer
 * 
 * @todo fix URLs for comparison to not include referrer URL
 */
class IntegerNet_Solr_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * @var ProductIndexer
     */
    protected $_productIndexer;
    /**
     * @var CategoryIndexer
     */
    protected $_categoryIndexer;
    /**
     * @var PageIndexer
     */
    protected $_pageIndexer;
    /**
     * @var string[]
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
        ),  
        Mage_Catalog_Model_Category::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
        ),
    );

    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $this->_productIndexer = Mage::helper('integernet_solr')->factory()->getProductIndexer();
        if ($this->_useCategoryIndexer()) {
            $this->_categoryIndexer = Mage::helper('integernet_solrpro')->factory()->getCategoryIndexer();
        }
        if ($this->_useCmsIndexer()) {
            $this->_pageIndexer = Mage::helper('integernet_solrpro')->factory()->getPageIndexer();
        }
    }


    public function getName()
    {
        return Mage::helper('integernet_solr')->__('Solr Search Index');
    }

    public function getDescription()
    {
        return Mage::helper('integernet_solr')->__('Indexing of Product Data for Solr');
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        $this->_reindexProducts(null, true);
        if ($this->_useCategoryIndexer()) {
            $this->_reindexCategories(null, true);
        }
        if ($this->_useCmsIndexer()) {
            $this->_reindexCmsPages(null, true);
        }
    }

    /**
     * @param Mage_Index_Model_Event $event
     * @return $this
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        if ($event->getEntity() == Mage_Catalog_Model_Product::ENTITY) {

            $productIds = array();

            /* @var $object Varien_Object */
            $object = $event->getDataObject();

            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    $productIds[] = $object->getId();
                    break;

                case Mage_Index_Model_Event::TYPE_DELETE:
                    $event->addNewData('solr_delete_product_ids', array($object->getId()));
                    break;

                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    $productIds = $object->getProductIds();
                    break;
            }

            if (sizeof($productIds)) {
                $event->addNewData('solr_update_product_ids', $productIds);
            }

        }        
        
        if ($this->_useCategoryIndexer() && $event->getEntity() == Mage_Catalog_Model_Category::ENTITY) {

            $categoryIds = array();

            /* @var $object Varien_Object */
            $object = $event->getDataObject();

            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    $categoryIds[] = $object->getId();
                    break;

                case Mage_Index_Model_Event::TYPE_DELETE:
                    $event->addNewData('solr_delete_category_ids', array($object->getId()));
                    break;
            }

            if (sizeof($categoryIds)) {
                $event->addNewData('solr_update_category_ids', $categoryIds);
            }

        }
        return $this;
    }

    /**
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();

        if (isset($data['solr_delete_product_ids'])) {
            $productIds = $data['solr_delete_product_ids'];
            if (is_array($productIds) && !empty($productIds)) {

                $this->_deleteProductsIndex($productIds);
            }
        }

        if (isset($data['solr_update_product_ids'])) {
            $productIds = $data['solr_update_product_ids'];
            if (is_array($productIds) && !empty($productIds)) {

                $this->_reindexProducts($productIds);
            }
        }
        
        if (isset($data['solr_delete_category_ids'])) {
            $categoryIds = $data['solr_delete_category_ids'];
            if (is_array($categoryIds) && !empty($categoryIds)) {

                $this->_deleteCategoriesIndex($categoryIds);
            }
        }

        if (isset($data['solr_update_category_ids'])) {
            $categoryIds = $data['solr_update_category_ids'];
            if (is_array($categoryIds) && !empty($categoryIds)) {

                $this->_reindexCategories($categoryIds);
            }
        }
    }

    /**
     * @param array|null $productIds
     * @param boolean $emptyIndex
     */
    protected function _reindexProducts($productIds = null, $emptyIndex = false)
    {
        try {
            $this->_productIndexer->reindex($productIds, $emptyIndex);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
    }

    /**
     * @param array|null $categoryIds
     * @param boolean $emptyIndex
     */
    protected function _reindexCategories($categoryIds = null, $emptyIndex = false)
    {
        try {
            $this->_categoryIndexer->reindex($categoryIds, $emptyIndex);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
    }

    /**
     * @param array|null $pageIds
     * @param boolean $emptyIndex
     */
    protected function _reindexCmsPages($pageIds = null, $emptyIndex = false)
    {
        try {
            $this->_pageIndexer->reindex($pageIds, $emptyIndex);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
    }

    /**
     * @param string[] $productIds
     */
    protected function _deleteProductsIndex($productIds)
    {
        $this->_productIndexer->deleteIndex($productIds);
    }

    /**
     * @param string[] $categoryIds
     */
    protected function _deleteCategoriesIndex($categoryIds)
    {
        $this->_categoryIndexer->deleteIndex($categoryIds);
    }

    /**
     * @return bool
     */
    protected function _useCategoryIndexer()
    {
        return Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro') && Mage::getStoreConfigFlag('integernet_solr/category/is_indexer_active');
    }

    /**
     * @return bool
     */
    protected function _useCmsIndexer()
    {
        return Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro') && Mage::getStoreConfigFlag('integernet_solr/cms/is_active');
    }
}