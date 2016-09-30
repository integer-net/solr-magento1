<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class IntegerNet_Solr_Model_Resource_Catalog_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /** @var IntegerNet_Solr_Model_Result_Collection */
    protected $_solrResultCollection;

    /**
     * @param IntegerNet_Solr_Model_Result_Collection $solrResultCollection
     * @return IntegerNet_Solr_Model_Resource_Catalog_Product_Collection
     */
    public function setSolrResultCollection($solrResultCollection)
    {
        $productIds = $solrResultCollection->getColumnValues('product_id');
        $this->addAttributeToFilter('entity_id', array('in' => $productIds));
        $this->_solrResultCollection = $solrResultCollection;
        return $this;
    }

    /**
     * @return IntegerNet_Solr_Model_Result_Collection
     */
    public function getSolrResultCollection()
    {
        if (is_null($this->_solrResultCollection)) {
            $this->setSolrResultCollection(Mage::getSingleton('integernet_solr/result_collection'));
        }
        return $this->_solrResultCollection;
    }

    protected function _beforeLoad()
    {
        if (Mage::helper('integernet_solr')->module()->isActive() && is_null($this->_solrResultCollection)) {
            $this->setSolrResultCollection(Mage::getSingleton('integernet_solr/result_collection'));
        }

        return parent::_beforeLoad();
    }

    /**
     * Bring collection items into order from solr
     *
     * @return IntegerNet_Solr_Model_Resource_Catalog_Product_Collection
     */
    protected function _afterLoad()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::_afterLoad();
        }

        parent::_afterLoad();

        $tempItems = array();
        foreach ($this->getSolrResultCollection()->getColumnValues('product_id') as $itemId) {
            $item = $this->getItemById($itemId);
            if (!is_null($item)) {
                $tempItems[$itemId] = $item;
            }
        }
        $this->_items = $tempItems;

        return $this;
    }

    /**
     * Get Collection size from Solr
     *
     * @return int
     */
    public function getSize()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::getSize();
        }
        return $this->getSolrResultCollection()->getSize();
    }

    /**
     * Add attribute to sort order
     * Fix for search result display with enabled flat index and not html from solr index
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (!$this->isEnabledFlat()) {
            return parent::addAttributeToSort($attribute, $dir);
        }

        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::addAttributeToSort($attribute, $dir);
        }

        if ($attribute == 'position') {
            if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->order($this->_getAttributeFieldName($attribute) . ' ' . $dir);
                return $this;
            }
            // optimize if using cat index
            $filters = $this->_productLimitationFilters;
            if (isset($filters['category_id']) || isset($filters['visibility'])) {
                $this->getSelect()->order('cat_index.position ' . $dir);
            } else {
                $this->getSelect()->order('e.entity_id ' . $dir);
            }

            return $this;
        }
    }
}