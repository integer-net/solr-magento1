<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

/**
 * @loadFixture registry
 * @loadFixture config
 */
class IntegerNet_Solr_Test_Model_Bridge_AttributeRepository extends EcomDev_PHPUnit_Test_Case
{
    protected function setUp()
    {
        parent::setUp();
        $setup = new Mage_Catalog_Model_Resource_Setup('catalog_setup');
        $setup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'manufacturer', 'is_filterable_in_search', '1');
    }
    /**
     * @test
     * @dataProvider dataLoadAttributes
     * @loadFixture catalog
     * @param $storeId
     * @param array $expectedSearchableAttributes
     * @param array $expectedFilterableAttributes
     */
    public function searchableShouldLoadAttributesWithStoreValues($storeId, array $expectedSearchableAttributes,
                                                        array $expectedFilterableAttributes)
    {
        $attributeRepository = Mage::getModel('integernet_solr/bridge_factory')->createAttributeRepository();
        $this->assertAttributeArrayContains($expectedSearchableAttributes,
            $attributeRepository->getSearchableAttributes($storeId), 'getSearchableAttributes');
    }
    /**
     * @test
     * @dataProvider dataLoadAttributes
     * @loadFixture catalog
     * @param $storeId
     * @param array $expectedSearchableAttributes
     * @param array $expectedFilterableAttributes
     */
    public function filterableShouldLoadAttributesWithStoreValues($storeId, array $expectedSearchableAttributes,
                                                        array $expectedFilterableAttributes)
    {
        $attributeRepository = Mage::getModel('integernet_solr/bridge_factory')->createAttributeRepository();
        $this->assertAttributeArrayContains($expectedFilterableAttributes,
            $attributeRepository->getFilterableAttributes($storeId), 'getFilterableAttributes');
    }
    /**
     * @test
     * @dataProvider dataLoadAttributes
     * @loadFixture catalog
     * @param $storeId
     * @param array $expectedSearchableAttributes
     * @param array $expectedFilterableAttributes
     */
    public function filterableInCatalogShouldLoadAttributesWithStoreValues($storeId, array $expectedSearchableAttributes,
                                                        array $expectedFilterableAttributes)
    {
        $attributeRepository = Mage::getModel('integernet_solr/bridge_factory')->createAttributeRepository();
        $this->assertAttributeArrayContains($expectedFilterableAttributes,
            $attributeRepository->getFilterableInCatalogAttributes($storeId), 'getFilterableInCatalogAttributes');
    }
    /**
     * @test
     * @dataProvider dataLoadAttributes
     * @loadFixture catalog
     * @param $storeId
     * @param array $expectedSearchableAttributes
     * @param array $expectedFilterableAttributes
     */
    public function filterableInSearchShouldLoadAttributesWithStoreValues($storeId, array $expectedSearchableAttributes,
                                                        array $expectedFilterableAttributes)
    {
        $attributeRepository = Mage::getModel('integernet_solr/bridge_factory')->createAttributeRepository();
        $this->assertAttributeArrayContains($expectedFilterableAttributes,
            $attributeRepository->getFilterableInSearchAttributes($storeId), 'getFilterableInSearchAttributes');
    }
    /**
     * @test
     * @dataProvider dataLoadAttributes
     * @loadFixture catalog
     * @param $storeId
     * @param array $expectedSearchableAttributes
     * @param array $expectedFilterableAttributes
     */
    public function filterableInCatalogOrSearchshouldLoadAttributesWithStoreValues($storeId, array $expectedSearchableAttributes,
                                                        array $expectedFilterableAttributes)
    {
        $attributeRepository = Mage::getModel('integernet_solr/bridge_factory')->createAttributeRepository();
        $this->assertAttributeArrayContains($expectedFilterableAttributes,
            $attributeRepository->getFilterableInCatalogOrSearchAttributes($storeId), 'getFilterableInCatalogOrSearchAttributes');
    }

    /**
     * data provider
     *
     * @return array
     */
    public static function dataLoadAttributes()
    {
        $manufacturer = [];
        $manufacturer[2] = $manufacturer[1] = $manufacturer[0] = ['label' => 'Manufacturer', 'options' => [5 => 'Herbert George Wells', 6 => 'Jack Williamson', 7 => 'Viktor Yanukovych']];
        $manufacturer[1]['label'] = 'Manufacturer1';
        $manufacturer[2]['label'] = 'Manufacturer2';
        $manufacturer[2]['options'][5] = 'Hodor Hodor Hodor';
        return [
            [0, [81 => $manufacturer[0]], [81 => $manufacturer[0]]],
            [1, [81 => $manufacturer[1]], [81 => $manufacturer[1]]],
            [2, [81 => $manufacturer[2]], [81 => $manufacturer[2]]],
        ];
    }
    /**
     * @param mixed[] $expectedSearchableAttributes
     * @param \IntegerNet\Solr\Implementor\Attribute[] $actualAttributes
     */
    private function assertAttributeArrayContains(array $expectedSearchableAttributes, array $actualAttributes, $message)
    {
        foreach ($expectedSearchableAttributes as $expectedAttributeId => $expectedAttribute) {
            $this->assertArrayHasKey($expectedAttributeId, $actualAttributes, $message);
            $attribute = $actualAttributes[$expectedAttributeId];
            $this->assertEquals($expectedAttribute['label'], $attribute->getStoreLabel(), $message);
            foreach ($expectedAttribute['options'] as $optionId => $expectedText) {
                $this->assertEquals($expectedText, $attribute->getSource()->getOptionText($optionId), $message);
            }
        }
    }
}