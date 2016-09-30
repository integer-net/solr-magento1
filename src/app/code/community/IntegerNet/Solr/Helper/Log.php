<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

final class IntegerNet_Solr_Helper_Log extends AbstractLogger
{
    /**
     * @var array
     */
    protected static $_levelMapping = array(
        LogLevel::ALERT     => Zend_Log::ALERT,
        LogLevel::CRITICAL  => Zend_Log::CRIT,
        LogLevel::DEBUG     => Zend_Log::DEBUG,
        LogLevel::EMERGENCY => Zend_Log::EMERG,
        LogLevel::ERROR     => Zend_Log::ERR,
        LogLevel::INFO      => Zend_Log::INFO,
        LogLevel::NOTICE    => Zend_Log::NOTICE,
        LogLevel::WARNING   => Zend_Log::WARN,
    );
    /**
     * @var string
     */
    protected $_file = 'solr.log';

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->_file = $file;
        return $this;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        Mage::log($message, self::$_levelMapping[$level], $this->_file);
    }

}