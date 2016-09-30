<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

use IntegerNet\SolrCategories\Implementor\CategoryRenderer;
use IntegerNet\SolrCategories\Implementor\Category;
use IntegerNet\Solr\Indexer\IndexDocument;

class IntegerNet_Solr_Model_Bridge_CategoryRenderer implements CategoryRenderer
{
    /** @var IntegerNet_Solr_Block_Indexer_Item[] */
    private $_itemBlocks = array();

    /**
     * @param Category $category
     * @param IndexDocument $categoryData
     * @param bool $useHtmlInResults
     */
    public function addResultHtmlToCategoryData(Category $category, IndexDocument $categoryData, $useHtmlInResults)
    {
        if (! $category instanceof IntegerNet_Solr_Model_Bridge_Category) {
            // We need direct access to the Magento category
            throw new InvalidArgumentException('Magento 1 category bridge expected, '. get_class($category) .' received.');
        }
    }
}