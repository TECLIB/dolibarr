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
 * Class for synchronize remote sites with Dolibarr
 */

dol_include_once('/ecommerceng/class/data/eCommerceRemoteAccess.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceCommande.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceFacture.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceProduct.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceSociete.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceSocpeople.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceSite.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceCategory.class.php');
dol_include_once('/ecommerceng/admin/class/data/eCommerceDict.class.php');

require_once(DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php');
require_once(DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once(DOL_DOCUMENT_ROOT . '/product/class/product.class.php');
require_once(DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php');
require_once(DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php');
require_once(DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php');
require_once(DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php');



class eCommerceSynchro
{
    public $error;
    public $errors;
    public $success;
    public $langs;
    public $user;
    
    //Data access
    private $db;
    public $eCommerceRemoteAccess;
    
    private $eCommerceSite;
    private $eCommerceSociete;
    private $eCommerceSocpeople;
    private $eCommerceProduct;
    private $eCommerceCategory;
    private $eCommerceMotherCategory;
    private $eCommerceCommande;
    private $eCommerceFacture;
    //class members	
    public $toDate;
    private $societeLastUpdateDate;
    private $sopeopleLastUpdateDate;
    private $productLastUpdateDate;
    private $commandeLastUpdateDate;
    private $factureLastUpdateDate;
    
    private $societeToUpdate;
    private $socpeopleToUpdate;
    private $productToUpdate;
    private $categoryToUpdate;
    private $commandeToUpdate;
    private $factureToUpdate;
    
	
    
    /**
     * Constructor
     * 
     * @param Database          $db           Database handler
     * @param eCommerceSite     $site         Object eCommerceSite       
     */
    function eCommerceSynchro($db, $site)
    {
        global $langs, $user;
        
        try {
            $this->langs = $langs;
            $this->user = $user;
            $this->db = $db;
            $this->eCommerceSite = $site;
            
            $this->eCommerceRemoteAccess = new eCommerceRemoteAccess($this->db, $this->eCommerceSite);
        
            $this->toDate = dol_now();      // Set date to use as last update date
        } 
        catch (Exception $e) 
        {
            $this->errors[] = $this->langs->trans('ECommerceConnectErrorCheckUsernamePasswordAndAdress');
        }
    }

    /**
     * Connect to remote
     */
    function connect()
    {
        dol_syslog("eCommerceSynchro Connect to remote", LOG_DEBUG);
        
        try
        {
            if (! $this->eCommerceRemoteAccess->connect())
            {
                $this->error = $this->langs->trans('ECommerceConnectErrorCheckUsernamePasswordAndAdress');
                $this->errors[] = $this->error; 
                $this->errors= array_merge($this->errors, $this->eCommerceRemoteAccess->errors);
                dol_syslog("eCommerceSynchro Connect error ".$this->error, LOG_DEBUG);
                return -1;
            }
            else
            {
                dol_syslog("eCommerceSynchro Connected", LOG_DEBUG);
            }
            
            return 1;
        }
        catch (Exception $e) 
        {
            $this->errors[] = $this->langs->trans('ECommerceConnectErrorCheckUsernamePasswordAndAdress');
        }

        return -1;
    }
    
    /**
     * Getter for toDate
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * Instanciate eCommerceSociete data class access
     */
    private function initECommerceSociete()
    {
        $this->eCommerceSociete = new eCommerceSociete($this->db);
    }

    /**
     * Instanciate eCommerceSocpeople data class access
     */
    private function initECommerceSocpeople()
    {
        $this->eCommerceSocpeople = new eCommerceSocpeople($this->db);
    }

    /**
     * Instanciate eCommerceProduct data class access
     */
    private function initECommerceProduct()
    {
        $this->eCommerceProduct = new eCommerceProduct($this->db);
    }

    /**
     * Instanciate eCommerceCategory data class access
     */
    private function initECommerceCategory()
    {
        $this->eCommerceCategory = new eCommerceCategory($this->db);
        $this->eCommerceMotherCategory = new eCommerceCategory($this->db);
    }

    /**
     * Instanciate eCommerceCommande data class access
     */
    private function initECommerceCommande()
    {
        $this->eCommerceCommande = new eCommerceCommande($this->db);
    }

    /**
     * Instanciate eCommerceFacture data class access
     */
    private function initECommerceFacture()
    {
        $this->eCommerceFacture = new eCommerceFacture($this->db);
    }

    
    
    /**
     * Get the last date of product update
     * @param $force bool to force update
     * @return datetime
     */
    public function getProductLastUpdateDate($force = false)
    {
        try {
            if (!isset($this->productLastUpdateDate) || $force == true)
            {
                if (!isset($this->eCommerceProduct))
                    $this->initECommerceProduct();
                $this->productLastUpdateDate = $this->eCommerceProduct->getLastUpdate($this->eCommerceSite->id);
            }
            return $this->productLastUpdateDate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetProductLastUpdateDate');
        }
    }
    
    /**
     * Get the last date of societe update
     * 
     * @param $force bool to force update
     * @return datetime
     */
    public function getSocieteLastUpdateDate($force = false)
    {
        try {
            if (!isset($this->societeLastUpdateDate) || $force == true)
            {
                if (!isset($this->eCommerceSociete))
                    $this->initECommerceSociete();      // Init $this->eCommerceSociete
                $this->societeLastUpdateDate = $this->eCommerceSociete->getLastUpdate($this->eCommerceSite->id);
            }
            return $this->societeLastUpdateDate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetSocieteLastUpdateDate');
        }
    }

    /**
     * Get the last date of commande update
     * @param $force bool to force update
     * @return datetime
     */
    public function getCommandeLastUpdateDate($force = false)
    {
        try {
            if (!isset($this->commandeLastUpdateDate) || $force == true)
            {
                if (!isset($this->eCommerceCommande))
                    $this->initECommerceCommande();
                $this->commandeLastUpdateDate = $this->eCommerceCommande->getLastUpdate($this->eCommerceSite->id);
            }
            return $this->commandeLastUpdateDate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetCommandeLastUpdateDate');
        }
    }

    /**
     * Get the last date of facture update
     * @param $force bool to force update
     * @return datetime
     */
    public function getFactureLastUpdateDate($force = false)
    {
        try {
            if (!isset($this->eCommerceFactureLastUpdateDate) || $force == true)
            {
                if (!isset($this->eCommerceFacture))
                    $this->initECommerceFacture();
                $this->factureLastUpdateDate = $this->eCommerceFacture->getLastUpdate($this->eCommerceSite->id);
            }
            return $this->factureLastUpdateDate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetFactureLastUpdateDate');
        }
    }

    
    
    public function getNbCategoriesInDolibarr()
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."categorie WHERE type = 0";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }

    public function getNbCategoriesInDolibarrLinkedToE($excludeid = 0)
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_category WHERE type = 0";
        $sql.=" AND fk_category <> ".$excludeid;
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    public function getNbProductInDolibarr()
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."product";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    public function getNbProductInDolibarrLinkedToE()
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_product";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    public function getNbSocieteInDolibarr()
    {
        /*$sql="SELECT COUNT(s.rowid) as nb FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."categorie_societe as cs";
        $sql.=" WHERE s.rowid = cs.fk_soc AND cs.fk_categorie = ".$this->eCommerceSite->fk_cat_societe;
		*/
    	$sql="SELECT COUNT(s.rowid) as nb FROM ".MAIN_DB_PREFIX."societe as s";
        
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }

    public function getNbSocieteInDolibarrLinkedToE()
    {
        $sql="SELECT COUNT(s.rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_societe as s";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    public function getNbCommandeInDolibarr()
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."commande";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    public function getNbCommandeInDolibarrLinkedToE()
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_commande";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    public function getNbFactureInDolibarr()
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."facture";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    public function getNbFactureInDolibarrLinkedToE()
    {
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_facture";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj=$this->db->fetch_object($resql);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }
    
    /**
     * Return list o categories to update
     */
    public function getCategoriesToUpdate($force = false)
    {
        try {
            if (!isset($this->categoryToUpdate) || $force == true)
            {
                $this->categoryToUpdate = array();

                // get a magento category tree in a one-leveled array
                $tmp=$this->eCommerceRemoteAccess->getRemoteCategoryTree();
                if (is_array($tmp))
                {
                    $resanswer = array();
                    eCommerceCategory::cuttingCategoryTreeFromMagentoToDolibarrNew($tmp, $resanswer);
                    
                    // $resanswer is array with all categories
                    // We must loop on each categorie to make a WS call to get updated_at info.
                    foreach ($resanswer as $remoteCatToCheck) // Check update for each entry into $resanswer -> $remoteCatToCheck = array('category_id'=>, 'parent_id'=>...)
                    {
                        dol_syslog("Process category remote_id=".$remoteCatToCheck['category_id']);
                        $this->initECommerceCategory(); // Initialise 2 properties eCommerceCategory and eCommerceMotherCategory
                        
                        // Complete info of $remoteCatToCheck['category_id']
                        $tmp=$this->eCommerceRemoteAccess->getCategoryData($remoteCatToCheck['category_id']);
                        
                        $remoteCatToCheck['updated_at']=$tmp['updated_at'];

                        // Check into link table ecommerce_category if record has been modified on magento or not 
                        if ($this->eCommerceCategory->checkForUpdate($this->eCommerceSite->id, $this->toDate, $remoteCatToCheck))
                            $this->categoryToUpdate[] = $remoteCatToCheck;
                        
                    }
                    
                    //var_dump($this->categoryToUpdate);exit;
                    
                    return $this->categoryToUpdate;
                }
            }
        } catch (Exception $e) {
            dol_syslog($e->getMessage(), LOG_ERR);
            $this->errors[] = $this->langs->trans('ECommerceErrorGetCategoryToUpdate');
        }
        return false;
    }
    
    /**
     * Get modified product since the last update
     * 
     * @param   int     $force      Bool to force to reload cache list $this->productToUpdate
     * @return  array               Array of remote product (also stored into this->productToUpdate)
     */
    public function getProductToUpdate($force = false)
    {
        try {
            if (!isset($this->productToUpdate) || $force == true)
            {
                $lastupdatedate = $this->getProductLastUpdateDate($force);
                $this->productToUpdate = $this->eCommerceRemoteAccess->getProductToUpdate($lastupdatedate, $this->toDate);
            }
            return $this->productToUpdate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetProductToUpdate');
        }
    }

    /**
     * Get modified societe since the last update
     * 
     * @param   int     $force      Bool to force to reload cache list $this->societeToUpdate 
     * @return  array               Array of remote societe (also stored into this->societeToUpdate)
     */
    public function getSocieteToUpdate($force = false)
    {
        try {
            if (!isset($this->societeToUpdate) || $force == true)
            {
                $lastupdatedate=$this->getSocieteLastUpdateDate($force);
                $this->societeToUpdate = $this->eCommerceRemoteAccess->getSocieteToUpdate($lastupdatedate, $this->toDate);
            }
            return $this->societeToUpdate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetSocieteToUpdate');
        }
    }

    /**
     * Get modified commande since the last update
     * 
     * @param   int     $force      Bool to force to reload cache list $this->commandeToUpdate
     * @return  array               Array of remote order (also stored into this->commandeToUpdate)
     */
    public function getCommandeToUpdate($force = false)
    {
        try {
            if (!isset($this->commandeToUpdate) || $force == true)
            {
                $lastupdatedate=$this->getCommandeLastUpdateDate($force);
                $this->commandeToUpdate = $this->eCommerceRemoteAccess->getCommandeToUpdate($lastupdatedate, $this->toDate);
            }
            return $this->commandeToUpdate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetCommandeToUpdate');
        }
    }

    /**
     * Get modified facture since the last update
     * 
     * @param   int     $force      Bool to force to reload cache list $this->factureToUpdate
     * @return  array               Array of remote invoice (also stored into this->factureToUpdate)
     */
    public function getFactureToUpdate($force = false)
    {
        try {
            if (!isset($this->factureToUpdate) || $force == true)
            {
                $lastupdatedate=$this->getFactureLastUpdateDate($force);
                $this->factureToUpdate = $this->eCommerceRemoteAccess->getFactureToUpdate($lastupdatedate, $this->toDate);
            }
            return $this->factureToUpdate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetFactureToUpdate');
        }
    }


    /* getNbXXXToUpdate */
    
    
    /**
     * Get count of modified product since the last update
     * 
     * @param $force bool to force update
     * @return int      <0 if KO, >=0 if OK
     */
    public function getNbProductToUpdate($force = false)
    {
        try {
            $result = $this->getProductToUpdate($force);
            if (is_array($result)) return count($result);
            else return -1; 
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetNbSocieteToUpdate');
            return -2;
        }
    }

    /**
     * Get count of modified societe since the last update
     * @param $force    Bool to force update
     * @return int      <0 if KO, >=0 if OK
     */
    public function getNbCategoriesToUpdate($force = false)
    {
        try {
            $result = $this->getCategoriesToUpdate($force);
            if (is_array($result)) return count($result);
            else return -1; 
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetNbCategoriesToUpdate');
            return -2;
        }
    }
    
    /**
     * Get count of modified societe since the last update
     * @param $force    Bool to force update
     * @return int      <0 if KO, >=0 if OK
     */
    public function getNbSocieteToUpdate($force = false)
    {
        try {
            $result = $this->getSocieteToUpdate($force);
            if (is_array($result)) return count($result);
            else return -1; 
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetNbSocieteToUpdate');
            return -2;
        }
    }

    /**
     * Get count of modified commande since the last update
     * 
     * @param $force bool to force update
     * @return int      <0 if KO, >=0 if OK
     */
    public function getNbCommandeToUpdate($force = false)
    {
        try {
            $result = $this->getCommandeToUpdate($force);
            if (is_array($result)) return count($result);
            else return -1; 
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetNbSocieteToUpdate');
            return -2;
        }
    }

    /**
     * Get count of modified facture since the last update
     * 
     * @param $force bool to force update
     * @return int      <0 if KO, >=0 if OK
     */
    public function getNbFactureToUpdate($force = false)
    {
        try {
            $result = $this->getFactureToUpdate($force);
            if (is_array($result)) return count($result);
            else return -1; 
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetNbSocieteToUpdate');
            return -2;
        }
    }

    
    
    /**
     * 	Sync categories
     * 
     * 	@return int     <0 if KO, >= 0 if ok
     */
    public function synchCategory()
    {
        try {
            $nbgoodsunchronize = 0;

            dol_syslog("***** eCommerceSynchro synchCategory");
            
            // Safety check : importRootCategory exists
            $dBCategorie = new Categorie($this->db);
            $importRootExists = ($dBCategorie->fetch($this->eCommerceSite->fk_cat_product) > 0) ? 1 : 0;

            if ($importRootExists)
            {
                $this->db->begin();
                
                dol_syslog("synchCategory importRootExists=".$importRootExists);
                $categories = $this->getCategoriesToUpdate();   // Return list of all categories that were modified on ecommerce side
                if (count($categories))
                {
                    foreach ($categories as $categoryArray)     // Loop on each categories found on ecommerce side. Cursor is $categoryArray
                    {
                        dol_syslog("synchCategory Process sync of magento category_id=".$categoryArray['category_id']." name=".$categoryArray['name']);

                        $this->initECommerceCategory();             // Initialise new objects
                        $dBCategorie = new Categorie($this->db);

                        // Check if the ecommerce category has an ecommerce parent category, if not, that implies it is root					
                        $motherExists = $this->eCommerceMotherCategory->fetchByRemoteId($categoryArray['parent_id'], $this->eCommerceSite->id);
                        // Now $this->eCommerceMotherCategory contains the mother category or null

                        // if fetch on eCommerceMotherCategory has failed, it is root
                        if ($motherExists < 1 && ($this->eCommerceMotherCategory->fetchByFKCategory($this->eCommerceSite->fk_cat_product, $this->eCommerceSite->id) < 0))
                        {
                            // get the importRootCategory of Dolibarr set for the eCommerceSite 
                            $dBCategorie->fetch($this->eCommerceSite->fk_cat_product);

                            $this->eCommerceMotherCategory->label = $dBCategorie->label;
                            $this->eCommerceMotherCategory->type = $dBCategorie->type;
                            $this->eCommerceMotherCategory->description = $dBCategorie->description;
                            $this->eCommerceMotherCategory->fk_category = $dBCategorie->id;
                            $this->eCommerceMotherCategory->fk_site = $this->eCommerceSite->id;
                            $this->eCommerceMotherCategory->remote_id = $categoryArray['parent_id'];

                            // reset $dBCategorie
                            $dBCategorie = new Categorie($this->db);

                            // Create an entry to map importRootCategory in eCommerceCategory
                            $this->eCommerceMotherCategory->create($this->user);
                        }
                        $eCommerceCatExists = $this->eCommerceCategory->fetchByRemoteId($categoryArray['category_id'], $this->eCommerceSite->id);

                        if ($this->eCommerceCategory->fk_category > 0)
                        {
                            $synchExists = $eCommerceCatExists >= 0 ? $dBCategorie->fetch($this->eCommerceCategory->fk_category) : -1;
                            if ($synchExists == 0) 
                            {
                                // Category entry exists into ecommerce_category with fk_category that link to non existing category
                                // Should not happend because we added a cleaned of all orphelins entries into getCategoriesToUpdate
                                $synchExists = -1;
                            }
                        }
                        else
                        {
                            $synchExists = $eCommerceCatExists >= 0 ? 0 : -1;
                        }
                        
                        // Affect attributes of catArray to dBCat	
                        $dBCategorie->fk_parent = $this->eCommerceMotherCategory->fk_category;
                        $dBCategorie->label = $categoryArray['name'];
                        $dBCategorie->description = $categoryArray['description'];
                        $dBCategorie->type = 0;             // for product category type	
                        $dBCategorie->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                        
                        //var_dump('synchExists='.$synchExists);
                        if ($synchExists >= 0)
                        {
                            $result = $dBCategorie->update($this->user);
                        } else
                        {
                            $result = $dBCategorie->create($this->user);
                        }
                        // if synchro category ok
                        if ($result >= 0)
                        {
                            $this->eCommerceCategory->label = $dBCategorie->label;
                            $this->eCommerceCategory->description = $dBCategorie->description;
                            $this->eCommerceCategory->remote_parent_id = $categoryArray['parent_id'];
                            $this->eCommerceCategory->last_update = strtotime($categoryArray['updated_at']);
                            if ($synchExists > 0)   // update it remotely
                            {
                                if ($this->eCommerceCategory->update($this->user) < 0)
                                {
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCategoryUpdateError');
                                    break;
                                }
                            } 
                            else       // create it remotely
                            {
                                $this->eCommerceCategory->fk_category = $dBCategorie->id;
                                $this->eCommerceCategory->type = $dBCategorie->type;
                                $this->eCommerceCategory->fk_site = $this->eCommerceSite->id;
                                $this->eCommerceCategory->remote_id = $categoryArray['category_id'];

                                if ($this->eCommerceCategory->create($this->user) < 0)  // insert into table lxx_ecommerce_category
                                {
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCategoryCreateError') . ' ' . $categoryArray['label'];
                                    break;
                                }
                            }
                        } 
                        else
                        {
							if ($result == -4)   // duplicate during create
							{
								// The category already exists
								$dBCategorie->fetch(0, $dBCategorie->label, $dBCategorie->type);
								$this->eCommerceCategory->label = $dBCategorie->label;                         	
								$this->eCommerceCategory->description = $dBCategorie->description;                        	
								$this->eCommerceCategory->fk_category = $dBCategorie->id;
                                $this->eCommerceCategory->type = $dBCategorie->type;
                                $this->eCommerceCategory->fk_site = $this->eCommerceSite->id;
                                $this->eCommerceCategory->remote_id = $categoryArray['category_id'];
                                $this->eCommerceCategory->remote_parent_id = $categoryArray['parent_id'];
                                
                                if ($this->eCommerceCategory->create($this->user) < 0)  // insert into table lxx_ecommerce_category
                                {
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCategoryCreateError') . ' ' . $categoryArray['label'];
                                    break;
                                }
							}
							else
							{
                            	$error++;
                            	$this->errors[] = $this->langs->trans('ECommerceSynchCategoryError').' '.$dBCategorie->error;
                            	break;
							}
                            
                            
                        }
                        $nbgoodsunchronize++;
                        
                        //var_dump($nbgoodsunchronize);exit;
                    }
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchCategorySuccess');
                }
                
                if (empty($this->errors) && ! $error)
                {
                    $this->db->commit();
                    return $nbgoodsunchronize;
                }
                else
                {
                    $this->db->rollback();
                    return -1;
                }
            }
            else
            {
                $this->error = $this->langs->trans('ECommerceSynchCategoryNoImportRoot');
                $this->errors[] = $this->error;
                return -1;
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceSynchCategoryConnectError');
            return -1;
        }
    }
    
    
    /**
     * Synchronize societe to update
     */
    public function synchSociete()
    {
        try {
            $nbgoodsunchronize = 0;
            $societes=array();
            
            //if ($this->getNbSocieteToUpdate(true) > 0)
            //    $societes = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrSociete($this->getSocieteToUpdate());

            dol_syslog("***** eCommerceSynchro synchSociete");
            $resulttoupdate=$this->getSocieteToUpdate();
            if (is_array($resulttoupdate))
            {
                if (count($resulttoupdate) > 0) $societes = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrSociete($resulttoupdate);
            }

            if (count($societes))
            {
                $this->db->begin();
                
                foreach ($societes as $societeArray)
                {
                    //check if societe exists in eCommerceSociete
                    $synchExists = $this->eCommerceSociete->fetchByRemoteId($societeArray['remote_id'], $this->eCommerceSite->id);
                    $dBSociete = new Societe($this->db);

                    //if societe exists in eCommerceSociete, societe must exists in societe
                    if ($synchExists > 0 && isset($this->eCommerceSociete->fk_societe))
                    {
                        $refExists = $dBSociete->fetch($this->eCommerceSociete->fk_societe);
                        if ($refExistst >= 0)
                        {
                            $dBSociete->name = $societeArray['name'];
                            //$dBSociete->ref_ext = $this->eCommerceSite->name.'-'.$societeArray['remote_id'];      // No need of ref_ext, we will search if already exists on name
                            $dBSociete->email = $societeArray['email'];
                            $dBSociete->client = $societeArray['client'];
                            $dBSociete->tva_intra = $societeArray['vatnumber'];
                            $dBSociete->tva_assuj = 1;      // tba_intra is not saved if this field is not set
                            $dBSociete->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                            
                            $result = $dBSociete->update($dBSociete->id, $this->user);
                        } 
                        else
                        {
                            $this->errors[] = $this->langs->trans('ECommerceSynchSocieteErrorBetweenECommerceSocieteAndSociete');
                        }
                    }
                    //if societe not exists in eCommerceSociete, societe is created
                    else
                    {
                        // First, we check object does not alreay exists. If not, we create it, if it exists, do nothing.
                        //$result = $dBSociete->fetch(0, '', $this->eCommerceSite->name.'-'.$societeArray['remote_id']);      // No need of ref_ext, we will search if already exists on name
                        $result = $dBSociete->fetch(0, $societeArray['name']);
                        if ($result == -2)
                        {
                            $error++;
                            $this->error='Several thirdparties with name '.$societeArray['name'].' were found in Dolibarr. Sync is not possible. Please rename one of it to avoid duplicate.';
                            $this->errors[]=$this->error;
                        }
                        if ($result == 0)
                        {
                            $dBSociete->name = $societeArray['name'];
                            //$dBSociete->ref_ext = $this->eCommerceSite->name.'-'.$societeArray['remote_id'];      // No need of ref_ext, we will search if already exists on name
                            $dBSociete->email = $societeArray['email'];
                            $dBSociete->client = $societeArray['client'];
                            $dBSociete->tva_intra = $societeArray['vatnumber'];
                            $dBSociete->tva_assuj = 1;                              // tva_intra is not saved if this field is not set
                            $dBSociete->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                            $dBSociete->code_client = -1;           // Automatic code
                            $dBSociete->code_fournisseur = -1;      // Automatic code
                            
                            $result = $dBSociete->create($this->user);
                            if ($result < 0)
                            {
                                $error++;
                                $this->error=$this->langs->trans('ECommerceSynchSocieteCreateError').' '.$dBSociete->error;
                                $this->errors[]=$this->error;
                            }
                        }
                    }

                    //if create/update of societe table ok
                    if ($result >= 0)
                    {
                        dol_syslog("synchSociete Now we will set the tags id=".$this->eCommerceSite->fk_cat_societe." to the thirdparty id=".$dBSociete->id." created or modified");
                        
                        //set category					
                        $cat = new Categorie($this->db);
                        $cat->fetch($this->eCommerceSite->fk_cat_societe);
                        $cat->add_type($dBSociete, 'customer');

                        dol_syslog("synchSociete Now we will update link rowid=".$this->eCommerceSociete->id." with last_update = ".$societeArray['last_update']);
                        $this->eCommerceSociete->last_update = $societeArray['last_update'];
                        $this->eCommerceSociete->fk_societe = $dBSociete->id;
                        //if a previous synchro exists
                        if ($synchExists > 0 && !isset($this->error))
                        {
                            //eCommerce update						
                            if ($this->eCommerceSociete->update($this->user) < 0)
                            {
                                $error++;
                                $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceSocieteUpdateError') . ' ' . $societeArray['name'] . ' ' . $societeArray['email'] . ' ' . $societeArray['client'];
                            }
                        }
                        //if no previous synchro exists
                        else
                        {
                            //eCommerce create
                            $this->eCommerceSociete->fk_site = $this->eCommerceSite->id;
                            $this->eCommerceSociete->remote_id = $societeArray['remote_id'];
                            if ($this->eCommerceSociete->create($this->user) < 0)
                            {
                                $error++;
                                $this->errors[] = $this->langs->trans('ECommerceSynchECommerceSocieteCreateError') . ' ' . $societeArray['name'] . ' ' . $societeArray['email'] . ' ' . $societeArray['client'];
                            }
                        }

                        // Sync also people of thirdparty
                        // We can disable this to have contact/address of thirdparty synchronize only when an order or invoice is synchronized
                        $listofaddressids=$this->eCommerceRemoteAccess->getRemoteAddressIdForSociete($societeArray['remote_id']);
                        if (is_array($listofaddressids))
                        {
                            $socpeoples = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrSocpeople($listofaddressids);
                            foreach($socpeoples as $tmpsocpeople)
                            {
                                $tmpsocpeople['fk_soc']=$dBSociete->id;
                                $tmpsocpeople['type']=1;    // address of company
                                $socpeopleCommandeId = $this->synchSocpeople($tmpsocpeople);
                            }
                        }
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    } 
                    else
                    {
                        $error++;
                        $this->errors[] = $this->langs->trans('ECommerceSynchSocieteErrorCreateUpdateSociete') . ' ' . $societeArray['name'] . ' ' . $societeArray['email'] . ' ' . $societeArray['client'];
                    }
                }
                
                if (empty($this->errors) && ! $error)
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchSocieteSuccess');
                    
                    $this->db->commit();
                    return $nbgoodsunchronize;
                }
                else
                {
                    $this->db->rollback();
                    return -1;
                }                
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchSociete');
        }
    }

    
    /**
     * Synchronize socpeople to update for a society: Create or update it into dolibarr, then update the ecommerce_socpeople table.
     * 
     * @param   array       $socpeople  Array array with all params to synchronize
     * @return  int                     Id of socpeople into Dolibarr if OK and false if KO
     */
    public function synchSocpeople($socpeopleArray)
    {
        try {
            dol_syslog("***** eCommerceSynchro synchSocPeople");
            
            if (!isset($this->eCommerceSocpeople))
                $this->initECommerceSocpeople();
            
            //print "Work on remote_id = " .$socpeopleArray['remote_id']." type = ".$socpeopleArray['type']."\n";
            
            //check if contact exists in eCommerceSocpeople table
            // $socpeopleArray['type'] = 1 = Contact de tiers
            // $socpeopleArray['type'] = 2 = Contact de commande
            // $socpeopleArray['type'] = 3 = Contact de facture
            // $socpeopleArray['type'] = 4 = Contact de livraison
            $synchExists = $this->eCommerceSocpeople->fetchByRemoteId($socpeopleArray['remote_id'], $socpeopleArray['type'], $this->eCommerceSite->id);

            //set data into contact
            $dBContact = new Contact($this->db);

            $contactExists = 0;
            
            if ($syncExists > 0)
            {
                $test = $dBContact->fetch($this->eCommerceSocpeople->fk_socpeople);
                if ($test > 0)
                {
                    $contactExists = $dBContact->id;
                }
            }

            if (! $contactExists)
            {
                $dBContact->socid = $socpeopleArray['fk_soc'];
                $dBContact->fk_soc = $socpeopleArray['fk_soc'];
                //$dBContact->fk_pays = $socpeopleArray['fk_pays'];
                $dBContact->lastname = $socpeopleArray['lastname'];
                $dBContact->town = $socpeopleArray['town'];
                $dBContact->ville = $socpeopleArray['town'];
                $dBContact->firstname = $socpeopleArray['firstname'];
                $dBContact->zip = $socpeopleArray['zip'];
                $dBContact->cp = $socpeopleArray['zip'];
                $dBContact->address = $socpeopleArray['address'];
                $dBContact->phone_pro = $socpeopleArray['phone'];
                $dBContact->fax = $socpeopleArray['fax'];
                $dBContact->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                
                $contactExists = $this->getContactIdFromInfos($dBContact);
            }
            
            if ($contactExists)
                $dBContact->id = $contactExists;
           
            //if contact exists in eCommerceSocpeople, contact should exists also in llx_socpeople
            if (($synchExists > 0 && $this->eCommerceSocpeople->fk_socpeople > 0) || $contactExists > 0)
            {
                $refExists = $dBContact->fetch($contactExists > 0 ? $contactExists : $this->eCommerceSocpeople->fk_socpeople);
                
                if ($refExists > 0)
                {
                    //dol_syslog("We don't know if contact on ecommerce was modified so we force update of all fields");
                    //$result = $dBContact->update($dBContact->id, $this->user);
                    $result = 0;
                }
                else if ($refExists == 0)   // If not, we create it
                {
                    $result = $dBContact->create($this->user);
                    if ($result < 0)
                    {
                        $error++;
                        $this->error=$this->langs->trans('ECommerceSynchContactCreateError').' '.$dBContact->error;
                        $this->errors[]=$this->error;
                    }
                }
                else if ($refExistst < 0)
                {
                    $this->errors[] = $this->langs->trans('ECommerceSynchSocieteErrorBetweenECommerceSocpeopleAndContact');
                    return false;
                }
            }
            //if no previous synchro exists
            else
            {
                $result = $dBContact->create($this->user);
            }

            //if create/update of contact table is ok
            if ($result >= 0)
            {
                $this->eCommerceSocpeople->last_update = $socpeopleArray['last_update'];
                $this->eCommerceSocpeople->fk_socpeople = $dBContact->id;
                //if a previous synchro exists
                if ($synchExists > 0)
                {
                    //eCommerce update
                    if ($this->eCommerceSocpeople->update($this->user) < 0)
                    {
                        $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceSocpeopleUpdateError');
                        return false;
                    }
                }
                //if not previous synchro exists
                else
                {
                    //eCommerce create
                    $this->eCommerceSocpeople->fk_site = $this->eCommerceSite->id;
                    $this->eCommerceSocpeople->remote_id = $socpeopleArray['remote_id'];
                    $this->eCommerceSocpeople->type = $socpeopleArray['type'];
                    if ($this->eCommerceSocpeople->create($this->user) < 0)
                    {
                        $this->errors[] = $this->langs->trans('ECommerceSynchECommerceSocpeopleCreateError');
                        return false;
                    }
                }
                return $dBContact->id;
            } else
            {
                $this->errors[] = $this->langs->trans('ECommerceSynchSocpeopleErrorCreateUpdateSocpeople');
                return false;
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchSocpeople');
        }
    }
    
    
    /**
     * Synchronize product to update
     * 
     * @return      void
     */
    public function synchProduct()
    {
        try {
            $nbgoodsunchronize = 0;
            $products = array();
            
            dol_syslog("***** eCommerceSynchro synchProduct");
            $resulttoupdate=$this->getProductToUpdate();
            if (is_array($resulttoupdate))
            {
                if (count($resulttoupdate) > 0) $products = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrProduct($resulttoupdate);
                //var_dump($products);exit;
            }

            if (count($products))
            {
                $this->db->begin();
                
                $ii = 0;
                foreach ($products as $productArray)
                {
                    $error=0;
                    
                    dol_syslog("Process product ecommerce remote_id=".$productArray['remote_id']);

                    //check if product exists in eCommerceProduct (with remote id)
                    $synchExists = $this->eCommerceProduct->fetchByRemoteId($productArray['remote_id'], $this->eCommerceSite->id);

                    $dBProduct = new Product($this->db);
                    
                    // First, we check object does not alreay exists. If not, we create it, if it exists, update it.
                    $refExists = $dBProduct->fetch('', dol_string_nospecial(trim($productArray['ref'])));
                    $result = -1;

                    //libelle of product object = label into database
                    $dBProduct->label = $productArray['label'];
                    $dBProduct->description = $productArray['description'];
                    $dBProduct->weight = $productArray['weight'];
                    $dBProduct->type = $productArray['fk_product_type'];
                    $dBProduct->finished = $productArray['finished'];
                    $dBProduct->status = $productArray['envente'];
                    $dBProduct->price = $productArray['price'];             // New price, later we will save/update price with price_base_type (TE/TI)
                    $dBProduct->tva_tx = $productArray['tax_rate'];
                    $dBProduct->tva_npr = 0;  // Avoiding _log_price's sql blank
                    $dBProduct->country_id = $productArray['fk_country'];
                    $dBProduct->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                    $dBProduct->ref_ext = $this->eCommerceSite->name.'-'.$productArray['remote_id'];
                    $dBProduct->url = $productArray['url'];
                    
                    if ($refExists > 0 && isset($dBProduct->id))
                    {
                        //update
                        $result = $dBProduct->update($dBProduct->id, $this->user);
                        if ($result >= 0)// rajouter constante TTC/HT
                        {
                            // If eCommerce setup hase change and now prices are switch TI/TE (Tax Include / Tax Excluded)
                            if ($dBProduct->price_base_type != $this->eCommerceSite->magento_price_type)
                            {
                                dol_syslog("Setup price for eCommerce are switched from TE toTI or TI to TE, we update price of product");
                                $dBProduct->updatePrice($dBProduct->price, $this->eCommerceSite->magento_price_type, $this->user);
                            }
                        }

                        // We must set the initial stock
                        if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr' && ($productArray['stock_qty'] != $dBProduct->stock_reel)) // Note: $dBProduct->stock_reel is 0 after a creation
                        {
                            dol_syslog("Stock for product updated is ".$productArray['stock_qty']," in ecommerce, but ".$dBProduct->stock_reel." in Dolibarr, we must update it");
                            if (empty($this->eCommerceSite->fk_warehouse))
                            {
                                $error++;
                                $this->errors[]='SetupOfWarehouseNotDefinedForThisSite';
                                break;
                            }
                        
                            // Update/init stock
                            include_once DOL_DOCUMENT_ROOT.'/product/stock/class/mouvementstock.class.php';
                            $movement = new MouvementStock($this->db);
                            $movement->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                            
                            $lot=null;
                            if ($dBProduct->status_batch) $lot='000000';
                            $result = $movement->reception($this->user, $dBProduct->id, $this->eCommerceSite->fk_warehouse, ($productArray['stock_qty'] - $dBProduct->stock_reel), 0, '(StockUpdateFromeCommerceSync)', '', '', $lot);
                            if ($result <= 0)
                            {
                                $error++;
                                $this->error=$this->langs->trans('ECommerceSynchMouvementStockChangeError').' '.$movement->error;
                                $this->errors[]=$this->error;
                            }
                        }
                    }
                    else
                    {
                        //create
                        $dBProduct->ref = dol_string_nospecial(trim($productArray['ref']));
                        $dBProduct->canvas = $productArray['canvas'];
                        $dBProduct->note = 'Initialy created from '.$this->eCommerceSite->name;
                        
                        $result = $dBProduct->create($this->user);
                        if ($result >= 0)// rajouter constante TTC/HT
                        {                            
                            $dBProduct->updatePrice($dBProduct->price, $this->eCommerceSite->magento_price_type, $this->user);                            
                        }
                        else
                        {
                            $error++;
                            if ($dBProduct->error == 'ErrorProductAlreadyExists') $this->error=$this->langs->trans('ECommerceSynchProductCreateError').' '.$this->langs->trans($dBProduct->error, $dBProduct->ref);
                            else $this->error=$this->langs->trans('ECommerceSynchProductCreateError').' '.$dBProduct->error;
                            $this->errors[]=$this->error;
                        }

                        // We must set the initial stock
                        if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr' && ($productArray['stock_qty'] != $dBProduct->stock_reel)) // Note: $dBProduct->stock_reel is 0 after a creation
                        {
                            dol_syslog("Stock for product created is ".$productArray['stock_qty']," in ecommerce, but ".$dBProduct->stock_reel." in Dolibarr, we must update it");
                            if (empty($this->eCommerceSite->fk_warehouse))
                            {
                                $error++;
                                $this->errors[]='SetupOfWarehouseNotDefinedForThisSite';
                                break;
                            }
                        
                            // Update/init stock
                            include_once DOL_DOCUMENT_ROOT.'/product/stock/class/mouvementstock.class.php';
                            $movement = new MouvementStock($this->db);
                            $movement->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;

                            $lot=null;
                            if ($dBProduct->status_batch) $lot='000000';
                            $result = $movement->reception($this->user, $dBProduct->id, $this->eCommerceSite->fk_warehouse, ($productArray['stock_qty'] - $dBProduct->stock_reel), 0, '(StockInitFromeCommerceSync)', '', '', $lot);
                            if ($result <= 0)
                            {
                                $error++;
                                $this->error=$this->langs->trans('ECommerceSynchMouvementStockChangeError').' '.$movement->error;
                                $this->errors[]=$this->error;
                            }
                        }
                    }

                    //if synchro product ok
                    if (! $error && $result >= 0)
                    {
                        // For safety, reinit eCommCat, then getDol catsIds from RemoteIds of the productArray
                        dol_syslog("Synch of product is ok, we check now categories");
                        
                        
                        $this->initECommerceCategory();
                        $catsIds = $this->eCommerceCategory->getDolibarrCategoryFromRemoteIds($productArray['categories']);     // Return array of dolibarr category ids found into link table

                        if (count($catsIds) > 0)  // This product belongs at least to a category
                        {
                            // The category should exist because we run synchCategory before synchProduct in most cases				
                            $cat = new Categorie($this->db);
                            $listofexistingcatsforproduct = array();
                            $tmpcatids = $cat->containing($dBProduct->id, 'product', 'id');
                            if (is_array($listofexistingcatsforproduct)) $listofexistingcatsforproduct = array_values($tmpcatids);
                            
                            foreach ($catsIds as $catId)
                            {
                                if (! in_array($catId, $listofexistingcatsforproduct))
                                {
                                    dol_syslog("The product id=".$dbProduct->id." seems to no be linked yet to category id=".$catId.", so we link it.");
                                    $cat = new Categorie($this->db); // Instanciate a new cat without id (to avoid fetch)
                                    $cat->id = $catId;     // Affecting id (for calling add_type)
                                    $cat->add_type($dBProduct, 'product');
                                    unset($cat);
                                }
                            }
                        } 
                        else      // This product doesn't belongs to any category yet (nothing found int the category link table)
                        {
                            // So we put it into category importRoot defined for the site
                            $cat = new Categorie($this->db);
                            $cat->id = $this->eCommerceSite->fk_cat_product;
                            $cat->add_type($dBProduct, 'product');
                            unset($cat);
                        }
                        //$cat = new Categorie($this->db, $this->eCommerceSite->fk_cat_product);	
                        //$cat->add_type($dBProduct, 'product');					
                        $this->eCommerceProduct->last_update = $productArray['last_update'];
                        $this->eCommerceProduct->fk_product = $dBProduct->id;
                        
                        //if a previous synchro exists
                        if ($synchExists > 0)
                        {
                            //eCommerce update
                            if ($this->eCommerceProduct->update($this->user) < 0)
                            {
                                $error++;
                                $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceProductUpdateError') . ' ' . $productArray['label'];
                                dol_syslog($this->langs->trans('ECommerceSyncheCommerceProductUpdateError') . ' ' . $productArray['label'], LOG_WARNING);
                            }
                        }
                        // if not previous synchro exists into link table (we faild to find it from the remote_id 
                        else
                        {
                            // May be an old record with an old product removed on eCommerce still exists, we delete it before insert.
                            $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_product WHERE fk_product=".$this->eCommerceProduct->fk_product;
                            $resql = $this->db->query($sql);
                            
                            //eCommerce create
                            $this->eCommerceProduct->fk_site = $this->eCommerceSite->id;
                            $this->eCommerceProduct->remote_id = $productArray['remote_id'];
                            if ($this->eCommerceProduct->create($this->user) < 0)
                            {
                                $error++;
                                $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceProductCreateError') . ' ' . $productArray['label'].', '.$this->eCommerceProduct->error;
                                dol_syslog($this->langs->trans('ECommerceSyncheCommerceProductCreateError') . ' ' . $productArray['label'].', '.$this->eCommerceProduct->error, LOG_WARNING);
                            }
                        }
                    } 
                    else
                    {
                        $error++;
                        $this->errors[] = $this->langs->trans('ECommerceSynchProductError') . ' ' . $productArray['label'];
                        dol_syslog($this->langs->trans('ECommerceSynchProductError') . ' ' . $productArray['label'], LOG_WARNING);
                    }
                    
                    if (! $error) 
                    {
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    }
                }
                
                if ($error)
                {
                    $this->db->rollback();
                }
                else
                {
                    $this->db->commit();
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchProductSuccess');
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchProduct');
            dol_syslog($this->langs->trans('ECommerceSynchProductError'), LOG_WARNING);
        }
    }


    /**
     * Synchronize commande to update
     * Inclut synchProduct et synchSociete
     */
    public function synchCommande()
    {
        global $conf, $user;
        
        try {
            $nbgoodsunchronize = 0;
            $nbrecorderror =0;
            $commandes = array();

            dol_syslog("***** eCommerceSynchro synchCommande");
            $resulttoupdate=$this->getCommandeToUpdate();
           
            if (is_array($resulttoupdate))
            {
                if (count($resulttoupdate) > 0) $commandes = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrCommande($resulttoupdate);
            }

            if (count($commandes))
            {
                $this->db->begin();
                
                // Local filter to exclude bundles and other complex types
                $productsTypesOk = array('simple', 'virtual', 'downloadable');
                
                // Loop on each modified order
                foreach ($commandes as $commandeArray)
                {
                    $error = 0;
                    
                    $this->initECommerceCommande();
                    $this->initECommerceSociete();
                    $dBCommande = new Commande($this->db);

                    //check if commande exists in eCommerceCommande (with remote id). It set ->fk_commande. This is a sql request.
                    $synchExists = $this->eCommerceCommande->fetchByRemoteId($commandeArray['remote_id'], $this->eCommerceSite->id);
                    //check if ref exists in commande
                    $refExists = $dBCommande->fetch($this->eCommerceCommande->fk_commande);

                    //check if societe exists in eCommerceSociete (with remote id). This init ->fk_societe. This is a sql request.
                    $societeExists = $this->eCommerceSociete->fetchByRemoteId($commandeArray['remote_id_societe'], $this->eCommerceSite->id);

                    //if societe exists start
                    if ($societeExists > 0)
                    {
                        if ($refExists > 0 && $dBCommande->id > 0)
                        {
                            dol_syslog("synchCommande Order with id=".$dBCommande->id." already exists in Dolibarr");
                            //update commande
                            $result = 1;
                            
                            $tmpdateorder1=dol_print_date($dBCommande->date_commande, 'dayrfc');
                            $tmpdateorder2=dol_print_date(strtotime($commandeArray['date_commande']), 'dayrfc');
                            $tmpdatedeliv1=dol_print_date($dBCommande->date_livraison, 'dayrfc');
                            $tmpdatedeliv2=dol_print_date(strtotime($commandeArray['date_livraison']), 'dayrfc');
                            
                            $dBCommande->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                            
                            if ($dBCommande->ref_client != $commandeArray['ref_client']
                                || $tmpdateorder1 != $tmpdateorder2
                                || $tmpdatedeliv1 != $tmpdatedeliv2
                            )
                            {
                                dol_syslog("Some info has changed on order, we update order");
                                
                                $dBCommande->ref_client = $commandeArray['ref_client'];
                                $dBCommande->date_commande = strtotime($commandeArray['date_commande']);
                                $dBCommande->date_livraison = strtotime($commandeArray['date_livraison']);
                                
                                $result = $dBCommande->update($user);
                                if ($result <= 0) $error++;
                            }
                            
                            // Now update status
                            if (! $error)
                            {
                                if ($dBCommande->statut != $commandeArray['status'])
                                {
                                    dol_syslog("Status of order has changed, we update order from status ".$dBCommande->statut." to status ".$commandeArray['status']);
                                    if ($commandeArray['status'] == Commande::STATUS_DRAFT)
                                    {
                                        $dBCommande->set_draft($user, 0);
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_VALIDATED)
                                    {
                                        $dBCommande->valid($user, 0);
                                    }
                                    if ($commandeArray['status'] == 2)      // Should be Commande::STATUS_SHIPMENTONPROCESS but not defined in dolibarr 3.9 
                                    {
                                        $dBCommande->setStatut(2, $dbCommande->id, $dbCommande->table_element);
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CANCELED)
                                    {
                                        $dBCommande->cancel(0);
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CLOSED)
                                    {
                                        $dBCommande->cloture($user);
                                        $dBCommande->classifyBilled($user);
                                    }
                                }
                            }
                        } 
                        else
                        {
                            dol_syslog("synchCommande Order not found in Dolibarr, so we create it");

                            // First, we check object does not alreay exists. If not, we create it, if it exists, do nothing.
                            $result = $dBCommande->fetch(0, '', $this->eCommerceSite->name.'-'.$commandeArray['ref_client']);
                            if ($result == 0)
                            {
                                //create commande
                                $dBCommande->statut=Commande::STATUS_DRAFT;             // STATUS_DRAFT by default
                                $dBCommande->ref_client = $commandeArray['ref_client'];
                                $dBCommande->ref_ext = $this->eCommerceSite->name.'-'.$commandeArray['ref_client'];
                                $dBCommande->date_commande = strtotime($commandeArray['date_commande']);
                                $dBCommande->date_livraison = strtotime($commandeArray['date_livraison']);
                                $dBCommande->socid = $this->eCommerceSociete->fk_societe;
                                $dBCommande->source=dol_getIdFromCode($this->db, 'OrderByWWW', 'c_input_method', 'code', 'rowid');
                                $dBCommande->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                                
                                $result = $dBCommande->create($this->user);
                                if ($result <= 0) 
                                {
                                    dol_syslog("synchCommande result=".$result." ".$dBCommande->error, LOG_ERR);
                                    $error++;
                                }
                                                        
                                // Add lines
                                if (! $error && count($commandeArray['items']))
                                {
                                    foreach ($commandeArray['items'] as $item)
                                    {
                                        if (in_array($item['product_type'], $productsTypesOk))  // sync of "simple", "virtual", "downloadable"
                                        {
                                            $this->initECommerceProduct();
                                            $this->eCommerceProduct->fetchByRemoteId($item['id_remote_product'], $this->eCommerceSite->id); // load info of table ecommerce_product
                                
                                            // Define the buy price for margin calculation
                                            $buyprice=0;
                                            $fk_product = $this->eCommerceProduct->fk_product;
                                            if (($result = $dBCommande->defineBuyPrice($item['price'], 0, $fk_product)) < 0)
                                            {
                                                $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCommandeUpdateError');
                                                return false;
                                            }
                                            else
                                            {
                                                $buyprice = $result;
                                            }
                                            /*                                            
                                            if (isset($conf->global->MARGIN_TYPE) && $conf->global->MARGIN_TYPE == 'pmp')   // If Rule is on PMP
                                            {
                                                include_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
                                                $product=new Product($this->db);
                                                $product->fetch($fk_product);
                                                $buyprice=$product->pmp;
                                            }
                                            if (empty($buyprice))    // Prend meilleur prix si option meilleur prix on (et donc buyprice par encore defini) ou si PMP n'a rien donné
                                            {
                                                // by external module, take lowest buying price
                                                include_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';
                                                $productFournisseur = new ProductFournisseur($this->db);
                                                $productFournisseur->find_min_price_product_fournisseur($fk_product);
                                                $buyprice = $productFournisseur->fourn_unitprice;
                                            }*/
                                
                                            $result = $dBCommande->addline($item['description'], $item['price'], $item['qty'], $item['tva_tx'], 0, 0,
                                                $this->eCommerceProduct->fk_product, //fk_product
                                                0, //remise_percent
                                                0, //info_bits
                                                0, //fk_remise_except
                                                'HT', //price_base_type
                                                0, //pu_ttc
                                                '', //date_start
                                                '', //date_end
                                                0, //type 0:product 1:service
                                                0, //rang
                                                0, //special_code
                                                0, // fk_parent_line
                                                0, // fk_prod four_price
                                                $buyprice
                                                );
                                            dol_syslog("result=".$result);
                                            if ($result <= 0)
                                            {
                                                $this->errors[]=$dBCommande->error;
                                                $error++;
                                            }
                                
                                            unset($this->eCommerceProduct);
                                        }
                                    }
                                }
                                
                                // Add specific line for delivery
                                if (! $error && $commandeArray['delivery']['qty'] > 0)
                                {
                                    $delivery = $commandeArray['delivery'];
                                
                                    // TODO Get buy price depending on margin option. No margin on delivery ?
                                    $buyprice=0;
                                
                                    $result = $dBCommande->addline($delivery['description'], $delivery['price'], $delivery['qty'], $delivery['tva_tx'], 0, 0,
                                        0, //fk_product
                                        0, //remise_percent
                                        0, //info_bits
                                        0, //fk_remise_except
                                        'HT', //price_base_type
                                        0, //pu_ttc
                                        '', //date_start
                                        '', //date_end
                                        1, //type 0:product 1:service
                                        0, //rang
                                        0, //special_code
                                        0, // fk_parent_line
                                        0, // fk_prod four_price
                                        $buyprice
                                        );
                                    if ($result <= 0)
                                    {
                                        $this->errors[]=$dBCommande->error;
                                        $error++;
                                    }
                                }                            
                            }
                            
                            // Now update status
                            dol_syslog("synchCommande Status of order = ".$commandeArray['status']." dbCommande = ".$dBCommande->statut);
                            if (! $error)
                            {
                                //if ($dBCommande->statut != $commandeArray['status'])      // Always when creating
                                //{
                                    dol_syslog("synchCommande Status of order must be now set, we update order from status ".$dBCommande->statut." to status ".$commandeArray['status']);
                                    if ($commandeArray['status'] == Commande::STATUS_DRAFT)   // status draft. Should not happen with magento
                                    {
                                        // Nothing to do
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_VALIDATED) 
                                    {
                                        $dBCommande->valid($this->user, 0);
                                    }
                                    if ($commandeArray['status'] == 2)            // Should be Commande::STATUS_SHIPMENTONPROCESS but not defined in dolibarr 3.9 
                                    {
                                        $dBCommande->setStatut(2, $dbCommande->id, $dbCommande->table_element);
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CANCELED)
                                    {
                                        $dBCommande->cancel(0);
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CLOSED)
                                    {
                                        $dBCommande->cloture($this->user);
                                        $dBCommande->classifyBilled($this->user);
                                    }
                                //}
                            }
                            
                            //add or update contacts of order
                            $commandeArray['socpeopleCommande']['fk_soc'] = $this->eCommerceSociete->fk_societe;
                            $commandeArray['socpeopleFacture']['fk_soc'] = $this->eCommerceSociete->fk_societe;
                            $commandeArray['socpeopleLivraison']['fk_soc'] = $this->eCommerceSociete->fk_societe;
                            
                            if (! $error)
                            {
                                dol_syslog("synchCommande Now we sync people/address");
                                $socpeopleCommandeId = $this->synchSocpeople($commandeArray['socpeopleCommande']);  // $socpeopleCommandeId = id of socpeople into dolibarr table
                                dol_syslog("synchCommande socpeopleCommandeId = ".$socpeopleCommandeId);
                                $socpeopleFactureId = $this->synchSocpeople($commandeArray['socpeopleFacture']);
                                dol_syslog("synchCommande socpeopleFactureId = ".$socpeopleFactureId);
                                $socpeopleLivraisonId = $this->synchSocpeople($commandeArray['socpeopleLivraison']);
                                dol_syslog("synchCommande socpeopleLivraisonId = ".$socpeopleLivraisonId);
                                
                                if ($socpeopleCommandeId > 0)
                                    $dBCommande->add_contact($socpeopleCommandeId, 'CUSTOMER');
                                if ($socpeopleFactureId > 0)
                                    $dBCommande->add_contact($socpeopleFactureId, 'BILLING');
                                if ($socpeopleLivraisonId > 0)
                                    $dBCommande->add_contact($socpeopleLivraisonId, 'SHIPPING');
                            }
                        }

                        //if synchro commande ok
                        if (! $error)
                        {
                            $this->eCommerceCommande->last_update = $commandeArray['last_update'];
                            $this->eCommerceCommande->fk_commande = $dBCommande->id;
                            //if a previous synchro exists
                            if ($synchExists > 0)
                            {
                                //eCommerce update
                                if ($this->eCommerceCommande->update($this->user) < 0)
                                {
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCommandeUpdateError');
                                }
                            }
                            //if not previous synchro exists
                            else
                            {
                                // May be an old record with an old product removed on eCommerce still exists, we delete it before insert.
                                $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_commande WHERE fk_commande=".$this->eCommerceCommande->fk_commande;
                                $resql = $this->db->query($sql);
                                
                                //eCommerce create
                                $this->eCommerceCommande->fk_site = $this->eCommerceSite->id;
                                $this->eCommerceCommande->remote_id = $commandeArray['remote_id'];
                                //$dBCommande->valid($this->user);
                                if ($this->eCommerceCommande->create($this->user) < 0)
                                {
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCommandeCreateError').' '.$dBCommande->id.', '.$this->eCommerceCommande->error;
                                    dol_syslog($this->langs->trans('ECommerceSyncheCommerceCommandeCreateError') . ' ' . $dBCommande->id.', '.$this->eCommerceCommande->error, LOG_WARNING);
                                }
                            }
                        }
                        else
                        {
                            $this->errors[] = $this->langs->trans('ECommerceSynchCommandeError');
                        }
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    } 
                    else
                    {
                        $error++;
                        $this->errors[] = $this->langs->trans('ECommerceSynchCommandeErrorSocieteNotExists') . ' ' . $commandeArray['remote_id_societe'];
                    }
                    unset($dBCommande);
                    unset($this->eCommerceSociete);
                    unset($this->eCommerceCommande);
                    
                    if ($error) 
                    {
                        $nbrecorderror++;
                        break;      // We decide to stop on first error
                    }
                }
                
                if (! $nbrecorderror)
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchCommandeSuccess');
                    $this->db->commit();
                }
                else
                {
                    $this->db->rollback();
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchCommande');
        }
    }

    /**
     * Synchronize facture to update
     */
    public function synchFacture()
    {
        global $conf, $user;
        
        $factures = array();
        
        try {
            dol_syslog("***** eCommerceSynchro synchFacture");
            
            $resulttoupdate=$this->getFactureToUpdate();
            if (is_array($resulttoupdate))
            {
                if (count($resulttoupdate) > 0) $factures = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrFacture($resulttoupdate);
            }
                
            if (count($factures))
            {
                $this->db->begin();
                
                // Local filter to exclude bundles and other complex types
//                $productsTypesOk = array('simple', 'virtual', 'downloadable');
                
                foreach ($factures as $factureArray)
                {
                    if (isset($this->errors))
                        return false;
                    
                    $result;
                    $this->initECommerceCommande();
                    $this->initECommerceFacture();
                    $this->initECommerceSociete();

                    $dBFacture = new Facture($this->db);
                    $dBCommande = new Commande($this->db);
                    $dBExpedition = new Expedition($this->db);

                    //check if commande exists in eCommerceCommande (with remote_order_id)
                    $synchCommandeExists = $this->eCommerceCommande->fetchByRemoteId($factureArray['remote_order_id'], $this->eCommerceSite->id);
                    
                    //check if ref exists in commande
                    $refCommandeExists = $dBCommande->fetch($this->eCommerceCommande->fk_commande);

                    //check if societe exists
                    $societeExists = $this->eCommerceSociete->fetchByRemoteId($factureArray['remote_id_societe'], $this->eCommerceSite->id);

                    //if societe and commande exists start
                    if ($societeExists > 0 && $synchCommandeExists > 0)
                    {
                        //check if facture exists in eCommerceFacture (with remote id)
                        $synchFactureExists = $this->eCommerceFacture->fetchByRemoteId($factureArray['remote_id'], $this->eCommerceSite->id);
                        if ($synchFactureExists > 0)
                        {
                            //check if facture exists in facture
                            $refFactureExists = $dBFacture->fetch($this->eCommerceFacture->fk_facture);
                            if ($refFactureExists > 0)
                            {
                                //update
                                $result = 1;

                                if ($dBFacture->statut != $factureArray['status'])
                                {
                                    dol_syslog("Status of invoice has changed, we update invoice from status ".$dBFacture->statut." to status ".$factureArray['status']);
                                    if ($factureArray['status'] == Facture::STATUS_DRAFT)   // status draft. Should not happen with magento
                                    {
                                        // Nothing to do
                                    }
                                    if ($factureArray['status'] == Facture::STATUS_VALIDATED)   // status validated done previously.
                                    {
                                        // Nothing to do
                                    }
                                    if ($factureArray['status'] == Facture::STATUS_ABANDONED)
                                    {
                                        $dBFacture->set_canceled($this->user, $factureArray['close_code'], $factureArray['close_note']);
                                    }
                                    if ($factureArray['status'] == Facture::STATUS_CLOSED)
                                    {
                                        // Enter payment
                                        // TODO
                                    }
                                }
                                
                            } else
                            {
                                $this->errors[] = $this->langs->trans('ECommerceSynchFactureErrorFactureSynchExistsButNotFacture');
                                return false;
                            }
                        } 
                        else
                        {
                            //create
                            /* **************************************************************
                             * 
                             * valid order
                             * 
                             * ************************************************************** */
                            if ($refCommandeExists > 0 && $dBCommande->statut == Commande::STATUS_DRAFT)
                            {
                                $idWareHouse = $this->eCommerceSite->fk_warehouse;
                                $dBCommande->valid($this->user, $idWareHouse);
                            }
                            if ($refCommandeExists > 0 && $dBCommande->statut == Commande::STATUS_VALIDATED)
                            {
                                $dBCommande->cloture($this->user);
                            }
                            //var_dump($factureArray);exit;

                            /* **************************************************************
                             * 
                             * create invoice
                             * 
                             * ************************************************************** */

                            $settlementTermsId = $this->getSettlementTermsId($factureArray['code_cond_reglement']);

                            // First, we check object does not alreay exists. If not, we create it, if it exists, do nothing.
                            $result = $dBFacture->fetch(0, '', $this->eCommerceSite->name.'-'.$factureArray['ref_client']);
                            if ($result == 0)
                            {
                                $origin = 'commande';
                                $originid = $dBCommande->id;
                                
                                $dBFacture->ref_client = $factureArray['ref_client'];
                                $dBFacture->ref_ext = $this->eCommerceSite->name.'-'.$factureArray['ref_client'];
                                $dBFacture->date = strtotime($factureArray['date']);
                                $dBFacture->socid = $this->eCommerceSociete->fk_societe;
                                $dBFacture->cond_reglement_id = $settlementTermsId;
                                $dBFacture->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
    
                                // Add link to order (cut takenf from facture card page)
                                $dBFacture->origin = $origin;
                                $dBFacture->origin_id = $originid;
                                $dBFacture->linked_objects[$dBFacture->origin] = $dBFacture->origin_id;
    
                                // Now we create invoice
                                $result = $dBFacture->create($this->user);
    
                                //add or update contacts of invoice
                                $factureArray['socpeopleLivraison']['fk_soc'] = $this->eCommerceSociete->fk_societe;
                                $factureArray['socpeopleFacture']['fk_soc'] = $this->eCommerceSociete->fk_societe;
    
                                $socpeopleLivraisonId = $this->synchSocpeople($factureArray['socpeopleLivraison']);
                                $socpeopleFactureId = $this->synchSocpeople($factureArray['socpeopleFacture']);
    
                                if ($socpeopleLivraisonId > 0)
                                    $dBFacture->add_contact($socpeopleLivraisonId, 'SHIPPING');
                                if ($socpeopleFactureId > 0)
                                    $dBFacture->add_contact($socpeopleFactureId, 'BILLING');
    
                                //add items
                                if (count($factureArray['items']))
                                    foreach ($factureArray['items'] as $item)
                                    {
                                        $this->initECommerceProduct();
                                        $this->eCommerceProduct->fetchByRemoteId($item['id_remote_product'], $this->eCommerceSite->id);
                                        
                                        // Define the buy price for margin calculation
                                        $buyprice=0;
                                        $fk_product = $this->eCommerceProduct->fk_product;
                                        if (($result = $dBFacture->defineBuyPrice($item['price'], 0, $fk_product)) < 0)
                                        {
                                            $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceFactureUpdateError');
                                            return false;
                                        }
                                        else
                                        {
                                            $buyprice = $result;
                                        }
                                        $dBFacture->addline(
                                            $item['description'], 
                                            $item['price'], 
                                            $item['qty'], 
                                            $item['tva_tx'], 
                                            0, 
                                            0, 
                                            $this->eCommerceProduct->fk_product,
                                            0,
                                            '', //date_start
                                            '', //date_end
                                            0, //ventil
                                            0, //info_bits
                                            0, //fk_remise_except
                                            'HT',
                                            0, //pu_ttc
                                            0, // FIXME Use type of article   0:product 1:service
                                            0, //rang
                                            0, //special code
                                            '', // This field seems not used
                                            0, // This field seems not used
                                            0, //fk_parent_line
                                            0, //fk_fourn_price
                                            $buyprice
                                            );
                                        unset($this->eCommerceProduct);
                                    }
    
                                //add delivery
                                if ($factureArray['delivery']['qty'] > 0)
                                {
                                    $delivery = $factureArray['delivery'];
                                    
                                    // TODO Get buy price depending on margin option. No margin on delivery line ?
                                    $buyprice=0;
                                    
                                    $dBFacture->addline($delivery['description'], $delivery['price'], $delivery['qty'], $delivery['tva_tx'], 0, 0, 0, //fk_product
                                            0, //remise_percent
                                            '', //date_start
                                            '', //date_end
                                            0, //ventil
                                            0, //info_bits
                                            0, //fk_remise_except
                                            'HT', //price_base_type
                                            0, //pu_ttc
                                            1, //type 0:product 1:service
                                            0, //rang
                                            0, //special code
                                            '', // origin
                                            0, // origin_id
                                            0, //fk_parent_line
                                            0, //fk_fourn_price
                                            $buyprice
                                    );
                                }
                            }
                            
                            $dBFacture->validate($this->user);
                            
                            // Now update status
                            dol_syslog("synchFacture Status of invoice = ".$factureArray['state']." dbFacture = ".$dBFacture->statut);
                            if (! $error)
                            {
                                //if ($dBFacture->statut != $factureArray['status'])      // Always when creating
                                //{
                                dol_syslog("synchFacture Status of invoice must be now set, we update invoice from status ".$dBFacture->statut." to status ".$factureArray['status']);
                                if ($factureArray['status'] == Facture::STATUS_DRAFT)   // status draft. Should not happen with magento
                                {
                                    // Nothing to do
                                }
                                if ($factureArray['status'] == Facture::STATUS_VALIDATED)   // status validated done previously.
                                {
                                    // Nothing to do
                                }
                                if ($factureArray['status'] == Facture::STATUS_ABANDONED)
                                {
                                    $dBFacture->set_canceled($this->user);
                                }
                                if ($factureArray['status'] == Facture::STATUS_CLOSED)
                                {
                                    // Enter payment
                                    // TODO
                                }
                                //}
                            }                            
                            
                        }
                        
                        /* **************************************************************
                         * 
                         * register into eCommerceFacture
                         * 
                         * ************************************************************** */
                        //if synchro invoice ok
                        if ($result >= 0)
                        {
                            $this->eCommerceFacture->last_update = $factureArray['last_update'];
                            $this->eCommerceFacture->fk_facture = $dBFacture->id;
                            //if a previous synchro exists
                            if ($synchFactureExists > 0)
                            {
                                //eCommerce update
                                if ($this->eCommerceFacture->update($this->user) < 0)
                                {
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceFactureUpdateError');
                                    return false;
                                }
                            }
                            //if not previous synchro exists
                            else
                            {
                                // May be an old record with an old product removed on eCommerce still exists, we delete it before insert.
                                $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_facture WHERE fk_facture=".$this->eCommerceFacture->fk_facture;
                                $resql = $this->db->query($sql);
                                
                                //eCommerce create
                                $this->eCommerceFacture->fk_site = $this->eCommerceSite->id;
                                $this->eCommerceFacture->remote_id = $factureArray['remote_id'];
                                if ($this->eCommerceFacture->create($this->user) < 0)
                                {
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceFactureCreateError').' '.$dBFacture->id.', '.$this->eCommerceFacture->error;
                                    dol_syslog($this->langs->trans('ECommerceSyncheCommerceFactureCreateError') . ' ' . $dBFacture->id.', '.$this->eCommerceFacture->error, LOG_WARNING);
                                    return false;
                                }
                            }
                            $nbgoodsunchronize = $nbgoodsunchronize + 1;
                        } 
                        else
                        {
                            $this->errors[] = $this->langs->trans('ECommerceSynchCommandeError');
                            return false;
                        }
                    } else
                    {
                        $this->errors[] = $this->langs->trans('ECommerceSynchFactureErrorSocieteOrCommandeNotExists');
                        return false;
                    }

                    unset($dBFacture);
                    unset($dBCommande);
                    unset($dBExpedition);
                    unset($this->eCommerceSociete);
                    unset($this->eCommerceFacture);
                    unset($this->eCommerceCommande);
                }
                
                if (! $error)
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchFactureSuccess');
                    $this->db->commit();
                }
                else
                {
                    $this->db->rollback();
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchFacture');
        }
    }

    /**
     * Synchronize shipment
     * 
     * @param   Shipment    $livraison          Shipment object
     * @param   int         $remote_order_id    Remote id of order
     * @return  bool                            true or false
     */
    public function synchLivraison($livraison, $remote_order_id)
    {
        try {
            dol_syslog("***** eCommerceSynchro syncLivraison");
            
            return $this->eCommerceRemoteAccess->createRemoteLivraison($livraison, $remote_order_id);
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorrCeateRemoteLivraison');
        }
    }

    
    
    /**
     * Return dictionnary entry for a code
     * 
     * @param   string    $code         Code of payment term
     * @return  mixed                   Record
     */
    public function getSettlementTermsId($code)
    {
        $table = MAIN_DB_PREFIX . "c_payment_term";
        $eCommerceDict = new eCommerceDict($this->db, $table);
        $settlementTerms = $eCommerceDict->fetchByCode($code);
        return $settlementTerms['rowid'];
    }

    /*private function getAnonymousConstValue()
    {
        $table = MAIN_DB_PREFIX . "const";
        $eCommerceDict = new eCommerceDict($this->db, $table);
        return $eCommerceDict->getAnonymousConstValue();
    }*/

    /**
     * Check if constant ECOMMERCE_COMPANY_ANONYMOUS exists with value of the generic thirdparty id.
     * 
     * @return	int		    <0 if KO, eCommerceAnonymous->id if OK
     */
    /*public function checkAnonymous()
    {
        $dbAnonymousExists=0;
        
        //check if dbSociete anonymous exists
        $dBSociete = new Societe($this->db);
        $anonymousId = $this->getAnonymousConstValue();             // Get id into var ECOMMERCE_COMPANY_ANONYMOUS if it exists
        if ($anonymousId > 0)
        {
            $dbAnonymousExists = $dBSociete->fetch($anonymousId);
        }
        if ($dbAnonymousExists > 0)
        {
            $eCommerceSocieteAnonymous = new eCommerceSociete($this->db);
            $eCommerceAnonymousExists = $eCommerceSocieteAnonymous->fetchByFkSociete($anonymousId, $this->eCommerceSite->id);   // search into llx_ecommerce_societe
            if ($eCommerceAnonymousExists < 0)  // If entry not found into llx_ecommerce_site, we create it.
            {
                $eCommerceSocieteAnonymous->fk_societe = $anonymousId;
                $eCommerceSocieteAnonymous->fk_site = $this->eCommerceSite->id;
                $eCommerceSocieteAnonymous->remote_id = 0;

                if ($eCommerceSocieteAnonymous->create($this->user) < 0)
                {
                    $this->errors[] = $this->langs->trans('ECommerceAnonymousCreateFailed') . ' ' . $this->langs->trans('ECommerceReboot');
                    return -1;
                }
            }
            return $eCommerceSocieteAnonymous->id;
        }
        else
        {
            $this->errors[] = $this->langs->trans('ECommerceNoDbAnonymous') . ' ' . $this->langs->trans('ECommerceReboot');
            return -1;
        }
    }*/

    /**
     * Delete any data linked to synchronization, then delete synchro's datas to clean sync
     * 
     * @param   int     $deletealsoindolibarr       0=Delete only link table, 1=Delete also record in dolibarr
     * @param   string  $mode                       '' to delete all, 'categories', 'products', 'thirdparties', 'orders', 'invoices'
     * @return  void
     */
    public function dropImportedAndSyncData($deletealsoindolibarr, $mode='')
    {
        dol_syslog("***** eCommerceSynchro dropImportedAndSyncData");
        
        // Drop invoices
        if (empty($mode) || preg_match('/^invoices/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceFacture();
            $arrayECommerceFactureIds = $this->eCommerceFacture->getAllECommerceFactureIds($this->eCommerceSite->id);
    
            foreach ($arrayECommerceFactureIds as $idFacture)
            {
                $this->initECommerceFacture();
                if ($this->eCommerceFacture->fetch($idFacture) > 0)
                {
                    if ($deletealsoindolibarr)
                    {
                        $dbFacture = new Facture($this->db);
                        if ($dbFacture->fetch($this->eCommerceFacture->fk_facture) > 0)
                        {
                            $idWarehouse = 0;   // We don't want to change stock here
                            $resultdelete = $dbFacture->delete($dbFacture->id, 0, $idWarehouse);
                            if ($resultdelete > 0)
                                $dolObjectsDeleted++;
                        }
                    }
                    if ($this->eCommerceFacture->delete($this->user) > 0)
                        $synchObjectsDeleted++;
                }
            }
    
            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolFactureSuccess');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchFactureSuccess');
            unset($this->eCommerceFacture);
        }

        //Drop commands
        if (empty($mode) || preg_match('/^orders/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceCommande();
            $arrayECommerceCommandeIds = $this->eCommerceCommande->getAllECommerceCommandeIds($this->eCommerceSite->id);
    
            foreach ($arrayECommerceCommandeIds as $idCommande)
            {
                $this->initECommerceCommande();
                if ($this->eCommerceCommande->fetch($idCommande) > 0)
                {
                    if ($deletealsoindolibarr)
                    {
                        $dbCommande = new Commande($this->db);
                        if ($dbCommande->fetch($this->eCommerceCommande->fk_commande) > 0)
                        {
                            $resultdelete = $dbCommande->delete($this->user);
                            if ($resultdelete > 0)
                                $dolObjectsDeleted++;
                        }
                    }
                    if ($this->eCommerceCommande->delete($this->user) > 0)
                        $synchObjectsDeleted++;
                }
            }
    
            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolCommandeSuccess');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchCommandeSuccess');
            unset($this->eCommerceCommande);
        }

        //Drop products
        if (empty($mode) || preg_match('/^products/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceProduct();
            $arrayECommerceProductIds = $this->eCommerceProduct->getAllECommerceProductIds($this->eCommerceSite->id);
    
            foreach ($arrayECommerceProductIds as $idProduct)
            {
                $this->initECommerceProduct();
                if ($this->eCommerceProduct->fetch($idProduct) > 0)
                {
                    if ($deletealsoindolibarr)
                    {
                        $dbProduct = new Product($this->db);
                        if ($dbProduct->fetch($this->eCommerceProduct->fk_product) > 0)
                        {
                            $resultdelete = $dbProduct->delete();
                            if ($resultdelete > 0)
                                $dolObjectsDeleted++;
                        }
                    }
                    if ($this->eCommerceProduct->delete($this->user, 0) > 0)
                        $synchObjectsDeleted++;
                }
            }
    
            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolProductSuccess');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchProductSuccess');
            unset($this->eCommerceProduct);
        }

        //Drop socPeople
        if (empty($mode) || preg_match('/^contacts/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceSocpeople();
            $arrayECommerceSocpeopleIds = $this->eCommerceSocpeople->getAllECommerceSocpeopleIds($this->eCommerceSite->id);
    
            foreach ($arrayECommerceSocpeopleIds as $idSocpeople)
            {
                $this->initECommerceSocpeople();
                if ($this->eCommerceSocpeople->fetch($idSocpeople) > 0)
                {
                    if ($deletealsoindolibarr)
                    {
                        $dbSocpeople = new Contact($this->db);
                        if ($dbSocpeople->fetch($this->eCommerceSocpeople->fk_socpeople) > 0)
                        {
                            $resultdelete = $dbSocpeople->delete(0);
                            if ($resultdelete > 0)
                                $dolObjectsDeleted++;
                        }
                    }
                    if ($this->eCommerceSocpeople->delete($this->user, 0) > 0)
                        $synchObjectsDeleted++;
                }
            }
    
            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolSocpeopleSuccess');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchSocpeopleSuccess');
            unset($this->eCommerceSocpeople);
        }

        //Drop societes
        if (empty($mode) || preg_match('/^thirdparties/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceSociete();
            $arrayECommerceSocieteIds = $this->eCommerceSociete->getAllECommerceSocieteIds($this->eCommerceSite->id);
    
            foreach ($arrayECommerceSocieteIds as $idSociete)
            {
                $this->initECommerceSociete();
                if ($this->eCommerceSociete->fetch($idSociete) > 0)
                {
                    if ($deletealsoindolibarr)
                    {
                        $dbSociete = new Societe($this->db);
                        if ($dbSociete->fetch($this->eCommerceSociete->fk_societe) > 0)
                        {
                            $resultdelete = $dbSociete->delete($dbSociete->id, $this->user, 1);
                            if ($resultdelete > 0)
                                $dolObjectsDeleted++;
                        }
                    }
                    if ($this->eCommerceSociete->delete($this->user, 0) > 0)
                        $synchObjectsDeleted++;
                }
            }
    
            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolSocieteSuccess');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchSocieteSuccess');
            unset($this->eCommerceSociete);
        }

        //Drop categories	
        if (empty($mode) || preg_match('/^categories/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceCategory();
            $arrayECommerceCategoryIds = $this->eCommerceCategory->getAllECommerceCategoryIds($this->eCommerceSite);
    
            foreach ($arrayECommerceCategoryIds as $idCategory)
            {
                $this->initECommerceCategory();
                if ($this->eCommerceCategory->fetch($idCategory) > 0)
                {
                    if ($deletealsoindolibarr)
                    {
                        $dbCategory = new Categorie($this->db);
                        if ($dbCategory->fetch($this->eCommerceCategory->fk_category) > 0)
                        {
                            $resultdelete = $dbCategory->delete($this->user);
                            if ($resultdelete > 0)
                                $dolObjectsDeleted++;
                        }
                    }
                    if ($this->eCommerceCategory->delete($this->user, 0) > 0)
                        $synchObjectsDeleted++;
                }
            }
    
            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolCategorySuccess');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchCategorySuccess');
            unset($this->eCommerceCategory);
        }
    }

    
    public function __destruct()
    {
        unset($this->eCommerceRemoteAccess);
    }

    
    /** 
	 * Function to check if a contact informations passed by params exists in DB.
	 *  
	 * @param      Contact     $contact        Object Contact
	 * @return	   int                         <0 if KO, >0 id of first contact corresponding if OK
	 */
	function getContactIdFromInfos($contact)
	{	
	    global $db;
	    
		$contactId = -1;
		
		$sql  = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'socpeople';
		$sql .= ' WHERE lastname="'.$db->escape(trim($contact->lastname)).'"';
		$sql .= ' AND firstname="'.$db->escape(trim($contact->firstname)).'"';
		$sql .= ' AND address="'.$db->escape(trim($contact->address)).'"';
		$sql .= ' AND town="'.$db->escape(trim($contact->town)).'"';
		$sql .= ' AND zip="'.$db->escape(trim($contact->zip)).'"';
		$sql .= ' AND fk_soc="'.$contact->fk_soc.'"';

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
			    $contactId = 0;
			}
			$this->db->free($resql);
			return $contactId;
		}
		else 
		{
			$this->error=$this->db->lasterror();
			dol_syslog("eCommerceSynchro::getContactIdFromInfos ".$this->error, LOG_ERR);
			return -1;
		}
	}
	
    
}

