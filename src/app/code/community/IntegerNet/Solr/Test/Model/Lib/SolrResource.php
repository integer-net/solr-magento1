<?php
use IntegerNet\Solr\Config\GeneralConfig;
use IntegerNet\Solr\Config\IndexingConfig;
use IntegerNet\Solr\Config\ServerConfig;
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
class IntegerNet_Solr_Test_Model_Lib_SolrResource extends PHPUnit_Framework_TestCase
{
    /**
     * @param array $config
     * @test
     * @dataProvider dataSwapConfig
     */
    public function coresShouldBeSwappedBasedOnConfig(array $config, array $expectation)
    {
        $configStubs = [];
        foreach ($config as $storeId => $storeConfig) {
            $configStubs[$storeId] = $this->getMockForAbstractClass(Config::class);
            foreach ($storeConfig as $method => $return) {
                $configStubs[$storeId]->expects($this->any())
                    ->method($method)
                    ->willReturn($return);
            }
        }
        $resource = new ResourceFacade($configStubs);

        foreach ($expectation as $storeId => $arguments) {
            $_solrMock = $this->getMock(\IntegerNet\Solr\Resource\ServiceBase::class, ['swapCores']);
            $_mocker = $_solrMock->expects($this->once())->method('swapCores');
            // PHP 5.6: ->with(...$arguments)
            call_user_func_array([$_mocker, 'with'], $arguments);
            $resource->setSolrService($storeId, $_solrMock);
        }
        $resource->swapCores();
    }
    public static function dataSwapConfig()
    {
        $defaultGeneralConfig = IntegerNet_Solr_Config_General_Builder::defaultConfig();
        $swapCoreServerConfig = IntegerNet_Solr_Config_Server_Builder::swapCoreConfig();
        $swapCoreIndexingConfig = IntegerNet_Solr_Config_Indexing_Builder::swapCoreConfig();
        $allData = [
            'singlestore' => [
                'config' => [0 => [
                    'getGeneralConfig' => $defaultGeneralConfig->build(),
                    'getServerConfig' => $swapCoreServerConfig->build(),
                    'getIndexingConfig' => $swapCoreIndexingConfig->build()]
                ],
                'expectation' => [0 => [IntegerNet_Solr_Config_Server_Builder::DEFAULT_CORE, IntegerNet_Solr_Config_Server_Builder::SWAP_CORE]]
            ],
            'stores-with-same-config' => [
                'config' => [
                    0 => [
                        'getGeneralConfig' => $defaultGeneralConfig->build(),
                        'getServerConfig' => $swapCoreServerConfig->build(),
                        'getIndexingConfig' => $swapCoreIndexingConfig->build()
                    ],
                    1 => [
                        'getGeneralConfig' => $defaultGeneralConfig->build(),
                        'getServerConfig' => $swapCoreServerConfig->build(),
                        'getIndexingConfig' => $swapCoreIndexingConfig->build()
                    ]
                ],
                'expectation' => [1 => [IntegerNet_Solr_Config_Server_Builder::DEFAULT_CORE, IntegerNet_Solr_Config_Server_Builder::SWAP_CORE]]
            ],
            'stores-with-different-config' => [
                'config' => [
                    0 => [
                        'getGeneralConfig' => $defaultGeneralConfig->build(),
                        'getServerConfig' => $swapCoreServerConfig->build(),
                        'getIndexingConfig' => $swapCoreIndexingConfig->build()
                    ],
                    1 => [
                        'getGeneralConfig' => $defaultGeneralConfig->build(),
                        'getServerConfig' => $swapCoreServerConfig->withCore('core2')->withSwapCore('core3')->build(),
                        'getIndexingConfig' => $swapCoreIndexingConfig->build()
                    ]
                ],
                'expectation' => [
                    0 => [IntegerNet_Solr_Config_Server_Builder::DEFAULT_CORE, IntegerNet_Solr_Config_Server_Builder::SWAP_CORE],
                    1 => ['core2', 'core3']
                ]
            ]
        ];
        foreach ($allData as $parameters) {
            yield $parameters;
        }
    }
}

// test classes from library not available in Magento autoloader

class IntegerNet_Solr_Config_General_Builder
{
    /*
     * Default values
     */
    private $active = true,
        $licenseKey = '',
        $log = true,
        $debug = false;

    private function __construct()
    {
    }

    public static function defaultConfig()
    {
        return new static;
    }

