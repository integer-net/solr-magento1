<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Resource_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
    /**
     * @var IntegerNet_Solr_Model_Bridge_Factory
     */
    protected $_bridgeFactory;

    protected function _construct()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            parent::_construct();
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            parent::_construct();
        }

        $this->_bridgeFactory = Mage::getModel('integernet_solr/bridge_factory');
    }
    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::applyFilterToCollection($filter, $value);
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            return parent::applyFilterToCollection($filter, $value);
        }

        $bridgeAttribute = $this->_bridgeFactory->createAttribute($filter->getAttributeModel());
        
        $attributeFilters = Mage::registry('attribute_filters');
        if (!is_array($attributeFilters)) {
            $attributeFilters = array();
        }
        $attributeFilters[] = array(
            'attribute' => $bridgeAttribute,
            'value' => $value,
        );
        Mage::unregister('attribute_filters');
        Mage::register('attribute_filters', $attributeFilters);

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return parent::getCount($filter);
        }

        if (!Mage::helper('integernet_solr')->page()->isSolrResultPage()) {
            return parent::getCount($filter);
        }

        /** @var $solrResult StdClass */
        $solrResult = Mage::getSingleton('integernet_solr/result')->getSolrResult();

        $attribute = $filter->getAttributeModel();

        $count = array();
        if (isset($solrResult->facet_counts->facet_fields->{$attribute->getAttributeCode() . '_facet'})) {
            foreach ((array)$solrResult->facet_counts->facet_fields->{$attribute->getAttributeCode() . '_facet'} as $key => $value) {
                $count[intval($key)] = $value;
            }
            return $count;
        }

        return array();
    }
}