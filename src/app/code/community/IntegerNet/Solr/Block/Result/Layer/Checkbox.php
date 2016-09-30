<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

/**
 * Class IntegerNet_Solr_Block_Result_Layer_Checkbox
 *
 * @method boolean getIsChecked()
 * @method IntegerNet_Solr_Block_Result_Layer_Checkbox setIsChecked(boolean $value)
 * @method boolean getIsTopNav()
 * @method IntegerNet_Solr_Block_Result_Layer_Checkbox setIsTopNav(boolean $value)
 * @method int getOptionId()
 * @method IntegerNet_Solr_Block_Result_Layer_Checkbox setOptionId(int $value)
 * @method string getAttributeCode()
 * @method IntegerNet_Solr_Block_Result_Layer_Checkbox setAttributeCode(string $value)
 */
class IntegerNet_Solr_Block_Result_Layer_Checkbox extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('integernet/solr/filter/checkbox.phtml');
    }
}