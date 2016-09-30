<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

if (@class_exists('GoMage_Navigation_Model_Resource_Eav_Mysql4_Layer_Filter_Price')) {
    class IntegerNet_Solr_Model_Resource_Catalog_Layer_Filter_Price_Abstract extends GoMage_Navigation_Model_Resource_Eav_Mysql4_Layer_Filter_Price
    {}
} else {
    class IntegerNet_Solr_Model_Resource_Catalog_Layer_Filter_Price_Abstract extends Mage_Catalog_Model_Resource_Layer_Filter_Price
    {}
}

class IntegerNet_Solr_Model_Resource_Catalog_Layer_Filter_Price extends IntegerNet_Solr_Model_Resource_Catalog_Layer_Filter_Price_Abstract
{
    /**
     * Retrieve maximal price for attribute
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return float
     */
    public function getMaxPrice($filter)
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::getMaxPrice($filter);
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            return parent::getMaxPrice($filter);
        }

        /** @var Apache_Solr_Response $result */
        $result = Mage::getSingleton('integernet_solr/result')->getSolrResult();
        if (isset($result->stats->stats_fields->price_f->max)) {
            return $result->stats->stats_fields->price_f->max;
        }
        
        return 0;
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::getCount($filter, $range);
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            return parent::getCount($filter, $range);
        }

        /** @var Apache_Solr_Response $result */
        $result = Mage::getSingleton('integernet_solr/result')->getSolrResult();
        if (isset($result->facet_counts->facet_intervals->price_f)) {
            $counts = array();
            $i = 1;
            foreach($result->facet_counts->facet_intervals->price_f as $borders => $qty) {
                if ($qty) {
                    $counts[$i] = $qty;
                }
                $i++;
            }
            return $counts;
        }

        if (isset($result->facet_counts->facet_ranges->price_f->counts)) {
            $counts = array();
            $stepSize = Mage::getStoreConfig('integernet_solr/results/price_step_size');
            if ($stepSize <= 0) {
                return array();
            }
            foreach($result->facet_counts->facet_ranges->price_f->counts as $lowerEndPrice => $qty) {
                $counts[intval($lowerEndPrice / $stepSize) + 1] = $qty;
            }
            return $counts;
        }

        return array();
    }

    /**
     * Apply price range filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Price
     */
    public function applyPriceRange($filter)
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::applyPriceRange($filter);
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            return parent::applyPriceRange($filter);
        }

        $interval = $filter->getInterval();
        if (!$interval) {
            return $this;
        }

        list($from, $to) = $interval;
        if ($from === '' && $to === '') {
            return $this;
        }

        $priceFilters = Mage::registry('price_filters');
        if (!is_array($priceFilters)) {
            $priceFilters = array();
        }
        $priceFilters[] = array(
            'min' => $from,
            'max' => $to,
        );
        Mage::unregister('price_filters');
        Mage::register('price_filters', $priceFilters);

        return $this;

    }
}