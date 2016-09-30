<?php
use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\Source;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
final class IntegerNet_Solr_Model_Bridge_Attribute implements Attribute
{
    /**
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_attribute;
    /**
     * @var IntegerNet_Solr_Model_Bridge_Source
     */
    protected $_source;

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @throws Mage_Core_Exception
     */
    public function __construct(Mage_Catalog_Model_Resource_Eav_Attribute $attribute)
    {
        $this->_attribute = $attribute;
        $this->_source = Mage::getModel('integernet_solr/bridge_factory')->createAttributeSource($this->_attribute->getSource());
    }
    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->_attribute->getAttributeCode();
    }

    /**
     * @return string
     */
    public function getStoreLabel()
    {
        return $this->_attribute->getStoreLabel();
    }

    /**
     * @return float
     */
    public function getSolrBoost()
    {
        return $this->_attribute->getData('solr_boost');
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getFacetType()
    {
        return $this->_attribute->getFrontendInput();
    }

    /**
     * @return bool
     */
    public function getIsSearchable()
    {
        return $this->_attribute->getIsSearchable();
    }

    /**
     * @return string
     */
    public function getBackendType()
    {
        return $this->_attribute->getBackendType();
    }

    /**
     * @return bool
     */
    public function getUsedForSortBy()
    {
        return $this->_attribute->getUsedForSortBy();
    }

    /**
     * Delegate all other calls (by Magento) to attribute
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_array(array($this->_attribute, $name), $arguments);
    }


}