<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Indexer\Data\ProductIdChunks;

/**
 * @loadFixture registry
 * @loadFixture config
 */
class IntegerNet_Solr_Test_Model_ProductIterator extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * @test
     * @loadFixture catalog
     * @dataProvider dataIteratorParameters
     */
    public function shouldLazyloadCollections($idFilter, $pageSize, $expectedProductIds, $expectedInnerIteratorCount)
    {
        // Magento needs a customer session to work with product collections :-/
        // and replacing it with a mock causes side effects with other tests :-(
        // these lines above accidentally have the same amount of characters :-)
        $this->customerSession(0);

        $productRepository = new IntegerNet_Solr_Model_Bridge_ProductRepository();

        $iterator = $productRepository->getProductsInChunks(1,
            ProductIdChunks::withAssociationsTogether(
                $idFilter ? $idFilter : $productRepository->getAllProductIds(),
                [], $pageSize
            )
        );
        $actualProductIds = [];
        $guard = 0;
        $callbackMock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        /*$callbackMock->expects($this->exactly($expectedInnerIteratorCount))
            ->method('__invoke');*/
        $iterator->setPageCallback($callbackMock);
        foreach ($iterator as $product)
        {
            if (!in_array(intval($product->getId()), $actualProductIds)) {
                $actualProductIds[]= intval($product->getId());
            }
            if (++$guard > 2 * count($expectedProductIds)) {
                $this->fail('Too many iterations. Collected product ids: ' . join(',', $actualProductIds));
                break;
            }
        }
        $this->assertEquals($expectedProductIds, array_unique($actualProductIds), 'product ids', 0.0, 10, false, true);
        $this->assertEventDispatchedExactly('integernet_solr_product_collection_load_after', $expectedInnerIteratorCount);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function dataIteratorParameters()
    {
        return [
            'no_filter_pagesize_1' => [null, 1, [1, 2, 3, 21001, 22101, 22111, 22201], 7],
            'no_filter_pagesize_3' => [null, 3, [1, 2, 3, 21001, 22101, 22111, 22201], 3],
            'no_filter_pagesize_6' => [null, 6, [1, 2, 3, 21001, 22101, 22111, 22201], 2],
            'no_filter_pagesize_7' => [null, 7, [1, 2, 3, 21001, 22101, 22111, 22201], 1],
            'no_filter_pagesize_8' => [null, 8, [1, 2, 3, 21001, 22101, 22111, 22201], 1],
            'filter_pagesize_1' => [[21000, 21001, 22101], 1, [21001, 22101], 3],
        ];
    }
}