<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class IntegerNet_Solr_Model_Resource_Db
{
    /**
     * Close all open MySQL connections (will be automatically reopened by Magento if used)
     *
     * Can be called during indexing to prevent wait timeout
     */
    public function disconnectMysql()
    {
        /** @var Zend_Db_Adapter_Abstract $connection */
        foreach (Mage::getSingleton('core/resource')->getConnections() as $name => $connection) {
            if ($connection instanceof Zend_Db_Adapter_Abstract) {
                if ($this->isTransactionOpen($connection)) {
                    continue;
                }
                $connection->closeConnection();
            }
        }
        // connections (adapter objects) must be fully reinitialized, otherwise initStatements are not executed
        Mage::unregister('_singleton/core/resource');
    }

    /**
     * Returns true if the given connection has open transactions
     *
     * @param Zend_Db_Adapter_Abstract $connection
     * @return bool
     */
    private function isTransactionOpen(Zend_Db_Adapter_Abstract $connection)
    {
        return $connection instanceof Magento_Db_Adapter_Pdo_Mysql && $connection->isTransaction();
    }
}