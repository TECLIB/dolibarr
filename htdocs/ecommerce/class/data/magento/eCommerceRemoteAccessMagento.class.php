<?php

/*
 * @module		ECommerce
 * @version		1.2
 * @copyright	Auguria
 * @author		<franck.charpentier@auguria.net>
 * @licence		GNU General Public License
 */
dol_include_once('/ecommerce/admin/class/data/eCommerceDict.class.php');
dol_include_once('/ecommerce/class/data/eCommerceCategory.class.php');
dol_include_once('/ecommerce/class/data/eCommerceSociete.class.php');


/**
 * Class for access remote sites
 */
class eCommerceRemoteAccessMagento
{

    private $site;
    private $session;
    private $client;
    private $filter;
    private $taxRates;
    private $db;

    /**
     *      Constructor
     *      @param      $site eCommerceSite
     */
    function eCommerceRemoteAccessMagento($db, $site)
    {
        $this->db = $db;
        $this->site = $site;
        return 1;
    }

    /**
     * Connect to API
     * 
     * @return boolean      True if OK, False if KO
     */
    public function connect()
    {
        try {
            require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');
            $params=getSoapParams();
            
            if ($this->site->timeout > 0)
            {
                $params['response_timeout'] = $this->site->timeout;
            }
            ini_set('default_socket_timeout', $params['response_timeout']);
            ini_set("memory_limit", "1024M");
            
            //ini_set("soap.wsdl_cache_enabled", "0");    // For test
            //$params['cache_wsdl']=WSDL_CACHE_NONE;
            
            dol_syslog("eCommerceRemoteAccessMagento Connect to API webservice_address=".$this->site->webservice_address." user_name=".$this->site->user_name." user_password=".$this->site->user_password);
            
            $this->client = new SoapClient($this->site->webservice_address, $params);
            
            
            $this->session = $this->client->login($this->site->user_name, $this->site->user_password);

            dol_syslog("eCommerceRemoteAccessMagento connected with session=".$this->session);
            
            return true;
        }
        catch (SoapFault $fault) 
        {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Call Magenta API to get last updated companies
     * 
     * @param   datetime $fromDate      From date
     * @param   datetime $toDate        To date
     * @return  void|mixed              Response from SOAP call, normally an associative array mirroring the structure of the XML response, nothing if error         
     */
    public function getSocieteToUpdate($fromDate, $toDate)
    {
        try {
            dol_syslog("getSocieteToUpdate start");
            $filter = array('updated_at' => array('gt' => $fromDate, 'lt' => $toDate));
            $result = $this->client->call($this->session, 'customer.list', array($filter));
            dol_syslog("getSocieteToUpdate end");
            return $result;
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Call Magenta API to get last updated products
     * 
     * @param   datetime $fromDate      From date
     * @param   datetime $toDate        To date
     * @return  void|mixed              Response from SOAP call, normally an associative array mirroring the structure of the XML response, nothing if error         
     */
    public function getProductToUpdate($fromDate, $toDate)
    {
        try {
            dol_syslog("getProductToUpdate start");
            $filter = array(
                    array('updated_at' => array('gt' => $fromDate, 'lt' => $toDate)),
//                    array('type_id', array('eq' => 'downloadable'))
            );
            $result = $this->client->call($this->session, 'catalog_product.list', $filter);
            
            $results = array();
            $productsTypesOk = array('simple', 'virtual', 'downloadable');
            foreach ($result as $product) {
                if (in_array($product['type'], $productsTypesOk))
                        $results[] = $product;
            }
            
            dol_syslog("getProductToUpdate end");
            return $results;            
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Call Magenta API to get last updated orders
     * 
     * @param   datetime $fromDate      From date
     * @param   datetime $toDate        To date
     * @return  void|mixed              Response from SOAP call, normally an associative array mirroring the structure of the XML response, nothing if error         
     */
    public function getCommandeToUpdate($fromDate, $toDate)
    {
        try {
            dol_syslog("getCommandeToUpdate start");
            $filter = array('updated_at' => array('gt' => $fromDate, 'lt' => $toDate));
            $result = $this->client->call($this->session, 'sales_order.list', array($filter));
            
            foreach ($result as $rcommande)
            {
                $calls[] = array('sales_order.info', $rcommande['increment_id']);
            }
            try {
                $results = $this->client->multiCall($this->session, $calls);
            } catch (SoapFault $fault) {
                //echo 'convertRemoteObjectIntoDolibarrCommande :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString();
            }
            
            dol_syslog("getCommandeToUpdate end");
            return $result;
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Call Magenta API to get last updated invoices
     * 
     * @param   datetime $fromDate      From date
     * @param   datetime $toDate        To date
     * @return  void|mixed              Response from SOAP call, normally an associative array mirroring the structure of the XML response, nothing if error         
     */
    public function getFactureToUpdate($fromDate, $toDate)
    {
        try {
            dol_syslog("getFactureToUpdate start");
            $filter = array('updated_at' => array('gt' => $fromDate, 'lt' => $toDate));
            $result = $this->client->call($this->session, 'sales_order_invoice.list', array($filter));
            dol_syslog("getFactureToUpdate end");
            return $result;
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Put the remote data into societe dolibarr data from instantiated class in the constructor
     * 
     * @param $remoteObject array
     * @return array societe
     */
    public function convertRemoteObjectIntoDolibarrSociete($remoteObject)
    {
        $societes = array();
        $calls = array();
        if (count($remoteObject))
        {
            foreach ($remoteObject as $rsociete)
            {
                $calls[] = array('customer.info', $rsociete['customer_id']);
            }
            try {
                $results = $this->client->multiCall($this->session, $calls);
            } catch (SoapFault $fault) {
                //echo 'convertRemoteObjectIntoDolibarrSociete :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString();
            }
            if (count($results))
                foreach ($results as $societe)
                {
                    $newobj=array(
                            'remote_id' => $societe['customer_id'],
                            'last_update' => $societe['updated_at'],
                            'name' =>dolGetFirstLastname($societe['firstname'], $societe['lastname']),
                            'email' => $societe['email'],
                            'client' => 3, //for client/prospect
                            'vatnumber'=> $societe['taxvat']
                    );
                    $societes[] = $newobj;
                }
        }

        //important - order by last update
        if (count($societes))
        {
            foreach ($societes as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $societes);
        }
        return $societes;
    }

    
    /**
     * Put the remote data into societe dolibarr data from instantiated class in the constructor
     * 
     * @param   $listofids      List of object with customer_address_id is id of addresss
     * @return array societe
     */
    public function convertRemoteObjectIntoDolibarrSocpeople($listofids)
    {
        $socpeoples = array();
        $calls = array();
        if (count($listofids))
        {
            foreach ($listofids as $listofid)
            {
                $calls[] = array('customer_address.info', $listofid['customer_address_id']);
            }
            try {
                $results = $this->client->multiCall($this->session, $calls);
            } catch (SoapFault $fault) {
                //echo 'convertRemoteObjectIntoDolibarrSociete :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString();
            }
            if (count($results))
                foreach ($results as $socpeople)
                {
                    $newobj=array(
                            'remote_id' => $socpeople['customer_address_id'],
                            'last_update' => $socpeople['updated_at'],
                            'name' =>dolGetFirstLastname($socpeople['firstname'], $socpeople['lastname']),
                            'email' => $socpeople['email'],
                            'address' => $socpeople['street'],
                            'town' => $socpeople['city'],
                            'zip' => $socpeople['postcode'],
                            'country_code' => $socpeople['country_id'],
                            'phone' => $socpeople['telephone'],
                            'fax' => $socpeople['fax'],
                            'firstname' => $socpeople['firstname'],
                            'lastname' => $socpeople['lastname'],
                            'vatnumber'=> $socpeople['taxvat'],
                            'is_default_billing' => $socpeople['is_default_billing'],
                            'is_default_shipping' => $socpeople['is_default_shipping']
                    );
                    $socpeoples[] = $newobj;
                }
        }
        
        //important - order by last update
        if (count($socpeoples))
        {
            foreach ($socpeoples as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $socpeoples);
        }
        return $socpeoples;
    }
    
    
    /**
     * Put the remote data into product dolibarr data from instantiated class in the constructor
     * 
     * @param $remoteObject array
     * @return array product
     */
    public function convertRemoteObjectIntoDolibarrProduct($remoteObject)
    {
        $products = array();
        $calls = array();
       
        $canvas = '';
        
        $nbsynchro = 0;
        if (count($remoteObject))
        {
            foreach ($remoteObject as $rproduct)
            {
                if ($rproduct['sku'])
                {
                    $calls[] = array('catalog_product.info', $rproduct['sku']);
                }
                $nbsynchro = $nbsynchro + 1;
            }

            try {
                $results = $this->client->multiCall($this->session, $calls);
            } catch (SoapFault $fault) {
                $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
                dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
                return false;
            }

            if (count($results))
                foreach ($results as $product)
                {
                    //var_dump($product);exit;
                    $products[] = array(
                            'ref' => dol_sanitizeFileName(stripslashes($product['sku'])),
                            'label' => $product['name'],
                            'description' => $product['description'],
                            'weight' => $product['weight'],
                            'last_update' => $product['updated_at'],
                            'price' => (($this->site->magento_use_special_price && $product['special_price'] != NULL ) ? $product['special_price'] : $product['price']),
                            'envente' => $product['status'] ? 1 : 0,
                            'remote_id' => $product['product_id'],  // id in ecommerce magento
                            'fk_product_type' => 0, //$product['fk_product_type'] type de produit (manufacturé ou matiere premiere) dépend d'un attribut dynamique
                            'finished' => 1, //Etat $product['price']
                            'canvas' => $canvas,
                            'categories' => $product['categories'],
                            'tax_rate' => $product['tax_rate'],
                            'price_min' => $product['minimal_price'],
                            'fk_country' => ($product['country_of_manufacture'] ? getCountry($product['country_of_manufacture'], 3, $this->db, '', 0, '') : null)
                    );
                    // We also get special_price, minimal_price => ?, msrp, 
                }
        }
        //important - order by last update
        if (count($products))
        {
            foreach ($products as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $products);
        }
        return $products;
    }

    /**
     * Put the remote data into commande dolibarr data from instantiated class in the constructor
     * @param $remoteObject array
     * @return array commande
     */
    public function convertRemoteObjectIntoDolibarrCommande($remoteObject)
    {
        $commandes = array();
        $calls = array();
        if (count($remoteObject))
        {
            foreach ($remoteObject as $rcommande)
            {
                $calls[] = array('sales_order.info', $rcommande['increment_id']);
            }
            try {
                $results = $this->client->multiCall($this->session, $calls);
            } catch (SoapFault $fault) {
                //echo 'convertRemoteObjectIntoDolibarrCommande :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString();
            }
            if (count($results))
            {
                foreach ($results as $commande)
                {
                    //set each items
                    $items = array();
                    $configurableItems = array();
                    if (count($commande['items']))
                        foreach ($commande['items'] as $item)
                        {
                            // If item is configurable, localMemCache it, to use its price and tax rate instead of the one of its child
                            if ($item['product_type'] == 'configurable') {
                                $configurableItems[$item['item_id']] = array(
                                    'item_id' => $item['item_id'],
                                    'id_remote_product' => $item['product_id'],
                                    'description' => $item['name'],
                                    'product_type' => $item['product_type'], 
                                    'price' => $item['price'],
                                    'qty' => $item['qty_ordered'],
                                    'tva_tx' => $item['tax_percent']
                                );
                            } else {
                                // If item has a parent item id defined in $configurableItems, get it's price and tax values instead of 0
                                if (!array_key_exists($item['parent_item_id'], $configurableItems)) {
                                    $items[] = array(
                                            'item_id' => $item['item_id'],
                                            'id_remote_product' => $item['product_id'],
                                            'description' => $item['name'],
                                            'product_type' => $item['product_type'], 
                                            'price' => $item['price'],
                                            'qty' => $item['qty_ordered'],
                                            'tva_tx' => $item['tax_percent']
                                    );
                                } else {
                                    $items[] = array(
                                            'item_id' => $item['item_id'],
                                            'id_remote_product' => $item['product_id'],
                                            'description' => $item['name'],
                                            'product_type' => $item['product_type'], 
                                            'price' => $configurableItems['price'],
                                            'qty' => $item['qty_ordered'],
                                            'tva_tx' => $configurableItems['tax_percent']
                                    );
                                }
                            }
                        }

                    //set order's address
                    $commandeSocpeople = $commande['billing_address'];
                    $socpeopleCommande = array(
                            'remote_id' => $commandeSocpeople['address_id'],
                            'type' => eCommerceSocpeople::CONTACT_TYPE_ORDER,
                            'last_update' => $commandeSocpeople['updated_at'],
                            'name' => $commandeSocpeople['lastname'],
                            'lastname' => $commandeSocpeople['lastname'],
                            'firstname' => $commandeSocpeople['firstname'],
                            'town' => $commandeSocpeople['city'],
                            //'fk_pays' => $commandeSocpeople['country_id'],
                            'fax' => $commandeSocpeople['fax'],
                            'zip' => $commandeSocpeople['postcode'],
                            //add wrap
                            'address' => addslashes((trim($commandeSocpeople['company'])) != '' ? addslashes(trim($commandeSocpeople['company'])) . ', ' : '') . addslashes($commandeSocpeople['street']),
                            'phone' => $commandeSocpeople['telephone']
                    );
                    //set billing's address
                    $socpeopleFacture = $socpeopleCommande;
                    $socpeopleFacture['type'] = eCommerceSocpeople::CONTACT_TYPE_INVOICE;

                    //set shipping's address
                    $livraisonSocpeople = $commande['shipping_address'];
                    $socpeopleLivraison = array(
                            'remote_id' => $livraisonSocpeople['address_id'],
                            'type' => eCommerceSocpeople::CONTACT_TYPE_DELIVERY,
                            'last_update' => $livraisonSocpeople['updated_at'],
                            'name' => $livraisonSocpeople['lastname'],
                            'lastname' => $livraisonSocpeople['lastname'],
                            'firstname' => $livraisonSocpeople['firstname'],
                            'town' => $livraisonSocpeople['city'],
                            //'fk_pays' => $commandeSocpeople['country_id'],
                            'fax' => $livraisonSocpeople['fax'],
                            'zip' => $livraisonSocpeople['postcode'],
                            //add wrap
                            'address' => (trim($livraisonSocpeople['company']) != '' ? trim($livraisonSocpeople['company']) . ', ' : '') . $livraisonSocpeople['street'],
                            'phone' => $livraisonSocpeople['telephone']
                    );

                    //set delivery as service
                    $delivery = array(
                            'description' => $commande['shipping_description'],
                            'price' => $commande['shipping_amount'],
                            'qty' => 1, //0 to not show
                            'tva_tx' => $this->getTaxRate($commande['shipping_amount'], $commande['shipping_tax_amount'])
                    );

                    //define remote id societe : 0 for anonymous
                    $eCommerceTempSoc = new eCommerceSociete($this->db);
                    if ($commande['customer_id'] == null || $eCommerceTempSoc->fetchByRemoteId($commande['customer_id'], $this->site->id) < 0)
                        $remoteIdSociete = 0;
                    else
                    {
                        $remoteIdSociete = $commande['customer_id'];
                    }

                    //define delivery date
                    if (isset($commande['delivery_date']) && $commande['delivery_date'] != null)
                        $deliveryDate = $commande['delivery_date'];
                    else
                        $deliveryDate = $commande['created_at'];

                    //add order to orders
                    $commandes[] = array(
                            'last_update' => $commande['updated_at'],
                            'remote_id' => $commande['order_id'],
                            'remote_increment_id' => $commande['increment_id'],
                            'remote_id_societe' => $remoteIdSociete,
                            'ref_client' => $commande['increment_id'],
                            'date_commande' => $commande['created_at'],
                            'date_livraison' => $deliveryDate,
                            'items' => $items,
                            'delivery' => $delivery,
                            'socpeopleCommande' => $socpeopleCommande,
                            'socpeopleFacture' => $socpeopleFacture,
                            'socpeopleLivraison' => $socpeopleLivraison,
                            //debug
                            //'commande' => $commande
                    );
                }
            }
        }
        //important - order by last update
        if (count($commandes))
        {
            foreach ($commandes as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $commandes);
        }
        return $commandes;
    }

    /**
     * Put the remote data into facture dolibarr data from instantiated class in the constructor
     * @param $remoteObject array
     * @return array facture
     */
    public function convertRemoteObjectIntoDolibarrFacture($remoteObject)
    {
        $factures = array();
        $calls = array();
        if (count($remoteObject))
        {
            foreach ($remoteObject as $rfacture)
            {
                $calls[] = array('sales_order_invoice.info', $rfacture['increment_id']);
            }
            try {
                $results = $this->client->multiCall($this->session, $calls);
            } catch (SoapFault $fault) {
                //echo 'convertRemoteObjectIntoDolibarrFacture :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString();
            }
            if (count($results))
            {
                foreach ($results as $facture)
                {
                    $configurableItems = array();
                    //retrive remote order from invoice
                    $commande = $this->getCommande($facture['order_id']);
                    //set each invoice items
                    $items = array();
                    if (count($facture['items']))
                        foreach ($facture['items'] as $item)
                        {
                            $items[] = array(
                                    'item_id' => $item['item_id'],
                                    'id_remote_product' => $item['product_id'],
                                    'description' => $item['name'],
                                    'product_type' => $item['product_type'], 
                                    'price' => $item['price'],
                                    'qty' => $item['qty'],
                                    'tva_tx' => $this->getTaxRate($item['row_total'], $item['tax_amount'])
                            );
                        }
                        
                    //set shipping address
                    $shippingAddress = $commande["shipping_address"];
                    $billingAddress = $commande["billing_address"];
                    $socpeopleLivraison = array(
                            'remote_id' => $shippingAddress['address_id'],
                            'type' => eCommerceSocpeople::CONTACT_TYPE_DELIVERY,
                            'last_update' => $shippingAddress['updated_at'],
                            'name' => $shippingAddress['lastname'],
                            'firstname' => $shippingAddress['firstname'],
                            'ville' => $shippingAddress['city'],
                            //'fk_pays' => $commandeSocpeople['country_id'],
                            'fax' => $shippingAddress['fax'],
                            'cp' => $shippingAddress['postcode'],
                            //add wrap
                            'address' => (trim($shippingAddress['company']) != '' ? trim($shippingAddress['company']) . '
                                                                            ' : '') . $shippingAddress['street'],
                            'phone' => $shippingAddress['telephone']
                    );
                    //set invoice address		
                    $socpeopleFacture = array(
                            'remote_id' => $billingAddress['address_id'],
                            'type' => eCommerceSocpeople::CONTACT_TYPE_INVOICE,
                            'last_update' => $billingAddress['updated_at'],
                            'name' => $billingAddress['lastname'],
                            'firstname' => $billingAddress['firstname'],
                            'ville' => $billingAddress['city'],
                            //'fk_pays' => $commandeSocpeople['country_id'],
                            'fax' => $billingAddress['fax'],
                            'cp' => $billingAddress['postcode'],
                            //add wrap
                            'address' => (trim($billingAddress['company']) != '' ? trim($billingAddress['company']) . '
                                                                            ' : '') . $billingAddress['street'],
                            'phone' => $billingAddress['telephone']
                    );
                    //set delivery as service			
                    $delivery = array(
                            'description' => $commande['shipping_description'],
                            'price' => $facture['shipping_amount'],
                            'qty' => 1, //0 to not show
                            'tva_tx' => $this->getTaxRate($facture['shipping_amount'], $facture['shipping_tax_amount'])
                    );

                    //define remote id societe : 0 for anonymous
                    $eCommerceTempSoc = new eCommerceSociete($this->db);
                    if ($commande['customer_id'] == null || $eCommerceTempSoc->fetchByRemoteId($commande['customer_id'], $this->site->id) < 0)
                        $remoteIdSociete = 0;
                    else
                    {
                        $remoteIdSociete = $commande['customer_id'];
                    }

                    //add invoice to invoices
                    $factures[] = array(
                            'last_update' => $facture['updated_at'],
                            'remote_id' => $facture['invoice_id'],
                            'remote_order_id' => $facture['order_id'],
                            'remote_id_societe' => $remoteIdSociete,
                            'socpeopleLivraison' => $socpeopleLivraison,
                            'socpeopleFacture' => $socpeopleFacture,
                            'ref_client' => $facture['increment_id'],
                            'date' => $facture['created_at'],
                            'code_cond_reglement' => 'CASH',
                            'delivery' => $delivery,
                            'items' => $items,
                            //debug
                            //'commande' => $commande,
                            //'facture' => $facture
                    );
                }
            }
        }
        //important - order by last update
        if (count($factures))
        {
            foreach ($factures as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $factures);
        }
        return $factures;
    }

    public function getCommande($remoteCommandeId)
    {
        $commande = array();
        try {
            dol_syslog("getCommande begin");
            $result = $this->client->call($this->session, 'sales_order.list', array(array('order_id' => $remoteCommandeId)));
            if (count($result == 1))
                $commande = $this->client->call($this->session, 'sales_order.info', $result[0]['increment_id']);
            dol_syslog("getCommande end");
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
        return $commande;
    }

    /**
     * Return the magento's category tree
     * 
     * @return  array|boolean       Array with categories or false if error
     */
    public function getRemoteCategoryTree()
    {
        dol_syslog("eCommerceRemoteAccessMagento getRemoteCategoryTree session=".$this->session);
        try {
            //$result = $this->client->call($this->session, 'auguria_dolibarrapi_catalog_category.tree');
            $result = $this->client->call($this->session, 'catalog_category.tree');
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
        dol_syslog("eCommerceRemoteAccessMagento getRemoteCategoryTree end");
        return $result;
    }

    
    /**
     * Return the magento's address id
     * 
     * @param   int             $remote_thirdparty_id       Id of thirdparty
     * @return  array|boolean                               Array with address id
     */
    public function getRemoteAddressIdForSociete($remote_thirdparty_id)
    {
        dol_syslog("eCommerceRemoteAccessMagento getRemoteAddressIdForSociete session=".$this->session);
        try {
            //$result = $this->client->call($this->session, 'auguria_dolibarrapi_catalog_category.tree');
            $result = $this->client->call($this->session, 'customer_address.list', array('customerId'=>$remote_thirdparty_id));
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
        dol_syslog("eCommerceRemoteAccessMagento getRemoteAddressIdForSociete end");
        return $result;
    }

    
    /**
     * Return content of one category
     * 
     * @param unknown $category_id
     * @return boolean|unknown
     */
    public function getCategoryData($category_id)
    {
        dol_syslog("eCommerceRemoteAccessMagento getCategoryData session=".$this->session);
        try {
            //$result = $this->client->call($this->session, 'auguria_dolibarrapi_catalog_category.tree');
            $result = $this->client->call($this->session, 'catalog_category.info', array('categoryId'=>$category_id));
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
        dol_syslog("eCommerceRemoteAccessMagento getCategoryData end");
        return $result;
    }
    
    
    public function createRemoteLivraison($livraison, $remote_order_id)
    {
        dol_syslog("eCommerceRemoteAccessMagento createRemoteLivraison session=".$this->session);
        $commande = $this->getCommande($remote_order_id);
        try {        
            $result = $this->client->call($this->session, 'sales_order_shipment.create', array($commande['increment_id'], array(), 'Shipment Created', true, true));
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
        dol_syslog("eCommerceRemoteAccessMagento createRemoteLivraison end");
        return $result;
    }

    /**
     * Calcul tax rate and return the closest dolibarr tax rate.
     * 
     * @param float $priceHT
     * @param float $priceTTC
     */
    private function getTaxRate($priceHT, $taxAmount)
    {
        $taxRate = 0;
        if ($taxAmount != 0)
        {
            //calcul tax rate from remote site
            $tempTaxRate = ($taxAmount / $priceHT) * 100;
            //get all dolibarr tax rates
            if (!isset($this->taxRates))
                $this->setTaxRates();
            if (count($this->taxRates))
            {
                $min = 1;
                $rate;
                foreach ($this->taxRates as $dolibarrTaxRate)
                {
                    $diff = $tempTaxRate - $dolibarrTaxRate['taux'];
                    if ($diff < 0)
                        $diff = (-1 * $diff);
                    if ($diff < $min)
                    {
                        $min = $diff;
                        $rate = $dolibarrTaxRate['taux'];
                    }
                }
                if ($rate > 0)
                    $taxRate = $rate;
            }
        }
        return $taxRate;
    }

    /**
     * Retrieve all Dolibarr tax rates
     */
    private function setTaxRates()
    {
        $taxTable = new eCommerceDict($this->db, MAIN_DB_PREFIX . "c_tva");
        $this->taxRates = $taxTable->getAll();
    }

    public function __destruct()
    {
        if (is_object($this->client)) $this->client->endSession($this->session);
        ini_set("memory_limit", "528M");
    }

}

