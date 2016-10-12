<?php
use IntegerNet\Solr\Config\CategoryConfig;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Sandro Wagner <sw@integer-net.de>
 */
class IntegerNet_Solr_Helper_Filter extends Mage_Core_Helper_Abstract
{
    public function getFilterPosition()
    {
        if (!@class_exists('CategoryConfig')) {
            return '';
        }
        if( $category = Mage::registry('current_category') ){
            switch ( $category->getData('filter_position') ){
                case CategoryConfig::FILTER_POSITION_DEFAULT:
                    switch(Mage::getStoreConfig('integernet_solr/category/filter_position')){
                        case CategoryConfig::FILTER_POSITION_LEFT:
                            return 'left';
                        case CategoryConfig::FILTER_POSITION_TOP:
                            return 'top';
                    }
                case CategoryConfig::FILTER_POSITION_LEFT:
                    return 'left';
                case CategoryConfig::FILTER_POSITION_TOP:
                    return 'top';
            };
        }else {
            switch(Mage::getStoreConfig('integernet_solr/results/filter_position')){
                case CategoryConfig::FILTER_POSITION_LEFT:
                    return 'left';
                case CategoryConfig::FILTER_POSITION_TOP:
                    return 'top';
            }
        }
    }
}