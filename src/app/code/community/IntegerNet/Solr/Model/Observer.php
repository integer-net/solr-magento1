<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class IntegerNet_Solr_Model_Observer
{
    /**
     * Add new field "solr_boost" to attribute form
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlCatalogProductAttributeEditPrepareForm(Varien_Event_Observer $observer)
    {
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $observer->getForm()->getElement('front_fieldset');

        $field = $fieldset->addField('solr_boost', 'text', array(
            'name' => 'solr_boost',
            'label' => Mage::helper('integernet_solr')->__('Solr Priority'),
            'title' => Mage::helper('integernet_solr')->__('Solr Priority'),
            'note' => Mage::helper('integernet_solr')->__('1 is default, use higher numbers for higher priority.'),
            'class' => 'validate-number',
        ));

        // Set default value
        $field->setValue('1.0000');
    }

    /**
     * Add new column "solr_boost" to attribute grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        // Add "Solr Priority" column to attribute grid
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid) {

            $block->addColumnAfter('solr_boost', array(
                'header' => Mage::helper('catalog')->__('Solr Priority'),
                'sortable' => true,
                'index' => 'solr_boost',
                'type' => 'number',
            ), 'is_comparable');
        }

        if ($block instanceof Mage_Page_Block_Html_Head) {
            $this->_adjustRobots($block);
        }

        if ($block instanceof Mage_Page_Block_Html) {
            $_class = 'solr-filter-'. Mage::helper('integernet_solr/filter')->getFilterPosition();
            $block->addBodyClass($_class);
        };
    }

    /**
     * Resolve conflicts in sort order
     *
     * @param Varien_Event_Observer $observer
     */
    public function controllerActionPredispatchCatalogsearchResultIndex(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfigFlag('integernet_solr/general/is_active') && !$this->_getPingResult()) {
            Mage::app()->getStore()->setConfig('integernet_solr/general/is_active', 0);
        }

        /** @var Mage_Core_Controller_Varien_Action $action */
        $action = $observer->getControllerAction();

        if (Mage::helper('integernet_solr')->module()->isActive() && $order = $action->getRequest()->getParam('order')) {
            if ($order === 'relevance') {
                $_GET['order'] = 'position';
            }
        }

        Mage::app()->getStore()->setConfig(Mage_Catalog_Model_Config::XML_PATH_LIST_DEFAULT_SORT_BY, 'position');

        $this->_redirectOnQuery($action);
    }

    /**
     * @return bool
     */
    protected function _getPingResult()
    {
        $solr = Mage::helper('integernet_solr')->factory()->getSolrResource()->getSolrService(Mage::app()->getStore()->getId());
        return (boolean)$solr->ping();
    }

    public function catalogProductDeleteAfter(Varien_Event_Observer $observer)
    {
        /** @var $indexer Mage_Index_Model_Process */
        $indexer = Mage::getModel('index/process')->load('integernet_solr', 'indexer_code');
        if ($indexer->getMode() != Mage_Index_Model_Process::MODE_REAL_TIME) {
            return;
        }
        
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getProduct();
        Mage::helper('integernet_solr')->factory()->getProductIndexer()->deleteIndex(array($product->getId()));
    }

    /**
     * Redirect to product/category page if search query matches one of the configured product/category attributes directly
     *
     * @param Mage_Core_Controller_Front_Action $action
     */
    protected function _redirectOnQuery($action)
    {
        if ($query = trim($action->getRequest()->getParam('q'))) {
            if (($url = $this->_getProductPageRedirectUrl($query)) || ($url = $this->_getCategoryPageRedirectUrl($query))) {
                $action->getResponse()->setRedirect($url);
                $action->getResponse()->sendResponse();
                $action->setFlag($action->getRequest()->getActionName(), Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            }
        }
    }

    /**
     * @param string $query
     * @return false|string;
     */
    protected function _getProductPageRedirectUrl($query)
    {
        $matchingProductAttributeCodes = explode(',', Mage::getStoreConfig('integernet_solr/results/product_attributes_redirect'));
        if (!sizeof($matchingProductAttributeCodes) || (sizeof($matchingProductAttributeCodes) && !current($matchingProductAttributeCodes))) {
            return false;
        }
        if (in_array('sku', $matchingProductAttributeCodes)) {
            $product = Mage::getModel('catalog/product');
            if ($productId = $product->getIdBySku($query)) {
                $product->load($productId);
                if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                    && in_array(Mage::app()->getWebsite()->getId(), $product->getWebsiteIds())
                ) {
                    return $this->_getProductUrl($product);
                }
            }
            $matchingProductAttributeCodes = array_diff($matchingProductAttributeCodes, array('sku'));
        }

        $filters = array();
        foreach ($matchingProductAttributeCodes as $attributeCode) {
            if (!$attributeCode) {
                continue;
            }
            $filters[] = array('attribute' => $attributeCode, 'eq' => $query);
        }
        
        if (!sizeof($filters)) {
            return false;
        }

        /** @var Mage_Catalog_Model_Resource_Product_Collection $matchingProductCollection */
        $matchingProductCollection = Mage::getResourceModel('catalog/product_collection');
        $matchingProductCollection
            ->addStoreFilter()
            ->addWebsiteFilter()
            ->addAttributeToFilter($filters)
            ->addAttributeToSelect(array('status', 'visibility', 'url_key'))
            ->setOrder('visibility', 'desc');

        if ($matchingProductCollection->getSize() >= 1) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $matchingProductCollection->getFirstItem();
            return $this->_getProductUrl($product);
        }
        return false;
    }

    /**
     * @param string $query
     * @return false|string;
     */
    protected function _getCategoryPageRedirectUrl($query)
    {
        $matchingCategoryAttributeCodes = explode(',', Mage::getStoreConfig('integernet_solr/results/category_attributes_redirect'));
        if (!sizeof($matchingCategoryAttributeCodes) || (sizeof($matchingCategoryAttributeCodes) && !current($matchingCategoryAttributeCodes))) {
            return false;
        }
        $filters = array();
        foreach ($matchingCategoryAttributeCodes as $attributeCode) {
            if (!$attributeCode) {
                continue;
            }
            $filters[] = array('attribute' => $attributeCode, 'eq' => $query);
        }

        if (!sizeof($filters)) {
            return;
        }
        
        $store = Mage::app()->getStore();

        /** @var Mage_Catalog_Model_Resource_Category_Collection $matchingCategoryCollection */
        $matchingCategoryCollection = Mage::getResourceModel('catalog/category_collection');
        $matchingCategoryCollection
            ->setStoreId($store->getId())
            ->addAttributeToFilter($filters)
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('path', array('like' => '1/' . $store->getGroup()->getRootCategoryId() . '/%'))
            ->addAttributeToSelect('url_key');

        if ($matchingCategoryCollection->getSize() == 1) {
            /** @var Mage_Catalog_Model_Category $category */
            $category = $matchingCategoryCollection->getFirstItem();
            return $category->getUrl();
        }

        return false;
    }

    /**
     * Set Robots to NOINDEX,NOFOLLOW depending on config
     *
     * @param Mage_Page_Block_Html_Head $block
     */
    protected function _adjustRobots($block)
    {
        /** @var $helper IntegerNet_Solr_Helper_Data */
        $helper = Mage::helper('integernet_solr');
        if (!$helper->module()->isActive()) {
            return;
        }
        $stateBlock = null;
        $robotOptions = explode(',', Mage::getStoreConfig('integernet_solr/seo/hide_from_robots'));
        if ($helper->page()->isSearchPage()) {
            if (in_array('search_results_all', $robotOptions)) {
                $block->setData('robots', 'NOINDEX,NOFOLLOW');
                return;
            }
            if (!in_array('search_results_filtered', $robotOptions)) {
                return;
            }
            /** @var IntegerNet_Solr_Block_Result_Layer_State $stateBlock */
            $stateBlock = $block->getLayout()->getBlock('catalogsearch.solr.layer.state');
        } elseif ($helper->page()->isCategoryPage() && $helper->isCategoryDisplayActive()) {
            if (!in_array('categories_filtered', $robotOptions)) {
                return;
            }
            /** @var IntegerNet_Solr_Block_Result_Layer_State $stateBlock */
            $stateBlock = $block->getLayout()->getBlock('catalog.solr.layer.state');
        }
        if ($stateBlock instanceof IntegerNet_Solr_Block_Result_Layer_State) {
            $activeFilters = $stateBlock->getActiveFilters();
            if (is_array($activeFilters) && sizeof($activeFilters)) {
                $block->setData('robots', 'NOINDEX,NOFOLLOW');
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    protected function _getProductUrl($product)
    {
        if ($product->isVisibleInSiteVisibility()) {
            return $product->getProductUrl();
        }
        if ($product->isComposite()) {
            return false;
        }
        
        $parentProductIds = array();
        
        /** @var $groupedTypeInstance Mage_Catalog_Model_Product_Type_Grouped */
        $groupedTypeInstance = Mage::getSingleton('catalog/product_type_grouped');
        foreach($groupedTypeInstance->getParentIdsByChild($product->getId()) as $parentProductId) {
            $parentProductIds[] = $parentProductId; 
        }

        /** @var $groupedTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $configurableTypeInstance = Mage::getSingleton('catalog/product_type_configurable');
        foreach($configurableTypeInstance->getParentIdsByChild($product->getId()) as $parentProductId) {
            $parentProductIds[] = $parentProductId; 
        }

        /** @var Mage_Catalog_Model_Resource_Product_Collection $parentProductCollection */
        $parentProductCollection = Mage::getResourceModel('catalog/product_collection');
        $parentProductCollection
            ->addStoreFilter()
            ->addWebsiteFilter()
            ->addIdFilter($parentProductIds)
            ->addAttributeToSelect(array('status', 'visibility', 'url_key'));

        foreach ($parentProductCollection as $parentProduct) {
            /** @var Mage_Catalog_Model_Product $parentProduct */
            if ($productUrl = $this->_getProductUrl($parentProduct)) {
                return $productUrl;
            }
        }
        return false;
    }

    public function afterFastSimpleImportReindex(Varien_Event_Observer $observer)
    {
        /** @var $indexer Mage_Index_Model_Process */
        $indexer = Mage::getModel('index/process')->load('integernet_solr', 'indexer_code');
        if ($indexer->getMode() != Mage_Index_Model_Process::MODE_REAL_TIME) {
            return;
        }

        $productIds = $observer->getEntityId();

        if (empty($productIds)) {
            return;
        }

        Mage::helper('integernet_solr')->factory()->getProductIndexer()->reindex($productIds);
    }
}