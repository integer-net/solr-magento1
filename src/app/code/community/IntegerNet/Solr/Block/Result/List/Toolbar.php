<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Result_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl($this->_getRoute(), $urlParams);
    }

    /**
     * @return string
     */
    protected function _getRoute()
    {
        if (Mage::helper('integernet_solr')->page()->isCategoryPage()) {

            return 'catalog/category/view';
        }
        return 'catalogsearch/result/*';
    }
}