    /**
     * @param boolean $active
     * @return IntegerNet_Solr_Config_General_Builder
     */
    public function withActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @param string $licenseKey
     * @return IntegerNet_Solr_Config_General_Builder
     */
    public function withLicenseKey($licenseKey)
    {
        $this->licenseKey = $licenseKey;
        return $this;
    }

    /**
     * @param boolean $log
     * @return IntegerNet_Solr_Config_General_Builder
     */
    public function withLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @param boolean $debug
     * @return IntegerNet_Solr_Config_General_Builder
     */
    public function withDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    public function build()
    {
        return new GeneralConfig(
            $this->active, $this->licenseKey, $this->log, $this->debug
        );
    }
}
/**
 * @internal test builder
 */
class IntegerNet_Solr_Config_Server_Builder
{
    /*
     * Default values
     */
    private $host = 'localhost',
        $port = 8983,
        $path= 'solr',
        $core = self::DEFAULT_CORE,
        $swapCore = '',
        $useHttps = false,
        $httpMethod = 'GET',
        $useHttpBasicAuth = false,
        $httpBasicAuthUsername = '',
        $httpBasicAuthPassword = '';

    const DEFAULT_CORE = 'core0';
    const SWAP_CORE = 'core1';

    private function __construct()
    {
    }
    public static function defaultConfig()
    {
        return new static;
    }
    public static function swapCoreConfig()
    {
        return self::defaultConfig()->withSwapCore(self::SWAP_CORE);
    }

    /**
     * @param string $host
     * @return $this
     */
    public function withHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function withPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function withPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $core
     * @return $this
     */
    public function withCore($core)
    {
        $this->core = $core;
        return $this;
    }

    /**
     * @param string $swapCore
     * @return $this
     */
    public function withSwapCore($swapCore)
    {
        $this->swapCore = $swapCore;
        return $this;
    }

    /**
     * @param boolean $useHttps
     * @return $this
     */
    public function withUseHttps($useHttps)
    {
        $this->useHttps = $useHttps;
        return $this;
    }

    /**
     * @param string $httpMethod
     * @return $this
     */
    public function withHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
        return $this;
    }

    /**
     * @param boolean $useHttpBasicAuth
     * @return $this
     */
    public function withUseHttpBasicAuth($useHttpBasicAuth)
    {
        $this->useHttpBasicAuth = $useHttpBasicAuth;
        return $this;
    }

    /**
     * @param string $httpBasicAuthUsername
     * @return $this
     */
    public function withHttpBasicAuthUsername($httpBasicAuthUsername)
    {
        $this->httpBasicAuthUsername = $httpBasicAuthUsername;
        return $this;
    }

    /**
     * @param string $httpBasicAuthPassword
     * @return $this
     */
    public function withHttpBasicAuthPassword($httpBasicAuthPassword)
    {
        $this->httpBasicAuthPassword = $httpBasicAuthPassword;
        return $this;
    }
    

    public function build()
    {
        return new ServerConfig(
            $this->host, $this->port, $this->path, $this->core, $this->swapCore, $this->useHttps, $this->httpMethod,
            $this->useHttpBasicAuth, $this->httpBasicAuthUsername, $this->httpBasicAuthPassword);
    }
}


/**
 * @internal test builder
 */
class IntegerNet_Solr_Config_Indexing_Builder
{
    /*
     * Default values
     */
    private $pagesize = 1000,
        $deleteDocumentsBeforeIndexing = true,
        $swapCores = false;

    private function __construct()
    {
    }
    public static function defaultConfig()
    {
        return new static;
    }
    public static function swapCoreConfig()
    {
        return self::defaultConfig()->withSwapCores(true);
    }

    /**
     * @param int $pagesize
     * @return IntegerNet_Solr_Config_Indexing_Builder
     */
    public function withPagesize($pagesize)
    {
        $this->pagesize = $pagesize;
        return $this;
    }

    /**
     * @param boolean $deleteDocumentsBeforeIndexing
     * @return IntegerNet_Solr_Config_Indexing_Builder
     */
    public function withDeleteDocumentsBeforeIndexing($deleteDocumentsBeforeIndexing)
    {
        $this->deleteDocumentsBeforeIndexing = $deleteDocumentsBeforeIndexing;
        return $this;
    }

    /**
     * @param boolean $swapCores
     * @return IntegerNet_Solr_Config_Indexing_Builder
     */
    public function withSwapCores($swapCores)
    {
        $this->swapCores = $swapCores;
        return $this;
    }

    public function build()
    {
        return new IndexingConfig($this->pagesize, $this->deleteDocumentsBeforeIndexing, $this->swapCores);
    }
}