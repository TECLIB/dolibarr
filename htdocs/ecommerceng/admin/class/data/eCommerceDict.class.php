<?php
/* Copyright (C) 2010 Franck Charpentier - Auguria <franck.charpentier@auguria.net>
 * Copyright (C) 2013 Laurent Destailleur          <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
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
     *
     * @param   string    $code     Code
     * @return  array               Aray of table fields values
     */
    public function fetchByCode($code)
	{
		$object = array();
		$sql = "SELECT * FROM ".$this->table." WHERE code = '".$code."'";
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
     *
     * @param	string	$sqlfilters		Sql filters. Example 'WHERE fk_pays = 1'
     * @return array
     */
    public function getAll($sqlfilters='')
    {
    	$lines = array();
		$sql = "SELECT * FROM ".$this->table;
		if ($sqlfilters) $sql .= ' '.$sqlfilters;

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

