<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Config\AutosuggestConfig;
use IntegerNet\Solr\Config\FuzzyConfig;
use IntegerNet\Solr\Config\GeneralConfig;
use IntegerNet\Solr\Config\IndexingConfig;
use IntegerNet\Solr\Config\ServerConfig;
use IntegerNet\Solr\Config\ResultsConfig;
use IntegerNet\Solr\Config\StoreConfig;
use IntegerNet\Solr\Config\CmsConfig;
use IntegerNet\Solr\Config\CategoryConfig;
use IntegerNet\Solr\Implementor\Config;

/**
 * Magento configuration reader, one instance per store view
 */
final class IntegerNet_Solr_Model_Config_Store implements Config
{
    /**
     * @var int
     */
    protected $_storeId;
    /**
     * @var GeneralConfig
     */
    protected $_general;
    /**
     * @var ServerConfig
     */
    protected $_server;
    /**
     * @var IndexingConfig
     */
    protected $_indexing;
    /**
     * @var AutosuggestConfig
     */
    protected $_autosuggest;
    /**
     * @var FuzzyConfig
     */
    protected $_fuzzySearch;
    /**
     * @var FuzzyConfig
     */
    protected $_fuzzyAutosuggest;
    /**
     * @var ResultsConfig
     */
    protected $_results;
    /**
     * @var CategoryConfig
     */
    protected $_category;
    /**
     * @var CmsConfig
     */
    protected $_cms;

    /**
     * @param int $_storeId
     */
    public function __construct($_storeId)
    {
        $this->_storeId = $_storeId;
    }

    /**
     * Returns required module independent store configuration
     *
     * @return StoreConfig
     */
    public function getStoreConfig()
    {
        return new StoreConfig(Mage::app()->getStore($this->_storeId)->getBaseUrl(), Mage::getBaseDir('log'));
    }

