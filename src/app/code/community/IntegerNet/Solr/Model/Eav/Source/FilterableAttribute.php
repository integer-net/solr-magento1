<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Eav_Source_FilterableAttribute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = array(array(
            'value' => '',
            'label' => '',
        ));
        $attributes = Mage::getModel('integernet_solr/bridge_factory')->getAttributeRepository()
            ->getFilterableInCatalogAttributes(Mage::app()->getStore()->getId());

        foreach($attributes as $attribute) { /** @var Mage_Catalog_Model_Entity_Attribute $attribute */
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getFrontendLabel(), $attribute->getAttributeCode()),
            );
        }
        return $options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}