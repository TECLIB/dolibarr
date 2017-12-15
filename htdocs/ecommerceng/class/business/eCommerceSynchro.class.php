<?php
/* Copyright (C) 2010 Franck Charpentier - Auguria <franck.charpentier@auguria.net>
 * Copyright (C) 2013 Laurent Destailleur          <eldy@users.sourceforge.net>
 * Copyright (C) 2017 Open-DSI                     <support@open-dsi.fr>
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
require_once(DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php');
require_once(DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php');
require_once(DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php');
require_once(DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php');



class eCommerceSynchro
{
    public $error;
    public $errors=array();
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
    public $toNb;

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
     * @param datetime          $toDate       Ending date to synch all data modified before this date (null by default = until now)
     * @param int               $toNb         Max nb of record to count or synch (Used only for synch, not for count for the moment !)
     */
    function __construct($db, $site, $toDate=null, $toNb=0)
    {
        global $langs, $user;

        try {
            $this->langs = $langs;
            $this->user = $user;
            $this->db = $db;
            $this->eCommerceSite = $site;

            $this->eCommerceRemoteAccess = new eCommerceRemoteAccess($this->db, $this->eCommerceSite);

            if (empty($toDate)) $this->toDate = (dol_now() - 10);      // Set date to use as last update date (we remove 10 second to be sure we don't have pb with not sync date)
            else $this->toDate = $toDate;
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
     *
     * @param Boolean       $force      Bool to force update
     * @return datetime                 Datetime
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
     * @param   boolean     $force      Bool to force update
     * @return  datetime                Date time
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
     *
     * @param   boolean     $force      Bool to force update
     * @return  datetime                Date time
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
     *
     * @param   boolean     $force      Bool to force update
     * @return  datetime                Date time
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
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_category WHERE type = 0 AND fk_site=".$this->eCommerceSite->id;
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
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_product WHERE fk_site=".$this->eCommerceSite->id;
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
        $sql="SELECT COUNT(s.rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_societe as s WHERE fk_site=".$this->eCommerceSite->id;

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
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_commande WHERE fk_site=".$this->eCommerceSite->id;
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
        $sql="SELECT COUNT(rowid) as nb FROM ".MAIN_DB_PREFIX."ecommerce_facture WHERE fk_site=".$this->eCommerceSite->id;
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
     *
     * @param   boolean     $force      Force analysis of list, even if array list $this->categoryToUpdate is already defined
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

                    $this->initECommerceCategory(); // Initialise 2 properties eCommerceCategory and eCommerceMotherCategory

                    // $resanswer is array with all categories
                    // We must loop on each categorie.
                    foreach ($resanswer as $remoteCatToCheck) // Check update for each entry into $resanswer -> $remoteCatToCheck = array('category_id'=>, 'parent_id'=>...)
                    {
                        // Test if category is disabled or not
                        if (isset($remoteCatToCheck['is_active']) && empty($remoteCatToCheck['is_active'])) // We keep because children may not be disabled.
                        {
                            dol_syslog("Category remote_id=".$remoteCatToCheck['category_id'].", category is disabled.");
                        }
                        //else
                        //{
                            if (! isset($remoteCatToCheck['updated_at'])) {   // The api that returns list of category did not return the updated_at property
                                // This is very long if there is a lot of categories because we make a WS call to get the 'updated_at' info at each loop pass.
                                dol_syslog("Process category remote_id=".$remoteCatToCheck['category_id'].", updated_at unknow.");

                                // Complete info of $remoteCatToCheck['category_id']
                                $tmp=$this->eCommerceRemoteAccess->getCategoryData($remoteCatToCheck['category_id']);   // This make a SOAP call

                                $remoteCatToCheck['updated_at']=$tmp['updated_at']; // Complete data we are missing
                            }
                            else
                            {
                                dol_syslog("Process category remote_id=".$remoteCatToCheck['category_id'].", updated_at is defined to ".$remoteCatToCheck['updated_at']);
                            }

                            // If the category was updated before the max limit date this->toDate
                            if (strtotime($remoteCatToCheck['updated_at']) <= $this->toDate)
                            {
                                // Check into link table ecommerce_category if record is older (so if has been modified on magento or not)
                                if ($this->eCommerceCategory->checkForUpdate($this->eCommerceSite->id, $this->toDate, $remoteCatToCheck))   // compare date in remoteCatToCheck and date in sync table. $this->toDate is not used.
                                    $this->categoryToUpdate[] = $remoteCatToCheck;
                            }
                        //}
                    }

                    //var_dump($this->categoryToUpdate);exit;
                    dol_syslog("Now tree are in an array ordered by hierarchy. Nb of record = ".count($this->categoryToUpdate));
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
            if (empty($this->productToUpdate) && (! empty($this->error) || !empty($this->errors) || !empty($this->eCommerceRemoteAccess->error) || !empty($this->eCommerceRemoteAccess->errors)))
            {
                if (! empty($this->eCommerceRemoteAccess->error)) $this->error=$this->eCommerceRemoteAccess->error;
                if (! empty($this->eCommerceRemoteAccess->errors)) $this->errors=array_merge($this->errors, $this->eCommerceRemoteAccess->errors);
                return -1;
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
            if (empty($this->societeToUpdate) && (! empty($this->error) || !empty($this->errors) || !empty($this->eCommerceRemoteAccess->error) || !empty($this->eCommerceRemoteAccess->errors)))
            {
                if (! empty($this->eCommerceRemoteAccess->error)) $this->error=$this->eCommerceRemoteAccess->error;
                if (! empty($this->eCommerceRemoteAccess->errors)) $this->errors=array_merge($this->errors, $this->eCommerceRemoteAccess->errors);
                return -1;
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
            if (empty($this->commandeToUpdate) && (! empty($this->error) || !empty($this->errors) || !empty($this->eCommerceRemoteAccess->error) || !empty($this->eCommerceRemoteAccess->errors)))
            {
                $this->errors[] = $this->langs->trans('ECommerceErrorGetCommandeToUpdate');
                if (! empty($this->eCommerceRemoteAccess->error)) $this->error=$this->eCommerceRemoteAccess->error;
                if (! empty($this->eCommerceRemoteAccess->errors)) $this->errors=array_merge($this->errors, $this->eCommerceRemoteAccess->errors);
                return -1;
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
            if (empty($this->factureToUpdate) && (! empty($this->error) || !empty($this->errors) || !empty($this->eCommerceRemoteAccess->error) || !empty($this->eCommerceRemoteAccess->errors)))
            {
                if (! empty($this->eCommerceRemoteAccess->error)) $this->error=$this->eCommerceRemoteAccess->error;
                if (! empty($this->eCommerceRemoteAccess->errors)) $this->errors=array_merge($this->errors, $this->eCommerceRemoteAccess->errors);
                return -1;
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
     * @param  boolean  $force      Bool to force update
     * @return int                  <0 if KO, >=0 if OK
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
     * Get count of modified categories since the last update
     *
     * @param   boolean     $force      Bool to force update
     * @return  int                     <0 if KO, >=0 if OK
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
     *
     * @param   boolean     $force      Bool to force update
     * @return  int                     <0 if KO, >=0 if OK
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
     * @param   boolean     $force      Bool to force update
     * @return  int                     <0 if KO, >=0 if OK
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
     * @param   boolean     $force      Bool to force update
     * @return  int                     <0 if KO, >=0 if OK
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
     *  @param  int     $toNb       Max nb to synch
     * 	@return int                 <0 if KO, >= 0 if ok
     */
    public function synchCategory($toNb=0)
    {
        $error=0;

        try {
            $nbgoodsunchronize = 0;
            $categories=array();

            dol_syslog("***** eCommerceSynchro synchCategory");

            // Safety check : importRootCategory exists
            $dBRootCategorie = new Categorie($this->db);
            $importRootExists = ($dBRootCategorie->fetch($this->eCommerceSite->fk_cat_product) > 0) ? 1 : 0;

            if ($importRootExists)
            {
                dol_syslog("synchCategory importRootExists=".$importRootExists);

                $resulttoupdate = $this->getCategoriesToUpdate();   // Return list of categories that were modified on ecommerce side
                /* Do not sort run this, we want to keep sort on parent categori first and not by updated_at date.
                if (is_array($resulttoupdate))
                {
                    if (count($resulttoupdate) > 0) $categories = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrCategory($resulttoupdate,$toNb);
                }
                else
                {
                    $error++;
                }*/
                $categories=$resulttoupdate;

                // Check return of remote...
                if (is_array($resulttoupdate) && count($resulttoupdate) > 0 && (! is_array($categories) || count($categories) == 0))    // return of remote is bad or empty when input was not empty
                {
                    $error++;
                }
                if (! $error && is_array($categories))
                {
                    $counter = 0;
                    foreach ($categories as $categoryArray)     // Loop on each categories found on ecommerce side. Cursor is $categoryArray
                    {
                        $counter++;
                        if ($toNb > 0 && $counter > $toNb) break;

                        dol_syslog("synchCategory Process sync of ecommerce category remote_id=".$categoryArray['category_id']." name=".$categoryArray['name']." remote parent_id=".$categoryArray['parent_id']);

                        $this->db->begin();

                        $this->initECommerceCategory();             // Initialise new objects eCommerceMotherCategory and eCommerceCategory

                        $dBCategorie = new Categorie($this->db);

                        // Check if the ecommerce category has an ecommerce parent category, if not, that implies it is root
                        $motherExists = $this->eCommerceMotherCategory->fetchByRemoteId($categoryArray['parent_id'], $this->eCommerceSite->id);
                        // Now $this->eCommerceMotherCategory contains the mother category or null

                        // if fetch on eCommerceMotherCategory has failed, it is root
                        /* Disable automatic creation in dolibarr of link to parent into table of links
			because, we can't know what is the parent. Here code suppose it is the root category defined into setup
			but we may have several levels
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
                        } */
			    
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
                            // $synchExists should be 1 here in common case
                        }
                        else
                        {
                            $synchExists = $eCommerceCatExists >= 0 ? 0 : -1;
                        }

                        // Affect attributes of $categoryArray to $dBCategorie

                        // If we did not find mother yet (creation was not done in hierarchy order), we create category in root for magento
                        if (empty($this->eCommerceMotherCategory->fk_category))
                        {
                            dol_syslog("We did not found parent category in dolibarr, for parent remote_id=".$categoryArray['parent_id'].", so we create ".$categoryArray['name']." with remote_id=".$categoryArray['category_id']." on root.");
                            $dBCategorie->fk_parent = $this->eCommerceSite->fk_cat_product;
                        }
                        else
                        {
                            dol_syslog("We found parent category dolibarr id=".$this->eCommerceMotherCategory->fk_category);
                            $dBCategorie->fk_parent = $this->eCommerceMotherCategory->fk_category;
                        }

                        $dBCategorie->label = $categoryArray['name'];
                        $dBCategorie->description = $categoryArray['description'];
                        $dBCategorie->type = 0;             // for product category type
                        $dBCategorie->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;

                        //var_dump('synchExists='.$synchExists);
                        if ($synchExists >= 0)
                        {
                            $result = $dBCategorie->update($this->user);
                        }
                        else
                        {
                            /* Disabled, not sure this is good/required
			    if ($dBCategorie->already_exists()) {
                                $cats = $dBCategorie->rechercher('', $dBCategorie->label, $dBCategorie->type, true, true);
                                foreach ($cats as $cat) {
                                    if ($cat->fk_parent == $dBCategorie->fk_parent) {
                                        $dBCategorie->id = $cat->id;
                                        $result = $dBCategorie->update($this->user);
                                    }
                                }
                            } else { */
                                $result = $dBCategorie->create($this->user);
                            /* } */
                        }
                        // if synchro category ok
                        if ($result >= 0)
                        {
                            $this->eCommerceCategory->label = $dBCategorie->label;
                            $this->eCommerceCategory->description = $dBCategorie->description;
                            $this->eCommerceCategory->remote_parent_id = $categoryArray['parent_id'];
                            if (!empty($categoryArray['updated_at'])) $this->eCommerceCategory->last_update = strtotime($categoryArray['updated_at']);
                            if ($synchExists > 0)   // update it remotely
                            {
                                if ($this->eCommerceCategory->update($this->user) < 0)
                                {
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCategoryUpdateError');
                                    $this->errors = array_merge($this->errors, $this->eCommerceCategory->errors);
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
                                    $this->errors = array_merge($this->errors, $this->eCommerceCategory->errors);
                                    break;
                                }
                            }
                        }
                        else
                        {
							if ($result == -4)   // duplicate during create of Dolibarr category
							{
								// The category already exists in Dolibarr
								$dBCategorie->fetch(0, $dBCategorie->label, $dBCategorie->type);    // Load full dolibarr category object


								$this->eCommerceCategory->label = $dBCategorie->label;
								$this->eCommerceCategory->description = $dBCategorie->description;
								$this->eCommerceCategory->fk_category = $dBCategorie->id;
                                $this->eCommerceCategory->type = $dBCategorie->type;
                                $this->eCommerceCategory->fk_site = $this->eCommerceSite->id;
                                $this->eCommerceCategory->remote_id = $categoryArray['category_id'];
                                $this->eCommerceCategory->remote_parent_id = $categoryArray['parent_id'];
                                if (!empty($categoryArray['updated_at'])) $this->eCommerceCategory->last_update = strtotime($categoryArray['updated_at']);

                                if ($this->eCommerceCategory->create($this->user) < 0)  // insert into table lxx_ecommerce_category
                                {
                                    // Note: creation of categorie dolibarr + creation of table link may fails if same categorie exists twice with same name in Magento.
                                    // The first time insert of categorie + link is ok, then insert of categorie return -4 and insert of link is duplicate !

                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceCategoryCreateError') . ' ' . $dBCategorie->label;
                                    $this->errors[] = $this->langs->trans("ECommerceCheckIfCategoryDoesNotExistsTwice");
                                    $this->errors = array_merge($this->errors, $this->eCommerceCategory->errors);
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

                        //var_dump($nbgoodsunchronize);exit;
                        unset($dBCategorie);

                        if ($error || ! empty($this->errors))
                        {
                            $this->db->rollback();
                            $nbrecorderror++;
                            break;      // We decide to stop on first error
                        }
                        else
                        {
                            $this->db->commit();
                            $nbgoodsunchronize = $nbgoodsunchronize + 1;
                        }
                    }   // end foreach

                    if (empty($this->errors) && ! $error)
                    {
                        $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchCategorySuccess');

                        // TODO If we commit even if there was an error (to validate previous record ok), we must also remove 1 second the the higher
                        // date into table of links to be sure we will retry (during next synch) also record with same update_at than the last record ok.

                        return $nbgoodsunchronize;
                    }
                    else
                    {
                        $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchCategorySuccess');
                        return -1;
                    }
                }
                else
                {
                    $this->error=$this->langs->trans('ECommerceErrorsynchCategory').' (Code FailToGetDetailsOfRecord)';
                    $this->errors[] = $this->error;
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

        return -1;
    }


    /**
     * Synchronize societe to update
     *
     * @param  int     $toNb       Max nb to synch
     * @return int                 Id of thirdparties synchronized if OK, -1 if KO
     */
    public function synchSociete($toNb=0)
    {
        global $conf;

        $error=0;

        try {
            $nbgoodsunchronize = 0;
            $societes=array();

            dol_syslog("***** eCommerceSynchro synchSociete");
            $resulttoupdate=$this->getSocieteToUpdate();
            if (is_array($resulttoupdate))
            {
                if (count($resulttoupdate) > 0) $societes = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrSociete($resulttoupdate,$toNb);
            }
            else
            {
                $error++;
            }

            // Check return of remote...
            if (is_array($resulttoupdate) && count($resulttoupdate) > 0 && (! is_array($societes) || count($societes) == 0))    // return of remote is bad or empty when input was not empty
            {
                $error++;
            }

            if (! $error && is_array($societes))
            {
                $counter = 0;
                foreach ($societes as $societeArray)
                {
                    $counter++;
                    if ($toNb > 0 && $counter > $toNb) break;

                    $this->db->begin();

                    //check if societe exists in eCommerceSociete
                    dol_syslog("-- Start thirdparty remote_id=".$societeArray['remote_id']." site=".$this->eCommerceSite->id);
                    $synchExists = $this->eCommerceSociete->fetchByRemoteId($societeArray['remote_id'], $this->eCommerceSite->id);
                    $dBSociete = new Societe($this->db);

                    //if societe exists in eCommerceSociete, societe must exists in societe
                    if ($synchExists > 0 && isset($this->eCommerceSociete->fk_societe))
                    {
                        $refExists = $dBSociete->fetch($this->eCommerceSociete->fk_societe);
                        if ($refExists >= 0)
                        {
                            $dBSociete->name = $societeArray['name'];
                            //$dBSociete->ref_ext = $this->eCommerceSite->name.'-'.$societeArray['remote_id'];      // No need of ref_ext, we will search if already exists on name
                            $dBSociete->client = $societeArray['client'];
                            if (isset($societeArray['name_alias'])) $dBSociete->name_alias = $societeArray['name_alias'];
                            if (isset($societeArray['email'])) $dBSociete->email = $societeArray['email'];
                            if (isset($societeArray['vatnumber'])) {
                                $dBSociete->tva_intra = $societeArray['vatnumber'];
                            }
                            $dBSociete->tva_assuj = 1;      // tva_intra is not saved if this field is not set
                            $dBSociete->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;

                            $result = $dBSociete->update($dBSociete->id, $this->user);
                            if ($result < 0)
                            {
                                $error++;
                                $this->errors[]=$this->langs->trans('ECommerceSynchSocieteUpdateError').' '.$dBSociete->error;
                                $this->errors = array_merge($this->errors, $dBSociete->errors);
                            }
                        }
                        else
                        {
                            $error++;
                            $this->errors[] = $this->langs->trans('ECommerceSynchSocieteErrorBetweenECommerceSocieteAndSociete');
                        }
                    }
                    //if societe not exists in eCommerceSociete, societe is created
                    else
                    {
                        $result = 0;
			    
                        // First, we check object does not alreay exists. If not, we create it, if it exists, do nothing.
                        if (isset($societeArray['email_key']) && !empty($societeArray['email_key'])) {
                            // Search into email company and contact
                            $result = get_company_by_email($this->db, $societeArray['email_key']);

                            if ($result > 0 && $result != $this->eCommerceSite->fk_anonymous_thirdparty) {
                                $result = $dBSociete->fetch($result);
                            }
                        }

                        if ($result < 1 && (!isset($societeArray['type']) || $societeArray['type'] == 'company')) {

				$unicity='name';
				if (! empty($conf->global->ECOMMERCENG_THIRDPARTY_UNIQUE_ON) && $conf->global->ECOMMERCENG_THIRDPARTY_UNIQUE_ON == 'email')
				{
				    $unicity='email';
				}

				// If unicity is on NAME
				if ($unicity == 'name')
				{
				    $result = $dBSociete->fetch(0, $societeArray['name']);
				}
				// If unicity is on EMAIL
				if ($unicity == 'email')
				{
				    $sql = 'SELECT s.rowid FROM '.MAIN_DB_PREFIX."societe as s where email = '".$this->db->escape($societeArray['email'])."'";
				    $resqlid = $this->db->query($sql);
				    if ($resqlid)
				    {
					$obj = $this->db->fetch_object($resqlid);
					if ($obj)
					{
					    $thirdpartyid = $obj->rowid;
					    $result = $dBSociete->fetch(0, $thirdpartyid);
					}
				    }
				    else
				    {
					$error++;
					$this->error='Error in getting id from email.';
					$this->errors[]=$this->error;
				    }
				}
                        }

                        if ($result == -2) {
                            $error++;
                            $this->error='Several thirdparties with name '.$societeArray['name'].' were found in Dolibarr. Sync is not possible. Please rename one of it to avoid duplicate.';
                            $this->errors[]=$this->error;
                        }

                        if (! $error && $result > 0)    // We did not found with remote id but we found one with the fetch on name.
                        {
                            $eCommerceSocieteBis=new eCommerceSociete($this->db);
                            $synchExistsBis = $eCommerceSocieteBis->fetchByFkSociete($dBSociete->id, $this->eCommerceSite->id);
                            dol_syslog("Warning: we did not found the remote id into dolibarr eCommerceSociete table but we found a record with the name.");
                            if ($synchExistsBis > 0 && $eCommerceSocieteBis->id != $this->eCommerceSociete->id)
                            {
                                // We found a dolibarr record with name, but this one is alreayd linked and we know it is linked with another remote id because
                                // the current remote_id was not found  when we previously did the fetchByRemoteId
                                // So we make as if we didn't found the thirdparty. It may be a duplicate name created in same transaction from Magento
                                dol_syslog("Warning: the record found with the name already has a remote_id in the eCommerceSite. So what we found is not what we want. We forget the find.");
                                unset($dBSociete);  // Clear object, fetch was not what we wanted
                                $dBSociete = new Societe($this->db);
                                $result = 0;
                            }
                        }

                        if ($result == 0)
                        {
                            $dBSociete->name = $societeArray['name'];
                            //$dBSociete->ref_ext = $this->eCommerceSite->name.'-'.$societeArray['remote_id'];      // No need of ref_ext, we will search if already exists on name
                            $dBSociete->client = $societeArray['client'];
                            if (isset($societeArray['name_alias'])) $dBSociete->name_alias = $societeArray['name_alias'];
                            if (isset($societeArray['email'])) $dBSociete->email = $societeArray['email'];
                            if (isset($societeArray['vatnumber'])) {
                                $dBSociete->tva_intra = dol_trunc($societeArray['vatnumber'], 20, 'right', 'UTF-8', 1);
                            }
                            $dBSociete->tva_assuj = 1;                              // tva_intra is not saved if this field is not set
                            $dBSociete->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                            $dBSociete->code_client = -1;           // Automatic code
                            $dBSociete->code_fournisseur = -1;      // Automatic code

                            $result = $dBSociete->create($this->user);
                            if ($result < 0)
                            {
                                $error++;
                                $this->errors[]=$this->langs->trans('ECommerceSynchSocieteCreateError').' '.$dBSociete->error;
                                $this->errors = array_merge($this->errors, $dBSociete->errors);
                            }

                           	$dBSociete->update_note($societeArray['note_private'],'_private');
                        }
                        else if ($result > 0)
                        {
                            $dBSociete->name = $societeArray['name'];
                            //$dBSociete->ref_ext = $this->eCommerceSite->name.'-'.$societeArray['remote_id'];      // No need of ref_ext, we will search if already exists on name
                            $dBSociete->client = $societeArray['client'];
                            if (isset($societeArray['name_alias'])) $dBSociete->name_alias = $societeArray['name_alias'];
                            if (isset($societeArray['email'])) $dBSociete->email = $societeArray['email'];
                            if (isset($societeArray['vatnumber'])) {
                                $dBSociete->tva_intra = $societeArray['vatnumber'];
                            }
                            $dBSociete->tva_assuj = 1;      // tva_intra is not saved if this field is not set
                            $dBSociete->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;

                            $result = $dBSociete->update($dBSociete->id, $this->user);
                            if ($result < 0)
                            {
                                $error++;
                                $this->errors[]=$this->langs->trans('ECommerceSynchSocieteUpdateError').' '.$dBSociete->error;
                                $this->errors = array_merge($this->errors, $dBSociete->errors);
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
                                $this->errors = array_merge($this->errors, $this->eCommerceSociete->errors);
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
                                $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceSocieteCreateError') . ' ' . $societeArray['name'] . ' ' . $societeArray['email'] . ' ' . $societeArray['client'].' '.$this->eCommerceSociete->error;
                                $this->errors = array_merge($this->errors, $this->eCommerceSociete->errors);
                            }
                        }

                        // Sync also people of thirdparty
                        // We can disable this to have contact/address of thirdparty synchronize only when an order or invoice is synchronized
                        if (! $error)
                        {
                            dol_syslog("Make a remote call to get contacts");   // Slow because done on each thirdparty to sync.
                            $listofaddressids=$this->eCommerceRemoteAccess->getRemoteAddressIdForSociete($societeArray['remote_id']);   // Ask contacts to magento
                            if ($this->eCommerceSite->type == 2) { // Woocommerce
                                $listofaddressids = $societeArray['remote_datas'];
                            }
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
                        }
                    }
                    else
                    {
                        $error++;
                        $this->errors[] = $this->langs->trans('ECommerceSynchSocieteErrorCreateUpdateSociete') . ' ' . $societeArray['name'] . ' ' . $societeArray['email'] . ' ' . $societeArray['client'];
                    }

                    unset($dBSociete);

                    if ($error || ! empty($this->errors))
                    {
                        $this->db->rollback();
                        $nbrecorderror++;
                        break;      // We decide to stop on first error
                    }
                    else
                    {
                        $this->db->commit();
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    }
                }   // end foreach

                if (empty($this->errors) && ! $error)
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchSocieteSuccess');

                    // TODO If we commit even if there was an error (to validate previous record ok), we must also remove 1 second the the higher
                    // date into table of links to be sure we will retry (during next synch) also record with same update_at than the last record ok.

                    return $nbgoodsunchronize;
                }
                else
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchSocieteSuccess');
                    return -1;
                }
            }
            else
            {
                $this->error=$this->langs->trans('ECommerceErrorsynchSociete').' (Code FailToGetDetailsOfRecord)';
                $this->errors[] = $this->error;
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchSociete');
        }
        return -1;
    }


    /**
     * Synchronize socpeople to update for a society: Create or update it into dolibarr, then update the ecommerce_socpeople table.
     *
     * @param   array   $socpeopleArray     Array with all params to synchronize
     * @return  int                         Id of socpeople into Dolibarr if OK and false if KO
     */
    public function synchSocpeople($socpeopleArray)
    {
        global $conf;

        $error=0;
        $synchExists = 0;
        $contactExists = 0;

        $dBContact = new Contact($this->db);

        try {
            dol_syslog("***** eCommerceSynchro synchSocPeople remote_id=".$socpeopleArray['remote_id']." site=".$this->eCommerceSite->id);

            if (!isset($this->eCommerceSocpeople))
                $this->initECommerceSocpeople();

            //print "Work on remote_id = " .$socpeopleArray['remote_id']." type = ".$socpeopleArray['type']."\n";

            //check if contact exists in eCommerceSocpeople table
            if (!empty($socpeopleArray['remote_id'])) {
                // $socpeopleArray['type'] = 1 = Contact de tiers
                // $socpeopleArray['type'] = 2 = Contact de commande
                // $socpeopleArray['type'] = 3 = Contact de facture
                // $socpeopleArray['type'] = 4 = Contact de livraison
                $synchExists = $this->eCommerceSocpeople->fetchByRemoteId($socpeopleArray['remote_id'], $socpeopleArray['type'], $this->eCommerceSite->id);

                if ($synchExists > 0) {
                    $contactExists = $dBContact->fetch($this->eCommerceSocpeople->fk_socpeople);
                }
            }

            //set data into contact
            $dBContact->socid = $socpeopleArray['fk_soc'];
            $dBContact->fk_soc = $socpeopleArray['fk_soc'];
            $dBContact->firstname = $socpeopleArray['firstname'];
            $dBContact->lastname = $socpeopleArray['lastname'];
            $dBContact->address = $socpeopleArray['address'];
            $dBContact->cp = $socpeopleArray['zip'];
            if ((float) DOL_VERSION >= 6.0)
            {
                $dBContact->zip = dol_trunc($socpeopleArray['zip'], 25, 'right', 'UTF-8', 1);
            }
            else
            {
                $dBContact->zip = dol_trunc($socpeopleArray['zip'], 10, 'right', 'UTF-8', 1);
            }
            $dBContact->town = dol_trunc($socpeopleArray['town'], 30, 'right', 'UTF-8', 1);
            $dBContact->ville = $dBContact->town;
            $dBContact->country_id = $socpeopleArray['country_id'];
            $dBContact->email = $socpeopleArray['email'];
            $dBContact->phone_pro = dol_trunc($socpeopleArray['phone'], 30, 'right', 'UTF-8', 1);
            $dBContact->fax = dol_trunc($socpeopleArray['fax'], 30, 'right', 'UTF-8', 1);
            $dBContact->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;

            if (!$contactExists) {
                $contactExists = $this->getContactIdFromInfos($dBContact);
                if ($contactExists > 0) {
                    $contactExists = $dBContact->fetch($contactExists);
                    if (isset($socpeopleArray['country_id'])) $dBContact->country_id = $socpeopleArray['country_id'];
                    if (isset($socpeopleArray['email'])) $dBContact->email = $socpeopleArray['email'];
                    if (isset($socpeopleArray['phone'])) $dBContact->phone_pro = dol_trunc($socpeopleArray['phone'], 30, 'right', 'UTF-8', 1);
                    if (isset($socpeopleArray['fax'])) $dBContact->fax = dol_trunc($socpeopleArray['fax'], 30, 'right', 'UTF-8', 1);
                    $dBContact->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                    $synchExists = $this->eCommerceSocpeople->fetchByFkSocpeople($dBContact->id, $this->eCommerceSite->id);
                }
            }

            if ($contactExists > 0) {
                $result = $dBContact->update($dBContact->id, $this->user);
                if ($result < 0) {
                    $error++;
                    $this->error = $this->langs->trans('ECommerceSynchContactUpdateError') . ' ' . $dBContact->error;
                    $this->errors[] = $this->error;
                }
            } else if ($contactExists == 0) {
                $result = $dBContact->create($this->user);
                if ($result < 0) {
                    $error++;
                    $this->error = $this->langs->trans('ECommerceSynchContactCreateError') . ' ' . $dBContact->error;
                    $this->errors[] = $this->error;
                }
            } else if ($synchExists > 0 && $contactExists < 0) {
                $error++;
                $this->errors[] = $this->langs->trans('ECommerceSynchSocieteErrorBetweenECommerceSocpeopleAndContact');
            } else if ($contactExists < 0) {
                $error++;
                $this->errors[] = $this->langs->trans('ECommerceSynchContactFetchError') . ' ' . $dBContact->error;
            }

            //if create/update of contact table is ok
            if (! $error && $result >= 0)
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
                        $this->errors = array_merge($this->errors, $this->eCommerceSocpeople->errors);
                        return false;
                    }
                }
                //if not previous synchro exists
                else
                {
                    //eCommerce create
                    $this->eCommerceSocpeople->fk_site = $this->eCommerceSite->id;
                    $this->eCommerceSocpeople->remote_id = isset($socpeopleArray['remote_id']) ? $socpeopleArray['remote_id'] : 'none-'.$dBContact->id;
                    $this->eCommerceSocpeople->type = $socpeopleArray['type'];
                    if ($this->eCommerceSocpeople->create($this->user) < 0)
                    {
                        $this->errors[] = $this->langs->trans('ECommerceSynchECommerceSocpeopleCreateError', $socpeopleArray['fk_soc'], $socpeopleArray['firstname'], $socpeopleArray['lastname']) . ' : ' . $this->eCommerceSocpeople->error;
                        $this->errors = array_merge($this->errors, $this->eCommerceSocpeople->errors);
                        return false;
                    }
                }
                return $dBContact->id;
            }
            else
            {
                $this->errors[] = $this->langs->trans('ECommerceSynchSocpeopleErrorCreateUpdateSocpeople');
                return false;
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchSocpeople');
        }
        return false;
    }


    /**
     * Synchronize product to update
     *
     * @param   int     $toNb       Max nb to synch
     * @return  int                 Id of product synchronized if OK, -1 if KO
     */
    public function synchProduct($toNb=0)
    {
        global $conf;

        $error=0;

        try {
            $nbgoodsunchronize = 0;
            $products = array();

            dol_syslog("***** eCommerceSynchro synchProduct");
            $resulttoupdate=$this->getProductToUpdate();
            /*$resulttoupdate=array( 0 =>
            array (
            'product_id' => '27',
            'sku' => 'QSINCP01384',
            'name' => 'xxxxx',
            'set' => '85',
            'type' => 'simple',
            'category_ids' =>  array (0 => '98', 1 => '225'),
            'website_ids' => array (0 => '1')
            ));*/

            /*$resulttoupdate=array( 0 =>array (
            'product_id' => '52',
            'sku' => '11115',
            'type' => 'simple',
            'category_ids' =>  array (0 => '98', 1 => '225'),
            'website_ids' => array (0 => '1')
            ));

            //var_dump($resulttoupdate);
			*/

            // Return an array like  array([product_id]=>27, [sku]=>'QSINCP01384', [name]=>'Name of product', [set]=>85, [type]=>simple, [category_ids]=>Array([0]=>98, [1]=>225), [website_ids] => Array([0]=>1))
            if (is_array($resulttoupdate))
            {
                //print_r($resulttoupdate);

                // Get details searching on $resulttoupdate[$i]['sku']
                if (count($resulttoupdate) > 0)
                {
                    $products = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrProduct($resulttoupdate, $toNb);	// Return max $toNb record only

                    /* Get more complete arrays like  array(
                        [fk_product_type] => 0
                        [ref] =>QSINCP01384
                        [label] =>
                        [description] =>
                        [weight] =>
                        [last_update] =>
                        [price] =>
                        [envente] => 0
                        [remote_id] =>
                        [finished] => 1
                        [canvas] =>
                        [categories] =>
                        [tax_rate] =>
                        [price_min] =>
                        [fk_country] =>
                        [url] => https://xxx.com/
                        [stock_qty] => -3.0000
                        [is_in_stock] => 1
                        )
                     */

                    // Check we get all detailed information for each record.
                    // This test is specific to product because Magento sometimes return not complete record
                    if (is_array($products) && count($products))
                    {
                        dol_syslog("Check we get all detailed information for each record. We compare resulttoupdate and productArray to list missing entries.");
                        $listofrefnotfound=array();
						if (empty($toNb))	// If not limit into number of answer by convertRemoteObjectIntoDolibarrProduct, then $products must contains all responses of $resulttoupdate
						{
	                        foreach($resulttoupdate as $val)
	                        {
	                            $found=false;
	                            foreach($products as $val2)
	                            {
	                                if ($val['sku'] == $val2['ref'])
	                                {
	                                    $found=true;
	                                    break;
	                                }
	                            }
	                            if (! $found)
	                            {
	                                $listofrefnotfound[]=$val['sku'];
	                            }
	                        }
						}
						else				// If a limit into number of answer by convertRemoteObjectIntoDolibarrProduct was provided, then $products is not complete, we can't make any tests
						{
							dol_syslog("We don't check that each requested record has an answer because number of answers was restricted, so we are sure we don't have all requested record");
						}
                        if (count($listofrefnotfound))
                        {
                            $error++;
                            $this->errors[]="Record with following ref were not returned: ".join(',', $listofrefnotfound);
                            if (is_numeric($listofrefnotfound[0]))
                            {
                                $this->errors[]="With some eCommerce platform, like Magento, the API to get data of product may fails if the Reference (Sku) contains only numbers. Try to introduce a letter (A-Z) into reference on your eCommerce products";
                            }
                        }
                    }
                    else
                    {
                    	if ($toNb <= 0) dol_syslog('We get an empty array from convertRemoteObjectIntoDolibarrProduct() with input $resulttoupdate = '.serialize($resulttoupdate), LOG_WARNING);
                        $error++;
                    }
                }
            }


            if (! $error && is_array($products))
            {
                $counter = 0;
                foreach ($products as $productArray)
                {
                    dol_syslog("- Process synch of product remote_id=".$productArray['remote_id']);

                    $counter++;
                   // if ($toNb > 0 && $counter > $toNb) break;

                    if (empty($productArray['remote_id']))
                    {
                        dol_syslog("Record with index ".$counter." is empty. Error.");
                        $error++;
                        $this->errors[]="Record with index ".$counter." is empty. Error.";
                        break;
                    }

                    $this->db->begin();

                    $dBProduct = new Product($this->db);

                    //check if product exists in eCommerceProduct (with remote id)
                    $idProduct = '';
                    $synchExists = $this->eCommerceProduct->fetchByRemoteId($productArray['remote_id'], $this->eCommerceSite->id);
                    if ($synchExists > 0) {
                        $idProduct = $this->eCommerceProduct->fk_product;
                    } else {
                    // First, we check object does not alreay exists. If not, we create it, if it exists, update it.
                        $refExists = $dBProduct->fetch('', dol_string_nospecial(trim($productArray['ref'])));
                        if ($refExists > 0 && $this->eCommerceProduct->fetchByProductId($dBProduct->id, $this->eCommerceSite->id) > 0) {
                            $refExists = '';
                        }
                    }

                    //libelle of product object = label into database
                    $dBProduct->ref = dol_string_nospecial(trim($productArray['ref']));
                    $dBProduct->label = $productArray['label'];
                    $dBProduct->description = isset($productArray['description']) ? $productArray['description'] : $dBProduct->description;
                    $dBProduct->weight = $productArray['weight'];
                    $dBProduct->type = $productArray['fk_product_type'];
                    $dBProduct->finished = $productArray['finished'];
                    $dBProduct->status = $productArray['envente'];
                    $dBProduct->status_buy = $productArray['enachat'];

                    $dBProduct->country_id = $productArray['fk_country'];
                    $dBProduct->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                    $dBProduct->ref_ext = $this->eCommerceSite->name.'-'.$productArray['remote_id'];
                    $dBProduct->url = $productArray['url'];
                    if ($conf->barcode->enabled)
                        $dBProduct->barcode = -1;

                    if (is_array($productArray['extrafields'])) {
                        foreach ($productArray['extrafields'] as $extrafield => $extrafield_value) {
                            $dBProduct->array_options['options_'.$extrafield] = $extrafield_value;
                        }
                    }

                    if ($refExists > 0 && isset($dBProduct->id))
                    {
                        //update
                        $result = $dBProduct->update($dBProduct->id, $this->user);
                        if ($result >= 0)
                        {
                            // Update price if need
                            $price_level = empty($this->eCommerceSite->price_level) ? 1 : $this->eCommerceSite->price_level;

                            // Get current product values
                            if (empty($conf->global->PRODUIT_MULTIPRICES)) {
                                $price_base_type_org = $dBProduct->price_base_type;
                                $price_org = $dBProduct->price;
                                $price_min_org = $dBProduct->price_min;
                                $tax_rate_org = $dBProduct->tva_tx;
                            } else {
                                $price_base_type_org = $dBProduct->multiprices_base_type[$price_level];
                                $price_org = $dBProduct->multiprices[$price_level];
                                $price_min_org = $dBProduct->multiprices_min[$price_level];
                                $tax_rate_org = $dBProduct->multiprices_tva_tx[$price_level];
                            }

			    $price_base_type = $this->eCommerceSite->ecommerce_price_type;
			    if (isset($productArray['price_base_type'])) $price_base_type = $productArray['price_base_type'];
				
                            if ($price_base_type_org != $price_base_type ||
                                $price_org != $productArray['price'] ||
                                (isset($productArray['price_min']) && $price_min_org != $productArray['price_min']) ||
                                price2num((float) $productArray['tax_rate']) != price2num((float) $tax_rate_org)
                            ) {
                                // The price type from eCommerce is defined for the site: TI/TE (Tax Include / Tax Excluded)
                                if (empty($conf->global->PRODUIT_MULTIPRICES)) {
                                    $dBProduct->updatePrice($productArray['price'], $price_base_type, $this->user, $productArray['tax_rate'], $productArray['price_min']);
                                } else {
                                    $dBProduct->updatePrice($productArray['price'], $price_base_type, $this->user, $productArray['tax_rate'], $productArray['price_min'], $price_level);
                                }
                            }
                        }
                        else
                        {
                            $error++;
                            $this->error=$this->langs->trans('ECommerceSynchProductUpdateError').' '.$dBProduct->error;
                            $this->errors[]=$this->error;
                        }

                        // We must set the initial stock
                        if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr' && ($productArray['stock_qty'] != $dBProduct->stock_reel)) // Note: $dBProduct->stock_reel is 0 after a creation
                        {
                            dol_syslog("Stock for product updated is ".$productArray['stock_qty']." in ecommerce, but ".$dBProduct->stock_reel." in Dolibarr, we must update it");
                            if (empty($this->eCommerceSite->fk_warehouse))
                            {
                                $error++;
                                $this->errors[]='SetupOfWarehouseNotDefinedForThisSite';
                            }

                            // Update/init stock
                            if (! $error)
                            {
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
                                    $this->errors[] = $this->error;
    	                        }
                            }
                        }
                    }
                    else
                    {
                        //create
                        $dBProduct->canvas = $productArray['canvas'];
                        $dBProduct->note = 'Initialy created from '.$this->eCommerceSite->name;

                        $result = $dBProduct->create($this->user);
                        if ($result >= 0)
                        {
                            // Set price
                            $price_level = !empty($this->eCommerceSite->price_level) ? $this->eCommerceSite->price_level : 1;

                            // The price type from eCommerce is defined for the site: TI/TE (Tax Include / Tax Excluded)
                            if (empty($conf->global->PRODUIT_MULTIPRICES)) {
                                $dBProduct->updatePrice($productArray['price'], $this->eCommerceSite->ecommerce_price_type, $this->user, $productArray['tax_rate'], $productArray['price_min']);
                            } else {
                                $dBProduct->updatePrice($productArray['price'], $this->eCommerceSite->ecommerce_price_type, $this->user, $productArray['tax_rate'], $productArray['price_min'], $price_level);
                            }
                        }
                        else
                        {
                            $error++;
                            if ($dBProduct->error == 'ErrorProductAlreadyExists') $this->error=$this->langs->trans('ECommerceSynchProductCreateError').' '.$this->langs->trans($dBProduct->error, $dBProduct->ref);
                            else $this->error=$this->langs->trans('ECommerceSynchProductCreateError').' '.$dBProduct->error;
                            $this->errors[] = $this->error;
                        }

                        // We must set the initial stock
                        if (! $error && $this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr' && ($productArray['stock_qty'] != $dBProduct->stock_reel)) // Note: $dBProduct->stock_reel is 0 after a creation
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
                                $this->errors[] = $this->error;
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

                        if (is_array($catsIds) && count($catsIds) > 0)  // This product belongs at least to a category
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
                                    dol_syslog("The product id=".$dBProduct->id." seems to no be linked yet to category id=".$catId.", so we link it.");
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

                        // Synchronize images
                        if (!empty($conf->global->ECOMMERCENG_ENABLE_SYNCHRO_IMAGES)) {
                            if (is_array($productArray['images'])) {
                                foreach ($productArray['images'] as $image) {
                                    $ret = ecommerceng_download_image($image, $dBProduct, $error_message);

                                    if (!$ret) {
                                        $error++;
                                        $error_label = $this->langs->trans('ECommerceSyncheCommerceProductDownloadImageError',
                                                implode(',', $image), $dBProduct->id, $productArray['remote_id'], $this->eCommerceSite->name) . ': ' . $error_message;
                                        $this->errors[] = $error_label;
                                        dol_syslog($error_label, LOG_ERR);
                                    }
                                }
                            }

                            // Remove obsolete image
                            $ret = ecommerceng_remove_obsolete_image($dBProduct, $productArray['images'], $error_message);
                            if (!$ret) {
                                $error++;
                                $error_label = $this->langs->trans('ECommerceSyncheCommerceProductDownloadImageError',
                                        $dBProduct->id, $productArray['remote_id'], $this->eCommerceSite->name) . ': ' . $error_message;
                                $this->errors[] = $error_label;
                                dol_syslog($error_label, LOG_ERR);
                            }
                        }

                        $this->eCommerceProduct->last_update = $productArray['last_update'];
                        $this->eCommerceProduct->fk_product = $dBProduct->id;

                        //if a previous synchro exists
                        if ($synchExists > 0)
                        {
                            //eCommerce update
                            if ($this->eCommerceProduct->update($this->user) < 0)
                            {
                                $error++;
                                $this->error = $this->langs->trans('ECommerceSyncheCommerceProductUpdateError') . ' ' . $productArray['label'];
                                $this->errors[] = $this->error;
                                dol_syslog($this->error, LOG_WARNING);
                            }
                        }
                        // if not previous synchro exists into link table (we faild to find it from the remote_id)
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
                                $this->error = $this->langs->trans('ECommerceSyncheCommerceProductCreateError') . ' ' . $productArray['label'].', '.$this->eCommerceProduct->error;
                                $this->errors[] = $this->error;
                                dol_syslog($this->error, LOG_WARNING);
                            }
                        }
                    }
                    else
                    {
                        $error++;
                        $this->error = $this->langs->trans('ECommerceSynchProductError') . ' Ref:' . $productArray['ref'] . ' Nom:' . $productArray['label'] . ', remote ID:' . $productArray['remote_id'];
                        $this->errors[] = $this->error;
                        dol_syslog($this->error, LOG_WARNING);
                    }

                    unset($dBProduct);

                    if ($error || ! empty($this->errors))
                    {
                        $this->db->rollback();

                        $nbrecorderror++;
                        break;      // We decide to stop on first error
                    }
                    else
                    {
                        $this->db->commit();
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    }
                }   // end foreach

                if ($error || ! empty($this->errors))
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchProductSuccess');

                    return -1;
                }
                else
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchProductSuccess');

                    // TODO If we commit even if there was an error (to validate previous record ok), we must also remove 1 second the the higher
                    // date into table of links to be sure we will retry also record with same update_at than the last record ok

                    return $nbgoodsunchronize;
                }
            }
            else
            {
                $this->error=$this->langs->trans('ECommerceErrorsynchProduct').' (Code FailToGetDetailsOfRecord)';
                $this->errors[] = $this->error;
            }
        } catch (Exception $e) {
            $this->error = $this->langs->trans('ECommerceErrorsynchProduct');
            $this->errors[] = $this->error;
            dol_syslog($this->error, LOG_WARNING);
        }

        return -1;
    }


    /**
     * Synchronize commande to update
     * Inclut synchProduct et synchSociete
     *
     * @param   int     $toNb       Max nb to synch
     * @return  int                 Id of product synchronized if OK, -1 if KO
     */
    public function synchCommande($toNb=0)
    {
        global $conf, $user;

        $error = 0;

        try {
            $nbgoodsunchronize = 0;
            $nbrecorderror =0;
            $commandes = array();

            dol_syslog("***** eCommerceSynchro synchCommande");
            $resulttoupdate=$this->getCommandeToUpdate();

            if (is_array($resulttoupdate))
            {
                if (count($resulttoupdate) > 0) $commandes = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrCommande($resulttoupdate, $toNb);
            }
            else
            {
                $error++;
            }

            // Check return of remote...
            if (is_array($resulttoupdate) && count($resulttoupdate) > 0 && (! is_array($commandes) || count($commandes) == 0))    // return of remote is bad or empty when input was not empty
            {
                $error++;
            }

            if (! $error && is_array($commandes))
            {
                // Local filter to exclude bundles and other complex types
                $productsTypesOk = array('simple', 'virtual', 'downloadable');

                // Loop on each modified order
                $counter = 0;
                foreach ($commandes as $commandeArray)
                {
                    dol_syslog("- Process synch of order remote_id=".$commandeArray['remote_id']);

                    $counter++;
                    if ($toNb > 0 && $counter > $toNb) break;

                    $this->db->begin();

                    $this->initECommerceCommande();
                    $this->initECommerceSociete();
                    $dBCommande = new Commande($this->db);

                    //check if commande exists in eCommerceCommande (with remote id). It set ->fk_commande. This is a sql request.
                    $synchExists = $this->eCommerceCommande->fetchByRemoteId($commandeArray['remote_id'], $this->eCommerceSite->id);
                    //check if ref exists in commande
                    $refExists = $dBCommande->fetch($this->eCommerceCommande->fk_commande);

                    //check if societe exists in eCommerceSociete (with remote id). This init ->fk_societe. This is a sql request.
                    //$societeExists will be 1 (found) or -1 (not found)
                    if (! empty($commandeArray['remote_id_societe']))    // May be empty if customer is a non logged user or was deleted on magento side.
                    {
                        $societeExists = $this->eCommerceSociete->fetchByRemoteId($commandeArray['remote_id_societe'], $this->eCommerceSite->id);
                    }
                    else
                    {
                        // This is an unknown customer. May be a non logged customer.
                        if ($this->eCommerceSite->fk_anonymous_thirdparty > 0)
                        {
                            $societeExists = 1;
                            $this->eCommerceSociete->fk_societe = $this->eCommerceSite->fk_anonymous_thirdparty;
                        }
                        else
                        {
                            $societeExists = 0;
                        }
                    }

                    //if societe exists start
                    if ($societeExists > 0)
                    {
                        if ($refExists > 0 && $dBCommande->id > 0)  // Order already synch
                        {
                            dol_syslog("synchCommande Order with id=".$dBCommande->id." and remote_id=".$commandeArray['remote_id']." already exists in Dolibarr");
                            //update commande
                            $result = 1;

                            $tmpdateorder1=dol_print_date($dBCommande->date_commande, 'dayrfc');
                            $tmpdateorder2=dol_print_date(strtotime($commandeArray['date_commande']), 'dayrfc');
                            $tmpdatedeliv1=dol_print_date($dBCommande->date_livraison, 'dayrfc');
                            $tmpdatedeliv2=dol_print_date(strtotime($commandeArray['date_livraison']), 'dayrfc');

                            $dBCommande->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;

                            if (is_array($commandeArray['extrafields'])) {
                                foreach ($commandeArray['extrafields'] as $extrafield => $extrafield_value) {
                                    $dBCommande->array_options['options_'.$extrafield] = $extrafield_value;
                                }
                            }

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
                                if ($result <= 0)
                                {
                                    $error++;
                                    $this->errors[]=$this->langs->trans('ECommerceSynchCommandeUpdateError').' '.$dBCommande->error;
                                    $this->errors = array_merge($this->errors, $dBCommande->errors);
                                }
                            }

                            // Now update status
                            if (! $error)
                            {
                                if ($dBCommande->statut != $commandeArray['status'])
                                {
                                    dol_syslog("Status of order has changed, we update order from status ".$dBCommande->statut." to status ".$commandeArray['status']);

                                    // Draft or not draft
                                    if ($commandeArray['status'] == Commande::STATUS_DRAFT)
                                    {
                                        // Target status is status draft. Should not happen with magento.
                                        // Nothing to do
                                    }
                                    else
                                    {
                                        // Target status is not draft. We validate if current status is still draft to get correct ref.
                                        if ($dBCommande->statut == Commande::STATUS_DRAFT)
                                        {
                                            $idWareHouse = 0;
                                            // We don't change stock here, even if dolibarr option is on because, this should be already done by product sync
                                            //if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr') $idWareHouse=$this->eCommerceSite->fk_warehouse;
                                            $dBCommande->valid($this->user, $idWareHouse);
                                        }
                                    }

                                    // Which target status ?
                                    if ($commandeArray['status'] == Commande::STATUS_DRAFT)
                                    {
                                        if ($dBCommande->statut != Commande::STATUS_DRAFT)
                                        {
                                            $dBCommande->set_draft($user, 0);
                                        }
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_VALIDATED)
                                    {
                                        if ($dBCommande->statut != Commande::STATUS_VALIDATED)
                                        {
                                            $dBCommande->setStatut(Commande::STATUS_VALIDATED, $dBCommande->id, $dBCommande->table_element);
                                        }
                                    }
                                    if ($commandeArray['status'] == 2)      // Should be Commande::STATUS_SHIPMENTONPROCESS but not defined in dolibarr 3.9
                                    {
                                        if ($dBCommande->statut != 2)
                                        {
                                            $dBCommande->setStatut(2, $dBCommande->id, $dBCommande->table_element);
                                        }
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CANCELED)
                                    {
                                        if ($dBCommande->statut != Commande::STATUS_CANCELED)
                                        {
                                            $idWareHouse = 0;
                                            // We don't change stock here, even if dolibarr option is on because, this should be already done by product sync
                                            //if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr') $idWareHouse=$this->eCommerceSite->fk_warehouse;
                                            $dBCommande->cancel(0, $idWareHouse);
                                        }
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CLOSED)
                                    {
                                        if ($dBCommande->statut != Commande::STATUS_CLOSED)
                                        {
                                            $dBCommande->cloture($user);
                                        }
                                        // order in Dolibarr not yet billed and billed status in ecommerce is done
                                        if (! $dBCommande->billed && $commandeArray['billed'] == 1)
                                        {
                                            $dBCommande->classifyBilled($this->user);
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            dol_syslog("synchCommande Order not found in Dolibarr, so we create it");

                            // First, we check object does not alreay exists without the link. Search using external ref. (This may occurs when we delete the table of links)
                            // If not, we create it, if it exists, do nothing (except update status).
                            $result = $dBCommande->fetch(0, '', $this->eCommerceSite->name.'-'.$commandeArray['ref_client']);
                            if ($result == 0)
                            {
                                //create commande
                                $dBCommande->statut=Commande::STATUS_DRAFT;             // STATUS_DRAFT by default at creation
                                $dBCommande->ref_client = $commandeArray['ref_client'];
                                $dBCommande->ref_ext = $this->eCommerceSite->name.'-'.$commandeArray['ref_client'];
                                $dBCommande->date_commande = strtotime($commandeArray['date_commande']);
                                $dBCommande->date_livraison = strtotime($commandeArray['date_livraison']);
                                $dBCommande->socid = $this->eCommerceSociete->fk_societe;
                                $input_method_id = dol_getIdFromCode($this->db, 'OrderByWWW', 'c_input_method', 'code', 'rowid');  // Order mode. Not visible with some Dolibarr versions
                                $dBCommande->source=$input_method_id;
                                $dBCommande->context['fromsyncofecommerceid'] = $this->eCommerceSite->id;
                                $dBCommande->note_private=isset($commandeArray['note'])?$commandeArray['note']:"";
                                if (! empty($conf->global->ECOMMERCENG_ENABLE_LOG_IN_NOTE))
                                {
                                    $dBCommande->note_private.="Last eCommerce order received:\n".serialize(var_export($commandeArray['remote_order'], true));
                                }

                                if (is_array($commandeArray['extrafields'])) {
                                    foreach ($commandeArray['extrafields'] as $extrafield => $extrafield_value) {
                                        $dBCommande->array_options['options_'.$extrafield] = $extrafield_value;
                                    }
                                }

                                $result = $dBCommande->create($this->user);
                                if ($result <= 0)
                                {
                                    dol_syslog("synchCommande result=".$result." ".$dBCommande->error, LOG_ERR);
                                    $error++;
                                    $this->error = $this->langs->trans('ECommerceSynchCommandeCreateError').' '.$dBCommande->error;
                                    $this->errors[] = $this->error;
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
                                                $this->error = $this->langs->trans('ECommerceSyncheCommerceCommandeUpdateError').' '.$dBCommande->error;
                                                $this->errors[] = $this->error;
                                                $error++;
                                                break;	// break on items
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

                                            $description = $item['description'];
                                            if (empty($description) && $fk_product > 0) {
                                                $dBProduct = new Product($this->db);
                                                $dBProduct->fetch($fk_product);
                                                $description = $dBProduct->label;
                                            }
                                            if (empty($description)) {
                                                $description = $this->langs->trans('ECommerceNoDescForProductLine');
                                            }

                                            $result = $dBCommande->addline($description, $item['price'], $item['qty'], $item['tva_tx'], 0, 0,
                                                $fk_product, //fk_product
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
                                                $this->error = $this->langs->trans('ECommerceSyncheCommerceCommandeUpdateError').' '.$dBCommande->error;
                                                $this->errors[] = $this->error;
                                                $error++;
                                                break;  // break on items
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
                                        $this->error = $this->langs->trans('ECommerceSyncheCommerceCommandeUpdateError').' '.$dBCommande->error;
                                        $this->errors[] = $this->error;
                                        $error++;
                                    }
                                }
                            }

                            // Now update status
                            if (! $error)
                            {
                                //if ($dBCommande->statut != $commandeArray['status'])      // Always when creating
                                //{
                                    dol_syslog("synchCommande Status of order must be now set: we update order id=".$dBCommande->id." ref_client=".$dBCommande->ref_client." from status ".$dBCommande->statut." to status ".$commandeArray['status']);

                                    // Draft or not draft
                                    if ($commandeArray['status'] == Commande::STATUS_DRAFT)
                                    {
                                        // Target status is status draft. Should not happen with magento.
                                        // Nothing to do
                                    }
                                    else
                                    {
                                        // Target status is not draft. We validate if current status is still draft to get correct ref.
                                        if ($dBCommande->statut == Commande::STATUS_DRAFT)
                                        {
                                            $idWareHouse = 0;
                                            // We don't change stock here, even if dolibarr option is on because, this should be already done by product sync
                                            //if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr') $idWareHouse=$this->eCommerceSite->fk_warehouse;
                                            $dBCommande->valid($this->user, $idWareHouse);
                                        }
                                    }

                                    // Which target status ?
                                    if ($commandeArray['status'] == Commande::STATUS_VALIDATED)
                                    {
                                        if ($dBCommande->statut != Commande::STATUS_VALIDATED)
                                        {
                                            $dBCommande->setStatut(Commande::STATUS_VALIDATED, $dBCommande->id, $dBCommande->table_element);
                                        }
                                    }
                                    if ($commandeArray['status'] == 2)            // Should be Commande::STATUS_SHIPMENTONPROCESS but not defined in dolibarr 3.9
                                    {
                                        if ($dBCommande->statut != 2)
                                        {
                                            $dBCommande->setStatut(2, $dBCommande->id, $dBCommande->table_element);
                                        }
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CANCELED)
                                    {
                                        if ($dBCommande->statut != Commande::STATUS_CANCELED)
                                        {
                                            $idWareHouse = 0;
                                            // We don't change stock here, even if dolibarr option is on because, this should be already done by product sync
                                            //if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr') $idWareHouse=$this->eCommerceSite->fk_warehouse;
                                            $dBCommande->cancel(0, $idWareHouse);
                                        }
                                    }
                                    if ($commandeArray['status'] == Commande::STATUS_CLOSED)
                                    {
                                        if ($dBCommande->statut != Commande::STATUS_CLOSED)
                                        {
                                            $dBCommande->cloture($this->user);
                                        }
                                        // order in Dolibarr not yet billed and billed status in ecommerce is done
                                        if (! $dBCommande->billed && $commandeArray['billed'] == 1)
                                        {
                                            $dBCommande->classifyBilled($this->user);
                                        }

                                    }
                                //}
                            }

                            //add or update contacts of order ($this->eCommerceSociete->fk_societe is id in Dolibarr of thirdparty but may be id of the generic "non logged user")
                            $fk_soc_socpeopleCommande = $this->eCommerceSociete->fk_societe;
                            $fk_soc_socpeopleFacture = $this->eCommerceSociete->fk_societe;
                            $fk_soc_socpeopleLivraison = $this->eCommerceSociete->fk_societe;
                            if ($this->eCommerceSociete->fk_societe == $this->eCommerceSite->fk_anonymous_thirdparty) {
                                if (!empty(trim($commandeArray['socpeopleCommande']['email']))) {
                                    $fk_soc_socpeopleCommande = get_company_by_email($this->db, $commandeArray['socpeopleCommande']['email'], $this->eCommerceSite->id);
                                }
                                if (!empty(trim($commandeArray['socpeopleFacture']['email']))) {
                                    $fk_soc_socpeopleFacture = get_company_by_email($this->db, $commandeArray['socpeopleFacture']['email'], $this->eCommerceSite->id);
                                }
                                if (!empty(trim($commandeArray['socpeopleLivraison']['email']))) {
                                    $fk_soc_socpeopleLivraison = get_company_by_email($this->db, $commandeArray['socpeopleLivraison']['email'], $this->eCommerceSite->id);
                                }
                            }
                            $commandeArray['socpeopleCommande']['fk_soc'] = $fk_soc_socpeopleCommande;
                            $commandeArray['socpeopleFacture']['fk_soc'] = $fk_soc_socpeopleFacture;
                            $commandeArray['socpeopleLivraison']['fk_soc'] = $fk_soc_socpeopleLivraison;

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

                        // Update Payment method
                        if (! $error) {
                            if (isset($commandeArray['payment_method'])) {
                                $payment_method = dol_getIdFromCode($this->db, $commandeArray['payment_method'], 'c_paiement', 'libelle', 'id');
                                if ($payment_method != '' && $payment_method > 0) {
                                    $dBCommande->setPaymentMethods($payment_method);
                                }
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
                                    $this->error = $this->langs->trans('ECommerceSyncheCommerceCommandeUpdateError').' '.$this->eCommerceCommande->error;
                                    $this->errors[] = $this->error;
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
                                    $this->error = $this->langs->trans('ECommerceSyncheCommerceCommandeCreateError').' '.$dBCommande->id.', '.$this->eCommerceCommande->error;
                                    $this->errors[] = $this->error;
                                    dol_syslog($this->error, LOG_WARNING);
                                }
                            }
                        }
                        else
                        {
                            $error++;
                            $this->errors[] = $this->langs->trans('ECommerceSynchCommandeError');
                        }
                    }
                    else {
                        if ($commandeArray['remote_id_societe'] != 0) {
                            $error++;
                            $this->errors[] = $this->langs->trans('ECommerceSynchCommandeErrorSocieteNotExists') . ' (Commande ID='.$commandeArray['remote_id'].', Client ID:' . $commandeArray['remote_id_societe'].')';
                        } else
                        {
                            $error++;
                            $this->errors[] = $this->langs->trans('ECommerceSynchCommandeErrorSocieteNotExists') . ' (remote_id='.$commandeArray['remote_id'].') - Unknown customer.';
                            if (empty($this->eCommerceSite->fk_anonymous_thirdparty)) {
                                $this->errors[] = 'This order is not linked to a dedicated customer. Try to set option Anonymous thirdparty into the site parameters';
                            }
                        }
                    }
                    unset($dBCommande);
                    unset($this->eCommerceSociete);
                    unset($this->eCommerceCommande);

                    if ($error)
                    {
                        $this->db->rollback();
                        $nbrecorderror++;
                        // We decide to stop on first error (Can continue if anonymous order with const ECOMMERCENG_PASS_ORDER_FOR_NONLOGGED_CUSTOMER)
                        if ($commandeArray['remote_id_societe'] != 0 || empty($conf->global->ECOMMERCENG_PASS_ORDER_FOR_NONLOGGED_CUSTOMER)) {
                            break;
                        } else {
                            $error = 0;
                        }
                    }
                    else
                    {
                        $this->db->commit();
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    }
                }

                if (! $nbrecorderror)
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchCommandeSuccess');

                    // TODO If we commit even if there was an error (to validate previous record ok), we must also remove 1 second the the higher
                    // date into table of links to be sure we will retry also record with same update_at than the last record ok

                    return $nbgoodsunchronize;
                }
                else
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchCommandeSuccess');

                    return -1;
                }
            }
            else
            {
                $this->error=$this->langs->trans('ECommerceErrorsynchCommande').' (Code FailToGetDetailsOfRecord)';
                $this->errors[] = $this->error;
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchCommande');
        }

        return -1;
    }

    /**
     * Synchronize facture to update
     *
     * @param   int     $toNb       Max nb to synch
     * @return  int                 Id of product synchronized if OK, -1 if KO
     */
    public function synchFacture($toNb=0)
    {
        global $conf, $user;

        $error = 0;

        try {
            $factures = array();

            dol_syslog("***** eCommerceSynchro synchFacture");

            $resulttoupdate=$this->getFactureToUpdate();
            if (is_array($resulttoupdate))
            {
                if (count($resulttoupdate) > 0) $factures = $this->eCommerceRemoteAccess->convertRemoteObjectIntoDolibarrFacture($resulttoupdate, $toNb);
            }
            else
            {
                $error++;
            }

            // Check return of remote...
            if (is_array($resulttoupdate) && count($resulttoupdate) > 0 && (! is_array($factures) || count($factures) == 0))    // return of remote is bad or empty when input was not empty
            {
                $error++;
            }

            if (! $error && is_array($factures))
            {
                // Local filter to exclude bundles and other complex types
//                $productsTypesOk = array('simple', 'virtual', 'downloadable');

                $counter=0;
                foreach ($factures as $factureArray)
                {
                    dol_syslog("- Process synch of invoice with remote_order_id=".$factureArray['remote_order_id']);

                    $counter++;
                    if ($toNb > 0 && $counter > $toNb) break;

                    $this->db->begin();

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

                    //check if societe exists in eCommerceSociete (with remote id). This init ->fk_societe. This is a sql request.
                    //$societeExists will be 1 (found) or -1 (not found)
                    if (! empty($factureArray['remote_id_societe']))    // May be empty if customer is a non logged user or was deleted on magento side.
                    {
                        $societeExists = $this->eCommerceSociete->fetchByRemoteId($factureArray['remote_id_societe'], $this->eCommerceSite->id);
                    }
                    else
                    {
                        // This is an unknown customer. May be a non logged customer.
                        if ($this->eCommerceSite->fk_anonymous_thirdparty > 0)
                        {
                            $societeExists = 1;
                            $this->eCommerceSociete->fk_societe = $this->eCommerceSite->fk_anonymous_thirdparty;
                        }
                        else
                        {
                            $societeExists = 0;
                        }
                    }

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
                                if ($dBFacture->statut != $factureArray['status'])
                                {
                                    dol_syslog("Status of invoice has changed, we update invoice from status ".$dBFacture->statut." to status ".$factureArray['status']);

                                    // Draft or not draft
                                    if ($factureArray['status'] == Facture::STATUS_DRAFT)   // status draft. Should not happen with magento
                                    {
                                        // Target status is status draft. Should not happen with magento.
                                        // Nothing to do
                                    }
                                    else
                                    {
                                        // Target status is not draft. We validate if current status is still draft to get correct ref.
                                        if ($dBFacture->statut == Facture::STATUS_DRAFT)
                                        {
                                            $idWareHouse = 0;
                                            // We don't change stock here, even if dolibarr option is on because, this should be already done by product sync
                                            //if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr') $idWareHouse=$this->eCommerceSite->fk_warehouse;
                                            $dBFacture->validate($this->user, '', $idWareHouse);
                                        }
                                    }

                                    // Which target status ?
                                    if ($factureArray['status'] == Facture::STATUS_VALIDATED)
                                    {
                                        if ($dBFacture->statut != Facture::STATUS_VALIDATED)
                                        {
                                            $dBFacture->setStatut(Facture::STATUS_VALIDATED, $dBFacture->id, $dBFacture->table_element);
                                        }
                                    }
                                    if ($factureArray['status'] == Facture::STATUS_ABANDONED)
                                    {
                                        if ($dBFacture->statut != Facture::STATUS_ABANDONED)
                                        {
                                            $dBFacture->set_canceled($this->user, $factureArray['close_code'], $factureArray['close_note']);
                                        }
                                    }
                                    if ($factureArray['status'] == Facture::STATUS_CLOSED)
                                    {
                                        if ($dBFacture->statut != Facture::STATUS_CLOSED)
                                        {
                                            // Enter payments
                                            //$dBFacture->cloture($this->user);
                                            $payment = new Paiement($this->db);
                                            /*
                                             $payment->datepaye = 'ee';
                                             $payment->paiementid = 0;
                                             $payment->num_paiement = 0;
                                             $payment->amounts=array();
                                             $resultpayment = $payment->create($user);
                                             if ($resultpayment < 0)
                                             {
                                             $error++;
                                             $this->errors[] = "Failed to create payment";
                                             }
                                             */

                                            $dBFacture->set_paid($this->user, '', '');
                                        }
                                    }

                                }

                            }
                            else
                            {
                                $error++;
                                $this->errors[] = $this->langs->trans('ECommerceSynchFactureErrorFactureSynchExistsButNotFacture');
                                break;
                            }
                        }
                        else
                        {
                            //create invoice

                            // If we create invoice, we can force status of order in some cases
                            if ($refCommandeExists > 0 && $dBCommande->statut == Commande::STATUS_DRAFT)
                            {
                                $idWareHouse = 0;
                                // We don't change stock here, even if dolibarr option is on because, this should be already done by product sync
                                //if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr') $idWareHouse=$this->eCommerceSite->fk_warehouse;
                                $dBCommande->valid($this->user, $idWareHouse);
                            }
                            if ($refCommandeExists > 0 && $dBCommande->statut == Commande::STATUS_VALIDATED)
                            {
                                $dBCommande->cloture($this->user);
                            }
                            //var_dump($factureArray);exit;


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
                                $dBFacture->note_private="";
                                if (! empty($conf->global->ECOMMERCENG_ENABLE_LOG_IN_NOTE))
                                {
                                    $dBFacture->note_private .= "Last eCommerce invoice received:\n".dol_trunc(serialize(var_export($factureArray['remote_invoice'], true)), 65000);
                                    $dBFacture->note_private .= "\n\n";
                                    $dBFacture->note_private .= "Last eCommerce order received:\n".dol_trunc(serialize(var_export($factureArray['remote_order'], true)), 65000);
                                }

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
                                            $error++;
                                            $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceFactureUpdateError').' '.$dbFacture->error;
                                            $this->errors = array_merge($this->errors, $dbFacture->errors);
                                            break;	// break items
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
                                if (! $error && $factureArray['delivery']['qty'] > 0)
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

                            // Now update status
                            if (! $error)
                            {
                                //if ($dBFacture->statut != $factureArray['status'])      // Always when creating
                                //{
                                dol_syslog("synchFacture Status of invoice must be now set: we update invoice id=".$dBFacture->id." ref_client=".$dBFacture->ref_client." from status ".$dBFacture->statut." to status ".$factureArray['status']);

                                // Draft or not draft
                                if ($factureArray['status'] == Facture::STATUS_DRAFT)   // status draft. Should not happen with magento
                                {
                                        // Target status is status draft. Should not happen with magento.
                                        // Nothing to do
                                }
                                else
                                {
                                    // Target status is not draft. We validate if current status is still draft to get correct ref.
                                    if ($dBFacture->statut == Facture::STATUS_DRAFT)
                                    {
                                        $idWareHouse = 0;
                                        // We don't change stock here, even if dolibarr option is on because, this should be already done by product sync
                                        //if ($this->eCommerceSite->stock_sync_direction == 'ecommerce2dolibarr') $idWareHouse=$this->eCommerceSite->fk_warehouse;
                                        $dBFacture->validate($this->user, '', $idWareHouse);
                                    }
                                }

                                // Which target status ?
                                if ($factureArray['status'] == Facture::STATUS_VALIDATED)
                                {
                                    if ($dBFacture->statut != Facture::STATUS_VALIDATED)
                                    {
                                        $dBFacture->setStatut(Facture::STATUS_VALIDATED, $dBFacture->id, $dBFacture->table_element);
                                    }
                                }
                                if ($factureArray['status'] == Facture::STATUS_ABANDONED)
                                {
                                    if ($dBFacture->statut != Facture::STATUS_ABANDONED)
                                    {
                                        $dBFacture->set_canceled($this->user, $factureArray['close_code'], $factureArray['close_note']);
                                    }
                                }
                                if ($factureArray['status'] == Facture::STATUS_CLOSED)
                                {
                                    if ($dBFacture->statut != Facture::STATUS_CLOSED)
                                    {
                                        // Enter payment
                                        // Magento seems to do one payment for one invoice

                                        $payment = new Paiement($this->db);
                                        /*
                                        $payment->datepaye = 'ee';
                                        $payment->paiementid = 0;
                                        $payment->num_paiement = 0;
                                        $payment->amounts=array();
                                        $resultpayment = $payment->create($user);
                                        if ($resultpayment < 0)
                                        {
                                            $error++;
                                            $this->errors[] = "Failed to create payment";
                                            $this->errors = array_merge($this->errors, $payment->errors);
                                        }
                                        */

                                        //$factureArray['remote_order']["payment"] is one record with summ of different payments/invoices.

                                        //exit;

                                        $dBFacture->set_paid($this->user, '', '');
                                    }
                                }

                            }

                        }

                        /* **************************************************************
                         *
                         * register into eCommerceFacture
                         *
                         * ************************************************************** */
                        //if synchro invoice ok
                        if (! $error)
                        {
                            $this->eCommerceFacture->last_update = $factureArray['last_update'];
                            $this->eCommerceFacture->fk_facture = $dBFacture->id;
                            //if a previous synchro exists
                            if ($synchFactureExists > 0)
                            {
                                //eCommerce update
                                if ($this->eCommerceFacture->update($this->user) < 0)
                                {
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceFactureUpdateError').' '.$this->eCommerceFacture->error;
                                    $this->errors = array_merge($this->errors, $this->eCommerceFacture->errors);
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
                                    $error++;
                                    $this->errors[] = $this->langs->trans('ECommerceSyncheCommerceFactureCreateError').' '.$dBFacture->id.', '.$this->eCommerceFacture->error;
                                    $this->errors = array_merge($this->errors, $this->eCommerceFacture->errors);
                                    dol_syslog($this->langs->trans('ECommerceSyncheCommerceFactureCreateError') . ' ' . $dBFacture->id.', '.$this->eCommerceFacture->error, LOG_WARNING);
                                }
                            }
                        }
                        else
                        {
                            $error++;
                            $this->errors[] = $this->langs->trans('ECommerceSynchCommandeError');
                        }
                    }
                    else
                    {
                        $error++;
                        if ($societeExists <= 0)
                        {
                            $this->errors[] = $this->langs->trans('ECommerceSynchFactureErrorSocieteNotExists', $factureArray['remote_id_societe']);
                        }
                        if ($synchCommandeExists <= 0)
                        {
                            $this->errors[] = $this->langs->trans('ECommerceSynchFactureErrorCommandeNotExists', $factureArray['remote_order_id']);
                        }
                    }

                    unset($dBFacture);
                    unset($dBCommande);
                    unset($dBExpedition);
                    unset($this->eCommerceSociete);
                    unset($this->eCommerceFacture);
                    unset($this->eCommerceCommande);

                    if ($error || ! empty($this->errors))
                    {
                        $this->db->rollback();
                        $nbrecorderror++;
                        break;      // We decide to stop on first error
                    }
                    else
                    {
                        $this->db->commit();
                        $nbgoodsunchronize = $nbgoodsunchronize + 1;
                    }
                }

                if (! $error)
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchFactureSuccess');

                    // TODO If we commit even if there was an error (to validate previous record ok), we must also remove 1 second the the higher
                    // date into table of links to be sure we will retry also record with same update_at than the last record ok

                    return $nbgoodsunchronize;
                }
                else
                {
                    $this->success[] = $nbgoodsunchronize . ' ' . $this->langs->trans('ECommerceSynchFactureSuccess');

                    return -1;
                }
            }
            else
            {
                $this->error=$this->langs->trans('ECommerceErrorsynchProduct').' (Code FailToGetDetailsOfRecord)';
                $this->errors[] = $this->error;
            }
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorsynchFacture');
        }

        return -1;
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
        $error = 0;

        try {
            dol_syslog("***** eCommerceSynchro syncLivraison");

            return $this->eCommerceRemoteAccess->createRemoteLivraison($livraison, $remote_order_id);
        } catch (Exception $e) {
            $this->errors[] = $this->langs->trans('ECommerceErrorrCeateRemoteLivraison');
        }
        return false;
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

            $this->db->begin();

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
                            $idWarehouse = 0;
                            // We don't change stock here, it's a clean of database that don't change stock
                            if ((float) DOL_VERSION < 5.0) $resultdelete = $dbFacture->delete($dbFacture->id, 0, $idWarehouse);
                            else $resultdelete = $dbFacture->delete($this->user, 0, $idWarehouse);

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

            $this->db->commit();
        }

        //Drop commands
        if (empty($mode) || preg_match('/^orders/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceCommande();
            $arrayECommerceCommandeIds = $this->eCommerceCommande->getAllECommerceCommandeIds($this->eCommerceSite->id);

            $this->db->begin();

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

            $this->db->commit();
        }

        //Drop products
        if (empty($mode) || preg_match('/^products/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceProduct();
            $arrayECommerceProductIds = $this->eCommerceProduct->getAllECommerceProductIds($this->eCommerceSite->id);

            $this->db->begin();

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

            $this->db->commit();
        }

        //Drop socPeople
        if (empty($mode) || preg_match('/^thirdparties/', $mode))
//        if (empty($mode) || preg_match('/^contacts/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceSocpeople();
            $arrayECommerceSocpeopleIds = $this->eCommerceSocpeople->getAllECommerceSocpeopleIds($this->eCommerceSite->id);

            $this->db->begin();

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

            $this->db->commit();
        }

        //Drop societes
        if (empty($mode) || preg_match('/^thirdparties/', $mode))
        {
            $dolObjectsDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceSociete();
            $arrayECommerceSocieteIds = $this->eCommerceSociete->getAllECommerceSocieteIds($this->eCommerceSite->id);

            $this->db->begin();

            foreach ($arrayECommerceSocieteIds as $idSociete)
            {
                $this->initECommerceSociete();
                if ($this->eCommerceSociete->fetch($idSociete) > 0)
                {
                    // Skip anonymous thirdparty
                    if ($this->eCommerceSociete->remote_id == 0) continue;

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
                    if ($this->eCommerceSociete->delete($this->user, 0, $this->eCommerceSite->name) > 0)
                        $synchObjectsDeleted++;
                }
            }

            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolSocieteSuccess');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchSocieteSuccess');
            unset($this->eCommerceSociete);

            $this->db->commit();
        }

        //Drop categories
        if (empty($mode) || preg_match('/^categories/', $mode))
        {
            $dolObjectsDeleted = 0;
            $dolObjectsNotDeleted = 0;
            $synchObjectsDeleted = 0;
            $this->initECommerceCategory();
            $arrayECommerceCategoryIds = $this->eCommerceCategory->getAllECommerceCategoryIds($this->eCommerceSite);

            $this->db->begin();

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
                            else
                                $dolObjectsNotDeleted++;
                        }
                    }
                    if ($this->eCommerceCategory->delete($this->user, 0) > 0)
                        $synchObjectsDeleted++;
                }
            }

            if ($deletealsoindolibarr) $this->success[] = $dolObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetDolCategorySuccess').($dolObjectsNotDeleted?' ('.$dolObjectsNotDeleted.' ko)':'');
            $this->success[] = $synchObjectsDeleted . ' ' . $this->langs->trans('ECommerceResetSynchCategorySuccess');
            unset($this->eCommerceCategory);

            $this->db->commit();
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
		$sql .= ' WHERE lastname LIKE "'.$db->escape(trim($contact->lastname)).'"';
		$sql .= ' AND firstname LIKE "'.$db->escape(trim($contact->firstname)).'"';
		$sql .= ' AND address LIKE "'.$db->escape(trim($contact->address)).'"';
		$sql .= ' AND town LIKE "'.$db->escape(trim($contact->town)).'"';
        $sql .= ' AND zip LIKE "'.$db->escape(trim($contact->zip)).'"';
        if (isset($contact->country_id)) $sql .= ' AND fk_pays='.($contact->country_id>0?$contact->country_id:'NULL');
        if (isset($contact->email)) $sql .= ' AND email LIKE "'.$db->escape(trim($contact->email)).'"';
        if (isset($contact->phone_pro)) $sql .= ' AND phone LIKE "'.$db->escape(trim($contact->phone_pro)).'"';
        if (isset($contact->fax)) $sql .= ' AND fax LIKE "'.$db->escape(trim($contact->fax)).'"';
		$sql .= ' AND fk_soc='.$contact->fk_soc;

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
			return $contactId;
		}
	}


}

