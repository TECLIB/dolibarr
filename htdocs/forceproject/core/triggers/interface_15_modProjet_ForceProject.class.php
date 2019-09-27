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
 *	\file       htdocs/forceproject/core/modules/triggers/interface_15_modProject_ForceProject.class.php
 *  \ingroup    forceproject
 *  \brief      Trigger file for forceproject module. This trigger must be called before the trigger
 *  			on actions and notifications.
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
     *      All functions "runTrigger" are triggered if file is inside directory htdocs/core/triggers
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
     *      @param	string	  $action     Event code (COMPANY_CREATE, PROPAL_VALIDATE, ...)
     *      @param  Object	  $object     Object action is done on
     *      @param  User	  $user       Object user
     *      @param  Translate $langs      Object langs
     *      @param  Conf	  $conf       Object conf
     *      @return int         		  <0 if KO, 0 if no action are done, >0 if OK
     */
    function runTrigger($action,$object,$user,$langs,$conf)
    {
		$ok=0;

		if (empty($conf->forceproject->enabled)) return 0;     // If module is not enabled, we do nothing

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

	            	// If this is the first time we set the counter and we want the counter to start to 1 for each project
	            	// The tag {PROJECTREF\-[1-9]\} must be present into ref numbering mask to have this working.
	            	if (preg_match('/\(PROV/', $object->ref) && ! empty($conf->global->FORCEPROJECT_COUNTER_FOREACH_PROJECT))
	            	{
	            		// Clean current ref of propal, so we can make again later a getNextNumRef and get same value for invoice number
	            		$sql="UPDATE ".MAIN_DB_PREFIX."propal SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
	            		$resql=$this->db->query($sql);

		            	$savmask=$conf->global->PROPALE_SAPHIR_MASK;
		            	$conf->global->PROPALE_SAPHIR_MASK=preg_replace('/projectref/',$projectref,$conf->global->PROPALE_SAPHIR_MASK);	// For proposal, counter is started to 1 for each project
		            	$conf->global->PROPALE_SAPHIR_MASK=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$conf->global->PROPALE_SAPHIR_MASK);
		            	$conf->global->PROPALE_SAPHIR_MASK=preg_replace('/%%+/',$projectref,$conf->global->PROPALE_SAPHIR_MASK);
		            	//var_dump($conf->global->PROPALE_SAPHIR_MASK);
		            	$newref=$object->getNextNumRef($object->thirdparty);
		            	//var_dump($newref);
		            	//$newref=$projectref.substr($newref,7);
		            	$conf->global->PROPALE_SAPHIR_MASK=$savmask;
				       	//var_dump($newref); exit;
	            	}
	            	dol_syslog("We validate proposal ".$object->id." oldref=".$object->ref." newref=".$newref." projectid=".$projectid." projectref=".$projectref);
                    $error = 0;


	            	// Rename directory if dir was a temporary ref
	            	if (preg_match('/^[\(]?PROV/i', $object->ref))
	            	{
	            	    // Now we rename also files into index
	            	    $sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($object->newref)."', SUBSTR(filename, ".(strlen($object->ref)+1).")), filepath = 'propale/".$this->db->escape($object->newref)."'";
	            	    $sql.= " WHERE filename LIKE '".$this->db->escape($object->ref)."%' AND filepath = 'propale/".$object->db->escape($object->ref)."' and entity = ".$conf->entity;
	            	    $resql = $this->db->query($sql);
	            	    if (! $resql) { $error++; $this->error = $this->db->lasterror(); }

	            	    // We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
	            	    $oldref = dol_sanitizeFileName($object->ref);
	            	    $newref = dol_sanitizeFileName($newref);
	            	    $dirsource = $conf->propal->multidir_output[$object->entity?$object->entity:$conf->entity].'/'.$oldref;
	            	    $dirdest = $conf->propal->multidir_output[$object->entity?$object->entity:$conf->entity].'/'.$newref;

	            	    if (! $error && file_exists($dirsource))
	            	    {
	            	        dol_syslog(get_class($this)."::validate rename dir ".$dirsource." into ".$dirdest);
	            	        if (@rename($dirsource, $dirdest))
	            	        {
	            	            dol_syslog("Rename ok");
	            	            // Rename docs starting with $oldref with $newref
	            	            $listoffiles=dol_dir_list($dirdest, 'files', 1, '^'.preg_quote($oldref, '/'));
	            	            foreach($listoffiles as $fileentry)
	            	            {
	            	                $dirsource=$fileentry['name'];
	            	                $dirdest=preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
	            	                $dirsource=$fileentry['path'].'/'.$dirsource;
	            	                $dirdest=$fileentry['path'].'/'.$dirdest;
	            	                @rename($dirsource, $dirdest);
	            	            }
	            	        }
	            	    }
	            	}


		            $sql="UPDATE ".MAIN_DB_PREFIX."propal SET ref = '".$this->db->escape($newref)."' WHERE rowid=".$object->id;
					dol_syslog("sql=".$sql);
	            	$resql=$this->db->query($sql);

	            	if ($resql)
	            	{
	            		$object->ref=$newref;
	            		$object->newref=$newref;
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
        if ($action == 'PROPAL_CLOSE_REFUSED' && (! empty($conf->global->FORCEPROJECT_PROPAL_CLOSE_REFUSED_REASON_REQUIRED)))
        {
        	if (empty($object->array_options['options_reasonnotsigned']))
        	{
        		$langs->load("forceproject@forceproject");
        		$this->errors[]=$langs->trans("PleaseEnterAReasonBefore");
        		$ok=-1;
        	}
        	else
        	{
        		if (isset($object->array_options['options_probasigna']))
        		{
        			$object->array_options['options_probasigna'] = 0;
        			$sql="UPDATE ".MAIN_DB_PREFIX."propal_extrafields SET probasigna = 0 WHERE fk_object=".$object->id;
        			$resql=$this->db->query($sql);
        		}
        		$ok=1;
        	}
        }
        if ($action == 'PROPAL_CLOSE_SIGNED' && (! empty($conf->global->FORCEPROJECT_PROPAL_CLOSE_REFUSED_REASON_REQUIRED)))
        {
        	if (isset($object->array_options['options_probasigna']))
        	{
        		$object->array_options['options_probasigna'] = 100;
        		$object->array_options['options_date_cloture'] = dol_now();
        		$sql="UPDATE ".MAIN_DB_PREFIX."propal_extrafields SET probasigna = 100, date_cloture = '".$this->db->idate(dol_now())."' WHERE fk_object=".$object->id;
        		$resql=$this->db->query($sql);
        	}
        	$ok=1;
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

	            	// If this is the first time we set the counter and we want the counter to start to 1 for each project
	            	// The tag {PROJECTREF\-[1-9]\} must be present into ref numbering mask to have this working.
	            	if (preg_match('/\(PROV/', $object->ref) && ! empty($conf->global->FORCEPROJECT_COUNTER_FOREACH_PROJECT))
	            	{
	            		// Clean current ref of invoice, so we can make again later a getNextNumRef and get same value for invoice number
	            		$sql="UPDATE ".MAIN_DB_PREFIX."commande SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
	            		$resql=$this->db->query($sql);

		            	$savmask=$conf->global->COMMANDE_SAPHIR_MASK;
		            	$conf->global->COMMANDE_SAPHIR_MASK=preg_replace('/projectref/',$projectref,$conf->global->COMMANDE_SAPHIR_MASK);
		            	$conf->global->COMMANDE_SAPHIR_MASK=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$conf->global->COMMANDE_SAPHIR_MASK);
		            	$conf->global->COMMANDE_SAPHIR_MASK=preg_replace('/%%+/',$projectref,$conf->global->COMMANDE_SAPHIR_MASK);
		            	$newref=$object->getNextNumRef($object->thirdparty);
		            	//$newref=$projectref.substr($newref,7);
		            	$conf->global->COMMANDE_SAPHIR_MASK=$savmask;
		            	//var_dump($newref); exit;
	            	}
	            	dol_syslog("We validate order ".$object->id." oldref=".$object->ref." newref=".$newref." projectid=".$projectid." projectref=".$projectref);
	            	$error = 0;


	            	// Rename directory if dir was a temporary ref
	            	if (preg_match('/^[\(]?PROV/i', $object->ref))
	            	{
	            	    // Now we rename also files into index
	            	    $sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($object->newref)."', SUBSTR(filename, ".(strlen($object->ref)+1).")), filepath = 'commande/".$this->db->escape($object->newref)."'";
	            	    $sql.= " WHERE filename LIKE '".$this->db->escape($object->ref)."%' AND filepath = 'commande/".$object->db->escape($object->ref)."' and entity = ".$conf->entity;
	            	    $resql = $this->db->query($sql);
	            	    if (! $resql) { $error++; $this->error = $this->db->lasterror(); }

	            	    // We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
	            	    $oldref = dol_sanitizeFileName($object->ref);
	            	    $newref = dol_sanitizeFileName($newref);
	            	    $dirsource = $conf->commande->multidir_output[$object->entity?$object->entity:$conf->entity].'/'.$oldref;
	            	    $dirdest = $conf->commande->multidir_output[$object->entity?$object->entity:$conf->entity].'/'.$newref;

	            	    if (! $error && file_exists($dirsource))
	            	    {
	            	        dol_syslog(get_class($this)."::validate rename dir ".$dirsource." into ".$dirdest);
	            	        if (@rename($dirsource, $dirdest))
	            	        {
	            	            dol_syslog("Rename ok");
	            	            // Rename docs starting with $oldref with $newref
	            	            $listoffiles=dol_dir_list($dirdest, 'files', 1, '^'.preg_quote($oldref, '/'));
	            	            foreach($listoffiles as $fileentry)
	            	            {
	            	                $dirsource=$fileentry['name'];
	            	                $dirdest=preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
	            	                $dirsource=$fileentry['path'].'/'.$dirsource;
	            	                $dirdest=$fileentry['path'].'/'.$dirdest;
	            	                @rename($dirsource, $dirdest);
	            	            }
	            	        }
	            	    }
	            	}


		            $sql="UPDATE ".MAIN_DB_PREFIX."commande SET ref = '".$this->db->escape($newref)."' WHERE rowid=".$object->id;
					dol_syslog("sql=".$sql);
	            	$resql=$this->db->query($sql);

	            	if ($resql)
	            	{
	            		$object->ref=$newref;
	            		$object->newref=$newref;
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

                $sql="SELECT ref FROM ".MAIN_DB_PREFIX."facture WHERE rowid=".$object->id;

                $resql=$this->db->query($sql);
                if ($resql)
                {
                    $obj=$this->db->fetch_object($resql);
                    $newref=$obj->ref;
                    $newref=preg_replace('/projectref/',$projectref,$newref);
                    $newref=preg_replace('/\{PROJECTREF\-[1-9]\}/',$projectref,$newref);  // When mask is  ...{PROJECTREF-9}... for example
                    $newref=preg_replace('/%%+/',$projectref,$newref);

                    // If this is the first time we set the counter and we want the counter to start to 1 for each project
                    // The tag {PROJECTREF\-[1-9]\} must be present into ref numbering mask to have this working.
                    if (preg_match('/\(PROV/', $object->ref) && ! empty($conf->global->FORCEPROJECT_COUNTER_FOREACH_PROJECT))
                    {
                        if ($object->type == 1)
                        {
                        	// Clean current ref of invoice, so we can make again later a getNextNumRef and get same value for invoice number
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
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
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
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
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
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
                        	$sql="UPDATE ".MAIN_DB_PREFIX."facture SET ref = '(TMP".$this->db->escape($newref).")' WHERE rowid=".$object->id;
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

                    dol_syslog("We validate invoice ".$object->id." oldref=".$object->ref." newref=".$newref." projectid=".$projectid." projectref=".$projectref);
                    $error = 0;


                    // Rename directory if dir was a temporary ref
                    if (preg_match('/^[\(]?PROV/i', $object->ref))
                    {
                        // Now we rename also files into index
                        $sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($object->newref)."', SUBSTR(filename, ".(strlen($object->ref)+1).")), filepath = 'facture/".$this->db->escape($object->newref)."'";
                        $sql.= " WHERE filename LIKE '".$this->db->escape($object->ref)."%' AND filepath = 'facture/".$object->db->escape($object->ref)."' and entity = ".$conf->entity;
                        $resql = $this->db->query($sql);
                        if (! $resql) { $error++; $this->error = $this->db->lasterror(); }

                        // We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
                        $oldref = dol_sanitizeFileName($object->ref);
                        $newref = dol_sanitizeFileName($newref);
                        $dirsource = $conf->facture->multidir_output[$object->entity?$object->entity:$conf->entity].'/'.$oldref;
                        $dirdest = $conf->facture->multidir_output[$object->entity?$object->entity:$conf->entity].'/'.$newref;

                        if (! $error && file_exists($dirsource))
                        {
                            dol_syslog(get_class($this)."::validate rename dir ".$dirsource." into ".$dirdest);
                            if (@rename($dirsource, $dirdest))
                            {
                                dol_syslog("Rename ok");
                                // Rename docs starting with $oldref with $newref
                                $listoffiles=dol_dir_list($dirdest, 'files', 1, '^'.preg_quote($oldref, '/'));
                                foreach($listoffiles as $fileentry)
                                {
                                    $dirsource=$fileentry['name'];
                                    $dirdest=preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
                                    $dirsource=$fileentry['path'].'/'.$dirsource;
                                    $dirdest=$fileentry['path'].'/'.$dirdest;
                                    @rename($dirsource, $dirdest);
                                }
                            }
                        }
                    }


                    $sql="UPDATE ".MAIN_DB_PREFIX."facture SET ref = '".$this->db->escape($newref)."' WHERE rowid=".$object->id;
                    dol_syslog("sql=".$sql);
                    $resql=$this->db->query($sql);

                    if ($resql)
                    {
                        $object->ref=$newref;
                        $object->newref=$newref;
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

        if ($action == 'PROJECT_MODIFY' && ! empty($conf->global->FORCEPROJECT_NEW_PROJECT_REF_ON_NEW_THIRDPARTY))
        {
            if ($object->oldcopy->socid && $object->socid && $object->socid != $object->oldcopy->socid)
            {
                $thirdparty = new Societe($this->db);
                $thirdparty->fetch($object->socid);

                $defaultref='';
                $modele = empty($conf->global->PROJECT_ADDON)?'mod_project_simple':$conf->global->PROJECT_ADDON;

                // Search template files
                $file=''; $classname=''; $filefound=0;
                $dirmodels=array_merge(array('/'), (array) $conf->modules_parts['models']);
                foreach($dirmodels as $reldir)
                {
                    $file=dol_buildpath($reldir."core/modules/project/".$modele.'.php', 0);
                    if (file_exists($file))
                    {
                        $filefound=1;
                        $classname = $modele;
                        break;
                    }
                }

                if ($filefound)
                {
                    $result=dol_include_once($reldir."core/modules/project/".$modele.'.php');
                    $modProject = new $classname;

                    $defaultref = $modProject->getNextValue($thirdparty, $object);
                }

                if (is_numeric($defaultref) && $defaultref <= 0) $defaultref='';

                if ($defaultref)
                {
                    $object->ref = $defaultref;

                    $object->update($user, 1);
                }
            }
        }

        return $ok;
    }
}
