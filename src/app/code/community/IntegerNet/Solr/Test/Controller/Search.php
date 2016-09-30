<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

/**
 * @loadFixture registry
 * @loadFixture config
 * @todo test with HTML from Solr turned on and off
 */
class IntegerNet_Solr_Test_Controller_Search extends IntegerNet_Solr_Test_Controller_Abstract
{
    protected function setUp()
    {
        parent::setUp();
        Mage::getModel('integernet_solr/indexer')->reindexAll();
    }

    /**
     * @test
     * @loadFixture catalog
     */
    public function shouldShowSearchResult()
    {
        $this->dispatch('catalogsearch/result/index', ['q' => 'wells']);
        $this->assertResponseBodyContains('Search results for \'wells\'');
        $this->assertResponseBodyContains('2 Item(s)');
        $this->assertResponseBodyContains('Wells, H.G. 1898. The Time Machine');
        $this->assertResponseBodyContains('Herbert George Wells: The War of the Worlds');
        $this->assertResponseBodyNotContains('Jack Williamson: The Humanoids: A Novel');
    }

    /**
     * @test
     * @loadFixture catalog
     */
    public function shouldShowChildrenSearchResult()
    {
        $this->dispatch('catalogsearch/result/index', ['q' => 'blue']);
        $this->assertResponseBodyContains('Search results for \'blue\'');
        $this->assertResponseBodyContains('1 Item(s)');
        $this->assertResponseBodyContains('Product One');
    }

    /**
     * @test
     * @loadFixture catalog
     */
    public function shouldFilterSearchResult()
    {
        $this->dispatch('catalogsearch/result/index', ['q' => 'wells', 'cat' => '222']);
        $this->assertResponseBodyContains('Search results for \'wells\'');
        $this->assertResponseBodyContains('1 Item(s)');
        $this->assertResponseBodyContains('Wells, H.G. 1898. The Time Machine');
        $this->assertResponseBodyNotContains('Herbert George Wells: The War of the Worlds');
        $this->assertResponseBodyNotContains('Jack Williamson: The Humanoids: A Novel');
    }

    /**
     * @test
     * @loadFixture bigcatalog
     */
    public function shouldShowSearchResultWithPagination()
    {
        $this->dispatch('catalogsearch/result/index', ['_query' => ['p' => '2', 'q' => 'clone']]);
        $this->assertResponseBodyContains('Search results for \'clone\'');
        $this->assertResponseBodyContains('13-24 of');
        $this->assertResponseBodyContains('Clone 13');
        $this->assertResponseBodyContains('Clone 24');
    }

}