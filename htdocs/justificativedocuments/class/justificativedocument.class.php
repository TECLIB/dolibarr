<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/justificativedocument.class.php
 * \ingroup     justificativedocuments
 * \brief       This file is a CRUD class file for JustificativeDocument (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for JustificativeDocument
 */
class JustificativeDocument extends CommonObject
{
	/**
	 * @var string ID to identify managed object (field 'elementtype' into llx_actioncomm)
	 */
	public $element = 'justificativedocuments_justificativedocument';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'justificativedocuments_justificativedocument';

	/**
	 * @var int  Does justificativedocument support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 *
	 * Note: To allow sharing on an external object, you must set MULTICOMPANY_EXTERNAL_MODULES_SHARING=myobject
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int  Does justificativedocument support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for justificativedocument. Must be the part after the 'object_' into object_justificativedocument.png
	 */
	public $picto = 'justificativedocument@justificativedocuments';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_APPROVED = 2;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arrayofkeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'index'=>1, 'comment'=>"Id"),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>5, 'notnull'=>1, 'visible'=>0, 'default'=>'1', 'index'=>1,),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'fk_type' => array('type'=>'integer:JustificativeType:justificativedocuments/class/justificativetype.class.php:0:(active:=:1)', 'label'=>'Type', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1,),
		'date_start' => array('type'=>'date', 'label'=>'DateStart', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1,),
		'date_end' => array('type'=>'date', 'label'=>'DateEnd', 'enabled'=>'1', 'position'=>32, 'notnull'=>0, 'visible'=>1,),
		'amount' => array('type'=>'price', 'label'=>'Amount', 'enabled'=>'1', 'position'=>35, 'notnull'=>0, 'visible'=>1, 'isameasure'=>'1',),
		'percent_reimbursed' => array('type'=>'double(24,8)', 'label'=>'PercentReimbursed', 'enabled'=>'1', 'position'=>35, 'notnull'=>0, 'visible'=>1,),
		'fk_user' => array('type'=>'integer:User:user/class/user.class.php:0:((statut:=:1) AND (entity:IN:__SHARED_ENTITIES__))', 'label'=>'User', 'enabled'=>'1', 'position'=>35, 'notnull'=>1, 'visible'=>1, 'foreignkey'=>'user.rowid',),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>-1, 'visible'=>0,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>-1, 'visible'=>0,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>-1, 'visible'=>-2,),
		'date_validation' => array('type'=>'datetime', 'label'=>'DateValidation', 'enabled'=>'1', 'position'=>502, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'fk_user_valid' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserValidation', 'enabled'=>'1', 'position'=>512, 'notnull'=>0, 'visible'=>-2,),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>3,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>2, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '2'=>'Approuv&eacute;', '9'=>'Annul&eacute;'),),
	);
	public $rowid;
	public $entity;
	public $ref;
	public $fk_type;
	public $date_start;
	public $date_end;
	public $amount;
	public $percent_reimbursed;
	public $fk_user;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $date_validation;
	public $fk_user_creat;
	public $fk_user_modif;
	public $fk_user_valid;
	public $description;
	public $import_key;
	public $status;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'justificativedocuments_justificativedocumentline';

	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_justificativedocument';

	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'JustificativeDocumentline';

	/**
	 * @var array	List of child tables. To test if we can delete object.
	 */
	//protected $childtables=array();

	/**
	 * @var array	List of child tables. To know object to delete on cascade.
	 */
	//protected $childtablesoncascade=array('justificativedocuments_justificativedocumentdet');

	/**
	 * @var JustificativeDocumentLine[]     Array of subtable lines
	 */
	//public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs, $user;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']=0;

		if ($user->hasRight('justificativedocuments', 'justificativedocument', 'approve')) {
		    $this->fields['percent_reimbursed']['visible'] = 1;
		    $this->fields['percent_reimbursed']['noteditable'] = 0;
		}

		// Unset fields that are disabled
		foreach($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		foreach($this->fields as $key => $val)
		{
			if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval']))
			{
				foreach($val['arrayofkeyval'] as $key2 => $val2)
				{
					$this->fields[$key]['arrayofkeyval'][$key2]=$langs->trans($val2);
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
	    $error = 0;

	    dol_syslog(__METHOD__, LOG_DEBUG);

	    $object = new self($this->db);

	    $this->db->begin();

	    // Load source object
	    $result = $object->fetchCommon($fromid);
	    if ($result > 0 && ! empty($object->table_element_line)) $object->fetchLines();

	    // get lines so they will be clone
	    //foreach($this->lines as $line)
	    //	$line->fetch_optionals();

	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);


	    // Clear fields
	    $object->ref = "copy_of_".$object->ref;
	    $object->title = $langs->trans("CopyOf")." ".$object->title;
	    // ...
	    // Clear extrafields that are unique
	    if (is_array($object->array_options) && count($object->array_options) > 0)
	    {
	    	$extrafields->fetch_name_optionals_label($this->table_element);
	    	foreach($object->array_options as $key => $option)
	    	{
	    		$shortkey = preg_replace('/options_/', '', $key);
	    		if (! empty($extrafields->attributes[$this->element]['unique'][$shortkey]))
	    		{
	    			//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
	    			unset($object->array_options[$key]);
	    		}
	    	}
	    }

	    // Create clone
		$object->context['createfromclone'] = 'createfromclone';
	    $result = $object->createCommon($user);
	    if ($result < 0) {
	        $error++;
	        $this->error = $object->error;
	        $this->errors = $object->errors;
	    }

	    if (! $error)
	    {
	    	// copy internal contacts
	    	if ($this->copy_linked_contact($object, 'internal') < 0)
	    	{
	    		$error++;
	    	}
	    }

	    if (! $error)
	    {
	    	// copy external contacts if same company
	    	if (property_exists($this, 'socid') && $this->socid == $object->socid)
	    	{
	    		if ($this->copy_linked_contact($object, 'external') < 0)
	    			$error++;
	    	}
	    }

	    unset($object->context['createfromclone']);

	    // End
	    if (!$error) {
	        $this->db->commit();
	        return $object;
	    } else {
	        $this->db->rollback();
	        return -1;
	    }
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines=array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  string      $filter       Filter USF.
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$records=array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key . ' = '. (int) $value;
				}
				elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				}
				else {
					$sqlwhere[] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND (' . implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .=  ' ' . $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
            $i = 0;
			while ($i < min($limit, $num))
			{
			    $obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0)
		{
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate bom
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
	    global $conf, $langs;

	    require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	    $error = 0;

	    // Protection
	    if ($this->statut == self::STATUS_VALIDATED)
	    {
	        dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
	        return 0;
	    }

	    /*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocument->create))
	     || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificatevidedocument->justificateivedocument_advance->validate))))
	     {
	     $this->error='NotEnoughPermissions';
	     dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
	     return -1;
	     }*/

	    $now = dol_now();

	    $this->db->begin();

	    // Define new ref
	    if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
	    {
	        $num = $this->getNextNumRef();
	    }
	    else
	    {
	        $num = $this->ref;
	    }
	    $this->newref = $num;

	    // Validate
	    $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
	    $sql .= " SET ref = '".$this->db->escape($num)."',";
	    $sql .= " status = ".self::STATUS_VALIDATED.",";
	    $sql .= " date_validation = '".$this->db->idate($now)."',";
	    $sql .= " fk_user_valid = ".$user->id;
	    $sql .= " WHERE rowid = ".((int) $this->id);

	    dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
	    $resql = $this->db->query($sql);
	    if (!$resql)
	    {
	        dol_print_error($this->db);
	        $this->error = $this->db->lasterror();
	        $error++;
	    }

	    if (!$error && !$notrigger)
	    {
	        // Call trigger
	        $result = $this->call_trigger('JUSTIFICATIVEDOCUMENT_VALIDATE', $user);
	        if ($result < 0) $error++;
	        // End call triggers
	    }

	    if (!$error)
	    {
	        $this->oldref = $this->ref;

	        // Rename directory if dir was a temporary ref
	        if (preg_match('/^[\(]?PROV/i', $this->ref))
	        {
	            // Now we rename also files into index
	            $sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'justificativedocuments/".$this->db->escape($this->newref)."'";
	            $sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'justificativedocuments/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
	            $resql = $this->db->query($sql);
	            if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

	            // We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
	            $oldref = dol_sanitizeFileName($this->ref);
	            $newref = dol_sanitizeFileName($num);

	            $dirsource = $conf->justificativedocuments->dir_output.'/justificativedocument/'.$oldref;
	            $dirdest = $conf->justificativedocuments->dir_output.'/justificativedocument/'.$newref;
	            if (!$error && file_exists($dirsource))
	            {
	                dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

	                if (@rename($dirsource, $dirdest))
	                {
	                    dol_syslog("Rename ok");
	                    // Rename docs starting with $oldref with $newref
	                    $listoffiles = dol_dir_list($conf->justificativedocuments->dir_output.'/justificativedocument/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
	                    foreach ($listoffiles as $fileentry)
	                    {
	                        $dirsource = $fileentry['name'];
	                        $dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
	                        $dirsource = $fileentry['path'].'/'.$dirsource;
	                        $dirdest = $fileentry['path'].'/'.$dirdest;
	                        @rename($dirsource, $dirdest);
	                    }
	                }
	            }
	        }
	    }

	    // Set new ref and current status
	    if (!$error)
	    {
	        $this->ref = $num;
	        $this->status = self::STATUS_VALIDATED;
	    }

	    if (!$error)
	    {
	        $this->db->commit();
	        return 1;
	    }
	    else
	    {
	        $this->db->rollback();
	        return -1;
	    }
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->justificativedocument_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'JUSTIFICATIVEDOCUMENT_UNVALIDATE');
	}


	/**
	 *	Approve
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function approve($user, $notrigger = 0)
	{
	    // Protection
	    if ($this->status != self::STATUS_VALIDATED)
	    {
	        return 0;
	    }

	    /*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->write))
	     || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->justificativedocument_advance->validate))))
	     {
	     $this->error='Permission denied';
	     return -1;
	     }*/

	    return $this->setStatusCommon($user, self::STATUS_APPROVED, $notrigger, 'JUSTIFICATIVEDOCUMENT_APPROVE');
	}


	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->justificativedocument_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'JUSTIFICATIVEDOCUMENT_CLOSE');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->justificativedocuments->justificativedocument_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'JUSTIFICATIVEDOCUMENT_REOPEN');
	}

    /**
     *  Return a link to the object card (with optionaly the picto)
     *
     *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
     *  @param  string  $option                     On what the link point to ('nolink', ...)
     *  @param  int     $notooltip                  1=Disable tooltip
     *  @param  string  $morecss                    Add more css on link
     *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
     *  @return	string                              String with URL
     */
    public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
    {
        global $conf, $langs, $hookmanager;

        if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

        $result = '';

        $label = '<u>' . $langs->trans("JustificativeDocument") . '</u>';
        $label.= '<br>';
        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $url = dol_buildpath('/justificativedocuments/justificativedocument_card.php', 1).'?id='.$this->id;

        if ($option != 'nolink')
        {
            // Add param to save lastsearch_values or not
            $add_save_lastsearch_values=($save_lastsearch_value == 1 ? 1 : 0);
            if ($save_lastsearch_value == -1 && isset($_SERVER["PHP_SELF"]) && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values=1;
            if ($add_save_lastsearch_values) $url.='&save_lastsearch_values=1';
        }

        $linkclose='';
        if (empty($notooltip))
        {
            if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
            {
                $label=$langs->trans("ShowJustificativeDocument");
                $linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
            }
            $linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';
        }
        else $linkclose = ($morecss?' class="'.$morecss.'"':'');

		$linkstart = '<a href="'.$url.'"';
		$linkstart.=$linkclose.'>';
		$linkend='</a>';

		$result .= $linkstart;
		if ($withpicto) $result.=img_object(($notooltip?'':$label), ($this->picto?$this->picto:'generic'), ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
		if ($withpicto != 2) $result.= $this->ref;
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action,$hookmanager;
		$hookmanager->initHooks(array('justificativedocumentdao'));
		$parameters=array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook=$hookmanager->executeHooks('getNomUrl', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
    }

	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
	    if (empty($this->labelStatus) || empty($this->labelStatusShort))
	    {
	        global $langs;
	        $langs->load("trips");
	        $this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
	        $this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('ValidatedWaitingApproval');
	        $this->labelStatus[self::STATUS_APPROVED] = $langs->trans('Approved');
	        $this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
	        $this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
	        $this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Validated');
	        $this->labelStatusShort[self::STATUS_APPROVED] = $langs->trans('Approved');
	        $this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
	    }

	    $statusType = 'status'.$status;
	    if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
	    if ($status == self::STATUS_APPROVED) $statusType = 'status6';

	    return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql.= ' fk_user_creat, fk_user_modif';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' WHERE t.rowid = '.((int) $id);
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;

				$this->user_creation_id = $obj->fk_user_author;
				$this->user_modification_id   = $obj->fk_user_modif;

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
			}

			$this->db->free($result);
		}
		else
		{
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
	    $this->lines=array();

	    $objectline = new JustificativeDocumentLine($this->db);
	    $result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_justificativedocument = '.$this->id));

	    if (is_numeric($result))
	    {
	        $this->error = $this->error;
	        $this->errors = $this->errors;
	        return $result;
	    }
	    else
	    {
	        $this->lines = $result;
	        return $this->lines;
	    }
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf,$langs;

		$langs->load("justificativedocuments@justificativedocuments");

		if (! dol_strlen($modele)) {
			$modele = 'standard';

			if ($this->model_pdf) {
				$modele = $this->model_pdf;
			} elseif (! empty($conf->global->JUSTIFICATIVEDOCUMENT_ADDON_PDF)) {
				$modele = $conf->global->JUSTIFICATIVEDOCUMENT_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/justificativedocuments/doc/";

		//return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		return 1;
	}

	/**
	 *  Returns the reference to the following non used Order depending on the active numbering module
	 *  defined into JUSTIFICATIVEDOCUMENT_ADDON
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
	    global $langs, $conf;
	    $langs->load("justificativedocuments@justificativedocuments");

	    if (empty($conf->global->JUSTIFICATIVEDOCUMENT_ADDON)) {
	        $conf->global->JUSTIFICATIVEDOCUMENT_ADDON = 'mod_justificativedocument_standard';
	    }

	    if (!empty($conf->global->JUSTIFICATIVEDOCUMENT_ADDON))
	    {
	        $mybool = false;

	        $file = getDolGlobalString('JUSTIFICATIVEDOCUMENT_ADDON') . ".php";
	        $classname = $conf->global->JUSTIFICATIVEDOCUMENT_ADDON;

	        // Include file with class
	        $dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
	        foreach ($dirmodels as $reldir)
	        {
	            $dir = dol_buildpath($reldir."core/modules/justificativedocuments/");

	            // Load file with numbering class (if found)
	            $mybool |= @include_once $dir.$file;
	        }

	        if ($mybool === false)
	        {
	            dol_print_error('', "Failed to include file ".$file);
	            return '';
	        }

	        $obj = new $classname();
	        $numref = $obj->getNextValue($soc, $this);

	        if ($numref != "")
	        {
	            return $numref;
	        }
	        else
	        {
	            $this->error = $obj->error;
	            //dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
	            return "";
	        }
	    }
	    else
	    {
	        print $langs->trans("Error")." ".$langs->trans("Error_JUSTIFICATIVEDOCUMENT_ADDON_NotDefined");
	        return "";
	    }
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error='';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}

/**
 * Class JustificativeDocumentLine. You can also remove this and generate a CRUD class for lines objects.
 */
class JustificativeDocumentLine
{
	// To complete with content of an object JustificativeDocumentLine
	// We should have a field rowid, fk_justificativedocument and position
}
