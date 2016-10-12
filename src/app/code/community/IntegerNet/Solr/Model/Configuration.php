<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Configuration
{
    protected $_messages = array();

    /**
     * @param int|null $storeId
     * @return string[]
     */
    public function getMessages($storeId = null)
    {
        $this->_checkConfiguration($storeId);
        return $this->_messages;
    }

    /**
     * @param int|null $storeId
     */
    protected function _checkConfiguration($storeId = null)
    {
        $this->_createGeneralInfoMessages($storeId);

        if (!$this->_isStoreEnabled($storeId)) {
            return;
        }

        if (!$this->_isModuleActive($storeId)) {
            return;
        }

        if (!$this->_isModuleLicensed()) {
            return;
        }

        if (!$this->_isServerConfigurationComplete($storeId)) {
            return;
        }

        if (!$this->_canPingSolrServer($storeId)) {
            return;
        }

        if (!$this->_canIssueSearchRequest($storeId)) {
            return;
        }

        if (Mage::getStoreConfigFlag('integernet_solr/indexing/swap_cores', $storeId)) {
            if (!$this->_isSwapcoreConfigurationComplete($storeId)) {
                return;
            }

            if (!$this->_canPingSwapCore($storeId)) {
                return;
            }

            if (!$this->_canIssueSearchRequestToSwapCore($storeId)) {
                return;
            }
        }
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _createGeneralInfoMessages($storeId)
    {
        $this->_addNoticeMessage(
            Mage::helper('integernet_solr')->__('Module version: %s', $this->_getModuleVersion())
        );

        $this->_addNoticeMessage(
            Mage::helper('integernet_solr')->__('Library version: %s', $this->_getLibraryVersion())
        );

        if (method_exists('Mage', 'getEdition')) {
            $this->_addNoticeMessage(
                Mage::helper('integernet_solr')->__('Magento version: %s (%s Edition)', Mage::getVersion(), Mage::getEdition())
            );
        } else {
            $this->_addNoticeMessage(
                Mage::helper('integernet_solr')->__('Magento version: %s', Mage::getVersion())
            );
        }
        if (!Mage::helper('integernet_solr')->isModuleEnabled('Aoe_LayoutConditions')) {
            $this->_addWarningMessage(
                Mage::helper('integernet_solr')->__('The module Aoe_LayoutConditions is not installed. Please get it from <a href="%s" target="_blank">%s</a>.', 'https://github.com/aoepeople/Aoe_LayoutConditions', 'https://github.com/aoepeople/Aoe_LayoutConditions')
            );
        }
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isStoreEnabled($storeId)
    {
        $store = Mage::app()->getStore($storeId);
        if (!$store->getIsActive()) {
            $this->_addNoticeMessage(
                Mage::helper('integernet_solr')->__('The store is disabled.')
            );
            return false;
        }
        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isModuleActive($storeId)
    {
        if (!Mage::getStoreConfigFlag('integernet_solr/general/is_active', $storeId)) {
            $this->_addNoticeMessage(
                Mage::helper('integernet_solr')->__('Solr Module is not activated.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            Mage::helper('integernet_solr')->__('Solr Module is activated.')
        );
        return true;
    }

    /**
     * @return boolean
     */
    protected function _isModuleLicensed()
    {
        if (!Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')) {
            return true;
        }

        if (!trim(Mage::getStoreConfig('integernet_solr/general/license_key'))) {

            if ($installTimestamp = Mage::getStoreConfig('integernet_solr/general/install_date')) {

                $diff = time() - $installTimestamp;
                if (($diff < 0) || ($diff > 2419200)) {

                    $this->_addErrorMessage(
                        Mage::helper('integernet_solr')->__('You haven\'t entered your license key yet.')
                    );
                    return false;

                } else {

                    $this->_addNoticeMessage(
                        Mage::helper('integernet_solr')->__('You haven\'t entered your license key yet.')
                    );
                }
            }

        } else {
            if (!Mage::helper('integernet_solr')->module()->isKeyValid(Mage::getStoreConfig('integernet_solr/general/license_key'))) {

                if ($installTimestamp = Mage::getStoreConfig('integernet_solr/general/install_date')) {

                    $diff = time() - $installTimestamp;
                    if (($diff < 0) || ($diff > 2419200)) {

                        $this->_addErrorMessage(
                            Mage::helper('integernet_solr')->__('The license key you have entered is incorrect.')
                        );
                        return false;

                    } else {

                        $this->_addNoticeMessage(
                            Mage::helper('integernet_solr')->__('The license key you have entered is incorrect.')
                        );
                    }
                }
            } else {
                $this->_addSuccessMessage(
                    Mage::helper('integernet_solr')->__('Your license key is valid.')
                );
            }
        }

        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isServerConfigurationComplete($storeId)
    {
        if (!Mage::getStoreConfig('integernet_solr/server/host', $storeId)
            || !Mage::getStoreConfig('integernet_solr/server/port', $storeId)
            || !Mage::getStoreConfig('integernet_solr/server/path', $storeId)
        ) {
            $this->_addErrorMessage(
                Mage::helper('integernet_solr')->__('Solr server configuration is incomplete.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            Mage::helper('integernet_solr')->__('Solr server configuration is complete.')
        );
        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canPingSolrServer($storeId)
    {
        $solr = Mage::helper('integernet_solr')->factory()->getSolrResource()->getSolrService($storeId);

        if (!$solr->ping()) {
            $this->_addErrorMessage(
                Mage::helper('integernet_solr')->__('Connection to Solr server failed.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            Mage::helper('integernet_solr')->__('Connection to Solr server established successfully.')
        );

        $info = Mage::helper('integernet_solr')->factory()->getSolrResource()->getInfo($storeId);
        if ($info instanceof Apache_Solr_Response) {
            if (isset($info->lucene->{'solr-spec-version'})) {
                $solrVersion = $info->lucene->{'solr-spec-version'};
                $this->_addNoticeMessage(
                    Mage::helper('integernet_solr')->__('Solr version: %s', $solrVersion)
                );
            }
        }

        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canIssueSearchRequest($storeId)
    {
        $solr = Mage::helper('integernet_solr')->factory()->getSolrResource()->getSolrService($storeId);

        try {
            $solr->search('text_autocomplete:test');

            $this->_addSuccessMessage(
                Mage::helper('integernet_solr')->__('Test search request issued successfully.')
            );
            return true;
        } catch (Exception $e) {
            $this->_addErrorMessage(
                Mage::helper('integernet_solr')->__('Test search request failed.')
            );
            $this->_addErrorMessage(
                Mage::helper('integernet_solr')->__('Probably the configuration files are not installed correctly on the Solr server.')
            );
            $this->_addNoticeMessage(
                Mage::helper('integernet_solr')->__('You can get a meaningful error message from the tab "Logging" on the Solr Admin Interface.')
            );

            return false;
        }

    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isSwapcoreConfigurationComplete($storeId)
    {
        if (!Mage::getStoreConfig('integernet_solr/server/core', $storeId) || !Mage::getStoreConfig('integernet_solr/indexing/swap_core', $storeId)) {
            $this->_addErrorMessage(
                Mage::helper('integernet_solr')->__('Please enter name of core and swap core.')
            );
            return false;
        }

        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canPingSwapCore($storeId)
    {
        $solr = Mage::helper('integernet_solr')->factory()->getSolrResource()->setUseSwapIndex()->getSolrService($storeId);

        if (!$solr->ping()) {
            $this->_addErrorMessage(
                Mage::helper('integernet_solr')->__('Solr Connection to swap core could not be established.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            Mage::helper('integernet_solr')->__('Solr Connection to swap core established successfully.')
        );
        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canIssueSearchRequestToSwapCore($storeId)
    {
        $solr = Mage::helper('integernet_solr')->factory()->getSolrResource()->
        setUseSwapIndex()->getSolrService($storeId);

        try {
            $solr->search('text_autocomplete:test');

            $this->_addSuccessMessage(
                Mage::helper('integernet_solr')->__('Test search request to swap core issued successfully.')
            );
            return true;
        } catch (Exception $e) {
            $this->_addErrorMessage(
                Mage::helper('integernet_solr')->__('Test search request to swap core failed.')
            );
            $this->_addNoticeMessage(
                Mage::helper('integernet_solr')->__('Maybe the configuration files are not installed correctly on the Solr swap core.')
            );

            return false;
        }

    }

    /**
     * @param string $text
     * @param string $type
     */
    protected function _addMessage($text, $type)
    {
        $this->_messages[$type][] = $text;
    }

    /**
     * @param string $text
     */
    protected function _addErrorMessage($text)
    {
        $this->_addMessage($text, 'error');
    }

    /**
     * @param string $text
     */
    protected function _addSuccessMessage($text)
    {
        $this->_addMessage($text, 'success');
    }

    /**
     * @param string $text
     */
    protected function _addWarningMessage($text)
    {
        $this->_addMessage($text, 'warning');
    }

    /**
     * @param string $text
     */
    protected function _addNoticeMessage($text)
    {
        $this->_addMessage($text, 'notice');
    }

    /**
     * @return string
     */
    protected function _getModuleVersion()
    {
        $moduleVersion = Mage::getConfig()->getModuleConfig('IntegerNet_Solr')->version;
        if ($versionSuffix = Mage::getConfig()->getModuleConfig('IntegerNet_Solr')->version_suffix) {
            $moduleVersion .= ' ' . $versionSuffix;
        }
        if (Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')) {
            $proModuleVersion = Mage::getConfig()->getModuleConfig('IntegerNet_SolrPro')->version;
            if ($versionSuffix = Mage::getConfig()->getModuleConfig('IntegerNet_SolrPro')->version_suffix) {
                $proModuleVersion .= ' ' . $versionSuffix;
            }
            $moduleVersion .= ' (Pro: ' . $proModuleVersion . ')';
        }
        return $moduleVersion;
    }

    /**
     * @return string
     */
    protected function _getLibraryVersion()
    {
        if (!class_exists('\IntegerNet\Solr\Util\Version')) {
            Mage::throwException(
                Mage::helper('integernet_solr')->__(
                    'The IntegerNet_Solr library is not installed. You can get it from <a href="%s" target="_blank">%s</a>.',
                    'https://github.com/integer-net/solr-base',
                    'https://github.com/integer-net/solr-base'
                )
            );
        }
        $libraryVersion = \IntegerNet\Solr\Util\Version::getVersion();

        if (Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro')) {
            if (!class_exists('\IntegerNet\SolrSuggest\Util\Version')) {
                Mage::throwException(
                    Mage::helper('integernet_solr')->__(
                        'The IntegerNet_Solr Pro library is not installed. You can get it from <a href="%s" target="_blank">%s</a>.',
                        'https://github.com/integer-net/solr-pro',
                        'https://github.com/integer-net/solr-pro'
                    )
                );
            }
            $libraryVersion .= ' (Pro: ' . \IntegerNet\SolrSuggest\Util\Version::getVersion() . ')';
        }
        return $libraryVersion;
    }
}