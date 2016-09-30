<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class IntegerNet_Solr_Block_Mana_Filters_Search extends Mana_Filters_Block_Search 
{
    public function delayedPrepareLayout()
    {
        if (Mage::helper('integernet_solr')->module()->isActive()) {

            return $this;
        }
        
        return parent::delayedPrepareLayout();
    }
}