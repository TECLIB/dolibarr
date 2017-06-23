# Hack for Magento.

This hacks solve a bug into Magento making performance very low when trying to get last modified categories.

On Magento 1.9 side, edit the file app/code/core/Mage/Catalog/Model/Category/Api.php to add the line that is commented

    ...
 
    /**
     * Convert node to array
     *
     * @param Varien_Data_Tree_Node $node
     * @return array
     */
    protected function _nodeToArray(Varien_Data_Tree_Node $node)
    {
        // Only basic category data
        $result = array();
        $result['category_id'] = $node->getId();
        $result['parent_id']   = $node->getParentId();
        $result['name']        = $node->getName();
        $result['is_active']   = $node->getIsActive();
        $result['position']    = $node->getPosition();
        $result['level']       = $node->getLevel();
 
	/* FIX Add here this new line to add the missing property returned by web service */
	$result['updated_at']  = $node->getUpdatedAt();
 
        $result['children']    = array();
 
        foreach ($node->getChildren() as $child) {
            $result['children'][] = $this->_nodeToArray($child);
        }
 
        return $result;
    }
 