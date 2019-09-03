<?php
/* Copyright (C) 2018 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modAdvancedDiscount_AdvancedDiscountTriggers.class.php
 * \ingroup advanceddiscount
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modAdvancedDiscount_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 * - The constructor method must be named InterfaceMytrigger
 * - The name property name must be MyTrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for AdvancedDiscount module
 */
class InterfaceAdvancedDiscountTriggers extends DolibarrTriggers
{
	/**
	 * @var DoliDB Database handler
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "AdvancedDiscount triggers.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'advanceddiscount@advanceddiscount';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}


	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		global $mysoc;

        if (empty($conf->advanceddiscount->enabled)) return 0;     // Module not active, we do nothing

	    // Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

        $special_code = 107860;		// Id of module

        switch ($action) {

        	case 'LINEORDER_INSERT':
        	case 'LINEPROPAL_INSERT':
        	case 'LINEBILL_INSERT':
        		dol_include_once('advanceddiscount/class/advanceddiscount.class.php');
		        dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

		        // Nothing done if creation is a create from a clone
		        if (! empty($object->context['createfromclone']))
		        {
		        	return 0;
		        }
		        // Nothing done if creation is done from another object
		        if (! empty($object->origin) && $object->origin_id > 0)
		        {
		        	return 0;
		        }

		        if ($object->special_code == $special_code)
		        {
		        	// this is an insert of an advanced discount line, we do nothing
		        	return 0;
		        }

		        $now = dol_now();

		        $parentobject = null;
		        if ($action == 'LINEPROPAL_INSERT')
		        {
		        	include_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
		        	$parentobject = new Propal($this->db);
		        	$parentobject->fetch($object->fk_propal);
		        }
		        if ($action == 'LINEORDER_INSERT')
		        {
		        	include_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
		        	$parentobject = new Commande($this->db);
		        	$parentobject->fetch($object->fk_commande);
		        }
		        if ($action == 'LINEBILL_INSERT')
		        {
		        	include_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
		        	$parentobject = new Facture($this->db);
		        	$parentobject->fetch($object->fk_facture);
		        }

		        // If object is invoice and type is credit note
		        if ($parentobject->element == 'facture' && $parentobject->type == Facture::TYPE_CREDIT_NOTE)
		        {
		        	return 0;
		        }

		        $parentobject->fetch_thirdparty();

		        // Check if fixed promotion already present and remove it
		        $promotionalreadyfound = 0;
		        foreach($parentobject->lines as $line)
		        {
		        	//var_dump($line->desc);
		        	//var_dump(preg_quote($labelforpromotiontext, '/'));
		        	//var_dump(preg_match('/'.preg_quote($labelforpromotiontext, '/').'/ims', $line->desc));
		        	//if ($line->special_code == $special_code && preg_match('/'.preg_quote($labelforpromotiontext, '/').'/ims', $line->desc))
		        	if ($line->special_code == $special_code)
		        	{
		        		//var_dump('eeee');
		        		//var_dump($line->id);
		        		dol_syslog("The promotion line already exists, we remove it");
		        		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.$parentobject->table_element_line." WHERE rowid = ".$line->id;
		        		$resqldelete = $this->db->query($sql);
		        		if (! $resqldelete)
		        		{
		        			dol_print_error($this->db);
		        		}
		        		$parentobject->fetch_lines();

		        		$promotionalreadyfound++;
		        	}
		        }

		        $parentobject->fetch_lines();
		        $parentobject->update_price(0, 'none', 1);
		        //var_dump($parentobject->total_ht);

		        // Get all valid promotion
				$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'advanceddiscount_advanceddiscount';
				$sql.= " WHERE (date_start <= '".$this->db->idate($now)."' OR date_start IS NULL) AND (date_end >= '".$this->db->idate($now)."' OR date_end IS NULL)";
		        $resql=$this->db->query($sql);
		        if ($resql)
		        {
		            include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

		            $arrayofpromotionqualified = array();

		        	$num_rows = $this->db->num_rows($resql);
		        	$i=0;

		        	while ($i < $num_rows)
		        	{
		        		$obj=$this->db->fetch_object($resql);

		        		$advanceddiscount = new AdvancedDiscount($this->db);
		        		$advanceddiscount->fetch($obj->rowid);

		        		$ispromotionqualified = 0;
		        		$idoflinesforitem = array();
		        		foreach($advanceddiscount->arrayofrules as $rules)
		        		{
		        			if ($rules['type'] == 'customercountry')
							{
								if (strtolower($rules['value']) == strtolower($parentobject->thirdparty->country_code)) $ispromotionqualified++;
							}
							elseif ($rules['type'] == 'customercategory')
							{
								$category = new Categorie($this->db);
								if (is_numeric($rules['value'])) $result = $category->fetch($rules['value'], '', 'customer');
								else $result = $category->fetch(0, $rules['value'], 'customer');
								if ($result > 0)
								{
									$found = $category->containsObject('customer', $parentobject->thirdparty->id);
									if ($found) $ispromotionqualified++;
								}
							}
							elseif ($rules['type'] == 'containsproduct')
							{
								$parentobject->fetch_lines();

								$tmpprod=new Product($this->db);

								$foundatleastonelinewithproduct = 0;
								foreach($parentobject->lines as $line)
								{
									if ($line->fk_product > 0)
									{
										$tmpprod->ref='';
										$tmpprod->fetch($line->fk_product);
										if ($line->fk_product == $rules['value'] || $tmpprod->ref == $rules['value'])
										{
											$foundatleastonelinewithproduct++;
											$idoflinesforitem[] = $line->id;
											//break;	// Do not break here, we want all lines
										}
									}
								}

								if ($foundatleastonelinewithproduct)
								{
									$ispromotionqualified++;
								}
							}
							elseif ($rules['type'] == 'totalgreaterorequal')
							{
								//var_dump($parentobject->total_ht);exit;
								if ($parentobject->total_ht >= $rules['value']) $ispromotionqualified++;
							}
		        		}

		        		//var_dump($idoflinesforitem);
		        		//var_dump($ispromotionqualified."/".count($advanceddiscount->arrayofrules));

		        		dol_syslog("For promotion id ".$obj->rowid." we found ".$ispromotionqualified."/".count($advanceddiscount->arrayofrules)." conditions ok - idoflinesforitem=".join(',',$idoflinesforitem));
		        		//var_dump($ispromotionqualified);
		        		//var_dump(count($advanceddiscount->arrayofrules));
		        		if ($ispromotionqualified == count($advanceddiscount->arrayofrules))
		        		{
		        			$arrayofpromotionqualified[$obj->rowid] = $advanceddiscount;
		        		}

		        		$defaultvatrate = get_default_tva($mysoc, $parentobject->thirdparty);
		        		$defaultlocaltaxrate1 = get_default_localtax($mysoc, $parentobject->thirdparty, 1);
		        		$defaultlocaltaxrate2 = get_default_localtax($mysoc, $parentobject->thirdparty, 2);

		        		foreach($arrayofpromotionqualified as $id => $advanceddiscount)
		        		{
		        			$labelforpromotion = 'ADVANCEDDISCOUNT-'.$id;
		        			$labelforpromotiontext = '#'.$advanceddiscount->ref;

		        			//var_dump($advanceddiscount);exit;

		        			// Sort so we do action objectpercentagediscount before objectfixeddiscount
		        			$advanceddiscount->arrayofactions = dol_sort_array($advanceddiscount->arrayofactions, 'type', 'desc');

		        			$j = 0;
		        			foreach($advanceddiscount->arrayofactions as $actiondiscount)
		        			{
		        				//var_dump($actiondiscount['type']);
		        				$j++;
		        				dol_syslog("Apply action #".$j.", id ".$actiondiscount['id'].", type ".$actiondiscount['type']." for promotion id ".$id."-".$advanceddiscount->ref." on objet id=".$parentobject->id." ".$parentobject->ref, LOG_DEBUG, 1);

		        				if ($actiondiscount['type'] == 'objectfixeddiscount')
		        				{
		        					//var_dump(count($parentobject->lines));
		        					//var_dump($parentobject->total_ht);

		        					$amounttouse = $actiondiscount['value'];
		        					if (abs($amounttouse) > $parentobject->total_ht) $amounttouse = $parentobject->total_ht;
		        					$amounttouse = -1 * $amounttouse;

		        					$descriptionforpromotion = $langs->trans('Promotion').': '.$langs->trans("Code").' #'.$advanceddiscount->ref.' - '.$advanceddiscount->label;
		        					if ($action == 'LINEPROPAL_INSERT')
		        					{
		        						$idoffixeddiscount = $parentobject->addline($descriptionforpromotion, $amounttouse, 1, $defaultvatrate, $defaultlocaltaxrate1, $defaultlocaltaxrate2, 0, 0, 'HT', 0, 0, 1, -1, $special_code, 0, 0, 0, '');
		        					}
		        					if ($action == 'LINEORDER_INSERT')
		        					{
		        						$idoffixeddiscount = $parentobject->addline($descriptionforpromotion, $amounttouse, 1, $defaultvatrate, $defaultlocaltaxrate1, $defaultlocaltaxrate2, 0, 0, 0, 0, 'HT', 0, '', '', 1, -1, $special_code, 0, 0, 0, '');
		        					}
		        					if ($action == 'LINEBILL_INSERT')
		        					{
		        						$idoffixeddiscount = $parentobject->addline($descriptionforpromotion, $amounttouse, 1, $defaultvatrate, $defaultlocaltaxrate1, $defaultlocaltaxrate2, 0, 0, '', '', 0, 0, 0, 'HT', 0, 1, -1, $special_code, '', 0, 0, 0, 0, '');
		        					}
		        				}
		        				elseif ($actiondiscount['type'] == 'objectpercentagediscount')
		        				{
									// Loop on each line to apply the % discount
		        					foreach($parentobject->lines as $line)
		        					{
		        						if ($line->special_code != $special_code && $line->remise_percent < $actiondiscount['value'])
	        							{
	        								//$line->remise_percent = $actiondiscount['value'];
	        								if ($action == 'LINEPROPAL_INSERT')
	        								{
	        									//$line->update(1);
	        									$parentobject->updateline($line->id, $line->subprice, $line->qty, $actiondiscount['value'], $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc, 'HT', $line->info_bits, $line->special_code, $line->fk_parent_line, 0, 0, $line->pa_ht, $line->label, $line->type, $line->date_start, $line->date_end, 0, $line->fk_unit, $line->multicurrency_subprice, 1);
	        								}
	        								if ($action == 'LINEORDER_INSERT')
	        								{
	        									//$line->update($user, 1);
	        									$parentobject->updateline($line->id, $line->desc, $line->subprice, $line->qty, $actiondiscount['value'], $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits, $line->date_start, $line->date_end, $line->type, $line->fk_parent_line, 0, null, $line->pa_ht, $line->label, $line->special_code, 0, $line->fk_unit, $line->multicurrency_subprice, 1);
	        								}
	        								if ($action == 'LINEBILL_INSERT')
	        								{
	        									//$line->update(1);
												$parentobject->updateline($line->id, $line->desc, $line->subprice, $line->qty, $actiondiscount['value'], $line->date_start, $line->date_end, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits, $line->type, $line->fk_parent_line, 0, null, $line->pa_ht, $line->label, $line->special_code, 0, 100, $line->fk_unit, $line->multicurrency_subprice, 1);
	        								}
	        							}
		        					}
		        				}
		        				/*elseif ($actiondiscount['type'] == 'itemfixeddiscount')
		        				{
		        					if (empty($idoflineforitem))
		        					{
		        						dol_print_error($this->db, 'Error no item line found to use for the advanceddiscount');
		        						return 0;
		        					}

		        					// Loop on each line to apply the % discount
		        					foreach($parentobject->lines as $line)
		        					{
		        						if ($line->id == $idoflineforitem)
		        						{
		        							//$idoflineforproduct
		        							$line->;
		        							$line->update(0);
		        						}
		        					}
		        				}*/
		        				elseif ($actiondiscount['type'] == 'itempercentagediscount')
		        				{
		        					if (empty($idoflinesforitem))
		        					{
		        						dol_print_error($this->db, 'Error no item line found to use for the advanceddiscount');
		        						return 0;
		        					}

		        					// Loop on each line to apply the % discount
		        					foreach($parentobject->lines as $line)
		        					{
		        						if (in_array($line->id, $idoflinesforitem))
		        						{
		        							if ($line->special_code != $special_code && $line->remise_percent < $actiondiscount['value'])
		        							{
		        								//var_dump('Fix line '.$line->id);
		        								//$line->remise_percent = $actiondiscount['value'];
		        								if ($action == 'LINEPROPAL_INSERT')
		        								{
		        									//$line->update(1);
		        									$parentobject->updateline($line->id, $line->subprice, $line->qty, $actiondiscount['value'], $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc, 'HT', $line->info_bits, $line->special_code, $line->fk_parent_line, 0, 0, $line->pa_ht, $line->label, $line->type, $line->date_start, $line->date_end, 0, $line->fk_unit, $line->multicurrency_subprice, 1);
		        								}
		        								if ($action == 'LINEORDER_INSERT')
		        								{
		        									//$line->update($user, 1);
		        									$parentobject->updateline($line->id, $line->desc, $line->subprice, $line->qty, $actiondiscount['value'], $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits, $line->date_start, $line->date_end, $line->type, $line->fk_parent_line, 0, null, $line->pa_ht, $line->label, $line->special_code, 0, $line->fk_unit, $line->multicurrency_subprice, 1);
		        								}
		        								if ($action == 'LINEBILL_INSERT')
		        								{
		        									//$line->update(1);
		        									$parentobject->updateline($line->id, $line->desc, $line->subprice, $line->qty, $actiondiscount['value'], $line->date_start, $line->date_end, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits, $line->type, $line->fk_parent_line, 0, null, $line->pa_ht, $line->label, $line->special_code, 0, 100, $line->fk_unit, $line->multicurrency_subprice, 1);
		        								}
		        							}
		        						}
		        					}
		        				}

		        				// We redo fetch_lines after each action to be sure to have array of lines up to date
		        				$parentobject->fetch_lines();

		        				//var_dump($parentobject->total_ht);
		        				dol_syslog("Apply action #".$j." end", LOG_DEBUG, -1);
		        			}
		        		}

		        		$i++;
		        	}
		        }
		        else dol_print_error($this->db);

		        break;
		    }


		return 0;
	}
}
