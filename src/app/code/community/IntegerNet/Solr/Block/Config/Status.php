<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Config_Status extends Mage_Core_Block_Template
{
    protected $_messages = null;

    /**
     * @return string[]
     */
    public function getSuccessMessages()
    {
        return $this->_getMessages('success');
    }

    /**
     * @return string[]
     */
    public function getErrorMessages()
    {
        return $this->_getMessages('error');
    }

    /**
     * @return string[]
     */
    public function getWarningMessages()
    {
        return $this->_getMessages('warning');
    }

    /**
     * @return string[]
     */
    public function getNoticeMessages()
    {
        return $this->_getMessages('notice');
    }

    /**
     * @param string $type
     * @return string[]
     */
    protected function _getMessages($type)
    {
        if (is_null($this->_messages)) {
            $this->_createMessages();
        }
        if (isset($this->_messages[$type])) {
            return $this->_messages[$type];
        }

        return array();
    }

    protected function _createMessages()
    {
        $storeId = null;
        if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
            $storeId = Mage::app()->getStore($storeCode)->getId();
        } else {
            if ($websiteCode = Mage::app()->getRequest()->getParam('website')) {
                $storeId = Mage::app()->getWebsite($websiteCode)->getDefaultStore()->getId();
            }
        }
        $this->_messages = Mage::getSingleton('integernet_solr/configuration')->getMessages($storeId);
    }
}