<?php
/* Copyright (C) 2005-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2009-2011 Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2011	   Juanjo Menent        <jmenent@2byte.es>
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
 *	\file       htdocs/forceproject/core/modules/triggers/interface_50_modProject_ForceProject.class.php
 *  \ingroup    forceproject
 *  \brief      Trigger file for forceproject module
 */


/**
 *  Class of triggered functions for forceproject module
 */
class InterfaceForceProject
{
    var $db;
    var $error;

    var $date;
    var $duree;
    var $texte;
    var $desc;

    /**
     *	Constructor
     *
     *  @param	DoliDB	$db		Database handler
     */
	function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "system";
        $this->description = "Triggers of this module will check a project is linked to validated element. It may also replace _projectref_ with ref of linked project.";
        $this->picto = 'project';
    }

    /**
     *   Return name of trigger file
     *
     *   @return     string      Name of trigger file
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   Return description of trigger file
     *
     *   @return     string      Description of trigger file
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Return version of trigger file
     *
     *   @return     string      Version of trigger file
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      Function called when a Dolibarrr business event is done.
     *      All functions "run_trigger" are triggered if file is inside directory htdocs/core/triggers
     *      Following properties must be filled:
     *      $object->actiontypecode (translation action code: AC_OTH, ...)
     *      $object->actionmsg (note, long text)
     *      $object->actionmsg2 (label, short text)
     *      $object->sendtoid (id of contact)
     *      $object->socid
     *      Optionnal:
     *      $object->fk_element
     *      $object->elementtype
     *
     *      @param	string	$action     Event code (COMPANY_CREATE, PROPAL_VALIDATE, ...)
     *      @param  Object	$object     Object action is done on
     *      @param  User	$user       Object user
     *      @param  Langs	$langs      Object langs
     *      @param  Conf	$conf       Object conf
     *      @return int         		<0 if KO, 0 if no action are done, >0 if OK
     */
    function run_trigger($action,$object,$user,$langs,$conf)
    {
		$ok=0;

		// Actions
        if ($action == 'PROPAL_VALIDATE' && (! empty($conf->global->FORCEPROJECT_ON_PROPOSAL) || ! empty($conf->global->FORCEPROJECT_ON_ALL)))
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

            if (empty($object->fk_project))
            {
				$langs->load("forceproject@forceproject");	// So files is loaded for function to show error message
            	$this->errors[]=$langs->trans("ProposalMustBeLinkedToProject");
            	return -1;
            }
            else
			{
	            $object->fetch_projet();
	            $projectid=$object->projet->id;
	            $projectref=$object->projet->ref;

	            $sql="SELECT ref FROM ".MAIN_DB_PREFIX."propal WHERE rowid=".$object->id;
	            $resql=$this->db->query($sql);
	            if ($resql)
	            {
	            	$obj=$this->db->fetch_object($resql);
	            	$newref=$obj->ref;
	            	$newref=preg_replace('/projectref/',$projectref,$newref);
	            	$newref=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$newref);  // When mask is  ...{PROJECTREF-9}... for example
	            	$newref=preg_replace('/%%+/',$projectref,$newref);

	            	// If we want the counter to start to 1 for each project
	            	if (! empty($conf->global->FORCEPROJECT_COUNTER_FOREACH_PROJECT))
	            	{
	            		// Clean current ref of propal, so we can make again later a getNextNumRef and get same value for invoice number
	            		$sql="UPDATE ".MAIN_DB_PREFIX."propal SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
	            		$resql=$this->db->query($sql);
	            		
		            	$savmask=$conf->global->PROPALE_SAPHIR_MASK;
		            	$conf->global->PROPALE_SAPHIR_MASK=preg_replace('/projectref/',$projectref,$conf->global->PROPALE_SAPHIR_MASK);	// For proposal, counter is started to 1 for each project
						//var_dump($conf->global->PROPALE_SAPHIR_MASK);
		            	$newref=$object->getNextNumRef($object->thirdparty);
		            	//var_dump($newref);
		            	//$newref=$projectref.substr($newref,7);
		            	$conf->global->PROPALE_SAPHIR_MASK=$savmask;
				       	//var_dump($newref); exit;
	            	}
	            	dol_syslog("We validate proposal ".$object->id." oldref=".$object->ref." newref=".$newref." projectid=".$projectid." projectref=".$projectref);

		            $sql="UPDATE ".MAIN_DB_PREFIX."propal SET ref = '".$this->db->escape($newref)."' WHERE rowid=".$object->id;
					dol_syslog("sql=".$sql);
	            	$resql=$this->db->query($sql);

	            	if ($resql)
	            	{
	            		$object->ref=$newref;
	            		$ok=1;
	            	}
	            	else
	            	{
	            		$this->errors[]=$this->db->lasterror();
	            		$ok=-1;
	            	}
	            }
	            else
				{
					dol_print_error($this->db);
					$ok=-1;
				}
			}
        }

    	// Actions
        if ($action == 'ORDER_VALIDATE' && (! empty($conf->global->FORCEPROJECT_ON_ORDER) || ! empty($conf->global->FORCEPROJECT_ON_ALL)))
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

            if (empty($object->fk_project))
            {
				$langs->load("forceproject@forceproject");	// So files is loaded for function to show error message
            	$this->errors[]=$langs->trans("OrderMustBeLinkedToProject");
            	return -1;
            }
            else
			{
	            $object->fetch_projet();
	            $projectid=$object->projet->id;
	            $projectref=$object->projet->ref;

	            $sql="SELECT ref FROM ".MAIN_DB_PREFIX."commande WHERE rowid=".$object->id;
	            $resql=$this->db->query($sql);
	            if ($resql)
	            {
	            	$obj=$this->db->fetch_object($resql);
	            	$newref=$obj->ref;
	            	$newref=preg_replace('/projectref/',$projectref,$newref);
	            	$newref=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$newref);  // When mask is  ...{PROJECTREF-9}... for example
	            	$newref=preg_replace('/%%+/',$projectref,$newref);

	            	// If we want the counter to start to 1 for each project
	            	if (! empty($conf->global->FORCEPROJECT_COUNTER_FOREACH_PROJECT))
	            	{
	            		// Clean current ref of invoice, so we can make again later a getNextNumRef and get same value for invoice number
	            		$sql="UPDATE ".MAIN_DB_PREFIX."commande SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
	            		$resql=$this->db->query($sql);
	            		
		            	$savmask=$conf->global->COMMANDE_SAPHIR_MASK;
		            	$conf->global->COMMANDE_SAPHIR_MASK=preg_replace('/projectref/',$projectref,$conf->global->COMMANDE_SAPHIR_MASK);
		            	$newref=$object->getNextNumRef($object->thirdparty);
		            	//$newref=$projectref.substr($newref,7);
		            	$conf->global->COMMANDE_SAPHIR_MASK=$savmask;
		            	//var_dump($newref); exit;
	            	}
	            	dol_syslog("We validate order ".$object->id." oldref=".$object->ref." newref=".$newref." projectid=".$projectid." projectref=".$projectref);

		            $sql="UPDATE ".MAIN_DB_PREFIX."commande SET ref = '".$this->db->escape($newref)."' WHERE rowid=".$object->id;
					dol_syslog("sql=".$sql);
	            	$resql=$this->db->query($sql);

	            	if ($resql)
	            	{
	            		$object->ref=$newref;
	            		$ok=1;
	            	}
	            	else
	            	{
	            		$this->errors[]=$this->db->lasterror();
	            		$ok=-1;
	            	}
	            }
	            else
				{
					dol_print_error($this->db);
					$ok=-1;
				}
			}
        }

        if ($action == 'BILL_VALIDATE' && (! empty($conf->global->FORCEPROJECT_ON_INVOICE) || ! empty($conf->global->FORCEPROJECT_ON_ALL)))
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

            if (empty($object->fk_project) || $object->fk_project < 0)
            {
				$langs->load("forceproject@forceproject");	// So files is loaded for function to show error message
            	$this->errors[]=$langs->trans("InvoiceMustBeLinkedToProject");
            	return -1;
            }
            else
            {
                $object->fetch_projet();
                $projectid=$object->projet->id;
                $projectref=$object->projet->ref;

                $sql="SELECT facnumber as ref FROM ".MAIN_DB_PREFIX."facture WHERE rowid=".$object->id;

                $resql=$this->db->query($sql);
                if ($resql)
                {
                    $obj=$this->db->fetch_object($resql);
                    $newref=$obj->ref;
                    $newref=preg_replace('/projectref/',$projectref,$newref);
                    $newref=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$newref);  // When mask is  ...{PROJECTREF-9}... for example
                    $newref=preg_replace('/%%+/',$projectref,$newref);

                    // If we want the counter of new invoice to start to 1 for each project 
                    // The tag {PROJECTREF\-[1-9]\} must be present into ref numbering mask to have this working.
                    if (! empty($conf->global->FORCEPROJECT_COUNTER_FOREACH_PROJECT))
                    {
                        if ($object->type == 1)
                        {
                        	// Clean current ref of invoice, so we can make again later a getNextNumRef and get same value for invoice number
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET facnumber = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
                        	$resql=$this->db->query($sql);

                        	$savmask=$conf->global->FACTURE_MERCURE_MASK_REPLACEMENT;
                            $conf->global->FACTURE_MERCURE_MASK_REPLACEMENT=preg_replace('/projectref/',$projectref,$conf->global->FACTURE_MERCURE_MASK_REPLACEMENT);
                            $conf->global->FACTURE_MERCURE_MASK_REPLACEMENT=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$conf->global->FACTURE_MERCURE_MASK_REPLACEMENT);
                            $conf->global->FACTURE_MERCURE_MASK_REPLACEMENT=preg_replace('/%%+/',$projectref,$conf->global->FACTURE_MERCURE_MASK_REPLACEMENT);
                            $newref=$object->getNextNumRef($object->thirdparty);
                            //$newref=$projectref.substr($newref,7);
                            $conf->global->FACTURE_MERCURE_MASK_REPLACEMENT=$savmask;
                            //var_dump($newref); exit;
                        }
                        elseif ($object->type == 2)
                        {
                        	// Clean current ref of invoice, so we can make again later a getNextNumRef and get same value for invoice number
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET facnumber = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
                        	$resql=$this->db->query($sql);

                            $savmask=$conf->global->FACTURE_MERCURE_MASK_CREDIT;
                            $conf->global->FACTURE_MERCURE_MASK_CREDIT=preg_replace('/projectref/',$projectref,$conf->global->FACTURE_MERCURE_MASK_CREDIT);
                            $conf->global->FACTURE_MERCURE_MASK_CREDIT=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$conf->global->FACTURE_MERCURE_MASK_CREDIT);
                            $conf->global->FACTURE_MERCURE_MASK_CREDIT=preg_replace('/%%+/',$projectref,$conf->global->FACTURE_MERCURE_MASK_CREDIT);
                            $newref=$object->getNextNumRef($object->thirdparty);
                            //$newref=$projectref.substr($newref,7);
                            $conf->global->FACTURE_MERCURE_MASK_CREDIT=$savmask;
                            //var_dump($newref); exit;
                        }
                        elseif ($object->type == 3)
                        {
                        	// Clean current ref of invoice, so we can make again later a getNextNumRef and get same value for invoice number
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET facnumber = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
                        	$resql=$this->db->query($sql);

                            $savmask=$conf->global->FACTURE_MERCURE_MASK_DEPOSIT;
                            $conf->global->FACTURE_MERCURE_MASK_DEPOSIT=preg_replace('/projectref/',$projectref,$conf->global->FACTURE_MERCURE_MASK_DEPOSIT);
                            $conf->global->FACTURE_MERCURE_MASK_DEPOSIT=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$conf->global->FACTURE_MERCURE_MASK_DEPOSIT);
                            $conf->global->FACTURE_MERCURE_MASK_DEPOSIT=preg_replace('/%%+/',$projectref,$conf->global->FACTURE_MERCURE_MASK_DEPOSIT);
                            $newref=$object->getNextNumRef($object->thirdparty);
                            //$newref=$projectref.substr($newref,7);
                            $conf->global->FACTURE_MERCURE_MASK_DEPOSIT=$savmask;
                            //var_dump($newref); exit;
                        }
                        else
                        {
                        	// Clean current ref of invoice, so we can make again later a getNextNumRef and get same value for invoice number
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET facnumber = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
                        	$resql=$this->db->query($sql);

                            $savmask=$conf->global->FACTURE_MERCURE_MASK_INVOICE;
                            $conf->global->FACTURE_MERCURE_MASK_INVOICE=preg_replace('/projectref/',$projectref,$conf->global->FACTURE_MERCURE_MASK_INVOICE);
                            $conf->global->FACTURE_MERCURE_MASK_INVOICE=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$conf->global->FACTURE_MERCURE_MASK_INVOICE);
                            $conf->global->FACTURE_MERCURE_MASK_INVOICE=preg_replace('/%%+/',$projectref,$conf->global->FACTURE_MERCURE_MASK_INVOICE);
                            $newref=$object->getNextNumRef($object->thirdparty);
                            //$newref=$projectref.substr($newref,7);
                            $conf->global->FACTURE_MERCURE_MASK_INVOICE=$savmask;
                        }
                    }

                    dol_syslog("We validate order ".$object->id." oldref=".$object->ref." newref=".$newref." projectid=".$projectid." projectref=".$projectref);

                    $sql="UPDATE ".MAIN_DB_PREFIX."facture SET facnumber = '".$this->db->escape($newref)."' WHERE rowid=".$object->id;
                    dol_syslog("sql=".$sql);
                    $resql=$this->db->query($sql);

                    if ($resql)
                    {
                        $object->ref=$newref;
                        $ok=1;
                    }
                    else
                    {
                        $this->errors[]=$this->db->lasterror();
                        $ok=-1;
                    }
                }
                else
                {
                    dol_print_error($this->db);
                    $ok=-1;
                }
            }
        }

        // Suppliers


        if (($action == 'SUPPLIER_PROPOSAL_VALIDATE' || $action == 'PROPOSAL_SUPPLIER_VALIDATE') && (! empty($conf->global->FORCEPROJECT_ON_PROPOSAL_SUPPLIER) || ! empty($conf->global->FORCEPROJECT_ON_ALL)))
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

            if (empty($object->fk_project))
            {
				$langs->load("forceproject@forceproject");	// So files is loaded for function to show error message
            	$this->errors[]=$langs->trans("ProposalMustBeLinkedToProject");
            	return -1;
            }
        }

        if ($action == 'ORDER_SUPPLIER_VALIDATE' && (! empty($conf->global->FORCEPROJECT_ON_ORDER_SUPPLIER) || ! empty($conf->global->FORCEPROJECT_ON_ALL)))
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

            if (empty($object->fk_project))
            {
				$langs->load("forceproject@forceproject");	// So files is loaded for function to show error message
				$this->errors[]=$langs->trans("OrderMustBeLinkedToProject");
            	return -1;
            }
        }

        if ($action == 'BILL_SUPPLIER_VALIDATE' && (! empty($conf->global->FORCEPROJECT_ON_INVOICE_SUPPLIER) || ! empty($conf->global->FORCEPROJECT_ON_ALL)))
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

            if (empty($object->fk_project))
            {
				$langs->load("forceproject@forceproject");	// So files is loaded for function to show error message
            	$this->errors[]=$langs->trans("InvoiceMustBeLinkedToProject");
            	return -1;
            }
        }
        return $ok;
    }

}

