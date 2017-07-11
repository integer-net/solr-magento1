<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

require_once 'abstract.php';

class IntegerNet_Solr_Shell extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('reindex')) {
            $storeIdentifiers = $this->getArg('stores');
            if (!$storeIdentifiers) {
                $storeIdentifiers = 'all';
            }
            $storeIds = $this->_getStoreIds($storeIdentifiers);

            $entityTypes = $this->getArg('types');
            if (!$entityTypes || $entityTypes == 'all') {
                $entityTypes = $this->_getDefaultEntityTypes();
            } else {
                $entityTypes = explode(',', $entityTypes);
            }

            $emptyIndex = true;
            if ($this->getArg('emptyindex')) {
                $emptyIndex = 'force';
            } else if ($this->getArg('noemptyindex')) {
                $emptyIndex = false;
            }

            $autoloader = new IntegerNet_Solr_Helper_Autoloader();
            $autoloader->createAndRegister();

            try {
                if (in_array('product', $entityTypes)) {
                    $indexer = Mage::helper('integernet_solr')->factory()->getProductIndexer();
                    $indexer->reindex(null, $emptyIndex, $storeIds);
                    $storeIdsString = implode(', ', $storeIds);
                    echo "Solr product index rebuilt for Stores {$storeIdsString}.\n";
                }

                if (in_array('page', $entityTypes) && $this->_useCmsIndexer()) {
                    $indexer = Mage::helper('integernet_solrpro')->factory()->getPageIndexer();
                    $indexer->reindex(null, $emptyIndex, $storeIds);
                    $storeIdsString = implode(', ', $storeIds);
                    echo "Solr page index rebuilt for Stores {$storeIdsString}.\n";
                }

                if (in_array('category', $entityTypes) && $this->_useCategoryIndexer()) {
                    $indexer = Mage::helper('integernet_solrpro')->factory()->getCategoryIndexer();
                    $indexer->reindex(null, $emptyIndex, $storeIds);
                    $storeIdsString = implode(', ', $storeIds);
                    echo "Solr category index rebuilt for Stores {$storeIdsString}.\n";
                }
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }

        } else if ($this->getArg('reindex_slice')) {
            $storeIdentifiers = $this->getArg('stores');
            if (!$storeIdentifiers) {
                $storeIdentifiers = 'all';
            }
            $storeIds = $this->_getStoreIds($storeIdentifiers);

            $autoloader = new IntegerNet_Solr_Helper_Autoloader();
            $autoloader->createAndRegister();

            try {
                $sliceArg = $this->getArg('slice');
                $this->_checkSliceArgument($sliceArg);
                list($sliceId, $totalNumberSlices) = explode('/', $sliceArg);

                $indexer = Mage::helper('integernet_solr')->factory()->getProductIndexer();

                if ($this->getArg('use_swap_core')) {
                    $indexer->activateSwapCore();
                }
                $indexer->reindex(null, false, $storeIds, $sliceId, $totalNumberSlices);
                if ($this->getArg('use_swap_core')) {
                    $indexer->deactivateSwapCore();
                }

                $storeIdsString = implode(', ', $storeIds);
                echo "Solr product index rebuilt for Stores {$storeIdsString}.\n";
                echo '(Slice ' . $sliceId . ' of ' . $totalNumberSlices . ')' . "\n";
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }

        } else if ($this->getArg('clear')) {
            $storeIdentifiers = $this->getArg('stores');
            if (!$storeIdentifiers) {
                $storeIdentifiers = 'all';
            }
            $storeIds = $this->_getStoreIds($storeIdentifiers);
            $indexer = Mage::helper('integernet_solr')->factory()->getProductIndexer();
            if ($this->getArg('use_swap_core')) {
                $indexer->activateSwapCore();
            }
            foreach($storeIds as $storeId) {
                $indexer->clearIndex($storeId);
            }
            if ($this->getArg('use_swap_core')) {
                $indexer->deactivateSwapCore();
            }
            $storeIdsString = implode(', ', $storeIds);
            echo "Solr product index cleared for Stores {$storeIdsString}.\n";

        } else if ($this->getArg('swap_cores')) {
            $storeIdentifiers = $this->getArg('stores');
            if (!$storeIdentifiers) {
                $storeIdentifiers = 'all';
            }
            $storeIds = $this->_getStoreIds($storeIdentifiers);
            $indexer = Mage::helper('integernet_solr')->factory()->getProductIndexer();
            try {
                $indexer->checkSwapCoresConfiguration($storeIds);
                $indexer->swapCores($storeIds);
                $storeIdsString = implode(', ', $storeIds);
                echo "Solr cores swapped for Stores {$storeIdsString}.\n";
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }

        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f integernet-solr.php -- [options]
        php -f integernet-solr.php -- reindex --stores de
        php -f integernet-solr.php -- reindex --stores all --emptyindex
        php -f integernet-solr.php -- reindex --stores 1 --slice 1/5 --use_swap_core
        php -f integernet-solr.php -- clear --stores 1

  reindex           Reindex solr for given stores (see "stores" param)
  --stores <stores> Reindex given stores (can be store id, store code, comma seperated. Or "all".) If not set, reindex all stores.
  --emptyindex      Force emptying the solr index for the given store(s). If not set, configured value is used.
  --noemptyindex    Force not emptying the solr index for the given store(s). If not set, configured value is used.
  --types <types>   Restrict indexing to certain entity types, i.e. "product", "category" or "page" (comma separated). Or "all". If not set, reindex products.

  reindex_slice     Reindex solr for given stores (see "stores" param). Use this if you want to index only a part of the products, i.e. for letting indexing run in parallel (for products only).
  --slice <number>/<total_number>, i.e. "1/5" or "2/5". 
  --stores <stores> Reindex given stores (can be store id, store code, comma seperated. Or "all".) If not set, reindex all stores.
  --use_swap_core   Use swap core for indexing instead of live solr core (only if configured correctly).
  
  clear             Clear solr product index for given stores (see "stores" param and "use_swap_core" param)
  --stores <stores> Reindex given stores (can be store id, store code, comma seperated. Or "all".) If not set, reindex all stores.
  --use_swap_core   Use swap core for clearing instead of live solr core (only if configured correctly).
  
  swap_cores        Swap cores. This is useful if using slices (see above) after indexing with the "--use_swap_core" param; it's not needed otherwise.
  --stores <stores> Reindex given stores (can be store id, store code, comma seperated. Or "all".) If not set, reindex all stores.
  
  help              This help

USAGE;
    }

    /**
     * @param mixed[] $storeIdentifiers
     * @return int[]
     */
    protected function _getStoreIds($storeIdentifiers)
    {
        $storeIds = array();
        foreach (explode(',', $storeIdentifiers) as $storeIdentifier) {
            $storeIdentifier = trim($storeIdentifier);
            if ($storeIdentifier == 'all') {
                $storeIds = array();
                foreach (Mage::app()->getStores(false) as $store) {
                    if ($store->getIsActive() && Mage::getStoreConfigFlag('integernet_solr/general/is_active', $store->getId())) {
                        $storeIds[] = $store->getId();
                    }
                }
                return $storeIds;
            }
            $store = Mage::app()->getStore($storeIdentifier);
            if ($store->getIsActive() && Mage::getStoreConfigFlag('integernet_solr/general/is_active', $store->getId())) {
                $storeIds[] = $store->getId();
            }
        }
        return $storeIds;
    }

    /**
     * @return array
     */
    protected function _getDefaultEntityTypes()
    {
        $entityTypes = array('product');
        if ($this->_useCategoryIndexer()) {
            $entityTypes[] = 'category';
        }
        if ($this->_useCmsIndexer()) {
            $entityTypes[] = 'page';
        }
        return $entityTypes;
    }

    /**
     * @return bool
     */
    protected function _useCategoryIndexer()
    {
        return Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro') && Mage::getStoreConfigFlag('integernet_solr/category/is_indexer_active');
    }

    /**
     * @return bool
     */
    protected function _useCmsIndexer()
    {
        return Mage::helper('core')->isModuleEnabled('IntegerNet_SolrPro') && Mage::getStoreConfigFlag('integernet_solr/cms/is_active');
    }

    /**
     * @param string $sliceArg
     * @throws InvalidArgumentException
     */
    protected function _checkSliceArgument($sliceArg)
    {
        if (!strlen($sliceArg)) {
            throw new InvalidArgumentException('The "slice" argument must be given.');
        }
        if (strpos($sliceArg, '/') < 1) {
            throw new InvalidArgumentException('The "slice" argument must be of format "1/5" or "20/20"');
        }
        list($sliceId, $totalNumberSlices) = explode('/', $sliceArg);
        $sliceId = intval($sliceId);
        $totalNumberSlices = intval($totalNumberSlices);
        if (!is_integer($sliceId) || !is_integer($totalNumberSlices)) {
            throw new InvalidArgumentException('The "slice" argument must be of format "1/5" or "20/20", only containing integer numbers before/after the slash.');
        }
        if ($totalNumberSlices < 2) {
            throw new InvalidArgumentException('The "slice" argument must be of format "1/5" or "20/20". The second number must be higher than 1.');
        }
        if ($sliceId < 1 || $sliceId > $totalNumberSlices) {
            throw new InvalidArgumentException('The "slice" argument must be of format "1/5" or "20/20". The first number is invalid, should be between 1 and the second number (including those).');
        }
    }
}

$shell = new IntegerNet_Solr_Shell();
$shell->run();
