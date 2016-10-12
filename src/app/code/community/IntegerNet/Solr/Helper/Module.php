<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class IntegerNet_Solr_Helper_Module
{
    /**
     * @return bool
     */
    public function isActive()
    {
        $helper = Mage::helper('integernet_solr');
        if (!Mage::getStoreConfigFlag('integernet_solr/general/is_active')) {
            return false;
        }

        if (!$this->isLicensed()) {
            return false;
        }

        if (Mage::registry('is_autosuggest')) {
            return true;
        }

        if ($helper->page()->isCategoryPage() && !$helper->isCategoryDisplayActive()) {
            return false;
        }

        if (!$helper->page()->isSolrResultPage()) {
            return false;
        }

        return true;
    }
    /**
     * @param string $key
     * @return bool
     */
    public function isKeyValid($key)
    {
        if (!$key) {
            return true;
        }
        $key = trim(strtolower($key));
        $key = str_replace(array('-', '_', ' '), '', $key);

        if (strlen($key) != 10) {
            return false;
        }

        $hash = md5($key);

        return substr($hash, -3) == 'f11';
    }

    /**
     * @return bool
     */
    public function isLicensed()
    {
        if (!Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')) {
            return true;
        }
        if (!$this->isKeyValid(Mage::getStoreConfig('integernet_solr/general/license_key'))) {

            if ($installTimestamp = Mage::getStoreConfig('integernet_solr/general/install_date')) {

                $diff = time() - $installTimestamp;
                if (($diff < 0) || ($diff > 2419200)) {

                    Mage::log('The IntegerNet_Solr module is not correctly licensed. Please enter your license key at System -> Configuration -> Solr or contact us via http://www.integer-net.com/solr-magento/.', Zend_Log::WARN, 'exception.log');
                    return false;

                } else if ($diff > 1209600) {

                    Mage::log('The IntegerNet_Solr module is not correctly licensed. Please enter your license key at System -> Configuration -> Solr or contact us via http://www.integer-net.com/solr-magento/.', Zend_Log::WARN, 'exception.log');
                }
            }
        }

        return true;
    }
}