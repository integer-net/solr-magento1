<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

use IntegerNet\SolrCms\Implementor\PageRenderer;
use IntegerNet\SolrCms\Implementor\Page;
use IntegerNet\Solr\Indexer\IndexDocument;

class IntegerNet_Solr_Model_Bridge_PageRenderer implements PageRenderer
{
    /** @var IntegerNet_Solr_Block_Indexer_Item[] */
    private $_itemBlocks = array();

    /**
     * @param Page $page
     * @param IndexDocument $pageData
     * @param bool $useHtmlInResults
     */
    public function addResultHtmlToPageData(Page $page, IndexDocument $pageData, $useHtmlInResults)
    {
        if (! $page instanceof IntegerNet_Solr_Model_Bridge_Page) {
            // We need direct access to the Magento page
            throw new InvalidArgumentException('Magento 1 page bridge expected, '. get_class($page) .' received.');
        }
    }
}