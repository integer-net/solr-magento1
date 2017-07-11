<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Resource\ResourceFacade;
use IntegerNet\Solr\Resource\ResponseDecorator;
use IntegerNet\Solr\Resource\SolrResponse;

/**
 * @loadFixture registry
 * @loadFixture config
 * @doNotIndexAll
 */
class IntegerNet_Solr_Test_Model_Result extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|\IntegerNet\Solr\Resource\ResourceFacade
     */
    protected $_resourceMock;
    protected $_resourceMockMethods = ['search'];

    protected function setUp()
    {
        parent::setUp();
        $this->_resourceMock = $this->getMock(ResourceFacade::class, $this->_resourceMockMethods);
        $factoryStub = $this->mockHelper('integernet_solr/factory', ['getSolrResource']);
        $factoryStub->expects($this->any())->method('getSolrResource')->willReturn($this->_resourceMock);
        $this->replaceByMock('helper', 'integernet_solr/factory', $factoryStub);
    }

    /**
     * @return EcomDev_PHPUnit_Mock_Proxy|\Psr\Log\LoggerInterface
     */
    protected function _mockLog()
    {
        $mock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
        $this->replaceByMock('helper', 'integernet_solr/log', $mock);
        return $mock;
    }

    /**
     * @test
     */
    public function shouldTriggerSearchAndEvents()
    {
        $this->_resourceMock->expects($this->once())
            ->method('search')
            ->willReturn($this->_getDummyResponse());
        $result = Mage::getModel('integernet_solr/result');
        $result->getSolrResult();
        $this->assertEventDispatchedExactly('integernet_solr_update_query_text', 1);
        $this->assertEventDispatchedExactly('integernet_solr_before_search_request', 1);
        $this->assertEventDispatchedExactly('integernet_solr_after_search_request', 1);

    }

    /**
     * @test
     */
    public function shouldTriggeSearchTwiceIfFuzzy()
    {
        $storeId = 1;
        $this->app()->getStore($storeId)->setConfig('integernet_solr/fuzzy/is_active', 1);
        $this->_resourceMock->expects($this->exactly(2))
            ->method('search')
            ->willReturn($this->_getDummyResponse());
        $result = Mage::getModel('integernet_solr/result');
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
        $this->assertEventDispatchedExactly('integernet_solr_update_query_text', 2);
        $this->assertEventDispatchedExactly('integernet_solr_before_search_request', 2);
        $this->assertEventDispatchedExactly('integernet_solr_after_search_request', 2);
    }

    /**
     * @test
     */
    public function shouldUseParametersBasedOnToolbar()
    {
        $storeId = 1;
        $query = 'tshirt';
        $currentPage = 2;
        $pageSize = 10;

        $logMock = $this->_mockLog();
        $logMock->expects($this->at(3))->method('debug')->with(
            $this->logicalAnd(
                $this->stringContains('name_t:"tshirt"~100^5'),
                $this->stringContains('short_description_t_mv:"tshirt"~100^1')
            )
        );
        $logMock->expects($this->at(4))->method('debug')->with(
            'Filter Query: content_type:product AND store_id:1 AND is_visible_in_search_i:1 AND -is_in_stock_i:0');

        /* @var Mage_Core_Block_Text $toolbar Not using actual toolbar block which reads from session */
        $toolbar = $this->app()->getLayout()->createBlock('core/text', 'product_list_toolbar');
        $toolbar->addData([
            'current_page' => $currentPage,
            'current_order' => 'price',
            'current_direction' => 'asc',
            'limit' => $pageSize
        ]);
        $searchHelperStub = $this->mockHelper('catalogsearch', ['getQueryText']);
        $searchHelperStub->expects($this->any())
            ->method('getQueryText')
            ->willReturn($query);
        $this->replaceByMock('helper', 'catalogsearch', $searchHelperStub);

        $this->_resourceMock->expects($this->once())
            ->method('search')
            ->with(
                $storeId,
                $this->stringContains($query),
                0,
                $currentPage * $pageSize,
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->contains('price_f asc', true, true),
                    $this->logicalNot($this->arrayHasKey('rows'))
                ))
            ->willReturn($this->_getDummyResponse());
        $result = Mage::getModel('integernet_solr/result');
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
    }

    /**
     * @test
     */
    public function shouldUseDefaultParametersWithoutToolbar()
    {
        $storeId = 1;
        $query = 'tshirt';

        $this->app()->getLayout()->unsetBlock('product_list_toolbar');
        $searchHelperStub = $this->mockHelper('catalogsearch', ['getQueryText']);
        $searchHelperStub->expects($this->any())
            ->method('getQueryText')
            ->willReturn($query);
        $this->replaceByMock('helper', 'catalogsearch', $searchHelperStub);

        $this->_resourceMock->expects($this->once())
            ->method('search')
            ->with(
                $storeId,
                $this->stringContains($query),
                0,
                Mage::getStoreConfig('integernet_solr/autosuggest/max_number_product_suggestions'),
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->contains('score desc', true, true),
                    $this->logicalNot($this->arrayHasKey('rows'))
                ))
            ->willReturn($this->_getDummyResponse());
        $result = Mage::getModel('integernet_solr/result');
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
    }
    /**
     * @test
     */
    public function shouldExpandPageSizeIfFuzzyIsActive()
    {
        $storeId = 1;
        $query = 'tshirt';

        $this->app()->getStore($storeId)->setConfig('integernet_solr/fuzzy/is_active', 1);
        $this->app()->getLayout()->unsetBlock('product_list_toolbar');
        $searchHelperStub = $this->mockHelper('catalogsearch', ['getQueryText']);
        $searchHelperStub->expects($this->any())
            ->method('getQueryText')
            ->willReturn($query);
        $this->replaceByMock('helper', 'catalogsearch', $searchHelperStub);

        $this->_resourceMock->expects($this->exactly(2))
            ->method('search')
            ->with(
                $storeId,
                $this->stringContains($query),
                0,
                99999,
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->contains('score desc', true, true),
                    $this->logicalNot($this->arrayHasKey('rows'))
                ))
            ->willReturn($this->_getDummyResponse());
        $result = Mage::getModel('integernet_solr/result');
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
    }

    /**
     * @test
     */
    public function shouldBroadenMultiwordSearchIfNoResults()
    {
        $storeId = 1;
        $query = 'blue tshirt';

        $this->app()->getLayout()->unsetBlock('product_list_toolbar');
        $searchHelperStub = $this->mockHelper('catalogsearch', ['getQueryText']);
        $searchHelperStub->expects($this->any())
            ->method('getQueryText')
            ->willReturn($query);
        $this->replaceByMock('helper', 'catalogsearch', $searchHelperStub);

        $this->_resourceMock->expects($this->exactly(2))
            ->method('search')
            ->willReturn($this->_getDummyResponse());
        $result = Mage::getModel('integernet_solr/result');
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
    }

    /**
     * @return SolrResponse
     */
    protected function _getDummyResponse()
    {
        $result = [
            'response' => ['docs' => [], 'numFound' => 0],
            'facet_counts' => ['facet_fields' => []]
        ];
        return new ResponseDecorator(new Apache_Solr_Response(new Apache_Solr_HttpTransport_Response(
            200, 'application/json', json_encode($result))));
    }
}