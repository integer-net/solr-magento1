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
 */
class IntegerNet_Solr_Test_Controller_Configuration extends IntegerNet_Solr_Test_Controller_Abstract
{
    /**
     * @test
     */
    public function shouldShowStatusBlock()
    {
        $this->adminSession();
        $this->dispatch('adminhtml/system_config/edit', ['section' => 'integernet_solr']);
        $this->assertRequestRoute('adminhtml/system_config/edit');
        $this->assertLayoutBlockRendered('integernet_solr_config_status');

        $expectedMessages = [
            'Solr version:',
            'Solr Module is activated.',
            'Solr server configuration is complete.',
            'Connection to Solr server established successfully.',
            'Test search request issued successfully.',
        ];
        foreach ($expectedMessages as $message) {
            /*
             * would use assertLayoutBlockRenderedContent() but it evaluates the constraint and discards the result
             * without actually failing.
             */
            $this->assertResponseBodyContains($message);
        }
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @dataProviderFile invalid-config.yaml
     */
    public function invalidConfigurationShouldShowError(array $config)
    {
        $this->markTestSkipped('Currently swap configuration is checked only on reindex');
    }
}