<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
require_once('Mage' . DS . 'Catalog' . DS . 'controllers' . DS . 'CategoryController.php');
class IntegerNet_Solr_Frontend_CategoryController extends Mage_Catalog_CategoryController
{
    /**
     * Category view action
     */
    public function viewAction()
    {
        if ($category = $this->_initCatagory()) {

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();
            $update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('CATEGORY_' . $category->getId());
            $this->loadLayoutUpdates();

            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-' . $category->getUrlPath())
                    ->addBodyClass('category-' . $category->getUrlKey());
                $root->setTemplate('integernet/solr/ajax/json.phtml');
            }

            $this->renderLayout();
        }
        elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }
}