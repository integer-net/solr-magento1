<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Helper_CatalogSearch_Data extends Mage_CatalogSearch_Helper_Data
{
    /**
     * Retrieve suggest url
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        if (Mage::getStoreConfigFlag('integernet_solr/general/is_active')) {
            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $baseUrl = Mage::getStoreConfig('web/secure/base_url');
            } else {
                $baseUrl = Mage::getStoreConfig('web/unsecure/base_url');
            }
            switch (Mage::getStoreConfig('integernet_solr/autosuggest/use_php_file_in_home_dir')) {
                case IntegerNet_Solr_Model_Source_AutosuggestMethod::AUTOSUGGEST_METHOD_PHP:
                    return $baseUrl . 'autosuggest.php?store_id=' . Mage::app()->getStore()->getId();
                case IntegerNet_Solr_Model_Source_AutosuggestMethod::AUTOSUGGEST_METHOD_MAGENTO_DIRECT:
                    return $baseUrl . 'autosuggest-mage.php?store_id=' . Mage::app()->getStore()->getId();
            }
        }

        return parent::getSuggestUrl();
    }
}