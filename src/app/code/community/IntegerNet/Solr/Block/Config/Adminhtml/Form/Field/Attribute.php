<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Config_Adminhtml_Form_Field_Attribute extends Mage_Core_Block_Html_Select {

    public function _toHtml()
    {
        $attributes = Mage::getModel('integernet_solr/bridge_factory')->getAttributeRepository()
            ->getFilterableInSearchAttributes(Mage::app()->getStore()->getId());

        foreach($attributes as $attribute) {
            $this->addOption($attribute->getAttributeCode(), $attribute->getFrontendLabel() . ' [' . $attribute->getAttributeCode() . ']');
        }

        return parent::_toHtml();
    }

    /**
     * @param string $value
     * @return IntegerNet_Solr_Block_Config_Adminhtml_Form_Field_Attribute
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}