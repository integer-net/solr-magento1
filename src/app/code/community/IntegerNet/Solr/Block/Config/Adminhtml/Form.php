<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Config_Adminhtml_Form extends Mage_Adminhtml_Block_System_Config_Form
{
    protected function _toHtml()
    {
        $html = '';
        if (!Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')) {
            $html .= $this->getLayout()->createBlock('integernet_solr/config_adminhtml_form_upgrade')->toHtml();
        }
        $html .= parent::_toHtml();
        return $html;
    }
}