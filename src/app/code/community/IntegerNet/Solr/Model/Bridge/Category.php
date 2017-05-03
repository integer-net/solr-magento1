<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_SolrCategories
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCategories\Implementor\Category;
use IntegerNet\SolrSuggest\Implementor\SuggestCategory;

class IntegerNet_Solr_Model_Bridge_Category implements Category, SuggestCategory
{
    const ABSTRACT_MAX_LENGTH = 100;

    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $_category;
    /**
     * @var string[]
     */
    protected $_categoryPathNames = array();

    protected $_description = null;

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param string[] $categoryPathNames
     */
    public function __construct(Mage_Catalog_Model_Category $category, array $categoryPathNames = null)
    {
        $this->_category = $category;
        $this->_categoryPathNames = $categoryPathNames;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_category->getId();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_category->getUrl();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_category->getName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (is_null($this->_description)) {
            $this->_description = $this->_category->getDescription();
        switch ($this->_category->getDisplayMode()) {
            case Mage_Catalog_Model_Category::DM_PAGE:
            case Mage_Catalog_Model_Category::DM_MIXED:
                if ($blockId = $this->_category->getLandingPage()) {
                    $block = Mage::getModel('cms/block')->load($blockId);
                    if ($block->getId() && $block->getIsActive()) {
                            $this->_description .= ' ' . Mage::helper('cms')->getPageTemplateProcessor()->filter($block->getContent());
                    }
                }
        }
        }
        return $this->_description;
    }

    public function getAbstract()
    {
        $content = preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $this->getDescription());
        $content = trim(strip_tags(html_entity_decode($content)));
        if (strlen($content) > self::ABSTRACT_MAX_LENGTH) {
            $content = substr($content, 0, self::ABSTRACT_MAX_LENGTH) . '&hellip;';
        }
        return $content;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getPath($separator)
    {
        return implode($separator, $this->_categoryPathNames);
    }

    public function getPathExcludingCurrentCategory($separator)
    {
        $pathIds = $this->_category->getPathIds();
        $pathParts = array();
        array_shift($pathIds);
        array_shift($pathIds);
        array_pop($pathIds);
        foreach($pathIds as $pathId) {
            $pathParts[] = Mage::getResourceModel('catalog/category')->getAttributeRawValue($pathId, 'name', $this->getStoreId());
        }
        return implode($separator, $pathParts);
    }

    public function getStoreId()
    {
        return $this->_category->getStoreId();
    }

    /**
     * @return int
     */
    public function getSolrId()
    {
        return 'category_' . $this->getId() . '_' . $this->getStoreId();
    }

    public function getSolrBoost()
    {
        return $this->_category->getData('solr_boost');
    }

    /**
     * Use first image in content area as page image
     *
     * @return string
     */
    public function getImageUrl()
    {
        if ($imageUrl = $this->_category->getImageUrl()) {
            return $imageUrl;
        }
        $content = $this->getDescription();
        preg_match('/<img.+src=\"(.*)\"/U', $content, $results);
        if (isset($results[1])) {
            return $results[1];
        }
        return '';
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isIndexable($storeId)
    {
        Mage::dispatchEvent('integernet_solr_can_index_category', array('category' => $this->_category));

        if ($this->_category->getSolrExclude()) {
            return false;
        }

        if (!$this->_category->getIsActive()) {
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
        return call_user_func_array(array($this->_category, $method), $args);
    }
}
