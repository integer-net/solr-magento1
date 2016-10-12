<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Config_Adminhtml_Form_Upgrade extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('integernet/solr/config/upgrade.phtml');
        parent::_construct();
    }

    public function getBannerUrl()
    {
        return $this->getSkinUrl('integernet/solr/solr_free_banner_upgrade_to_pro.png');
    }

    public function getLinkUrl()
    {
        $languageCode = Mage::app()->getLocale()->getLocale()->getLanguage();
        if ($languageCode == 'de') {
            return 'https://www.integer-net.de/solr-magento/features/?utm_source=free-user&utm_medium=banner&utm_term=features&utm_content=features&utm_campaign=upgrade';
        }
        return 'https://www.integer-net.com/solr-magento/features/?utm_source=free-user&utm_medium=banner&utm_term=features&utm_content=features&utm_campaign=upgrade';
    }
}