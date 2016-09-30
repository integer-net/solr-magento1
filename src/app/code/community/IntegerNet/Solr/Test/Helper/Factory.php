<?php
use IntegerNet\Solr\Config\IndexingConfig;
use IntegerNet\Solr\Config\ServerConfig;
use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\Config;
use IntegerNet\Solr\Resource\ResourceFacade;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class IntegerNet_Solr_Test_Helper_Factory extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     */
    public function shouldCreateSolrResourceWithStoreConfiguration()
    {
        $resource = Mage::helper('integernet_solr')->factory()->getSolrResource();
        $this->assertInstanceOf(ResourceFacade::class, $resource);
        $storeConfigs = [
            $resource->getStoreConfig(1),   // default store view
            $resource->getStoreConfig(0),   // admin store view
            $resource->getStoreConfig(null) // admin store view
        ];
        foreach ($storeConfigs as $storeConfig) {
            $this->assertInstanceOf(Config::class, $storeConfig);
            $this->assertInstanceOf(IndexingConfig::class, $storeConfig->getIndexingConfig());
            $this->assertInstanceOf(ServerConfig::class, $storeConfig->getServerConfig());
        }

        $this->setExpectedException(Exception::class, "Store with ID -1 not found.");
        $resource->getStoreConfig(-1);
    }

}