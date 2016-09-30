<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Config_AttributeFilterSuggestions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_itemRenderer = null;

    public function __construct()
    {
        $this->addColumn('attribute_code', array(
            'label' => Mage::helper('integernet_solr')->__('Attribute'),
            'style' => 'width:120px',
            'renderer' => $this->_getRenderer(),
        ));
        $this->addColumn('max_number_suggestions', array(
            'label' => Mage::helper('integernet_solr')->__('Maximum number of suggestions'),
            'style' => 'width:60px',
            'class' => 'validate-number validate-zero-or-greater',
        ));
        $this->addColumn('sorting', array(
            'label' => Mage::helper('integernet_solr')->__('Sorting'),
            'style' => 'width:60px',
            'class' => 'validate-number validate-zero-or-greater',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('integernet_solr')->__('Add');
        parent::__construct();
    }

    /**
     * @return IntegerNet_Solr_Block_Config_Adminhtml_Form_Field_Attribute
     */
    protected function  _getRenderer() {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = Mage::app()->getLayout()->createBlock(
                'integernet_solr/config_adminhtml_form_field_attribute', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()->calcOptionHash($row->getData('attribute_code')),
            'selected="selected"'
        );
    }

}
