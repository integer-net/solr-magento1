<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Resource_Catalog_Product_Type_Configurable extends Mage_Catalog_Model_Resource_Product_Type_Configurable
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
        $select = $this->_getReadAdapter()->select()
            ->from(array('l' => $this->getMainTable()), array('product_id', 'parent_id'))
            ->join(
                array('e' => $this->getTable('catalog/product')),
                'e.entity_id = l.product_id AND e.required_options = 0',
                array()
            );
        if (!is_null($parentIds)) {
            $select->where('parent_id IN (?)', $parentIds);
        }

        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            $childrenIds[$row['parent_id']][$row['product_id']] = $row['product_id'];
        }

        return $childrenIds;
    }
}