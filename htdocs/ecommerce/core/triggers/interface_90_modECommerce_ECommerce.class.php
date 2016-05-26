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


dol_include_once('/ecommerce/class/business/eCommerceSynchro.class.php');

require_once(DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php');

class InterfaceECommerce
{
    private $db;
    private $name;
    private $description;
    private $version;
    
    public $family;
    public $errors;
    
    /**
     *   This class is a trigger on delivery to update delivery on eCommerce Site
     *   @param      DoliDB		$DB      Handler database access
     */
    function InterfaceECommerce($DB)
    {
        $this->db = $DB ;
    
        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "eCommerce";
        $this->description = "Triggers of this module update delivery on eCommerce Site according to order status.";
        $this->version = '1.0';
    }
    
    
    /**
     *   Renvoi nom du lot de triggers
     *   @return     string      Nom du lot de triggers
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     *   Renvoi descriptif du lot de triggers
     *   @return     string      Descriptif du lot de triggers
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Renvoi version du lot de triggers
     *   @return     string      Version du lot de triggers
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }
    
    /**
     *      Fonction appelee lors du declenchement d'un evenement Dolibarr.
     *      D'autres fonctions run_trigger peuvent etre presentes dans includes/triggers
     *      @param      action      Code de l'evenement
     *      @param      object      Objet concerne
     *      @param      user        Objet user
     *      @param      lang        Objet lang
     *      @param      conf        Objet conf
     *      @return     int         <0 if fatal error, 0 si nothing done, >0 if ok
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
    	
    	
        if ($action == 'COMPANY_MODIFY')
        {
            $this->db->begin();

			$eCommerceSite = new eCommerceSite($this->db);
			$sites = $eCommerceSite->listSites('object');
			
			foreach($sites as $site)
			{
			    $eCommerceSynchro = new eCommerceSynchro($this->db, $site);
			    dol_syslog("Trigger ".$action." try to connect to eCommerce site ".$site->name);
			    $eCommerceSynchro->connect();
			    if (count($eCommerceSynchro->errors))
			    {
			        $error++;
			        setEventMessages($eCommerceSynchro->error, $eCommerceSynchro->errors, 'errors');
			    }
			
			    if (! $error)
			    {
    				$eCommerceSociete = new eCommerceSociete($this->db);
    				$eCommerceSociete->fetchByFkSociete($object->id, $site->id);
    
    				if ($eCommerceSociete->remote_id > 0)
    				{
    				    $result = $eCommerceSynchro->eCommerceRemoteAccess->updateRemoteSociete($eCommerceSociete->remote_id, $object);
    				}
    				else
    				{
    				    // Get current categories
    				    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
    				    $c = new Categorie($this->db);
    				    $catids = $c->containing($object->id, Categorie::TYPE_CUSTOMER, 'id');
    
    				    if (in_array($site->fk_cat_societe, $catids))
    				    {
    				        dol_syslog("Societe with id ".$object->id." is not linked to an ecommerce record but has category flag to push on eCommerce. So we push it");
    				        // TODO
    				        //$result = $eCommerceSynchro->eCommerceRemoteAccess->updateRemoteProduct($eCommerceProduct->remote_id);
    				    }
    				    else
    				    {
    				        dol_syslog("Societe with id ".$object->id." is not linked to an ecommerce record and does not has category flag to push on eCommerce.");
    				    }
    				}
			    }
			}
				
			if ($error)
			{
			    $this->db->rollback();
			    return -1;
			}
			else
			{
			    $this->db->commit();
			    return 1;
			}
        }
        
        if ($action == 'PRODUCT_MODIFY')
        {
            $this->db->begin();

            $eCommerceSite = new eCommerceSite($this->db);
			$sites = $eCommerceSite->listSites('object');

			foreach($sites as $site)
			{
				$eCommerceSynchro = new eCommerceSynchro($this->db, $site);
				dol_syslog("Trigger ".$action." try to connect to eCommerce site ".$site->name);
				$eCommerceSynchro->connect();
				if (count($eCommerceSynchro->errors))
				{
				    $error++;
				    setEventMessages($eCommerceSynchro->error, $eCommerceSynchro->errors, 'errors');
				}
				
				if (! $error)
				{
    				$eCommerceProduct = new eCommerceProduct($this->db);
    				$eCommerceProduct->fetchByProductId($object->id, $site->id);
    				
    				if ($eCommerceProduct->remote_id > 0)
    				{
                		$result = $eCommerceSynchro->eCommerceRemoteAccess->updateRemoteProduct($eCommerceProduct->remote_id, $object);
    				}
    				else
    				{
    				    // Get current categories
    				    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
    				    $c = new Categorie($this->db);
    				    $catids = $c->containing($object->id, Categorie::TYPE_PRODUCT, 'id');
    				    
    				    if (in_array($site->fk_cat_product, $catids))
    				    {
    				        dol_syslog("Product with id ".$object->id." is not linked to an ecommerce record but has category flag to push on eCommerce. So we push it");
    				        // TODO
    				        //$result = $eCommerceSynchro->eCommerceRemoteAccess->updateRemoteProduct($eCommerceProduct->remote_id);
    				    }
    				    else
    				    {
    					   dol_syslog("Product with id ".$object->id." is not linked to an ecommerce record and does not has category flag to push on eCommerce.");
    				    }
    				}
				}
			}
			
            if ($error) 
            {
                $this->db->rollback();
                return -1;
            }
            else
            {
                $this->db->commit();
                return 1;
            }
        }
        
    	
    	if ($action == 'CATEGORY_DELETE' && ((int) $object->type == 0))     // Product category
        {
            $this->db->begin();

            // TODO If product category and oldest parent is category for magento then delete category into magento.
            
            $sql = "SELECT remote_id, remote_parent_id FROM ".MAIN_DB_PREFIX."ecommerce_category WHERE label ='".$this->db->escape($object->label)."' AND type = 0";
            $resql=$this->db->query($sql);
            if ($resql) 
            {
                $obj=$this->db->fetch_object($resql);
                $remote_parent_id=$obj->remote_parent_id;
                $remote_id=$obj->remote_id;
                $sql = "UPDATE ".MAIN_DB_PREFIX."ecommerce_category SET last_update = NULL, remote_parent_id = ".$remote_parent_id." WHERE remote_parent_id = ".$remote_id;
                $resql=$this->db->query($sql);
                if (! $resql)
                {
                    $error++;
                }
            }
            if (! $error)
            {
                $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_category WHERE label ='".$this->db->escape($object->label)."' AND type = 0";
    
                $resql=$this->db->query($sql);
                if (! $resql)
                {
                    $error++;
                }
            }
            
            if ($error) 
            {
                $this->db->rollback();
                return -1;
            }
            else
            {
                $this->db->commit();
                return 1;
            }
        }
        
        
        if ($action == 'COMPANY_DELETE')
        {
            $this->db->begin();

            $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_socpeople WHERE fk_socpeople IN (SELECT rowid FROM ".MAIN_DB_PREFIX."socpeople WHERE fk_soc = '".$this->db->escape($object->id)."')";
            $resql=$this->db->query($sql);
            if (! $resql)
            {
                $this->error=$this->db->lasterror();
                $error++;
            }
            
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_societe WHERE fk_societe ='".$this->db->escape($object->id)."'";
            $resql=$this->db->query($sql);
            if (! $resql)
            {
                $this->error=$this->db->lasterror();
                $error++;
            }
            
            if ($error) 
            {
                $this->db->rollback();
                return -1;
            }
            else
            {
                $this->db->commit();
                return 1;
            }
        } 
        
        if ($action == 'CONTACT_DELETE')
        {
            $this->db->begin();

            $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_socpeople WHERE fk_socpeople = '".$this->db->escape($object->id)."'";
            $resql=$this->db->query($sql);
            if (! $resql)
            {
                $this->error=$this->db->lasterror();
                $error++;
            }
            
            if ($error) 
            {
                $this->db->rollback();
                return -1;
            }
            else
            {
                $this->db->commit();
                return 1;
            }
        } 
        
        if ($action == 'ORDER_DELETE')
        {
            $this->db->begin();

            $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_commande WHERE fk_commande ='".$this->db->escape($object->id)."'";
            $resql=$this->db->query($sql);
            if (! $resql)
            {
                $this->error=$this->db->lasterror();
                $error++;
            }
            
            if ($error) 
            {
                $this->db->rollback();
                return -1;
            }
            else
            {
                $this->db->commit();
                return 1;
            }
        }        

        if ($action == 'BILL_DELETE')
        {
            $this->db->begin();

            $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_facture WHERE fk_facture ='".$this->db->escape($object->id)."'";
            $resql=$this->db->query($sql);
            if (! $resql)
            {
                $this->error=$this->db->lasterror();
                $error++;
            }
            
            if ($error) 
            {
                $this->db->rollback();
                return -1;
            }
            else
            {
                $this->db->commit();
                return 1;
            }
        }        
        
        
        
        
        
        if ($action == 'SHIPPING_VALIDATE')
        {
        	try
        	{
	            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
	            
        		//retrieve shipping id
        		$shippingId = $object->id;        		
        	
        		$origin = $object->origin;
        		$origin_id = $object->origin_id;
        		
				$orderId = $origin_id;

        		//load eCommerce Commande by order id
	            $eCommerceCommande = new eCommerceCommande($this->db);
	            $eCommerceCommande->fetchByCommandeId($orderId);
	            
	            if (isset($eCommerceCommande->id) &&  $eCommerceCommande->id > 0)
	            {
		            //set eCommerce site
		            $eCommerceSite = new eCommerceSite($this->db);
		            $eCommerceSite->fetch($eCommerceCommande->fk_site);
		            
		            $synchro = new eCommerceSynchro($this->db, $eCommerceSite);
		            $synchro->synchLivraison($object, $eCommerceCommande->remote_id);
					return 1;
	            }
        	}
        	catch (Exception $e)
        	{
            	$this->errors = 'Trigger exception : '.$e;
	            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id." ".$this->errors);
            	return -1;
        	}
        }
		return 0;
    }

}
