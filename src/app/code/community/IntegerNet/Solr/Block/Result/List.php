<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Result_List extends Mage_Catalog_Block_Product_List
{
    /**
     * @return int
     */
    public function getFoundProductsCount()
    {
        return $this->_getProductCollection()->getSize();
    }

    /**
     * Retrieve loaded category collection
     *
     * @return IntegerNet_Solr_Model_Result_Collection|IntegerNet_Solr_Model_Resource_Catalog_Product_Collection
     */
    protected function _getProductCollection()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::_getProductCollection();
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            parent::_getProductCollection();
        }

        if (Mage::getStoreConfig('integernet_solr/results/use_html_from_solr')) {
            return Mage::getSingleton('integernet_solr/result_collection');
        }

        if (is_null($this->_productCollection) || !($this->_productCollection instanceof IntegerNet_Solr_Model_Resource_Catalog_Product_Collection)) {

            /** @var $productCollection IntegerNet_Solr_Model_Resource_Catalog_Product_Collection */
            $productCollection = Mage::getResourceModel('integernet_solr/catalog_product_collection')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addUrlRewrite()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addAttributeToSelect(array('url_key'));

            Mage::dispatchEvent('catalog_block_product_list_collection', array(
                'collection' => $productCollection
            ));

            $this->_productCollection = $productCollection;
        }

        return $this->_productCollection;
    }

    /**
     * @return IntegerNet_Solr_Block_Result_List
     */
    protected function _beforeToHtml()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::_beforeToHtml();
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            parent::_beforeToHtml();
        }

        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);

        if (! Mage::getStoreConfigFlag('integernet_solr/results/use_html_from_solr')) {
            // disable pagination for collection loading because we select ids based on solr result
            // FS: the previous solution had a bug where the pagination toolbar always showed "Item(s) 1-X of Y"
            $_pageSize = $collection->getPageSize();
            $collection->setPageSize(false);
            $collection->load();
            $collection->setPageSize($_pageSize);
        } else {
            $collection->load();
        }


        return $this;
    }
}