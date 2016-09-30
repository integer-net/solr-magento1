<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Source_VarcharCategoryAttribute
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(array(
            'value' => '',
            'label' => '',
        ));

        /** @var $attributes Mage_Catalog_Model_Resource_Category_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/category_attribute_collection')
            ->addFieldToFilter('backend_type', array('in' => array('static', 'varchar')))
            ->addFieldToFilter('frontend_input', 'text')
            ->addFieldToFilter('attribute_code', array('nin' => array(
                'url_path',
                'children_count',
                'level',
                'path',
                'position',
            )))
            ->setOrder('frontend_label', Mage_Eav_Model_Entity_Collection_Abstract::SORT_ORDER_ASC);
        
        foreach ($attributes as $attribute) {
            /** @var Mage_Catalog_Model_Entity_Attribute $attribute */
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getFrontendLabel(), $attribute->getAttributeCode()),
            );
        }
        return $options;
    }
}