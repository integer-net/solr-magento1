<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Block_Config_Description extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->getLayout()
            ->createBlock('integernet_solr/config_status', 'integernet_solr_config_status')
            ->setTemplate('integernet/solr/config/status.phtml')
            ->toHtml();
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $html = '<tr id="row_' . $id . '">'
            . '<td colspan="3">' . $this->_getElementHtml($element) . '</td>';


        $html.= '<td></td>';
        $html.= '</tr>';
        return $html;
    }
}