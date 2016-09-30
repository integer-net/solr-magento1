<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class IntegerNet_Solr_Model_Source_VarcharProductAttribute
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
        $attributes = Mage::getModel('integernet_solr/bridge_factory')->getAttributeRepository()->getVarcharProductAttributes();

        foreach($attributes as $attribute) { /** @var Mage_Catalog_Model_Entity_Attribute $attribute */
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getFrontendLabel(), $attribute->getAttributeCode()),
            );
        }
        return $options;
    }
}