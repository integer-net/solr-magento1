<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Resource\ResourceFacade;

/**
 * @loadFixture registry
 * @loadFixture config
 */
class IntegerNet_Solr_Test_Model_Indexer_Product extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * @param array $config
     * @test
     * @dataProvider dataProvider
     * @dataProviderFile invalid-config.yaml
     * @expectedException Exception
     * @expectedExceptionMessage Configuration Error
     */
    public function invalidSwapConfigurationShouldThrowException(array $config)
    {
        foreach (Mage::app()->getStores(true) as $store) {
            $store->resetConfig();
        }
        foreach ($config as $path => $value) {
            Mage::getConfig()->setNode($path, $value);
        }
        Mage::helper('integernet_solr')->factory()->getProductIndexer()->reindex();
    }

    /**
     * @test
     * @loadFixture catalog
     */
    public function saveProductShouldUpdateSolrIndex()
    {
        $this->setCurrentStore(0);
        $this->adminSession();
        $productId = 1;
        $product = Mage::getModel('catalog/product')->load($productId);
        $product->setData('name', 'Product One SUPERDUPER');
        $product->save();
        $searchResponse = $this->searchFor('SUPERDUPER');
        $this->assertCount(1, $searchResponse->documents());
    }

    /**
     * @param $queryText
     * @return \IntegerNet\Solr\Resource\SolrResponse
     */
    public function searchFor($queryText)
    {
        $storeId = 1;
        $queryStub = $this->getMockBuilder(\IntegerNet\Solr\Implementor\HasUserQuery::class)
            ->getMockForAbstractClass();
        $queryStub->method('getUserQueryText')->willReturn($queryText);
        $factory = Mage::helper('integernet_solr/factory');
        $applicationContext = $factory->getApplicationContext();
        $applicationContext->setFuzzyConfig($factory->getCurrentStoreConfig()->getFuzzySearchConfig());
        $applicationContext->setQuery($queryStub);
        $searchRequestFactory = new \IntegerNet\Solr\Request\SearchRequestFactory(
            $applicationContext,
            $factory->getSolrResource(),
            $storeId
        );
        return $searchRequestFactory->createRequest()->doRequest();
    }
}