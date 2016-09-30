<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Js extends Mage_Core_Block_Template
{
    public function getAjaxBaseUrl()
    {
        if (!Mage::helper('integernet_solr')->module()->isActive()) {
            return false;
        }
        
        if (!Mage::getStoreConfigFlag('integernet_solr/results/use_ajax_for_filter_results')) {
            return false;
        }
        
        if (Mage::helper('integernet_solr')->page()->isCategoryPage()) {
            if (!Mage::helper('integernet_solr')->isCategoryDisplayActive()) {
                return false;
            }
            return Mage::getUrl('integernet_solr/category/view', array('id' => Mage::registry('current_category')->getId()));
        }
        
        return Mage::getUrl('integernet_solr/result');
    }
}