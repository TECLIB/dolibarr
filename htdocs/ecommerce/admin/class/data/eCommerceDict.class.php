<?php
/*
 * @module		ECommerce
 * @version		1.0
 * @copyright	Auguria
 * @author		<franck.charpentier@auguria.net>
 * @licence		GNU General Public License
 */

/**
 * Class data access to dict
 */
class eCommerceDict
{
	private $db;
	private $table;
	
    function eCommerceDict($db, $table)
    {
    	$this->db = $db;
    	$this->table = $table;
        return 1;
    }
    
    /**
     * Get object from database
     * @param $code string 
     * @return array of table fields values
     */
    public function fetchByCode($code)
	{
		$object = array();
		$sql = "SELECT * FROM `".$this->table."` WHERE `code` = '".$code."'";
		$result = $this->db->query($sql);
		if ($result)
		{
			$numRows = $this->db->num_rows($result);
			if ($numRows == 1)
			{
				$obj = $this->db->fetch_object($result);
				if (count($obj))
				{
					foreach ($obj as $field=>$value)
					{
						$object[$field] = $value;
					}
				}
			}
			elseif ($numRows > 1)
				$object = false;
		}
		return $object;
    }
    /**
     * Get all lines from database
     * @return array
     */
    public function getAll()
    {
    	$lines = array();
		$sql = "SELECT * FROM `".$this->table."`";
		$result = $this->db->query($sql);
		if ($result)
		{
			$numRows = $this->db->num_rows($result);
			if ($numRows > 0)
			{
				while($obj = $this->db->fetch_object($result))
				{
					$line = array();
					if (count($obj))
					{
						foreach ($obj as $field=>$value)
						{
							$line[$field] = $value;
						}
					}
					$lines[] = $line;
				}
			}
		}
		return $lines;
    }
    
    /**
     * Get the value of ECOMMERCE_COMPANY_ANONYMOUS from db
     * @return int > 0 if OK, 0 if KO
     */
    /*public function getAnonymousConstValue()
    {
    	$sql = "SELECT value FROM ".$this->table." WHERE name='ECOMMERCE_COMPANY_ANONYMOUS'";
    	$result = -1;
    	$resql = $this->db->query($sql);
    	if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			$result = $obj->value;
		}
		return $result;
    }*/
    
}