    /**
     * Returns general Solr module configuration
     *
     * @return GeneralConfig
     */
    public function getGeneralConfig()
    {
        if ($this->_general === null) {
            $prefix = 'integernet_solr/general/';
            $this->_general = new GeneralConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'license_key'),
                $this->_getConfigFlag($prefix . 'log'),
                $this->_getConfigFlag($prefix . 'debug')
            );
        }
        return $this->_general;
    }


    /**
     * Returns Solr server configuration
     *
     * @return ServerConfig
     */
    public function getServerConfig()
    {
        if ($this->_server === null) {
            $prefix = 'integernet_solr/server/';
            $this->_server = new ServerConfig(
                $this->_getConfig($prefix . 'host'),
                $this->_getConfig($prefix . 'port'),
                $this->_getConfig($prefix . 'path'),
                $this->_getConfig($prefix . 'core'),
                $this->_getConfig('integernet_solr/indexing/swap_core'),
                $this->_getConfigFlag($prefix . 'use_https'),
                $this->_getConfig($prefix . 'http_method'),
                $this->_getConfigFlag($prefix . 'use_http_basic_auth'),
                $this->_getConfig($prefix . 'http_basic_auth_username'),
                $this->_getConfig($prefix . 'http_basic_auth_password')
            );
        }
        return $this->_server;
    }

    /**
     * Returns indexing configuration
     *
     * @return IndexingConfig
     */
    public function getIndexingConfig()
    {
        if ($this->_indexing === null) {
            $prefix = 'integernet_solr/indexing/';
            $this->_indexing = new IndexingConfig(
                $this->_getConfig($prefix . 'pagesize'),
                $this->_getConfigFlag($prefix . 'delete_documents_before_indexing'),
                $this->_getConfigFlag($prefix . 'swap_cores')
            );
        }
        return $this->_indexing;
    }

    /**
     * Returns autosuggest configuration
     *
     * @return AutosuggestConfig
     */
    public function getAutosuggestConfig()
    {
        if ($this->_autosuggest === null) {
            $prefix = 'integernet_solr/autosuggest/';
            $this->_autosuggest = new AutosuggestConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'use_php_file_in_home_dir'),
                $this->_getConfig($prefix . 'max_number_searchword_suggestions'),
                $this->_getConfig($prefix . 'max_number_product_suggestions'),
                $this->_getConfig($prefix . 'max_number_category_suggestions'),
                $this->_getConfig($prefix . 'max_number_cms_page_suggestions'),
                $this->_getConfigFlag($prefix . 'show_complete_category_path'),
                $this->_getConfig($prefix . 'category_link_type'),
                @unserialize($this->_getConfig($prefix . 'attribute_filter_suggestions')),
                $this->_getConfig($prefix . 'show_outofstock')
            );
        }
        return $this->_autosuggest;
    }

    /**
     * Returns fuzzy configuration for search
     *
     * @return FuzzyConfig
     */
    public function getFuzzySearchConfig()
    {
        if ($this->_fuzzySearch === null) {
            $prefix = 'integernet_solr/fuzzy/';
            $this->_fuzzySearch = new FuzzyConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'sensitivity'),
                $this->_getConfig($prefix . 'minimum_results')
            );
        }
        return $this->_fuzzySearch;
    }

    /**
     * Returns fuzzy configuration for autosuggest
     *
     * @return FuzzyConfig
     */
    public function getFuzzyAutosuggestConfig()
    {
        if ($this->_fuzzyAutosuggest === null) {
            $prefix = 'integernet_solr/fuzzy/';
            $this->_fuzzyAutosuggest = new FuzzyConfig(
                $this->_getConfigFlag($prefix . 'is_active_autosuggest'),
                $this->_getConfig($prefix . 'sensitivity_autosuggest'),
                $this->_getConfig($prefix . 'minimum_results_autosuggest')
            );
        }
        return $this->_fuzzyAutosuggest;
    }

    /**
     * Returns search results configuration
     *
     * @return ResultsConfig
     */
    public function getResultsConfig()
    {
        if ($this->_results === null) {
            $prefix = 'integernet_solr/results/';
            $this->_results = new ResultsConfig(
                $this->_getConfigFlag($prefix . 'use_html_from_solr'),
                $this->_getConfig($prefix . 'search_operator'),
                $this->_getConfig($prefix . 'priority_categories'),
                $this->_getConfig($prefix . 'price_step_size'),
                $this->_getConfig($prefix . 'max_price'),
                $this->_getConfigFlag($prefix . 'use_custom_price_intervals'),
                explode(',', $this->_getConfig($prefix . 'custom_price_intervals')),
                $this->_getConfig($prefix . 'show_outofstock')
            );
        }
        return $this->_results;
    }

    /**
     * Returns search results configuration
     *
     * @return CmsConfig
     */
    public function getCmsConfig()
    {
        if ($this->_cms === null) {
            $prefix = 'integernet_solr/cms/';
            $this->_cms = new CmsConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfigFlag($prefix . 'use_in_search_results'),
                intval($this->_getConfig($prefix . 'max_number_results')),
                $this->_getConfigFlag($prefix . 'fuzzy_is_active'),
                floatval($this->_getConfig($prefix . 'fuzzy_sensitivity'))
            );
        }
        return $this->_cms;
    }

    /**
     * Returns search results configuration
     *
     * @return CategoryConfig
     */
    public function getCategoryConfig()
    {
        if ($this->_category === null) {
            $prefix = 'integernet_solr/category/';
            $this->_category = new CategoryConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'filter_position'),
                $this->_getConfigFlag($prefix . 'is_indexer_active'),
                $this->_getConfigFlag($prefix . 'use_in_search_results'),
                intval($this->_getConfig($prefix . 'max_number_results')),
                $this->_getConfigFlag($prefix . 'fuzzy_is_active'),
                floatval($this->_getConfig($prefix . 'fuzzy_sensitivity')),
                $this->_getConfig($prefix . 'show_outofstock')
            );
        }
        return $this->_category;
    }


    protected function _getConfig($path)
    {
        return Mage::getStoreConfig($path, $this->_storeId);
    }

    protected function _getConfigFlag($path)
    {
        return Mage::getStoreConfigFlag($path, $this->_storeId);
    }

}
