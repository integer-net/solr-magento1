<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCms\Implementor\Page;

class IntegerNet_Solr_Model_Bridge_Page implements Page
{
    const ABSTRACT_MAX_LENGTH = 100;
    /**
     * @var Mage_Cms_Model_Page
     */
    protected $_page;

    protected $_content = null;

    /**
     * @param Mage_Cms_Model_Page $_page
     */
    public function __construct(Mage_Cms_Model_Page $_page)
    {
        $this->_page = $_page;
    }

    /**
     * @return Mage_Cms_Model_Page
     */
    public function getMagentoPage()
    {
        return $this->_page;
    }


    public function getId()
    {
        return $this->_page->getId();
    }

    public function getStoreId()
    {
        return $this->_page->getStoreId();
    }

    public function getSolrBoost()
    {
        return $this->_page->getData('solr_boost');
    }
    
    public function getTitle()
    {
        return $this->_page->getData('title');
    }
    
    public function getContent()
    {
        if (is_null($this->_content)) {
            $this->_content = Mage::helper('cms')->getPageTemplateProcessor()->filter($this->_page->getData('content'));
        }
        return $this->_content;
    }

    public function getAbstract()
    {
        $content = trim(strip_tags(html_entity_decode(str_replace(array("\r", "\n", "\t"), ' ', $this->getContent()))));
        if (strlen($content) > self::ABSTRACT_MAX_LENGTH) {
            $content = substr($content, 0, self::ABSTRACT_MAX_LENGTH) . '&hellip;';
        }
        return $content;
    }

    public function getUrl()
    {
        return Mage::helper('cms/page')->getPageUrl($this->getId());
    }

    /**
     * Use first image in content area as page image
     *
     * @return string
     */
    public function getImageUrl()
    {
        $content = $this->getContent();
        preg_match('/<img.+src=\"(http.*)\"/U', $content, $results);
        if (isset($results[1])) {
            return $results[1];
        }
        return '';
    }

    /**
     * @return int
     */
    public function getSolrId()
    {
        return 'page_' . $this->getId() . '_' . $this->getStoreId();
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isIndexable($storeId)
    {
        Mage::dispatchEvent('integernet_solr_can_index_page', array('page' => $this->_page));

        if ($this->_page->getSolrExclude()) {
            return false;
        }
        
        if (!$this->_page->getIsActive()) {
            return false;
        }
        
        return true;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @deprecated only use interface methods!
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_page, $method), $args);
    }
}