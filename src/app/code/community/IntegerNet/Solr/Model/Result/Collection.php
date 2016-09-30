<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class IntegerNet_Solr_Model_Result_Collection extends Varien_Data_Collection
{
    /**
     * Collection constructor
     *
     * @param Mage_Core_Model_Resource_Abstract $resource
     */
    public function __construct($resource = null)
    {}

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return  Varien_Data_Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $this->_items = $this->_getSolrResult()->response->docs;

        return $this;
    }
    
    public function getColumnValues($colName)
    {
        $this->load();

        $col = array();
        foreach ($this->getItems() as $item) {
            $field = $item->getField($colName);
            $col[] = $field['value'];
        }
        return $col;

    }

    /**
     * Retrieve collection all items count
     *
     * @return int
     */
    public function getSize()
    {
        $this->load();
        if (is_null($this->_totalRecords)) {
            $this->_totalRecords = $this->_getSolrResult()->response->numFound;
        }
        return intval($this->_totalRecords);
    }

    /**
     * Adding product count to categories collection
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $categoryCollection
     * @return IntegerNet_Solr_Model_Result_Collection
     */
    public function addCountToCategories($categoryCollection)
    {
        $isAnchor    = array();
        $isNotAnchor = array();
        foreach ($categoryCollection as $category) {
            if ($category->getIsAnchor()) {
                $isAnchor[]    = $category->getId();
            } else {
                $isNotAnchor[] = $category->getId();
            }
        }
        $productCounts = array();
        if ($isAnchor || $isNotAnchor) {

            foreach((array)$this->_getSolrResult()->facet_counts->facet_fields->category as $categoryId => $productCount) {
                $productCounts[intval($categoryId)] = intval($productCount);
            }
        }

        foreach ($categoryCollection as $category) {
            $_count = 0;
            if (isset($productCounts[$category->getId()])) {
                $_count = $productCounts[$category->getId()];
            }
            $category->setProductCount($_count);
        }

        return $this;
    }

    /**
     * Specify category filter for product collection
     *
     * @param Mage_Catalog_Model_Category $category
     * @return IntegerNet_Solr_Model_Result_Collection
     */
    public function addCategoryFilter(Mage_Catalog_Model_Category $category)
    {
        $categoryFilters = Mage::registry('category_filters');
        if (!is_array($categoryFilters)) {
            $categoryFilters = array();
        }
        $categoryFilters[] = $category;
        Mage::unregister('category_filters');
        Mage::register('category_filters', $categoryFilters);
        return $this;
    }

    /**
     * Retrieve maximal price
     *
     * @return float
     */
    public function getMaxPrice()
    {
        /** @var Apache_Solr_Response $result */
        $result = Mage::getSingleton('integernet_solr/result')->getSolrResult();
        if (isset($result->stats->stats_fields->price_f->max)) {
            return $result->stats->stats_fields->price_f->max;
        }

        return 0;
    }

    public function addPriceData($customerGroupId = null, $websiteId = null)
    {
        return $this;
    }

    /**
     * @return Apache_Solr_Response
     */
    protected function _getSolrResult()
    {
        return Mage::getSingleton('integernet_solr/result')->getSolrResult();
    }

    public function getLoadedIds ()
    {}
}