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
 * @doNotIndexAll
 */
class IntegerNet_Solr_Test_Model_Resource_Db extends EcomDev_PHPUnit_Test_Case
{
    public function testDisconnectUnsetsConnection()
    {
        Mage::getResourceModel('integernet_solr/db')->disconnectMysql();
        $this->assertEquals(
            [],
            Mage::getSingleton('core/resource')->getConnections()
        );
    }
}