<?php
/*
 * @module		ECommerce
 * @version		1.0
 * @copyright	Auguria
 * @author		<anthony.poiret@auguria.net>
 * @licence		GNU General Public License
 */

/**
 *      \file       dev/skeletons/skeleton_class.class.php
 *      \ingroup    mymodule othermodule1 othermodule2
 *      \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *		\version    $Id: skeleton_class.class.php,v 1.29 2010/04/29 14:54:13 grandoc Exp $
 *		\author		Put author name here
 *		\remarks	Put here some comments
 */
require_once(DOL_DOCUMENT_ROOT ."/contact/class/contact.class.php");


/**
 *	\class      Contact
 *	\brief      Classe surchargeant les contact
 */
class auguriaContact extends Contact
{
        /** 
	 * \brief		Function to check if a contact informations passed by params exists in DB. 
	 * \return		id of first contact corresponding if OK, -1 if KO
	 */
	function getIdFromInfos()
	{	
		$contactId = -1;
		
		$this->name 	= $this->name 		? $this->name : "";
		$this->firstname= $this->firstname 	? $this->firstname : "";
		$this->address 	= $this->address 	? $this->address : "";
		$this->ville 	= $this->ville 		? $this->ville : "";
		$this->cp		= $this->cp			? $this->cp : "";
		$this->phone_pro= $this->phone_pro 	? $this->phone_pro : "";
		$this->fax 		= $this->fax 		? $this->fax : "";
		
		$sql  = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'socpeople';
		$sql .= ' WHERE name="'.trim($this->name).'"';
		$sql .= ' AND firstname="'.trim($this->firstname).'"';
		$sql .= ' AND address="'.trim($this->address).'"';
		$sql .= ' AND ville="'.trim($this->ville).'"';
		$sql .= ' AND cp="'.trim($this->cp).'"';
		$sql .= ' AND phone="'.trim($this->phone_pro).'"';
		$sql .= ' AND fax="'.trim($this->fax).'"';
		$sql .= ' AND fk_soc="'.$this->socid.'";';
		
		$resql = $this->db->query($sql);
		
		if($resql)
		{			
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);
				
				$contactId = $obj->rowid;				
			}
			else 
			{
				$this->error=$this->db->error();
				dol_syslog("Contact::getContactFromInfos ".$this->error, LOG_ERR);
			}
			$this->db->free($resql);
			
		}
		return $contactId;		
	}
}
?>
