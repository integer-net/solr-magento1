<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_Solr_Model_Resource_Catalog_Product_Type_Grouped extends Mage_Catalog_Model_Resource_Product_Link
{
    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param null|int[] $parentIds
     * @param int $typeId
     * @return int[][]
     */
    public function getChildrenIdsForMultipleParents($parentIds, $typeId = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED)
    {
        $adapter     = $this->_getReadAdapter();
        $childrenIds = array();
        $bind        = array(
            ':link_type_id'  => (int)$typeId,
        );
        $select = $adapter->select()
            ->from(array('l' => $this->getMainTable()), array('product_id', 'linked_product_id'))
            ->where('link_type_id = :link_type_id');
        if (!is_null($parentIds)) {
            $bind[':product_ids'] = implode(',', $parentIds);
            $select->where('product_id IN (:product_ids)');
        }
        
        $result = $adapter->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[$row['product_id']][$row['linked_product_id']] = $row['linked_product_id'];
        }

        return $childrenIds;
    }
}