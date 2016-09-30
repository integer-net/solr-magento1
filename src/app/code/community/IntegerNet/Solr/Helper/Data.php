<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

/**
 * This class is only meant to be used as proxy for other helpers with concrete responsibilities.
 *
 * Use the methods to instantiate other helpers, this way it is ensured that the autoloader
 * is registered before.
 *
 */
class IntegerNet_Solr_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function __construct()
    {
        IntegerNet_Solr_Helper_Autoloader::createAndRegister();
    }

    /**
     * @return IntegerNet_Solr_Helper_Factory
     */
    public function factory()
    {
        return Mage::helper('integernet_solr/factory');
    }

    /**
     * @return IntegerNet_Solr_Helper_Attribute
     */
    public function attribute()
    {
        return Mage::helper('integernet_solr/attribute');
    }

    /**
     * @return IntegerNet_Solr_Helper_Module
     */
    public function module()
    {
        return Mage::helper('integernet_solr/module');
    }

    /**
     * @return IntegerNet_Solr_Helper_Page
     */
    public function page()
    {
        return Mage::helper('integernet_solr/page');
    }

    /**
     * @return bool
     * @deprecated use Module helper instead: module()->isActive()
     */
    public function isActive()
    {
        return $this->module()->isActive();
    }

    /**
     * @return bool
     * @deprecated use Page helper instead: page()->isSearchPage()
     */
    public function isSearchPage()
    {
        return $this->page()->isSearchPage();
    }

    /**
     * @return bool
     * @deprecated use Page helper instead: page()->isCategoryPage()
     */
    public function isCategoryPage()
    {
        return $this->page()->isCategoryPage();
    }

    /**
     * @return bool
     * @deprecated use Page helper instead: page()->isSolrResultPage()
     */
    public function isSolrResultPage()
    {
        return $this->page()->isSolrResultPage();
    }

    /**
     * @return bool
     */
    public function isCategoryDisplayActive()
    {
        return Mage::getStoreConfigFlag('integernet_solr/category/is_active');
    }

}