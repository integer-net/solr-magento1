<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
use IntegerNet\Solr\Query\Params\FilterQueryBuilder;
use IntegerNet\Solr\Request\HasFilter;

class IntegerNet_Solr_Model_Result
{
    /**
     * @var $_solrRequest \IntegerNet\Solr\Request\Request
     */
    protected $_solrRequest;
    /**
     * @var $_filterQueryBuilder FilterQueryBuilder
     */
    protected $_filterQueryBuilder;
    /**
     * @var $_solrResult null|\IntegerNet\Solr\Resource\SolrResponse
     */
    protected $_solrResult;

    protected $activeFilterAttributeCodes = array();

    public function __construct()
    {
        $this->_solrRequest = Mage::helper('integernet_solr')->factory()->getSolrRequest();
        if ($this->_solrRequest instanceof HasFilter) {
            $this->_filterQueryBuilder = $this->_solrRequest->getFilterQueryBuilder();
            $this->_addCategoryFilters();
            $this->_addAttributeFilters();
            $this->_addPriceFilters();
        }
    }

    /**
     * Call Solr server twice: Once without fuzzy search, once with (if configured)
     *
     * @return \IntegerNet\Solr\Resource\SolrResponse
     */
    public function getSolrResult()
    {
        if (null === $this->_solrResult) {
            $this->_solrResult = $this->_solrRequest->doRequest($this->activeFilterAttributeCodes);
        }

        return $this->_solrResult;
    }


    /**
     * @param IntegerNet_Solr_Model_Bridge_Attribute $attribute
     * @param int $value
     */
    public function addAttributeFilter($attribute, $value)
    {
        $this->_filterQueryBuilder->addAttributeFilter($attribute, $value);
        $this->activeFilterAttributeCodes[] = $attribute->getAttributeCode();
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     */
    public function addCategoryFilter($category)
    {
        $this->_filterQueryBuilder->addCategoryFilter($category->getId());
        $this->activeFilterAttributeCodes[] = 'category';
    }

    /**
     * @param int $range
     * @param int $index
     */
    public function addPriceRangeFilterByIndex($range, $index)
    {
        $this->_filterQueryBuilder->addPriceRangeFilterByConfiguration($range, $index);
        $this->activeFilterAttributeCodes[] = 'price';
    }

    /**
     * @param float $minPrice
     * @param float $maxPrice
     */
    public function addPriceRangeFilterByMinMax($minPrice, $maxPrice = null)
    {
        $this->_filterQueryBuilder->addPriceRangeFilterByMinMax($minPrice, $maxPrice);
        $this->activeFilterAttributeCodes[] = 'price';
    }

    /**
     * Store category filters in registry until request is done
     */
    private function _addCategoryFilters()
    {
        $categoryFilters = Mage::registry('category_filters');
        if (is_array($categoryFilters)) {
            foreach ($categoryFilters as $category) {
                $this->addCategoryFilter($category);
            }
        }
        Mage::unregister('category_filters');
    }

    /**
     * Store category filters in registry until request is done
     */
    private function _addAttributeFilters()
    {
        $attributeFilters = Mage::registry('attribute_filters');
        if (is_array($attributeFilters)) {
            foreach ($attributeFilters as $attributeFilter) {
                $this->addAttributeFilter($attributeFilter['attribute'], $attributeFilter['value']);
            }
        }
        Mage::unregister('attribute_filters');
    }

    /**
     * Store category filters in registry until request is done
     */
    private function _addPriceFilters()
    {
        $priceFilters = Mage::registry('price_filters');
        if (is_array($priceFilters)) {
            foreach ($priceFilters as $priceFilter) {
                $this->addPriceRangeFilterByMinMax($priceFilter['min'], $priceFilter['max']);
            }
        }
        Mage::unregister('price_filters');
    }

}