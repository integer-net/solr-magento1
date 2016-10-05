<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class IntegerNet_Solr_Helper_Page
{
    /**
     * @return bool
     */
    public function isSearchPage()
    {
        return Mage::app()->getRequest()->getModuleName() == 'catalogsearch'
        && Mage::app()->getRequest()->getControllerName() == 'result';
    }

    /**
     * @return bool
     */
    public function isCategoryPage()
    {
        return Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')
        && ((Mage::app()->getRequest()->getModuleName() == 'catalog' && Mage::app()->getRequest()->getControllerName() == 'category')
        || (Mage::app()->getRequest()->getModuleName() == 'solr' && Mage::app()->getRequest()->getControllerName() == 'category'));
    }

    /**
     * @return bool
     */
    public function isSolrResultPage()
    {
        return Mage::app()->getRequest()->getModuleName() == 'catalogsearch'
        || Mage::app()->getRequest()->getModuleName() == 'solr'
        || $this->isCategoryPage();
    }

}