<?php
/* Copyright (C) 2017 Open-DSI                     <support@open-dsi.fr>
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

dol_include_once('/ecommerceng/admin/class/data/eCommerceDict.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceCategory.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceSociete.class.php');

dol_include_once('/ecommerceng/includes/WooCommerce/Client.php');
dol_include_once('/ecommerceng/includes/WooCommerce/HttpClient/BasicAuth.php');
dol_include_once('/ecommerceng/includes/WooCommerce/HttpClient/HttpClient.php');
dol_include_once('/ecommerceng/includes/WooCommerce/HttpClient/HttpClientException.php');
dol_include_once('/ecommerceng/includes/WooCommerce/HttpClient/OAuth.php');
dol_include_once('/ecommerceng/includes/WooCommerce/HttpClient/Options.php');
dol_include_once('/ecommerceng/includes/WooCommerce/HttpClient/Request.php');
dol_include_once('/ecommerceng/includes/WooCommerce/HttpClient/Response.php');

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

/**
 * Class for access remote sites
 */
class eCommerceRemoteAccessWoocommerce
{

    private $site;
    private $client;
    private $clientOld;
    private $filter;
    private $taxRates;
    private $db;

    /**
     *      Constructor
     *
     *      @param      DoliDB      $db         Database handler
     *      @param      string      $site       eCommerceSite
     */
    function __construct($db, $site)
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
        global $conf;

