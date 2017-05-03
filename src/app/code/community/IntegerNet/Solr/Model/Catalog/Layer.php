<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class IntegerNet_Solr_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{
    /**
     * Get current layer product collection
     *
     * @return Varien_Data_Collection
     */
    public function getProductCollection()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::getProductCollection();
        }

        $category = $this->getCurrentCategory();

        if ($category->getData('solr_exclude')) {
            return parent::getProductCollection();
        }

        if (! isset($this->_productCollections[$category->getId()])) {
            $this->_productCollections[$category->getId()] = Mage::getModel('integernet_solr/result_collection');
        }

        return $this->_productCollections[$category->getId()];
    }

    /**
     * Get collection of all filterable attributes for layer products set
     *
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    public function getFilterableAttributes()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::getFilterableAttributes();
        }

        /** @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->addStoreLabel(Mage::app()->getStore()->getId())
            ->setOrder('position', 'ASC');
        $collection = $this->_prepareAttributeCollection($collection);
        $collection->load();

        return $collection;
    }

}
