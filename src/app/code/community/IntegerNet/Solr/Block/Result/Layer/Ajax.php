<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

class IntegerNet_Solr_Block_Result_Layer_Ajax extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        if ($this->getLayout()->getBlock('search.result')) {
            $content = array(
                'products' => $this->getLayout()->getBlock('search.result')->toHtml(),
                'leftnav' => $this->getLayout()->getBlock('catalogsearch.solr.leftnav')->toHtml(),
                'topnav' => $this->getLayout()->getBlock('catalogsearch.solr.topnav')->toHtml(),
            );
        } else {
            $content = array(
                'products' => $this->getLayout()->getBlock('category.products')->toHtml(),
                'leftnav' => $this->getLayout()->getBlock('catalog.solr.leftnav')->toHtml(),
                'topnav' => $this->getLayout()->getBlock('catalog.solr.topnav')->toHtml(),
            );
        }
        
        return Zend_Json::encode($content);
    }
}