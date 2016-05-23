<?php

/*
 * @module		ECommerce
 * @version		1.2
 * @copyright	Auguria
 * @author		<franck.charpentier@auguria.net>
 * @licence		GNU General Public License
 */

/**
 * Class for synchronize remote sites with Dolibarr
 */
 
dol_include_once('/ecommerce/class/data/eCommerceRemoteAccess.class.php');
dol_include_once('/ecommerce/class/data/eCommerceCommande.class.php');
dol_include_once('/ecommerce/class/data/eCommerceFacture.class.php');
dol_include_once('/ecommerce/class/data/eCommerceProduct.class.php');
dol_include_once('/ecommerce/class/data/eCommerceSociete.class.php');
dol_include_once('/ecommerce/class/data/eCommerceSocpeople.class.php');
dol_include_once('/ecommerce/class/data/eCommerceSite.class.php');
dol_include_once('/ecommerce/class/data/eCommerceCategory.class.php');
dol_include_once('/ecommerce/admin/class/data/eCommerceDict.class.php');

require_once(DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php');
require_once(DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once(DOL_DOCUMENT_ROOT . '/product/class/product.class.php');
require_once(DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php');
require_once(DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php');
require_once(DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php');
require_once(DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php');



class eCommerceSynchro
{
    public $errors;
    public $success;
    public $langs;
    public $user;
    
    //Data access
    private $db;
    private $eCommerceSite;
    private $eCommerceSociete;
    private $eCommerceSocpeople;
    private $eCommerceProduct;
    private $eCommerceCategory;
    private $eCommerceMotherCategory;
    private $eCommerceCommande;
    private $eCommerceFacture;
    private $eCommerceRemoteAccess;
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
     * @param eCommerceSote     $site         Object eCommerceSite       
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
                $this->errors[] = $this->langs->trans('ECommerceConnectErrorCheckUsernamePasswordAndAdress');
                $this->errors = array_merge($this->errors, $this->eCommerceRemoteAccess->errors);
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
    
    public function getNbSocieteInDolibarr()
    {
        $sql="SELECT COUNT(s.rowid) as nb FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."categorie_societe as cs";
        $sql.=" WHERE s.rowid = cs.fk_soc AND cs.fk_categorie = ".$this->eCommerceSite->fk_cat_societe;

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
                    // Clean orphelins entries to have a clean database (having such records should not happen)
                    $sql = "DELETE FROM ".MAIN_DB_PREFIX."ecommerce_category WHERE fk_category NOT IN (select rowid from ".MAIN_DB_PREFIX."categorie)";
                    $this->db->query($sql);
                    
                    $resanswer = array();
                    eCommerceCategory::cuttingCategoryTreeFromMagentoToDolibarrNew($tmp, $resanswer);

                    foreach ($resanswer as $remoteCatToCheck) // Check update for each entry into $resanswer -> $remoteCatToCheck = array('category_id'=>, 'parent_id'=>...)
                    {
                        $this->initECommerceCategory(); // Initialise 2 properties eCommerceCategory and eCommerceMotherCategory
                        
                        // Complete info of $remoteCatToCheck
                        $tmp=$this->eCommerceRemoteAccess->getCategoryData($remoteCatToCheck['category_id']);
                        
                        $remoteCatToCheck['updated_at']=$tmp['updated_at'];

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
     * @param $force bool to force update
     * @return array
     */
    public function getProductToUpdate($force = false)
    {
        try {
            if (!isset($this->productToUpdate) || $force == true)
            {
                $this->productToUpdate = $this->eCommerceRemoteAccess->getProductToUpdate($this->getProductLastUpdateDate($force), $this->toDate);
            }
            return $this->productToUpdate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetProductToUpdate');
        }
    }

    /**
     * Get modified societe since the last update
     * @param $force bool to force update
     * @return array
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
     * @param $force bool to force update
     * @return array
     */
    public function getCommandeToUpdate($force = false)
    {
        try {
            if (!isset($this->commandeToUpdate) || $force == true)
                $this->commandeToUpdate = $this->eCommerceRemoteAccess->getCommandeToUpdate($this->getCommandeLastUpdateDate($force), $this->toDate);
            return $this->commandeToUpdate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetCommandeToUpdate');
        }
    }

    /**
     * Get modified facture since the last update
     * @param $force bool to force update
     * @return array
     */
    public function getFactureToUpdate($force = false)
    {
        try {
            if (!isset($this->factureToUpdate) || $force == true)
                $this->factureToUpdate = $this->eCommerceRemoteAccess->getFactureToUpdate($this->getFactureLastUpdateDate($force), $this->toDate);
            return $this->factureToUpdate;
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorGetFactureToUpdate');
        }
    }

    
    
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

            // Safety check : importRootCategory exists
            $dBCategorie = new Categorie($this->db);
            $importRootExists = ($dBCategorie->fetch($this->eCommerceSite->fk_cat_product) > 0) ? 1 : 0;

            if ($importRootExists)
            {
                $this->db->begin();
                
                dol_syslog("synchCategory::getCategoriesToUpdate");
                $categories = $this->getCategoriesToUpdate();   // Return list of all categories that were modified on ecommerce side
                if (count($categories))
                {
                    foreach ($categories as $categoryArray)     // Loop on each categories found on ecommerce side
                    {
                        dol_syslog("synchCategory Process sync of magento category_id=".$categoryArray['category_id']." name=".$categoryArray['name']);

                        $this->initECommerceCategory();             // Initialise new objects
                        $dBCategorie = new Categorie($this->db);

                        // Mother should exists in eCommerceCat, if not, that implies motherIsImportRoot					
                        $motherExists = $this->eCommerceMotherCategory->fetchByRemoteId($categoryArray['parent_id'], $this->eCommerceSite->id);
                        // Now $this->eCommerceMotherCategory contains the mother category or null
                        
                        // if fetch on eCommerceMotherCat has failed
                        if ($motherExists < 1 && ($this->eCommerceMotherCategory->fetchByFKCategory($this->eCommerceSite->fk_cat_product, $this->eCommerceSite->id) < 0))
                        {
                            // get the importRootCategory defined in eCommerceSite 
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
                                // Should not happend because we added a cleaned of all orphelins entrie into getCategoriesToUpdate
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
                                    $this->errors[] = $this->errors . '<br/>' . $this->langs->trans('ECommerceSyncheCommerceCategoryCreateError') . ' ' . $categoryArray['label'];
                                    break;
                                }
                            }
                        } 
                        else
                        {
                            $error++;
                            $this->errors[] = $this->errors . '<br/>' . $this->langs->trans('ECommerceSynchCategoryError');
                            break;
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
                $this->errors[] = $this->langs->trans('ECommerceSynchCategoryNoImportRoot');
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
            if ($this->getNbSocieteToUpdate(true) > 0)
                $societes = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrSociete($this->getSocieteToUpdate());

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
                            $dBSociete->email = $societeArray['email'];
                            $dBSociete->client = $societeArray['client'];
                            $dBSociete->tva_intra = $societeArray['vatnumber'];
                            $dBSociete->tva_assuj = 1;      // tba_intra is not saved if this field is not set

                            $result = $dBSociete->update($dBSociete->id, $this->user);
                        } else
                        {
                            $this->errors[] = $this->langs->trans('ECommerceSynchSocieteErrorBetweenECommerceSocieteAndSociete');
                            return false;
                        }
                    }
                    //if societe not exists in eCommerceSociete, societe is created
                    else
                    {
                        $dBSociete->name = $societeArray['name'];
                        $dBSociete->email = $societeArray['email'];
                        $dBSociete->client = $societeArray['client'];
                        $dBSociete->tva_intra = $societeArray['vatnumber'];
                        $dBSociete->tva_assuj = 1;      // tba_intra is not saved if this field is not set
                        
                        $result = $dBSociete->create($this->user);
                    }
                    //if create/update of societe table ok
                    if ($result >= 0)
                    {
                        //set category					
                        $cat = new Categorie($this->db);
                        $cat->fetch($this->eCommerceSite->fk_cat_societe);
                        $cat->add_type($dBSociete, 'customer');

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
                                $this->errors[] = $this->errors . '<br/>' . $this->langs->trans('ECommerceSynchECommerceSocieteCreateError') . ' ' . $societeArray['name'] . ' ' . $societeArray['email'] . ' ' . $societeArray['client'];
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
                        $this->errors[] = $this->errors . '<br/>' . $this->langs->trans('ECommerceSynchSocieteErrorCreateUpdateSociete') . ' ' . $societeArray['name'] . ' ' . $societeArray['email'] . ' ' . $societeArray['client'];
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
            
            //$this->synchCategory();
            
            $nbofproduct = $this->getNbProductToUpdate(true);

            if ($nbofproduct > 0)
            {
                $products = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrProduct($this->getProductToUpdate());
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

                    //check if ref exists in product
                    $dBProduct = new Product($this->db);
                    $refExists = $dBProduct->fetch('', $productArray['ref']);
                    $result = -1;

                    //libelle of product object = label into database
                    $dBProduct->label = $productArray['label'];
                    $dBProduct->description = $productArray['description'];
                    $dBProduct->weight = $productArray['weight'];
                    $dBProduct->type = $productArray['fk_product_type'];
                    $dBProduct->finished = $productArray['finished'];
                    $dBProduct->status = $productArray['envente'];
                    $dBProduct->price = $productArray['price'];
                    $dBProduct->tva_tx = $productArray['tax_rate'];
                    $dBProduct->tva_npr = 0;  // Avoiding _log_price's sql blank
                    $dBProduct->country_id = $productArray['fk_country'];

                    if ($refExists > 0 && isset($dBProduct->id))
                    {
                        //update
                        $result = $dBProduct->update($dBProduct->id, $this->user);
                        if ($result >= 0)// rajouter constante TTC/HT
                        {
                            $dBProduct->updatePrice($dBProduct->price, $this->eCommerceSite->magento_price_type, $this->user);
                        }
                        
                        // We must update the stock ?
                        // TODO
                    }
                    else
                    {
                        //create
                        $dBProduct->ref = $productArray['ref'];
                        $dBProduct->canvas = $productArray['canvas'];
                        $result = $dBProduct->create($this->user);
                        if ($result >= 0)// rajouter constante TTC/HT
                        {                            
                            $dBProduct->updatePrice($dBProduct->price, $this->eCommerceSite->magento_price_type, $this->user);                            
                        }
                        
                        // We must set the initial stock
                        // TODO
                    }

                    //if synchro product ok
                    if ($result >= 0)
                    {
                        // For safety, reinit eCommCat, then getDol catsIds from RemoteIds of the productArray
                        $this->initECommerceCategory();
                        $catsIds = $this->eCommerceCategory->getDolibarrCategoryFromRemoteIds($productArray['categories']);

                        if (count($catsIds) > 0)  // This product belongs at least to a category
                        {
                            foreach ($catsIds as $catId)
                            {
                                // The category must exist because of synchCategory on start of synchProduct				
                                $cat = new Categorie($this->db); // Instanciate a new cat without id (to avoid fetch)
                                $cat->id = $catId;     // Affecting id (for calling add_type)
                                $cat->add_type($dBProduct, 'product');
                                unset($cat);
                            }
                        } else      // This product doesn't belongs to any category
                        {
                            // So we put it in importRoot defined for the site
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
                                $this->errors[] = $this->error . '<br>' . $this->langs->trans('ECommerceSyncheCommerceProductUpdateError') . ' ' . $productArray['label'];
                                dol_syslog($this->error . '<br>' . $this->langs->trans('ECommerceSyncheCommerceProductUpdateError') . ' ' . $productArray['label'], LOG_WARNING);
                            }
                        }
                        //if not previous synchro exists
                        else
                        {
                            //eCommerce create
                            $this->eCommerceProduct->fk_site = $this->eCommerceSite->id;
                            $this->eCommerceProduct->remote_id = $productArray['remote_id'];
                            if ($this->eCommerceProduct->create($this->user) < 0)
                            {
                                $error++;
                                $this->errors[] = $this->error . '<br>' . $this->langs->trans('ECommerceSyncheCommerceProductCreateError') . ' ' . $productArray['label'];
                                dol_syslog($this->error . '<br>' . $this->langs->trans('ECommerceSyncheCommerceProductCreateError') . ' ' . $productArray['label'], LOG_WARNING);
                            }
                        }
                    } 
                    else
                    {
                        $error++;
                        $this->errors[] = $this->error . '<br>' . $this->langs->trans('ECommerceSynchProductError') . ' ' . $productArray['label'];
                        dol_syslog($this->error . '<br>' . $this->langs->trans('ECommerceSynchProductError') . ' ' . $productArray['label'], LOG_WARNING);
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
            
            /*$this->synchSociete();
            $this->synchProduct();*/

            if ($this->getNbCommandeToUpdate(true) > 0)
                $commandes = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrCommande($this->getCommandeToUpdate());

            if (count($commandes))
            {
                $this->db->begin();
                
                // Local filter to exclude bundles and other complex types
                $productsTypesOk = array('simple', 'virtual', 'downloadable');
                
                foreach ($commandes as $commandeArray)
                {
                    $this->initECommerceCommande();
                    $this->initECommerceSociete();
                    $dBCommande = new Commande($this->db);

                    //check if commande exists in eCommerceCommande (with remote id)
                    $synchExists = $this->eCommerceCommande->fetchByRemoteId($commandeArray['remote_id'], $this->eCommerceSite->id);
                    //check if ref exists in commande
                    $refExists = $dBCommande->fetch($this->eCommerceCommande->fk_commande);

                    //check if societe exists. This init $this->eCommerceSociete->fk_societe
                    $societeExists = $this->eCommerceSociete->fetchByRemoteId($commandeArray['remote_id_societe'], $this->eCommerceSite->id);

                    //if societe exists start
                    if ($societeExists > 0)
                    {
                        if ($refExists > 0 && $dBCommande->id > 0)
                        {
                            dol_syslog("synchCommande Order with id=".$dBCommande->id." already exists");
                            //update commande
                            $result = 1;
                            
                            $tmpdateorder1=dol_print_date($dBCommande->date_commande, 'dayrfc');
                            $tmpdateorder2=dol_print_date(strtotime($commandeArray['date_commande']), 'dayrfc');
                            $tmpdatedeliv1=dol_print_date($dBCommande->date_livraison, 'dayrfc');
                            $tmpdatedeliv2=dol_print_date(strtotime($commandeArray['date_livraison']), 'dayrfc');
                            
                            if ($dBCommande->ref_client != $commandeArray['ref_client']
                                || $tmpdateorder1 != $tmpdateorder2
                                || $tmpdatedeliv1 != $tmpdatedeliv2
                            )
                            {
                                dol_syslog("Some info has changed on order, we update order");
                                
                                $dBCommande->ref_client = $commandeArray['ref_client'];
                                $dBCommande->date_commande = strtotime($commandeArray['date_commande']);
                                $dBCommande->date_livraison = strtotime($commandeArray['date_livraison']);
                                $dBCommande->update($user);
                            }
                            
                            if ($dBCommande->statut != $commandeArray['status'])
                            {
                                dol_syslog("Status of order has changed, we update order from status "+$dBCommande->statut+" to status "+$commandeArray['status']);
                                if ($commandeArray['status'] == 0)
                                {
                                    $dBCommande->set_draft($user, 0);
                                }
                                if ($commandeArray['status'] == 1)
                                {
                                    $dBCommande->valid($user, 0);
                                }
                                if ($commandeArray['status'] == -1)
                                {
                                    $dBCommande->cancel(0);
                                }
                            }
                        } 
                        else
                        {
                            dol_syslog("Create order");

                            //create commande
                            $dBCommande->statut=0;  // draft == pending on magento
                            $dBCommande->ref_client = $commandeArray['ref_client'];
                            $dBCommande->date_commande = strtotime($commandeArray['date_commande']);
                            $dBCommande->date_livraison = strtotime($commandeArray['date_livraison']);
                            $dBCommande->socid = $this->eCommerceSociete->fk_societe;
                            $dBCommande->source=dol_getIdFromCode($this->db, 'OrderByWWW', 'c_input_method', 'code', 'rowid');

                            $result = $dBCommande->create($this->user);

                            if ($dBCommande->statut != $commandeArray['status'])
                            {
                                dol_syslog("synchCommande Status of order must be now set, we update order from status "+$dBCommande->statut+" to status "+$commandeArray['status']);
                                if ($commandeArray['status'] == 0)
                                {
                                    $dBCommande->set_draft($user, 0);
                                }
                                if ($commandeArray['status'] == 1)
                                {
                                    $dBCommande->valid($user, 0);
                                }
                                if ($commandeArray['status'] == -1)
                                {
                                    $dBCommande->cancel(0);
                                }
                            }
                            
                            //add or update contacts of order
                            $commandeArray['socpeopleCommande']['fk_soc'] = $this->eCommerceSociete->fk_societe;
                            $commandeArray['socpeopleFacture']['fk_soc'] = $this->eCommerceSociete->fk_societe;
                            $commandeArray['socpeopleLivraison']['fk_soc'] = $this->eCommerceSociete->fk_societe;

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

                            //add items
                            dol_syslog("synchCommande Now we add lines on order");
                            if (count($commandeArray['items'])) {
                                foreach ($commandeArray['items'] as $item)
                                {
                                    if (in_array($item['product_type'], $productsTypesOk))  // sync of "simple", "virtual", "downloadable"
                                    {
                                        $this->initECommerceProduct();
                                        $this->eCommerceProduct->fetchByRemoteId($item['id_remote_product'], $this->eCommerceSite->id); // load info of table ecommerce_product
                                        
                                        // Define the buy price for margin calculation
                                        $buyprice=0;
                                        $fk_product = $this->eCommerceProduct->fk_product;
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
                            			}
                                        
                                        $dBCommande->addline($item['description'], $item['price'], $item['qty'], $item['tva_tx'], 0, 0, 
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
                                        unset($this->eCommerceProduct);
                                    }
                                }
                            }
                            //add delivery
                            if ($commandeArray['delivery']['qty'] > 0)
                            {
                                $delivery = $commandeArray['delivery'];
                                
                                // TODO Get buy price depending on margin option. No margin on delivery ?
                                $buyprice=0;
                                
                                $dBCommande->addline($delivery['description'], $delivery['price'], $delivery['qty'], $delivery['tva_tx'], 0, 0, 
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
                            }
                        }

                        //if synchro commande ok
                        if ($result >= 0)
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
                                //eCommerce create
                                $this->eCommerceCommande->fk_site = $this->eCommerceSite->id;
                                $this->eCommerceCommande->remote_id = $commandeArray['remote_id'];
                                //$dBCommande->valid($this->user);
                                if ($this->eCommerceCommande->create($this->user) < 0)
                                {
                                    $error++;
                                    $this->errors[] = $this->errors . '<br>' . $this->langs->trans('ECommerceSyncheCommerceCommandeCreateError') . ' ' . $dBCommande->id;
                                }
                            }
                        } else
                        {
                            $this->errors[] = $this->errors . '<br>' . $this->langs->trans('ECommerceSynchCommandeError');
                        }
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    } else
                    {
                        $error++;
                        $this->errors[] = $this->errors . '<br>' . $this->langs->trans('ECommerceSynchCommandeErrorSocieteNotExists') . ' ' . $commandeArray['remote_id_societe'];
                    }
                    unset($dBCommande);
                    unset($this->eCommerceSociete);
                    unset($this->eCommerceCommande);
                }
                
                if (! $error)
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
        
        try {
            
            //Synchronize orders before
            //$this->synchCommande();
            
            $nbgoodsunchronize = 0;
            if ($this->getNbFactureToUpdate(true) > 0)
                $factures = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrFacture($this->getFactureToUpdate());
            
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

                    //check if commande exists in eCommerceCommande (with remote id)
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
                            if ($refCommandeExists > 0)
                            {
                                $dBCommande->valid($this->user);
                            }
                            
                            /* **************************************************************
                             * 
                             * create invoice
                             * 
                             * ************************************************************** */

                            $settlementTermsId = $this->getSettlementTermsId($factureArray['code_cond_reglement']);

                            $dBFacture->ref_client = $factureArray['ref_client'];
                            $dBFacture->date = strtotime($factureArray['date']);
                            $dBFacture->socid = $this->eCommerceSociete->fk_societe;
                            $dBFacture->cond_reglement_id = $settlementTermsId;
                            $dBFacture->origin = 'commande';
                            $dBFacture->origin_id = $dBCommande->id;

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
                                        'HT',
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
                                    unset($this->eCommerceProduct);
                                }

                            //add delivery
                            if ($factureArray['delivery']['qty'] > 0)
                            {
                                $delivery = $factureArray['delivery'];
                                
                                // TODO Get buy price depending on margin option. No margin on delivery ?
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

                            $dBFacture->validate($this->user);
                        }

                        /* **************************************************************
                         * 
                         * register into eCommerceFacture
                         * 
                         * ************************************************************** */
                        //if synchro commande ok
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
                                //eCommerce create
                                $this->eCommerceFacture->fk_site = $this->eCommerceSite->id;
                                $this->eCommerceFacture->remote_id = $factureArray['remote_id'];
                                if ($this->eCommerceFacture->create($this->user) < 0)
                                {
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceFactureCreateError');
                                    return false;
                                }
                            }
                            $nbgoodsunchronize = $nbgoodsunchronize + 1;
                        } else
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
     * Synchronize delivery
     * @param object livraison
     * @return bool true or false
     */
    public function synchLivraison($livraison, $remote_order_id)
    {
        try {
            return $this->eCommerceRemoteAccess->createRemoteLivraison($livraison, $remote_order_id);
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorrCeateRemoteLivraison');
        }
    }

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
     */
    public function dropImportedAndSyncData()
    {
        // Drop invoices
        $dolObjectsDeleted = 0;
        $synchObjectsDeleted = 0;
        $this->initECommerceFacture();
        $arrayECommerceFactureIds = $this->eCommerceFacture->getAllECommerceFactureIds($this->eCommerceSite->id);

        foreach ($arrayECommerceFactureIds as $idFacture)
        {
            $this->initECommerceFacture();
            if ($this->eCommerceFacture->fetch($idFacture) > 0)
            {
                $dbFacture = new Facture($this->db);
                if ($dbFacture->fetch($this->eCommerceFacture->fk_facture) > 0)
                {
                    if ($dbFacture->delete() > 0)
                        $dolObjectsDeleted++;
                }
                if ($this->eCommerceFacture->delete($this->user) > 0)
                    $synchObjectsDeleted++;
            }
        }

        $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolFactureSuccess');
        $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchFactureSuccess');
        unset($this->eCommerceFacture);


        //Drop commands
        $dolObjectsDeleted = 0;
        $synchObjectsDeleted = 0;
        $this->initECommerceCommande();
        $arrayECommerceCommandeIds = $this->eCommerceCommande->getAllECommerceCommandeIds($this->eCommerceSite->id);

        foreach ($arrayECommerceCommandeIds as $idCommande)
        {
            $this->initECommerceCommande();
            if ($this->eCommerceCommande->fetch($idCommande) > 0)
            {
                $dbCommande = new Commande($this->db);
                if ($dbCommande->fetch($this->eCommerceCommande->fk_commande) > 0)
                {
                    if ($dbCommande->delete($this->user) > 0)
                        $dolObjectsDeleted++;
                }
                if ($this->eCommerceCommande->delete($this->user) > 0)
                    $synchObjectsDeleted++;
            }
        }

        $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolCommandeSuccess');
        $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchCommandeSuccess');
        unset($this->eCommerceCommande);


        //Drop products
        $dolObjectsDeleted = 0;
        $synchObjectsDeleted = 0;
        $this->initECommerceProduct();
        $arrayECommerceProductIds = $this->eCommerceProduct->getAllECommerceProductIds($this->eCommerceSite->id);

        foreach ($arrayECommerceProductIds as $idProduct)
        {
            $this->initECommerceProduct();
            if ($this->eCommerceProduct->fetch($idProduct) > 0)
            {
                $dbProduct = new Product($this->db);
                if ($dbProduct->fetch($this->eCommerceProduct->fk_product) > 0)
                {
                    if ($dbProduct->delete($dbProduct->id) >= 0)
                        $dolObjectsDeleted++;
                }
                if ($this->eCommerceProduct->delete($this->user) > 0)
                    $synchObjectsDeleted++;
            }
        }

        $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolProductSuccess');
        $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchProductSuccess');
        unset($this->eCommerceProduct);


        //Drop socPeople
        $dolObjectsDeleted = 0;
        $synchObjectsDeleted = 0;
        $this->initECommerceSocpeople();
        $arrayECommerceSocpeopleIds = $this->eCommerceSocpeople->getAllECommerceSocpeopleIds($this->eCommerceSite->id);

        foreach ($arrayECommerceSocpeopleIds as $idSocpeople)
        {
            $this->initECommerceSocpeople();
            if ($this->eCommerceSocpeople->fetch($idSocpeople) > 0)
            {
                $dbSocpeople = new Contact($this->db);
                if ($dbSocpeople->fetch($this->eCommerceSocpeople->fk_socpeople) > 0)
                {
                    if ($dbSocpeople->delete() > 0)
                        $dolObjectsDeleted++;
                }
                if ($this->eCommerceSocpeople->delete($this->user) > 0)
                    $synchObjectsDeleted++;
            }
        }

        $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolSocpeopleSuccess');
        $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchSocpeopleSuccess');
        unset($this->eCommerceSocpeople);


        //Drop societes
        $dolObjectsDeleted = 0;
        $synchObjectsDeleted = 0;
        $this->initECommerceSociete();
        $arrayECommerceSocieteIds = $this->eCommerceSociete->getAllECommerceSocieteIds($this->eCommerceSite->id);

        foreach ($arrayECommerceSocieteIds as $idSociete)
        {
            $this->initECommerceSociete();
            if ($this->eCommerceSociete->fetch($idSociete) > 0)
            {
                $dbSociete = new Societe($this->db);
                if ($dbSociete->fetch($this->eCommerceSociete->fk_societe) > 0)
                {
                    if ($dbSociete->delete($dbSociete->id,$this->user) > 0)
                        $dolObjectsDeleted++;
                }
                if ($this->eCommerceSociete->delete($this->user) > 0)
                    $synchObjectsDeleted++;
            }
        }

        $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolSocieteSuccess');
        $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchSocieteSuccess');
        unset($this->eCommerceSociete);


        //Drop categories	
        $dolObjectsDeleted = 0;
        $synchObjectsDeleted = 0;
        $this->initECommerceCategory();
        $arrayECommerceCategoryIds = $this->eCommerceCategory->getAllECommerceCategoryIds($this->eCommerceSite);

        foreach ($arrayECommerceCategoryIds as $idCategory)
        {
            $this->initECommerceCategory();
            if ($this->eCommerceCategory->fetch($idCategory) > 0)
            {
                $dbCategory = new Categorie($this->db);
                if ($dbCategory->fetch($this->eCommerceCategory->fk_category) > 0)
                {
                    if ($dbCategory->delete($this->user) > 0)
                        $dolObjectsDeleted++;
                }
                if ($this->eCommerceCategory->delete($this->user) > 0)
                    $synchObjectsDeleted++;
            }
        }

        $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolCategorySuccess');
        $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchCategorySuccess');
        unset($this->eCommerceCategory);
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

