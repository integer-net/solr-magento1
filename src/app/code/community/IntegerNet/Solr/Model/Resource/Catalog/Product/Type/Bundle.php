<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Resource_Catalog_Product_Type_Bundle extends Mage_Bundle_Model_Resource_Selection
{
    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param null|int[] $parentIds
     * @return int[][]
     */
    public function getChildrenIdsForMultipleParents($parentIds)
    {
        $childrenIds = array();
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(
                array('tbl_selection' => $this->getMainTable()),
                array('product_id', 'parent_product_id', 'option_id')
            )
            ->join(
                array('e' => $this->getTable('catalog/product')),
                'e.entity_id = tbl_selection.product_id AND e.required_options=0',
                array()
            )
            ->join(
                array('tbl_option' => $this->getTable('bundle/option')),
                'tbl_option.option_id = tbl_selection.option_id',
                array('required')
            );
        if (!is_null($parentIds)) {
            $select->where('tbl_selection.parent_product_id IN (?)', $parentIds);
        }

        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            $childrenIds[$row['parent_product_id']][$row['product_id']] = $row['product_id'];
        }

        return $childrenIds;
    }
}