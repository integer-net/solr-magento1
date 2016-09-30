<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Result_Layer extends Mage_Core_Block_Abstract
{
    /**
     * @return IntegerNet_Solr_Block_Result_Layer_State
     */
    public function getState()
    {
        if (Mage::helper('integernet_solr')->page()->isCategoryPage()) {
            if ($block = $this->getLayout()->getBlock('catalog.solr.layer.state')) {
                return $block;
            }

            $block = $this->getLayout()->createBlock('integernet_solr/result_layer_state')
                ->setTemplate('integernet/solr/layer/top/state.phtml')
                ->setLayer($this);

            return $block;
        }

        if ($block = $this->getLayout()->getBlock('catalogsearch.solr.layer.state')) {
            return $block;
        }

        $block = $this->getLayout()->createBlock('integernet_solr/result_layer_state')
            ->setTemplate('integernet/solr/layer/top/state.phtml')
            ->setLayer($this);

        return $block;
    }
}