        try {
            require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');
            $params=getSoapParams();

            @ini_set('default_socket_timeout', $params['response_timeout']);
            @ini_set("memory_limit", "1024M");

            $response_timeout = (empty($conf->global->MAIN_USE_RESPONSE_TIMEOUT)?$params['response_timeout']:$conf->global->MAIN_USE_RESPONSE_TIMEOUT);    // Response timeout

            dol_syslog("eCommerceRemoteAccessWoocommerce Connect to API webservice_address=".$this->site->webservice_address." user_name=".$this->site->user_name." user_password=".$this->site->user_password);
            $this->client = new Client(
                $this->site->webservice_address,
                $this->site->user_name,
                $this->site->user_password,
                [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                    'timeout' => $response_timeout,
                ]
            );
            $this->clientOld = new Client(
                $this->site->webservice_address,
                $this->site->user_name,
                $this->site->user_password,
                [
                    'version' => 'v3',
                    'timeout' => $response_timeout,
                ]
            );

            dol_syslog("eCommerceRemoteAccessWoocommerce connected with new Client ok.");

            return true;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Call Woocommerce API to get last updated companies
     *
     * @param   datetime $fromDate      From date
     * @param   datetime $toDate        To date
     * @return  boolean|mixed           Response from REST Api call, normally an associative array mirroring the structure of the XML response, nothing if error
     */
    public function getSocieteToUpdate($fromDate, $toDate)
    {
        global $conf;

        try {
            $result = array();
            $idxPage = 1;
            $per_page = empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL) ? 100 : $conf->global->ECOMMERCENG_MAXSIZE_MULTICALL;
            $from_date = isset($fromDate) && !empty($fromDate) ? new DateTime(dol_print_date($fromDate, 'standard')) : null;
            $to_date = isset($toDate) && !empty($toDate) ? new DateTime(dol_print_date($toDate, 'standard')) : null;

            $filter = [ 'limit' => $per_page ];
            // Not work with customers
            //if (isset($fromDate) && !empty($fromDate)) $filter['updated_at_min'] = dol_print_date($fromDate - (24 * 60 * 60), 'dayrfc');
            //if (isset($toDate) && !empty($toDate)) $filter['updated_at_max'] = dol_print_date($toDate + (24 * 60 * 60), 'dayrfc');

            dol_syslog("getSocieteToUpdate start gt = " . dol_print_date($fromDate, 'standard') . ", lt = " . dol_print_date($toDate, 'standard'));
            while (true) {
                $page = $this->clientOld->get('customers',
                    [
                        'page' => $idxPage++,
                        'filter' => $filter,
                        'fields' => 'id,created_at,last_update'
                    ]
                );
                if (!isset($page['customers']) || ($nbCustomers = count($page['customers'])) == 0) break;
                $page = $page['customers'];

                for ($idxCustomer = 0; $idxCustomer < $nbCustomers; $idxCustomer++) {
                    $created_at = new DateTime($page[$idxCustomer]['created_at']);
                    $date = new DateTime($page[$idxCustomer]['last_update']);
                    $date = $date < $created_at ? $created_at : $date;

                    if ((!isset($from_date) || $from_date < $date) && (!isset($to_date) || $date <= $to_date)) {
                        $result[] = $page[$idxCustomer]['id'];
                    }
                }
            }

            dol_syslog("getSocieteToUpdate end (found ".count($result)." record)");
            return $result;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Call Magenta API to get last updated products. We are interested here by list of id only. We will retreive properties later.
     *
     * @param   datetime $fromDate      From date
     * @param   datetime $toDate        To date
     * @return  boolean|mixed           Response from SOAP call, normally an associative array mirroring the structure of the XML response, nothing if error
     */
    public function getProductToUpdate($fromDate, $toDate)
    {
        global $conf;

        try {
            dol_syslog("getProductToUpdate start gt=".dol_print_date($fromDate, 'standard')." lt=".dol_print_date($toDate, 'standard'));
            $result = array();
            $idxPage = 1;
            $per_page = empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL) ? 100 : $conf->global->ECOMMERCENG_MAXSIZE_MULTICALL;
            $from_date = isset($fromDate) && !empty($fromDate) ? new DateTime(dol_print_date($fromDate, 'standard')) : null;
            $to_date = isset($toDate) && !empty($toDate) ? new DateTime(dol_print_date($toDate, 'standard')) : null;

            $filter = [ 'limit' => $per_page ];
            if (isset($fromDate) && !empty($fromDate)) $filter['updated_at_min'] = dol_print_date($fromDate - (24 * 60 * 60), 'dayrfc');
            if (isset($toDate) && !empty($toDate)) $filter['updated_at_max'] = dol_print_date($toDate + (24 * 60 * 60), 'dayrfc');

            dol_syslog("getProductToUpdate start gt=".dol_print_date($fromDate != null ? $fromDate : 0, 'standard')." lt=".dol_print_date($toDate, 'standard'));
            while (true) {
                $page = $this->clientOld->get('products',
                    [
                        'page' => $idxPage++,
                        'filter' => $filter,
                        'fields' => 'id,created_at,updated_at'
                    ]
                );
                if (!isset($page['products']) || ($nbProducts = count($page['products'])) == 0) break;
                $page = $page['products'];

                for ($idxProduct = 0; $idxProduct < $nbProducts; $idxProduct++) {
                    $created_at = new DateTime($page[$idxProduct]['created_at']);
                    $date = new DateTime($page[$idxProduct]['updated_at']);
                    $date = $date < $created_at ? $created_at : $date;

                    if ((!isset($from_date) || $from_date < $date) && (!isset($to_date) || $date <= $to_date)) {
                        $product = $page[$idxProduct];
                        //if ($product['virtual'] || $product['downloadable']) continue;
                        $result[] = $product['id'];
                    }
                }
            }

            dol_syslog("getProductToUpdate end (found ".count($results)." record)");
            return $result;
        } catch (HttpClientException $fault) {
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
     * @return  boolean|mixed           Response from SOAP call, normally an associative array mirroring the structure of the XML response, nothing if error
     */
    public function getCommandeToUpdate($fromDate, $toDate)
    {
        global $conf;

        try {
            $result = array();
            $idxPage = 1;
            $per_page = empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL) ? 100 : $conf->global->ECOMMERCENG_MAXSIZE_MULTICALL;
            $from_date = isset($fromDate) && !empty($fromDate) ? new DateTime(dol_print_date($fromDate, 'standard')) : null;
            $to_date = isset($toDate) && !empty($toDate) ? new DateTime(dol_print_date($toDate, 'standard')) : null;

            $filter = [ 'limit' => $per_page ];
            if (isset($fromDate) && !empty($fromDate)) $filter['updated_at_min'] = dol_print_date($fromDate - (24 * 60 * 60), 'dayrfc');
            if (isset($toDate) && !empty($toDate)) $filter['updated_at_max'] = dol_print_date($toDate + (24 * 60 * 60), 'dayrfc');

            dol_syslog("getCommandeToUpdate start gt=".dol_print_date($fromDate, 'standard')." lt=".dol_print_date($toDate, 'standard'));
            while (true) {
                $page = $this->clientOld->get('orders',
                    [
                        'page' => $idxPage++,
                        'filter' => $filter,
                        'fields' => 'id,created_at,updated_at'
                    ]
                );
                if (!isset($page['orders']) || ($nbOrders = count($page['orders'])) == 0) break;
                $page = $page['orders'];

                for ($idxOrder = 0; $idxOrder < $nbOrders; $idxOrder++) {
                    $created_at = new DateTime($page[$idxOrder]['created_at']);
                    $date = new DateTime($page[$idxOrder]['updated_at']);
                    $date = $date < $created_at ? $created_at : $date;

                    if ((!isset($from_date) || $from_date < $date) && (!isset($to_date) || $date <= $to_date)) {
                        $result[] = $page[$idxOrder]['id'];
                    }
                }
            }

            dol_syslog("getCommandeToUpdate end (found ".count($result)." record)");
            return $result;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
        catch (Exception $e) {
            $this->errors[]=$e->getMessage().'-'.$e->getCode();
            dol_syslog(__METHOD__.': '.$e->getMessage().'-'.$e->getCode().'-'.$e->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Call Magenta API to get last updated invoices
     *
     * @param   datetime $fromDate      From date
     * @param   datetime $toDate        To date
     * @return  boolean|mixed           Response from SOAP call, normally an associative array mirroring the structure of the XML response, nothing if error
     */
    public function getFactureToUpdate($fromDate, $toDate)
    {
    /*    global $conf;

        try {
            $result = array();
            $idxPage = 1;
            $per_page = empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL) ? 100 : $conf->global->ECOMMERCENG_MAXSIZE_MULTICALL;
            $from_date = isset($fromDate) && !empty($fromDate) ? new DateTime(dol_print_date($fromDate, 'standard')) : null;
            $to_date = isset($toDate) && !empty($toDate) ? new DateTime(dol_print_date($toDate, 'standard')) : null;

            $filter = [ 'limit' => $per_page ];
            if (isset($fromDate) && !empty($fromDate)) $filter['updated_at_min'] = dol_print_date($fromDate - (24 * 60 * 60), 'dayrfc');
            if (isset($toDate) && !empty($toDate)) $filter['updated_at_max'] = dol_print_date($toDate + (24 * 60 * 60), 'dayrfc');

            dol_syslog("getFactureToUpdate start gt=".dol_print_date($fromDate, 'standard')." lt=".dol_print_date($toDate, 'standard'));
            while (true) {
                $page = $this->clientOld->get('orders',
                    [
                        'page' => $idxPage++,
                        'filter' => $filter,
                        'fields' => 'id,created_at,updated_at'
                    ]
                );
                if (!isset($page['orders']) || ($nbOrders = count($page['orders'])) == 0) break;
                $page = $page['orders'];

                for ($idxOrder = 0; $idxOrder < $nbOrders; $idxOrder++) {
                    $created_at = new DateTime($page[$idxOrder]['created_at']);
                    $date = new DateTime($page[$idxOrder]['updated_at']);
                    $date = $date < $created_at ? $created_at : $date;

                    if ((!isset($from_date) || $from_date < $date) && (!isset($to_date) || $date <= $to_date)) {
//                    $status = $page[$idxOrder]['status'];
//                    if ($status == 'completed' || $status == 'refunded') {
                        $result[] = $page[$idxOrder]['id'];
//                    }
                    }
                }
            }

            dol_syslog("getFactureToUpdate end (found ".count($result)." record)");
            return $result;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }*/
    }


    /**
     * Put the remote data into category dolibarr data from instantiated class in the constructor
     * Return array of category by update time.
     *
     * @param   array   $remoteObject         Array of ids of objects to convert
     * @param   int     $toNb                 Max nb
     * @return  array                         societe
     */
    public function convertRemoteObjectIntoDolibarrCategory($remoteObject, $toNb=0)
    {
        global $conf;

        $categories = array();

        // No need to make $this->client->multiCall($this->session, $calls); to get details.

        // We just need to sort array on updated_at
        $categories = $remoteObject;

        //important - order by last update
        if (count($categories))
        {
            $last_update=array();
            foreach ($categories as $key => $row)
            {
                $last_update[$key] = $row['updated_at'];
            }
            array_multisort($last_update, SORT_ASC, $categories);
        }

        return $categories;
    }

    /**
     * Put the remote data into societe dolibarr data from instantiated class in the constructor
     * Return array of thirdparty by update time.
     *
     * @param   array   $remoteObject         Array of ids of objects to convert
     * @param   int     $toNb                 Max nb
     * @return  array                         societe
     */
    public function convertRemoteObjectIntoDolibarrSociete($remoteObject, $toNb=0)
    {
        global $conf;

        $societes = array();

        $maxsizeofmulticall = (empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL)?100:$conf->global->ECOMMERCENG_MAXSIZE_MULTICALL);
        $nbsynchro = 0;
        $nbremote = count($remoteObject);
        if ($nbremote)
        {
            // Create n groups of $maxsizeofmulticall records max to call the multiCall
            $callsgroup = array();
            $calls = array();
            foreach ($remoteObject as $rsociete)
            {
                if (($nbsynchro % $maxsizeofmulticall) == 0)
                {
                    if (count($calls)) $callsgroup[] = $calls;    // Add new group for lot of 1000 call arrays
                    $calls = array();
                }

                $calls[] = $rsociete;

                $nbsynchro++;   // nbsynchro is now number of calls to do
            }
            if (count($calls)) $callsgroup[] = $calls;    // Add new group for the remain lot of calls not yet added

            dol_syslog("convertRemoteObjectIntoDolibarrSociete Call WS to get detail for the " . count($remoteObject) . " objects (" . count($callsgroup) . " calls with " . $maxsizeofmulticall . " max of records each) then create a Dolibarr array for each object");
            //var_dump($callsgroup);exit;

            $results=array();
            $nbcall=0;
            foreach ($callsgroup as $calls)
            {
                try {
                    $nbcall++;
                    dol_syslog("convertRemoteObjectIntoDolibarrSociete Call WS nb ".$nbcall." (".count($calls)." record)");
                    $resulttmp = $this->client->get('customers',
                        [
                            'per_page' => $maxsizeofmulticall,
                            'include' => implode(',', $calls),
                        ]
                    );
                    $results=array_merge($results, $resulttmp);
                } catch (HttpClientException $fault) {
                    dol_syslog('convertRemoteObjectIntoDolibarrSociete :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
                    return false;
                }
            }

            if (count($results))
            {
                foreach ($results as $societe)
                {
                    $newobj = array(
                        'remote_id' => $societe['id'],
                        'last_update' => isset($societe['date_modified']) ? $societe['date_modified'] : $societe['date_created'],
                        'name' => dolGetFirstLastname($societe['first_name'], $societe['last_name']),
                        'name_alias' => $this->site->name . ' id ' . $societe['id'],                // See also the delete in eCommerceSociete
                        'email' => $societe['email'],
                        'client' => 3, //for client/prospect
                        'vatnumber' => $societe['taxvat']
                    );
                    $societes[] = $newobj;
                }
            }
        }

        //important - order by last update
        if (count($societes))
        {
            $last_update = array();
            foreach ($societes as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $societes);
        }

        dol_syslog("convertRemoteObjectIntoDolibarrSociete end (found ".count($societes)." record)");
        return $societes;
    }


    /**
     * Put the remote data into societe dolibarr data from instantiated class in the constructor
     * Return array of people by update time.
     *
     * @param   array   $listofids          List of object with customer_address_id that is remote id of addresss
     * @param   int     $toNb               Max nb. Not used for socpeople.
     * @return  array                       societe
     */
    public function convertRemoteObjectIntoDolibarrSocpeople($listofids, $toNb=0)
    {
        global $conf;

        $socpeoples = array();
        $calls = array();
        if (count($listofids))
        {
            dol_syslog("convertRemoteObjectIntoDolibarrSocpeople Call WS to get detail for the ".count($listofids)." objects then create a Dolibarr array for each object");
            foreach ($listofids as $listofid)
            {
                $calls[] = $listofid;
            }
            try {
                $results =  $this->client->get('customers',
                    [
                        'per_page' => 100,
                        'include' => implode(',', $calls),
                    ]
                );
            } catch (HttpClientException $fault) {
                dol_syslog('convertRemoteObjectIntoDolibarrSocpeople :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            }

            if (count($results)) {
                $billingName = (empty($conf->global->ECOMMERCENG_BILLING_CONTACT_NAME) ? 'Billing' : $conf->global->ECOMMERCENG_BILLING_CONTACT_NAME);      // Contact name treated as billing address.
                $shippingName = (empty($conf->global->ECOMMERCENG_SHIPPING_CONTACT_NAME) ? 'Shipping' : $conf->global->ECOMMERCENG_SHIPPING_CONTACT_NAME);  // Contact name treated as shipping address.

                foreach ($results as $socpeople) {
                    $billing = $socpeople['billing'];
                    $newobj = array(
                        'remote_id' => $socpeople['id'] . '|1',
                        //'type'                  => eCommerceSocpeople::CONTACT_TYPE_COMPANY,
                        'last_update' => isset($socpeople['date_modified']) ? $socpeople['date_modified'] : $socpeople['date_created'],
                        'name' => $billingName,
                        'email' => $billing['email'],
                        'address' => $billing['address_1'] . (!empty($billing['address_1']) && !empty($billing['address_2']) ? "\n" : "") . $billing['address_2'],
                        'town' => $billing['city'],
                        'zip' => $billing['postcode'],
                        'country_code' => getCountry($billing['country'], 3),
                        'phone' => $billing['phone'],
                        'fax' => "",
                        'firstname' => "", // $billing['first_name'],
                        'lastname' => $billingName, // $billing['last_name'],
                        'vatnumber' => "",
                        'is_default_billing' => true,
                        'is_default_shipping' => false
                    );
                    $socpeoples[] = $newobj;

                    $shipping = $socpeople['shipping'];
                    if ((!empty($shipping['address_1']) || !empty($shipping['address_1'])) && !empty($shipping['city']) && !empty($shipping['postcode']) && !empty($shipping['country'])) {
                        $newobj = array(
                            'remote_id' => $socpeople['id'] . '|2',
                            //'type'                  => eCommerceSocpeople::CONTACT_TYPE_COMPANY,
                            'last_update' => isset($socpeople['date_modified']) ? $socpeople['date_modified'] : $socpeople['date_created'],
                            'name' => $shippingName,
                            'email' => "",
                            'address' => $shipping['address_1'] . (!empty($shipping['address_1']) && !empty($shipping['address_2']) ? "\n" : "") . $shipping['address_2'],
                            'town' => $shipping['city'],
                            'zip' => $shipping['postcode'],
                            'country_code' => getCountry($shipping['country'], 3),
                            'phone' => "",
                            'fax' => "",
                            'firstname' => "", // $shipping['first_name'],
                            'lastname' => $shippingName, // $shipping['last_name'],
                            'vatnumber' => "",
                            'is_default_billing' => false,
                            'is_default_shipping' => true
                        );
                    } else {
                        $newobj = array(
                            'remote_id' => $socpeople['id'] . '|2',
                            //'type'                  => eCommerceSocpeople::CONTACT_TYPE_COMPANY,
                            'last_update' => isset($socpeople['date_modified']) ? $socpeople['date_modified'] : $socpeople['date_created'],
                            'name' => $shippingName,
                            'email' => $billing['email'],
                            'address' => $billing['address_1'] . (!empty($billing['address_1']) && !empty($billing['address_2']) ? "\n" : "") . $billing['address_2'],
                            'town' => $billing['city'],
                            'zip' => $billing['postcode'],
                            'country_code' => getCountry($billing['country'], 3),
                            'phone' => "",
                            'fax' => "",
                            'firstname' => "", // $billing['first_name'],
                            'lastname' => $shippingName, // $billing['last_name'],
                            'vatnumber' => "",
                            'is_default_billing' => false,
                            'is_default_shipping' => true
                        );
                    }
                    $socpeoples[] = $newobj;
                }
            }
        }

        //important - order by last update
        if (count($socpeoples))
        {
            $last_update = array();
            foreach ($socpeoples as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $socpeoples);
        }

        dol_syslog("convertRemoteObjectIntoDolibarrSocPeople end (found ".count($socpeoples)." record)");
        return $socpeoples;
    }


    /**
     * Put the remote data into product dolibarr data from instantiated class in the constructor
     * Return array or products by update time.
     *
     * @param   array   $remoteObject       Array of remote products (got by caller from getProductToUpdate. Only few properties defined)
     * @param   int     $toNb               Max nb
     * @return  array                       product
     */
    public function convertRemoteObjectIntoDolibarrProduct($remoteObject, $toNb=0)
    {
        global $conf;

        include_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

        $products = array();

        $canvas = '';

        $ecommerceurl =  $this->site->getFrontUrl();

        $maxsizeofmulticall = (empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL)?100:$conf->global->ECOMMERCENG_MAXSIZE_MULTICALL);      // 1000 seems ok for multicall.
        $nbsynchro = 0;
        $nbremote = count($remoteObject);
        if ($nbremote)
        {
            // Create n groups of $maxsizeofmulticall records max to call the multiCall
            $callsgroup = array();
            $calls=array();
            foreach ($remoteObject as $rproduct)
            {
                if (($nbsynchro % $maxsizeofmulticall) == 0)
                {
                    if (count($calls)) $callsgroup[]=$calls;    // Add new group for lot of 1000 call arrays
                    $calls=array();
                }

                $calls[] = $rproduct;

                $nbsynchro++;   // nbsynchro is now number of calls to do
            }
            if (count($calls)) $callsgroup[]=$calls;    // Add new group for the remain lot of calls not yet added

            dol_syslog("convertRemoteObjectIntoDolibarrProduct Call WS to get detail for the ".count($remoteObject)." objects (".count($callsgroup)." calls with ".$maxsizeofmulticall." max of records each) then create a Dolibarr array for each object");
            //var_dump($callsgroup);exit;

            $results=array();
            $nbcall=0;
            foreach ($callsgroup as $calls)
            {
                try {
                    $nbcall++;
                    dol_syslog("convertRemoteObjectIntoDolibarrProduct Call WS nb ".$nbcall." (".count($calls)." record)");
                    $resulttmp =  $this->client->get('products',
                        [
                            'per_page' => $maxsizeofmulticall,
                            'include' => implode(',', $calls),
                        ]
                    );
                    $results=array_merge($results, $resulttmp);
                } catch (HttpClientException $fault) {
                    dol_syslog('convertRemoteObjectIntoDolibarrProduct :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);

					return false;
                }
            }

            if (count($results))
            {
                foreach ($results as $cursorproduct => $product)
                {
                    // Variations
                    if (count($product['variations'])) {
                            try {
                                $variations = $this->client->get('products/' . $product['id'] . '/variations');
                            } catch (HttpClientException $fault) {
                                $this->errors[] = $fault->getMessage() . '-' . $fault->getCode();
                                dol_syslog($fault->getRequest(), LOG_WARNING);
                                dol_syslog($fault->getResponse(), LOG_WARNING);
                                dol_syslog(__METHOD__ . ': ' . $fault->getMessage() . '-' . $fault->getCode() . '-' . $fault->getTraceAsString(), LOG_WARNING);
                                return false;
                            }

                            foreach ($variations as $variation) {
                                $attributesLabel = '';
                                foreach ($variation['attributes'] as $attribute) {
                                    $attributesLabel .= ', '.$attribute['name'].':'.$attribute['option'];
                                }

                                $products[] = array(
                                    'remote_id'         => $product['id'].'|'.$variation['id'],  // id product | id variation
                                    'last_update'       => isset($variation['date_modified'])?$variation['date_modified']:$variation['date_created'],
                                    'fk_product_type'   => ($variation['virtual'] ? 1 : 0), // 0 (product) or 1 (service)
                                    'ref'               => dol_string_nospecial($variation['sku']),
                                    'label'             => ($product['name']?$product['name']:dol_string_nospecial($variation['sku'])).$attributesLabel,
                                    'description'       => $variation['description'],
                                    'weight'            => $variation['weight'],
                                    'price'             => $variation['price'],  //sale_price
                                    'envente'           => $variation['purchasable'] ? 1 : 0,
                                    'finished'          => 1,    // 1 = manufactured, 0 = raw material
                                    'canvas'            => $canvas,
                                    'categories'        => '', //$product['categories'],  // a check   // Same as property $product['category_ids']
                                    'tax_rate'          => '', // $variation['tax_rate'], // a check
                                    'price_min'         => $variation['price'],
                                    'fk_country'        => '',
                                    'url'               => $variation['permalink'],
                                    // Stock
                                    'stock_qty'         => $variation['stock_quantity'],
                                    'is_in_stock'       => $variation['in_stock'],   // not used
                                );
                            }
                    } else {
                            $products[] = array(
                                'remote_id'         => $product['id'],  // id product
                                'last_update'       => isset($product['date_modified'])?$product['date_modified']:$product['date_created'],
                                'fk_product_type'   => ($product['virtual'] ? 1 : 0), // 0 (product) or 1 (service)
                                'ref'               => dol_string_nospecial($product['sku']),
                                'label'             => ($product['name']?$product['name']:dol_string_nospecial($product['sku'])),
                                'description'       => $product['description'],
                                'weight'            => $product['weight'],
                                'price'             => $product['price'],  //sale_price
                                'envente'           => $product['purchasable'] ? 1 : 0,
                                'finished'          => 1,    // 1 = manufactured, 0 = raw material
                                'canvas'            => $canvas,
                                'categories'        => '', // $product['categories'],  // a check   // Same as property $product['category_ids']
                                'tax_rate'          => '', // $product['tax_rate'], // a check
                                'price_min'         => $product['price'],
                                'fk_country'        => '',
                                'url'               => $product['permalink'],
                                // Stock
                                'stock_qty'         => $product['stock_quantity'],
                                'is_in_stock'       => $product['in_stock'],   // not used
                            );
                    }
                }
            }
        }

        //important - order by last update
        if (count($products))
        {
            $last_update = array();
            foreach ($products as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $products);
        }

        dol_syslog("convertRemoteObjectIntoDolibarrProduct end (found ".count($products)." record)");
        return $products;
    }

    /**
     * Put the remote data into commande dolibarr data from instantiated class in the constructor
     * Return array of orders by update time.
     *
     * @param   array   $remoteObject       array of remote orders
     * @param   int     $toNb               Max nb
     * @return  array                       commande
     */
    public function convertRemoteObjectIntoDolibarrCommande($remoteObject, $toNb=0)
    {
        global $conf, $langs;

        $commandes = array();

        $maxsizeofmulticall = (empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL)?100:$conf->global->ECOMMERCENG_MAXSIZE_MULTICALL);      // 1000 seems ok for multicall.
        $nbsynchro = 0;
        $nbremote = count($remoteObject);
        if ($nbremote)
        {
            // Create n groups of $maxsizeofmulticall records max to call the multiCall
            $callsgroup = array();
            $calls=array();
            foreach ($remoteObject as $rcommande)
            {
                if (($nbsynchro % $maxsizeofmulticall) == 0)
                {
                    if (count($calls)) $callsgroup[]=$calls;    // Add new group for lot of 1000 call arrays
                    $calls=array();
                }

                $calls[] = $rcommande;

                $nbsynchro++;   // nbsynchro is now number of calls to do
            }
            if (count($calls)) $callsgroup[]=$calls;    // Add new group for the remain lot of calls not yet added

            dol_syslog("convertRemoteObjectIntoDolibarrCommande Call WS to get detail for the ".count($remoteObject)." objects (".count($callsgroup)." calls with ".$maxsizeofmulticall." max of records each) then create a Dolibarr array for each object");
            //var_dump($callsgroup);exit;

            $results=array();
            $nbcall=0;
            foreach ($callsgroup as $calls)
            {
                try {
                    $nbcall++;
                    dol_syslog("convertRemoteObjectIntoDolibarrCommande Call WS nb ".$nbcall." (".count($calls)." record)");
                    $resulttmp = $this->client->get('orders',
                        [
                            'per_page' => $maxsizeofmulticall,
                            'include' => implode(',', $calls),
                        ]
                    );
                    $results=array_merge($results, $resulttmp);
                } catch (HttpClientException $fault) {
                    dol_syslog('convertRemoteObjectIntoDolibarrCommande :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
					return false;
                }
            }

            if (count($results))
            {
                foreach ($results as $commande)
                {
                    // Process order
                    dol_syslog("- Process order remote_id=".$commande['order_id']." last_update=".$commande['updated_at']." societe remote_id=".$commande['customer_id']);

                    //set each items
                    $items = array();
                    $configurableItems = array();
                    if (count($commande['line_items']))
                    {
                        foreach ($commande['line_items'] as $item)
                        {
                                $items[] = array(
                                    'item_id' => $item['id'],
                                    'id_remote_product' => !empty($item['variation_id']) ? $item['product_id'].'|'.$item['variation_id'] : $item['product_id'],
                                    'description' => $item['name'],
                                    'product_type' => 'simple',
                                    'price' => $item['price'],
                                    'qty' => $item['quantity'],
                                    'tva_tx' => $this->getTaxRate($commande['total'], $commande['total_tax']) // tax_class > requete taxes rates
                                );
                        }

                        //define remote id societe : 0 for anonymous
                        $eCommerceTempSoc = new eCommerceSociete($this->db);
                        if ($commande['customer_id'] == null || $eCommerceTempSoc->fetchByRemoteId($commande['customer_id'], $this->site->id) < 0)
                        {
                            dol_syslog("The customer of this order was not found into table link", LOG_WARNING);
                            $remoteIdSociete = 0;   // If thirdparty was not found into thirdparty table link
                        }
                        else
                        {
                            $remoteIdSociete = $commande['customer_id'];
                        }

                        //set order's address
                        $billingName = (empty($conf->global->ECOMMERCENG_BILLING_CONTACT_NAME)?'Billing':$conf->global->ECOMMERCENG_BILLING_CONTACT_NAME);      // Contact name treated as billing address.
                        $commandeSocpeople = $commande['billing'];
                        $socpeopleCommande = array(
                            'remote_id'     => $remoteIdSociete.'|1',
                            'type'          => eCommerceSocpeople::CONTACT_TYPE_ORDER,
                            'last_update'   => isset($commande['date_modified'])?$commande['date_modified']:$commande['date_created'],
                            'name'          => $billingName, // $commandeSocpeople['last_name'],
                            'lastname'      => $billingName, // $commandeSocpeople['last_name'],
                            'firstname'     => '', // $commandeSocpeople['first_name'],
                            'town'          => $commandeSocpeople['city'],
                            //'fk_pays'       => getCountry($commandeSocpeople['country'], 3),
                            'fax'           => '',
                            'zip'           => $commandeSocpeople['postcode'],
                            //add wrap
                            'address'       => (!empty(trim($commandeSocpeople['company'])) ? trim($commandeSocpeople['company']) . ", " : "") .
                                $commandeSocpeople['address_1'] . (!empty($commandeSocpeople['address_1']) && !empty($commandeSocpeople['address_2']) ? "\n" : "") . $commandeSocpeople['address_2'],
                            'phone'         => $commandeSocpeople['phone'],
                        );

                        //set billing's address
                        $socpeopleFacture = $socpeopleCommande;
                        $socpeopleFacture['type'] = eCommerceSocpeople::CONTACT_TYPE_INVOICE;

                        //set shipping's address
                        $livraisonSocpeople = $commande['shipping'];
                        if ((!empty($livraisonSocpeople['address_1']) || !empty($livraisonSocpeople['address_1'])) && !empty($livraisonSocpeople['city']) && !empty($livraisonSocpeople['postcode']) && !empty($livraisonSocpeople['country'])) {
                            $shippingName = (empty($conf->global->ECOMMERCENG_SHIPPING_CONTACT_NAME) ? 'Shipping' : $conf->global->ECOMMERCENG_SHIPPING_CONTACT_NAME);  // Contact name treated as shipping address.
                            $socpeopleLivraison = array(
                                'remote_id'     => $remoteIdSociete . '|2',
                                'type'          => eCommerceSocpeople::CONTACT_TYPE_DELIVERY,
                                'last_update'   => isset($commande['date_modified'])?$commande['date_modified']:$commande['date_created'],
                                'name'          => $shippingName, // $livraisonSocpeople['last_name'],
                                'lastname'      => $shippingName, // $livraisonSocpeople['last_name'],
                                'firstname'     => '', // $livraisonSocpeople['first_name'],
                                'town'          => $livraisonSocpeople['city'],
                                //'fk_pays'       => getCountry($commandeSocpeople['country'], 3),
                                'fax'           => '',
                                'zip'           => $livraisonSocpeople['postcode'],
                                //add wrap
                                'address'       => (!empty(trim($livraisonSocpeople['company'])) ? addslashes(trim($livraisonSocpeople['company'])) . ", " : "") .
                                    $livraisonSocpeople['address_1'] . (!empty($livraisonSocpeople['address_1']) && !empty($livraisonSocpeople['address_2']) ? "\n" : "") .
                                    $livraisonSocpeople['address_2'],
                                'phone'         => '',
                            );
                        } else {
                            $socpeopleLivraison = $socpeopleCommande;
                            $socpeopleLivraison['type'] = eCommerceSocpeople::CONTACT_TYPE_DELIVERY;
                        }

                        //set delivery as service
                        $langs->load("ecommerce@ecommerceng");
                        $shippingDisplayIfNull = (empty($conf->global->ECOMMERCENG_SHIPPING_NOT_DISPLAY_IF_NULL)?true:false);  // Contact name treated as shipping address.
                        $delivery = array(
                            'description'   => $langs->trans('ECommerceShipping') . (isset($commande['shipping_lines'][0]) ? ' - '.$commande['shipping_lines'][0]['method_title'] : ''), // $commande['customer_note']
                            'price'         => $commande['shipping_total'],
                            'qty'           => $shippingDisplayIfNull || isset($commande['shipping_lines'][0]) ? 1 : 0, //0 to not show
                            'tva_tx'        => $this->getTaxRate($commande['shipping_total'], $commande['shipping_tax'])
                        );

                        //define delivery date
                        if (isset($commande['date_completed']) && $commande['date_completed'] != null)
                            $deliveryDate = $commande['date_completed'];
                        else
                            $deliveryDate = $commande['date_created'];

                        // define status of order
                        // $commande['status'] is: 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'
                        $tmp = $commande['status'];

                        // try to match dolibarr status
                        $status = '';
                        if (preg_match('/^pending/', $tmp))          $status = Commande::STATUS_VALIDATED;           // manage 'pending', 'pending_payment', 'pending_paypal', 'pending_ogone', 'pending_...'
                        elseif ($tmp == 'processing')                $status = Commande::STATUS_ACCEPTED;                                    // shipment in process or invoice done = processing       // Should be constant Commande::STATUS_SHIPMENTONPROCESS but not defined in dolibarr 3.9
                        elseif ($tmp == 'on-hold')                   $status = Commande::STATUS_ACCEPTED;
                        elseif (preg_match('/^cancelled/', $tmp))    $status = Commande::STATUS_CANCELED;            // manage 'canceled', 'canceled_bnpmercanetcw', 'canceled_...'
                        elseif ($tmp == 'completed')                 $status = Commande::STATUS_CLOSED;
                        elseif ($tmp == 'refunded')                  $status = Commande::STATUS_CLOSED;
                        elseif ($tmp == 'failed')                    $status = Commande::STATUS_CANCELED;
                        if ($status == '')
                        {
                            dol_syslog("Status: We found an order id ".$commande['increment_id']." with ecommerce status '".$tmp."' that is unknown, not supported. We will use '0' for Dolibarr", LOG_WARNING);
                            $status = Commande::STATUS_DRAFT;   // draft by default (draft does not exists with magento, so next line will set correct status)
                        }
                        else
                        {
                            dol_syslog("Status: We found an order id ".$commande['increment_id']." with ecommerce status '".$tmp."'. We convert it into Dolibarr status '".$status."'");
                        }

                        // try to match dolibarr billed status (payed or not)
                        $billed = -1;   // unknown
                        if ($commande['status'] == 'pending')    $billed = 0;
                        if ($commande['status'] == 'processing') $billed = 0;   //
                        if ($commande['status'] == 'on-hold')    $billed = 0;      //
                        if ($commande['status'] == 'completed')  $billed = 1;    // We are sure for complete that order is payed
                        if ($commande['status'] == 'cancelled')  $billed = 0;    // We are sure for canceled that order was not payed
                        if ($commande['status'] == 'refunded')   $billed = 1;     //
                        if ($commande['status'] == 'failed')     $billed = 0;       //
                        // Note: with processing, billed can be 0 or 1, so we keep -1


                        // Add order content to array or orders
                        $commandes[] = array(
                            'last_update'           => isset($commande['date_modified'])?$commande['date_modified']:$commande['date_created'],
                            'remote_id'             => $commande['id'],
                            'remote_increment_id'   => $commande['id'],
                            'remote_id_societe'     => $remoteIdSociete,
                            'ref_client'            => $commande['id'],
                            'date_commande'         => $commande['date_created'],
                            'date_livraison'        => $commande['date_completed'], // $deliveryDate,
                            'items'                 => $items,
                            'delivery'              => $delivery,
                            'note'                  => $commande['customer_note'],
                            'socpeopleCommande'     => $socpeopleCommande,
                            'socpeopleFacture'      => $socpeopleFacture,
                            'socpeopleLivraison'    => $socpeopleLivraison,
                            'status'                => $status,                         // dolibarr status
                            'billed'                => $billed,
                            'remote_state'          => $commande['status'],        // remote state, for information only (less accurate than status)
                            'remote_status'         => $commande['status'],      // remote status, for information only (more accurate than state)
                            'remote_order'          => $commande
                        );
                    }
                    else
                    {
                        dol_syslog("No items in this order", LOG_WARNING);
                    }
                }
            }
        }

        //important - order by last update
        if (count($commandes))
        {
            $last_update = array();
            foreach ($commandes as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $commandes);
        }

        dol_syslog("convertRemoteObjectIntoDolibarrCommande end (found ".count($commandes)." array of orders filled with complete data from eCommerce)");
        return $commandes;
    }

    /**
     * Put the remote data into facture dolibarr data from instantiated class
     * Return array of invoices by update time.
     *
     * @param   array   $remoteObject       array of remote invoices
     * @param   int     $toNb               Max nb
     * @return  array                       facture
     */
    public function convertRemoteObjectIntoDolibarrFacture($remoteObject, $toNb=0)
    {
        global $conf;

        $factures = array();

/*        $maxsizeofmulticall = (empty($conf->global->ECOMMERCENG_MAXSIZE_MULTICALL)?100:$conf->global->ECOMMERCENG_MAXSIZE_MULTICALL);      // 1000 seems ok for multicall.
        $nbsynchro = 0;
        $nbremote = count($remoteObject);
        if ($nbremote)
        {
            // Create n groups of $maxsizeofmulticall records max to call the multiCall
            $callsgroup = array();
            $calls=array();
            foreach ($remoteObject as $rfacture)
            {
                if (($nbsynchro % $maxsizeofmulticall) == 0)
                {
                    if (count($calls)) $callsgroup[]=$calls;    // Add new group for lot of 1000 call arrays
                    $calls=array();
                }

                $calls[] = $rfacture;

                $nbsynchro++;   // nbsynchro is now number of calls to do
            }
            if (count($calls)) $callsgroup[]=$calls;    // Add new group for the remain lot of calls not yet added

            dol_syslog("convertRemoteObjectIntoDolibarrFacture Call WS to get detail for the ".count($remoteObject)." objects (".count($callsgroup)." calls with ".$maxsizeofmulticall." max of records each) then create a Dolibarr array for each object");
            //var_dump($callsgroup);exit;

            $results=array();
            $nbcall=0;
            foreach ($callsgroup as $calls)
            {
                try {
                    $nbcall++;
                    dol_syslog("convertRemoteObjectIntoDolibarrFacture Call WS nb ".$nbcall." (".count($calls)." record)");
                    $resulttmp =  $this->client->get('orders',
                        [
                            'include' => implode(',', $calls),
                        ]
                    );
                    $results=array_merge($results, $resulttmp);
                } catch (HttpClientException $fault) {
                    dol_syslog('convertRemoteObjectIntoDolibarrFacture :'.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
                }
			}

            if (count($results))
            {
                $i=0;
                foreach ($results as $facture)
                {
                    // Process invoice
   	                dol_syslog("- Process invoice remote_id=".$facture['order_id']." last_update=".$facture['updated_at']." societe order_id=".$facture['order_id']);

                    $i++;

                    $configurableItems = array();
                    //retrive remote order from invoice
                    $commande = $this->getRemoteCommande($facture['order_id']);
                    //set each invoice items
                    $items = array();
                    if (count($facture['items']))
					{
                        foreach ($facture['items'] as $item)
                        {
                                //var_dump($item);    // show invoice item as it is from magento

                                $product_type = $this->getProductTypeOfItem($item, $commande, $facture);
                                $parent_item_id = $this->getParentItemOfItem($item, $commande, $facture);

                                // If item is configurable, localMemCache it, to use its price and tax rate instead of the one of its child
                                if ($product_type == 'configurable') {
                                    $configurableItems[$item['item_id']] = array(
                                        'item_id' => $item['item_id'],
                                        'id_remote_product' => $item['product_id'],
                                        'description' => $item['name'],
                                        'product_type' => $product_type,
                                        'price' => $item['price'],
                                        'qty' => $item['qty'],
                                        'tva_tx' => $this->getTaxRate($item['row_total'], $item['tax_amount'])
                                    );
                                } else {
                                    // If item has a parent item id defined in $configurableItems, it's a child simple item so we get it's price and tax values instead of 0
                                    if (!array_key_exists($parent_item_id, $configurableItems)) {
                                        $items[] = array(
                                                'item_id' => $item['item_id'],
                                                'id_remote_product' => $item['product_id'],
                                                'description' => $item['name'],
                                                'product_type' => $product_type,
                                                'price' => $item['price'],
                                                'qty' => $item['qty'],
                                                'tva_tx' => $this->getTaxRate($item['row_total'], $item['tax_amount'])
                                        );
                                    } else {
                                        $items[] = array(
                                                'item_id' => $item['item_id'],
                                                'id_remote_product' => $item['product_id'],
                                                'description' => $item['name'],
                                                'product_type' => $product_type,
                                                'price' => $configurableItems[$parent_item_id]['price'],
                                                'qty' => $item['qty'],
                                                'tva_tx' => $configurableItems[$parent_item_id]['tva_tx']
                                        );
                                    }
                                }
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

                        $eCommerceTempSoc = new eCommerceSociete($this->db);
                        if ($commande['customer_id'] == null || $eCommerceTempSoc->fetchByRemoteId($commande['customer_id'], $this->site->id) < 0)
                        {
                            $remoteIdSociete = 0;
                        }
                        else
                        {
                            $remoteIdSociete = $commande['customer_id'];
                        }

                        // load local order to be used to retreive some data for invoice
                        $eCommerceTempCommande = new eCommerceCommande($this->db);
                        $eCommerceTempCommande->fetchByRemoteId($commande['order_id'], $this->site->id);
                        $dbCommande = new Commande($this->db);
                        $dbCommande->fetch($eCommerceTempCommande->fk_commande);


                        // define status of invoice
                        $tmp = $facture['state'];                                                   // state from is 1, 2, 3

                        // try to match dolibarr status
                        $status = '';
                        if ($tmp == 1)     $status = Facture::STATUS_VALIDATED;            // validated = pending
                        if ($tmp == 2)     $status = Facture::STATUS_CLOSED;               // complete
                        if ($tmp == 3)     $status = Facture::STATUS_ABANDONED;            // canceled = holded
                        if ($status == '')
                        {
                            dol_syslog("Status: We found an invoice id ".$commande['increment_id']." with ecommerce status '".$tmp."' that is unknown, not supported. We will use '0' for Dolibarr", LOG_WARNING);
                            $status = Facture::STATUS_DRAFT;                                            // draft by default (draft does not exists with magento, so next line will set correct status)
                        }
                        else
                        {
                            dol_syslog("Status: We found an invoice id ".$commande['increment_id']." with ecommerce status '".$tmp."'. We convert it into Dolibarr status '".$status."'");
                        }


                        $close_code = '';
                        $close_note = '';
                        if ($tmp == 3)
                        {
                            $close_code = Facture::CLOSECODE_ABANDONED;
                            $close_note = 'Holded on ECommerce';
                        }

                        //add invoice to invoices
                        $factures[] = array(
                                'last_update' => $facture['updated_at'],
                                'remote_id' => $facture['invoice_id'],
                                'remote_increment_id' => $facture['increment_id'],
                                'ref_client' => $facture['increment_id'],
                                'remote_order_id' => $facture['order_id'],
                                'remote_order_increment_id' => $facture['order_increment_id'],
                                'remote_id_societe' => $remoteIdSociete,
                                'socpeopleLivraison' => $socpeopleLivraison,
                                'socpeopleFacture' => $socpeopleFacture,
                                'date' => $facture['created_at'],
                                'code_cond_reglement' => $dbCommande->cond_reglement_code,      // Take for local order
                                'delivery' => $delivery,
                                'items' => $items,
                                'status' => $tmp,
                                'close_code' => $close_code,
                                'close_note' => $close_note,
                                'remote_state' => $facture['state'],
                                'remote_order' => $commande,
                                'remote_invoice' => $facture
                        );
                    }
                    else
                    {
                        dol_syslog("No items in this invoice", LOG_WARNING);
                    }
                }
            }
        }

        //important - order by last update
        if (count($factures))
        {
            $last_update=array();
            foreach ($factures as $key => $row)
            {
                $last_update[$key] = $row['last_update'];
            }
            array_multisort($last_update, SORT_ASC, $factures);
        }

        //var_dump($factures);exit;
        */

        dol_syslog("convertRemoteObjectIntoDolibarrFacture end (found ".count($products)." record)");
        return $factures;
    }


    /**
     * Return if type of an item of invoice (information comue from item of order)
     *
     * @param   array   $item       Item of invoice
     * @param   array   $commande   Commande with items
     * @param   array   $facture    Facture with items
     * @return string
     */
    function getProductTypeOfItem($item, $commande, $facture)
    {
        $product_type = 'notfound';   // By default

        //print "Try to find product type of invoice item_id=".$item['item_id']." (invoice ".$facture['increment_id'].") and order_item_id=".$item['order_item_id']." (order ".$commande['increment_id'].")\n";

        $order_item_id = $item['order_item_id'];

        // We scan item of order to find this order item id
        foreach($commande['items'] as $itemorder)
        {
            if ($itemorder['item_id'] == $order_item_id)
            {
                // We've got it
                $product_type = $itemorder['product_type'];
                break;
            }
        }

        //print "Found product type = ".$product_type."\n";

        if ($product_type == 'notfound') $product_type = 'simple';

        return $product_type;
    }

    /**
     * Return if type of an item of invoice (information comue from item of order)
     *
     * @param   array   $item       Item of invoice
     * @param   array   $commande   Commande with items
     * @param   array   $facture    Facture with items
     * @return string
     */
    function getParentItemOfItem($item, $commande, $facture)
    {
        //print "Try to find invoice parent item id of invoice item_id=".$item['item_id']." (invoice ".$facture['increment_id'].") and order_item_id=".$item['order_item_id']." (order ".$commande['increment_id'].")\n";

        $parent_item_id = 0;   // By default
        $parent_item_id_in_order = 0;

        $order_item_id = $item['order_item_id'];

        // We scan item of order to find this order item id
        foreach($commande['items'] as $itemorder)
        {
            if ($itemorder['item_id'] == $order_item_id)
            {
                // We've got it
                $product_type = $itemorder['product_type'];

                $parent_item_id_in_order = $itemorder['parent_item_id'];
                break;
            }
        }

        // If the item is linked to an order item id that has a parent order item id
        if ($parent_item_id_in_order)
        {
            // We scan now invoice items to find the item that is linked to order item id $parent_item_id_in_order
            foreach($facture['items'] as $itemfacture)
            {
                if ($itemfacture['order_item_id'] == $parent_item_id_in_order)
                {
                    // We've got it
                    $parent_item_id = $itemfacture['item_id'];
                    break;
                }
            }
        }

        //print "Found invoice parent_item_id=".$parent_item_id." and order parent_item_id=".$parent_item_id_in_order."\n";

        return $parent_item_id;
    }




    // Now functions to get data on remote shop, from the remote id.


    /**
     * Return the magento's category tree
     *
     * @return  array|boolean       Array with categories or false if error:
     *                              array(array('level'=>, 'updated_at'=>, 'category_id'=> ), ...)
     */
    public function getRemoteCategoryTree()
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce getRemoteCategoryTree session=".$this->session);
        $result = array();
 /*       try {
            $result = $this->client->call($this->session, 'catalog_category.tree');
            return $result;
            //dol_syslog($this->client->__getLastRequest());
        } catch (SoapFault $fault) {
            $this->errors[]=$this->site->name.': '.$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }*/
        //var_dump($result);
        dol_syslog("eCommerceRemoteAccessWoocommerce getRemoteCategoryTree end. Nb of record of result = ".count($result));
        return $result;
    }

    /**
     * Return the magento's category att
     *
     * @return  array|boolean       Array with categories or false if error
     */
    /*public function getRemoteCategoryAtt()
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce getRemoteCategoryAtt session=".$this->session);
        try {
            $result = $this->client->call($this->session, 'catalog_category_attribute.list');
        } catch (SoapFault $fault) {
            $this->errors[]=$this->site->name.': '.$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
        dol_syslog("eCommerceRemoteAccessWoocommerce getRemoteCategoryAtt end");
        return $result;
    }*/

    /**
     * Return the magento's address id
     *
     * @param   int             $remote_thirdparty_id       Id of thirdparty
     * @return  array|boolean                               Array with address id
     */
    public function getRemoteAddressIdForSociete($remote_thirdparty_id)
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce getRemoteAddressIdForSociete session=".$this->session);
        $result = array($remote_thirdparty_id);
        dol_syslog("eCommerceRemoteAccessWoocommerce getRemoteAddressIdForSociete end");
        return $result;
    }


    /**
     * Return content of one category
     *
     * @param   int             $category_id        Remote category id
     * @return  boolean|unknown                     Return
     */
    public function getCategoryData($category_id)
    {
        $result = array();
        dol_syslog("eCommerceRemoteAccessWoocommerce getCategoryData session=".$this->session);
/*        try {
            $result = $this->client->call($this->session, 'catalog_category.info', array('categoryId'=>$category_id));
            //dol_syslog($this->client->__getLastRequest());
        } catch (SoapFault $fault) {
            $this->errors[]=$this->site->name.': '.$fault->getMessage().'-'.$fault->getCode();
            dol_syslog($this->client->__getLastRequestHeaders(), LOG_WARNING);
            dol_syslog($this->client->__getLastRequest(), LOG_WARNING);
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }*/
        dol_syslog("eCommerceRemoteAccessWoocommerce getCategoryData end");
        return $result;
    }

    /**
     * Return the magento's order
     *
     * @param   int         $remoteCommandeId       Id of remote order
     * @return  object                              Order
     */
    public function getRemoteCommande($remoteCommandeId)
    {
        $commande = array();
        try {
            dol_syslog("getCommande begin");
            $commande =  $this->client->get("orders/$remoteCommandeId");
            dol_syslog("getCommande end");
        } catch (HttpClientException $fault) {
            if ($fault->getCode() != 404) {
                $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
                dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            }
        }
        return $commande;
    }



    /**
     * Update the remote product
     *
     * @param   int     $remote_id      Id of product on remote ecommerce
	 * @param   Product $object         Product object
     * @return  boolean                 True or false
     */
    public function updateRemoteProduct($remote_id, $object)
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteProduct session=".$this->session." remote_id=".$remote_id." object->id=".$object->id);

        $totalWeight = $object->weight;
        if ($object->weight_units < 50)   // >50 means a standard unit (power of 10 of official unit), > 50 means an exotic unit (like inch)
        {
            $trueWeightUnit=pow(10, $object->weight_units);
            $totalWeight = sprintf("%f", $object->weight * $trueWeightUnit);
        }


        try {
            if (preg_match('/^(\d+)\|(\d+)$/', $remote_id, $idsProduct) == 1) {
                // Variations
/*
                // Product variation - Downloads properties
                $downloads = [
                    [
                        'name' => '',       // string     File name.
                        'file' => '',       // string     File URL.
                    ],
                ];
*/
/*
                // Product variation - Dimensions properties
                $dimensions = [
                    'length'    => '',      // string   Product length (cm).
                    'width'     => '',      // string   Product width (cm).
                    'height'    => '',      // string   Product height (cm).
                ];
*/
/*
                // Product variation - Image properties
                $images = [
                    [
                        'id'        => 0,       // integer	Image ID. Not required
                        'src'       => '',      // string	Image URL.
                        'name'      => '',      // string	Image name.
                        'alt'       => '',      // string	Image alternative text.
                        'position'  => 0,       // integer	Image position. 0 means that the image is featured.
                    ],
                ];
*/
/*
                // Product variation - Attributes properties
                $attributes = [
                    [
                        'id'        => 0,       // integer  Attribute ID.
                        'name'      => '',      // string   Attribute name.
                        'option'    => '',      // string   Selected attribute term name.
                    ],
                ];
*/
/*
                // Product variation - Meta data properties
                $meta_data = [
                    'key'   => '',  // string	Meta key.
                    'value' => '',  // string	Meta value.
                ];
*/
                $variationData = array(
                    'description' => $object->description,                  // string       Variation description.
                    'sku' => $object->ref,                                  // string       Unique identifier.
                    'regular_price' => $object->price,                      // string       Variation regular price.
                    //'sale_price' => '',                                     // string       Variation sale price.
                    //'date_on_sale_from' => '',                              // date-time    Start date of sale price, in the site’s timezone.
                    //'date_on_sale_from_gmt' => '',                          // date-time    Start date of sale price, as GMT.
                    //'date_on_sale_to' => '',                                // date-time    End date of sale price, in the site’s timezone.
                    //'date_on_sale_to_gmt' => '',                            // date-time    End date of sale price, in the site’s timezone.
                    //'visible' => '',                                        // boolean      Define if the attribute is visible on the “Additional information” tab in the product’s page. Default is true.
                    //'virtual' => $object->type == Product::TYPE_SERVICE,    // boolean      If the variation is virtual. Default is false.
                    //'downloadable' => '',                                   // boolean      If the variation is downloadable. Default is false.
                    //'downloads' => $downloads,                              // array        List of downloadable files. See Product variation - Downloads properties
                    //'download_limit' => '',                                 // integer      Number of times downloadable files can be downloaded after purchase. Default is -1.
                    //'download_expiry' => '',                                // integer      Number of days until access to downloadable files expires. Default is -1.
                    //'tax_status' => '',                                     // string       Tax status. Options: taxable, shipping and none. Default is taxable.
                    //'tax_class' => '',                                      // string       Tax class.
                    //'manage_stock' => '',                                   // boolean      Stock management at variation level. Default is false.
                    //'stock_quantity' => '',                                 // integer      Stock quantity.
                    //'in_stock' => '',                                       // boolean      Controls whether or not the variation is listed as “in stock” or “out of stock” on the frontend. Default is true.
                    //'backorders' => '',                                     // string       If managing stock, this controls if backorders are allowed. Options: no, notify and yes. Default is no.
                    'weight' => $totalWeight,                               // string       Variation weight (kg).
                    //'dimensions' => $dimensions,                            // object       Variation dimensions. See Product variation - Dimensions properties
                    //'shipping_class' => '',                                 // string       Shipping class slug.
                    //'image' => $images,                                     // object       Variation image data. See Product variation - Image properties
                    //'attributes' => $attributes,                            // array        List of attributes. See Product variation - Attributes properties
                    //'menu_order' => '',                                     // integer      Menu order, used to custom sort products.
                    //'meta_data' => $meta_data,                              // array        Meta data. See Product variation - Meta data properties
                );

                // Product
                // 'name'    => $object->label,			                    // string		Product name.
                // 'status'  => $object->status ? 'publish' : 'pending',	// string		Product status (post status). Options: draft, pending, private and publish. Default is publish.

                $result = $this->client->put("products/$idsProduct[1]/variations/$idsProduct[2]", $variationData);
            } else {
                // Product
/*
                // Product - Downloads properties
                $downloads = [
                    [
                        'name' => '',       // string     File name.
                        'file' => '',       // string     File URL.
                    ],
                ];
*/
/*
                // Product - Dimensions properties
                $dimensions = [
                    'length' => '',     // string   Product length (cm).
                    'width' => '',      // string   Product width (cm).
                    'height' => '',     // string   Product height (cm).
                ];
*/
/*
                // Product - Categories properties
                $categories = [
                    [
                        'id' => 0,      // integer  Category ID.
                    ],
                ];
*/
/*
                // Product - Tags properties
                $tags = [
                    [
                        'id' => 0,      // integer  Tag ID.
                    ],
                ];
*/
/*
                // Product - Images properties
                $images = [
                    [
                        'id' => 0,              // integer	Image ID. Not required
                        'src' => '',            // string	Image URL.
                        'name' => '',           // string	Image name.
                        'alt' => '',            // string	Image alternative text.
                        'position' => 0,        // integer	Image position. 0 means that the image is featured.
                    ],
                ];
*/
/*
                // Product - Attributes properties
                $attributes = [
                    [
                        'id' => 0,              // integer	Attribute ID. Not required
                        'name' => '',           // string	Attribute name.
                        'position' => 0,        // integer	Attribute position.
                        'visible' => false,     // boolean	Define if the attribute is visible on the “Additional information” tab in the product’s page. Default is false.
                        'variation' => false,   // boolean	Define if the attribute can be used as variation. Default is false.
                        'options' => [],        // array	List of available term names of the attribute.
                    ],
                ];
*/
/*
                // Product - Default attributes properties
                $default_attributes = [
                    'id' => 0,              // integer	Attribute ID. Not required
                    'name' => '',           // string	Attribute name.
                    'option' => '',         // string	Selected attribute term name.
                ];
*/
/*
                // Product - Meta data properties
                $meta_data = [
                    'key' => '', // string	Meta key.
                    'value' => '', // string	Meta value.
                ];
*/
                $productData = array(
                    'name'                  => $object->label,			                // string		Product name.
                    //'slug'                  => '',			                            // string		Product slug.
                    //'type'                  => '',			                            // string		Product type. Options: simple, grouped, external and variable. Default is simple.
                    'status'                => $object->status ? 'publish' : 'pending',	// string		Product status (post status). Options: draft, pending, private and publish. Default is publish.
                    //'featured'              => false,		                            // boolean		Featured product. Default is false.
                    //'catalog_visibility'    => '',                                      // string		Catalog visibility. Options: visible, catalog, search and hidden. Default is visible.
                    'description'           => $object->description,                    // string		Product description.
                    //'short_description'     => '',                                      // string		Product short description.
                    'sku'                   => $object->ref,                            // string		Unique identifier.
                    'regular_price'         => $object->price,                          // string		Product regular price.
                    //'sale_price'            => '',                                      // string		Product sale price.
                    //'date_on_sale_from'     => '',                                      // date-time	Start date of sale price, in the site’s timezone.
                    //'date_on_sale_from_gmt' => '',                                      // date-time	Start date of sale price, as GMT.
                    //'date_on_sale_to'       => '',                                      // date-time	End date of sale price, in the site’s timezone.
                    //'date_on_sale_to_gmt'   => '',                                      // date-time	End date of sale price, in the site’s timezone.
                    //'virtual'               => $object->type == Product::TYPE_SERVICE,  // boolean		If the product is virtual. Default is false.
                    //'downloadable'          => false,                                   // boolean		If the product is downloadable. Default is false.
                    //'downloads'             => $downloads,                              // array		List of downloadable files. See Product - Downloads properties
                    //'download_limit'        => -1,                                      // integer		Number of times downloadable files can be downloaded after purchase. Default is -1.
                    //'download_expiry'       => -1,                                      // integer		Number of days until access to downloadable files expires. Default is -1.
                    //'external_url'          => '',                                      // string		Product external URL. Only for external products.
                    //'button_text'           => '',                                      // string		Product external button text. Only for external products.
                    //'tax_status'            => '',                                      // string		Tax status. Options: taxable, shipping and none. Default is taxable.
                    //'tax_class'             => '',                                      // string		Tax class.
                    //'manage_stock'          => false,                                   // boolean		Stock management at product level. Default is false.
                    //'stock_quantity'        => $object->stock_reel,                     // integer		Stock quantity.
                    //'in_stock'              => $object->stock_reel > 0,                 // boolean		Controls whether or not the product is listed as “in stock” or “out of stock” on the frontend. Default is true.
                    //'backorders'            => '',                                      // string		If managing stock, this controls if backorders are allowed. Options: no, notify and yes. Default is no.
                    //'sold_individually'     => false,                                   // boolean		Allow one item to be bought in a single order. Default is false.
                    'weight'                => $totalWeight,                            // string		Product weight (kg).
                    //'dimensions'            => $dimensions,                             // object		Product dimensions. See Product - Dimensions properties
                    //'shipping_class'        => '',                                      // string		Shipping class slug.
                    //'reviews_allowed'       => true,                                    // boolean		Allow reviews. Default is true.
                    //'upsell_ids'            => [],                                      // array		List of up-sell products IDs.
                    //'cross_sell_ids'        => [],                                      // array		List of cross-sell products IDs.
                    //'parent_id'             => 0,                                       // integer		Product parent ID.
                    //'purchase_note'         => '',                                      // string		Optional note to send the customer after purchase.
                    //'categories'            => $categories,                             // array		List of categories. See Product - Categories properties
                    //'tags'                  => $tags,                                   // array		List of tags. See Product - Tags properties
                    //'images'                => $images,                                 // object		List of images. See Product - Images properties
                    //'attributes'            => $attributes,			                    // array		List of attributes. See Product - Attributes properties
                    //'default_attributes'    => $default_attributes,			            // array		Defaults variation attributes. See Product - Default attributes properties
                    //'menu_order'            => 0,			                            // integer		Menu order, used to custom sort products.
                    //'meta_data'             => $meta_data,                              // array		Meta data. See Product - Meta data properties
                );

                $result = $this->client->put("products/$remote_id", $productData);
            }

            dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteProduct end");
            return true;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Update the remote stock of product
     *
     * @param   int         $remote_id      Id of product on remote ecommerce
     * @param   Movement    $object         Movement object, enhanced with property qty_after be the trigger STOCK_MOVEMENT.
     * @return  boolean                     True or false
     */
    public function updateRemoteStockProduct($remote_id, $object)
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteStockProduct session=".$this->session." product remote_id=".$remote_id." movement object->id=".$object->id.", new qty=".$object->qty_after);

        // $object->qty is the qty of movement
        try {
            if (preg_match('/^(\d+)\|(\d+)$/', $remote_id, $idsProduct) == 1) {
                // Variations
                $variationData = array(
                    //'manage_stock'      => '',                                      // boolean      Stock management at variation level. Default is false.
                    'stock_quantity'    => $object->qty_after,                      // integer      Stock quantity.
                    'in_stock'          => $object->qty_after > 0,                  // boolean      Controls whether or not the variation is listed as “in stock” or “out of stock” on the frontend. Default is true.
                    //'backorders'        => '',                                      // string       If managing stock, this controls if backorders are allowed. Options: no, notify and yes. Default is no.
                );

                // Product
                // 'name'    => $object->label,			                    // string		Product name.
                // 'status'  => $object->status ? 'publish' : 'pending',	// string		Product status (post status). Options: draft, pending, private and publish. Default is publish.

                $result = $this->client->put("products/$idsProduct[1]/variations/$idsProduct[2]", $variationData);
            } else {
                $productData = array(
                    //'manage_stock'      => false,                                   // boolean      Stock management at product level. Default is false.
                    'stock_quantity'    => $object->qty_after,                      // integer      Stock quantity.
                    'in_stock'          => $object->qty_after > 0,                  // boolean      Controls whether or not the product is listed as “in stock” or “out of stock” on the frontend. Default is true.
                    //'backorders'        => '',                                      // string       If managing stock, this controls if backorders are allowed. Options: no, notify and yes. Default is no.
                );

                $result = $this->client->put("products/$remote_id", $productData);
            }

            dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteStockProduct end");
            return true;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Update the remote societe
     *
     * @param   int     $remote_id      Id of societe on remote ecommerce
     * @param   Societe $object         Societe object
     * @return  boolean                 True or false
     */
    public function updateRemoteSociete($remote_id, $object)
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteSociete session=".$this->session." remote_id=".$remote_id." object->id=".$object->id);
/*
        // Customer - Meta data properties
        $meta_data = [
            'key' => '',        // string   Meta key.
            'value' => '',      // string   Meta value.
        ];
*/
        try {
            $societeData = array(
                'email'         => $object->email,      // string   The email address for the customer. MANDATORY
                //'first_name'    => '',                  // string   Customer first name.
                //'last_name'     => $object->name,       // string   Customer last name.
                //'username'      => '',                  // string   Customer login name.
                //'password'      => '',                  // string   Customer password.
                //'meta_data'     => $meta_data,          // array    Meta data. See Customer - Meta data properties
            );

            $result = $this->client->put("customers/$remote_id", $societeData);

            dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteSociete end");
            return true;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Update the remote contact
     *
     * @param   int     $remote_id      Id of contact on remote ecommerce
     * @param   Contact $object         Contact object
     * @return  boolean                 True or false
     */
    public function updateRemoteSocpeople($remote_id, $object)
    {
        global $conf;

        dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteSocpeople session=".$this->session." remote_id=".$remote_id." object->id=".$object->id);

        // Get societe
        require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
        $societe = new Societe($this->db);
        $societe->fetch($object->socid);

        $billingName = (empty($conf->global->ECOMMERCENG_BILLING_CONTACT_NAME)?'Billing':$conf->global->ECOMMERCENG_BILLING_CONTACT_NAME);      // Contact name treated as billing address.
        $shippingName = (empty($conf->global->ECOMMERCENG_SHIPPING_CONTACT_NAME)?'Shipping':$conf->global->ECOMMERCENG_SHIPPING_CONTACT_NAME);  // Contact name treated as shipping address.

        try {
            if ($object->lastname == $billingName) {
                $address = explode("\n", $object->address);
                // Billing
                $contactData = [
                    'billing' => [
                        //'first_name'    => '',                                  // string   First name.
                        //'last_name'     => '',                                  // string   Last name.
                        //'company'       => $societe->name,                      // string   Company name.
                        'address_1'     => isset($address[0])?$address[0]:'',   // string   Address line 1
                        'address_2'     => isset($address[1])?implode(" ", array_slice($address, 1)):'',   // string   Address line 2
                        'city'          => $object->town,                       // string   City name.
                        //'state'         => '',                                  // string   ISO code or name of the state, province or district.
                        'postcode'      => $object->zip,                        // string   Postal code.
                        'country'       => getCountry($object->country_id, 2),  // string   ISO code of the country.
                        'email'         => $object->email,                      // string   Email address.
                        'phone'         => $object->phone_pro,                  // string   Phone number.
                    ],
                ];
            } elseif ($object->lastname == $shippingName) {
                $address = explode("\n", $object->address);
                // Shipping
                $contactData = [
                    'shipping' => [
                        //'first_name'    => '',                                  // string   First name.
                        //'last_name'     => '',                                  // string   Last name.
                        //'company'       => $societe->name,                      // string   Company name.
                        'address_1'     => isset($address[0])?$address[0]:'',   // string   Address line 1
                        'address_2'     => isset($address[1])?implode(" ", array_slice($address, 1)):'',   // string   Address line 2
                        'city'          => $object->town,                       // string   City name.
                        //'state'         => '',                                  // string   ISO code or name of the state, province or district.
                        'postcode'      => $object->zip,                        // string   Postal code.
                        'country'       => getCountry($object->country_id, 2),  // string   ISO code of the country.
                    ],
                ];
            }

            if (isset($contactData)) {
                if (preg_match('/^(\d+)\|(\d+)$/', $remote_id, $idsCustomer) == 1) {
                    $result = $this->client->put("customers/$idsCustomer[1]", $contactData);
                }
            }

            dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteSocpeople end");
            return true;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Update the remote order
     *
     * @param   int      $remote_id     Id of order on remote ecommerce
	 * @param   Commande $object        Commande object
     * @return  boolean                 True or false
     */
    public function updateRemoteCommande($remote_id, $object)
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteCommande session=".$this->session." remote_id=".$remote_id." object->id=".$object->id);

        try {
            switch ($object->statut) {
                //case Commande::STOCK_NOT_ENOUGH_FOR_ORDER: $status = ''; break;
                case Commande::STATUS_CANCELED: $status = 'cancelled'; break;
                //case Commande::STATUS_DRAFT: $status = ''; break;
                case Commande::STATUS_VALIDATED: $status = 'pending'; break;
                case Commande::STATUS_ACCEPTED: $status = 'processing'; break;
                case Commande::STATUS_SHIPMENTONPROCESS: $status = 'processing'; break;
                case Commande::STATUS_CLOSED: $status = 'completed'; break;
            }

            if (isset($status)) {
                $commandeData = array(
                    'status' => $status,  // string  Order status. Options: pending, processing, on-hold, completed, cancelled, refunded and failed.
                );

                $result = $this->client->put("orders/$remote_id", $commandeData);
            }

            dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteCommande end");

            return true;
        } catch (HttpClientException $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }
    }

    /**
     * Update the remote invoice
     *
     * @param   int      $remote_id     Id of invoice on remote ecommerce
     * @param   Facture $object         Invoice object
     * @return  boolean                 True or false
     */
    public function updateRemoteFacture($remote_id, $object)
    {
        dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteFacture session=".$this->session." remote_id=".$remote_id." object->id=".$object->id);

        $result = false;
        /*
        try {
            $factureData = array(
                'status' => $object->status,
            );

            $result = $this->client->call($this->session, 'invoice.update', array($remote_id, $factureData, null, 'order_id'));
            //dol_syslog($this->client->__getLastRequest());
        } catch (SoapFault $fault) {
            $this->errors[]=$fault->getMessage().'-'.$fault->getCode();
            dol_syslog(__METHOD__.': '.$fault->getMessage().'-'.$fault->getCode().'-'.$fault->getTraceAsString(), LOG_WARNING);
            return false;
        }*/
        dol_syslog("eCommerceRemoteAccessWoocommerce updateRemoteFacture end");
        return $result;
    }

    /**
     * Create shipment
     *
     * @param   int     $livraison              Object shipment ?
     * @param   int     $remote_order_id        Id of order
     * @return  boolean                         True or false
     */
    public function createRemoteLivraison($livraison, $remote_order_id)
    {
        $result = false;

        dol_syslog("eCommerceRemoteAccessWoocommerce createRemoteLivraison session=" . $this->session . " dolibarr shipment id = " . $livraison->id . ", ref = " . $livraison->ref . ", order remote id = " . $remote_order_id);
/*        $remoteCommande = $this->getRemoteCommande($remote_order_id); // SOAP request to get data
        $livraisonArray = get_object_vars($livraison);
        try {
            $orderItemQty = array();
            foreach ($remoteCommande['items'] as $productWoocommerce) {
                foreach ($livraisonArray['lines'] as $lines) {
                    if ($lines->product_ref == $productWoocommerce['sku']) {
                        $orderItemQty[$productWoocommerce['item_id']] = $lines->qty_shipped;
                    }
                }
            }
            $result = $this->client->call($this->session, 'sales_order_shipment.create', array(
                $remoteCommande['increment_id'],
                $orderItemQty,
                'Shipment Created from ' . ($livraison->newref ? $livraison->newref : $livraison->ref),
                true,
                true
            ));
            //dol_syslog($this->client->__getLastResponse());
        } catch (SoapFault $fault) {
            $this->errors[] = $this->site->name . ': ' . $fault->getMessage() . ' - ' . $fault->getCode();
            dol_syslog(__METHOD__ . ': ' . $fault->getMessage() . '-' . $fault->getCode() . '-' . $fault->getTraceAsString(), LOG_WARNING);
            return false;
        }*/
        dol_syslog("eCommerceRemoteAccessWoocommerce createRemoteLivraison end");
        return $result;
    }




    /**
     * Calcul tax rate and return the closest dolibarr tax rate.
     *
     * @param float $priceHT         Price HT
     * @param float $taxAmount       Tax amount
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
                $rate = 0;
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
        //ini_set("memory_limit", "528M");
    }

}

