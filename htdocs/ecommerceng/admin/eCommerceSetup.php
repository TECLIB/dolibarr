<?php
/* Copyright (C) 2010 Franck Charpentier - Auguria <franck.charpentier@auguria.net>
 * Copyright (C) 2016 Laurent Destailleur          <eldy@users.sourceforge.net>
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

/* PAGE setup ecommerce */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once(DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php');
require_once(DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php');
dol_include_once('/ecommerceng/class/data/eCommerceSite.class.php');
dol_include_once('/ecommerceng/admin/class/gui/eCommerceMenu.class.php');
dol_include_once('/ecommerceng/lib/eCommerce.lib.php');


$langs->load('admin');
$langs->load('companies');
$langs->load('ecommerce@ecommerceng');

$siteId = null;
$errors = array();
$success = array();
//CHECK ACCESS
if (!$user->admin || !$user->rights->ecommerceng->site)
    accessforbidden();

//DATABASE ACCESS
$siteDb = new eCommerceSite($db);

$sites = $siteDb->listSites();
$siteTypes = $siteDb->getSiteTypes();
$siteTypesLabel = $siteDb->getSiteTypesLabel();
$site_form_select_site = 0;

// Set $site_form_select_site on first site.
if (count($sites))
{
    foreach ($sites as $option)
    {
        $site_form_select_site = $option['id'];
        break;
    }
}

//LOAD SELECTED SITE
if (isset($_POST['site_form_select_site']))
    $siteId = $_POST['site_form_select_site'];
elseif (isset($_POST['ecommerce_id']))
    $siteId = $_POST['ecommerce_id'];
elseif ($site_form_select_site)
    $siteId = $site_form_select_site;

if ($siteId != null)
    $siteDb->fetch($siteId);


/*
 * Actions
 */

if ($_POST['site_form_detail_action'] == 'save')
{
    if (trim($_POST['ecommerce_name']) == '')
        $errors[] = $langs->trans('ECommerceSetupNameEmpty');
    if ($_POST['ecommerce_fk_cat_product'] == 0)
        $errors[] = $langs->trans('ECommerceSetupCatProductEmpty');
    if ($_POST['ecommerce_fk_cat_societe'] == 0)
        $errors[] = $langs->trans('ECommerceSetupCatSocieteEmpty');
    if ($_POST['ecommerce_type'] == 0)
        $errors[] = $langs->trans('ECommerceSetupTypeEmpty');
    if (! ($_POST['ecommerce_fk_warehouse'] > 0) && $_POST['ecommerce_stock_sync_direction'] == 'ecommerce2dolibarr')
        setEventMessages($langs->trans('WarningStockProductNotFilled'), null, 'warnings');
    if (trim($_POST['ecommerce_webservice_address']) == '')
        $errors[] = $langs->trans('ECommerceSetupAddressEmpty');
    /*if (trim($_POST['ecommerce_timeout']) == '')
        $errors[] = $langs->trans('ECommerceSetupTimeoutEmpty');
    elseif (!ctype_digit($_POST['ecommerce_timeout']))
        $errors[] = $langs->trans('ECommerceSetupTimeoutMustBeInt');*/

    if ($errors == array())
    {
        $db->begin();
        $last_price_level = $siteDb->price_level;
        $siteDb->name = $_POST['ecommerce_name'];
        $siteDb->type = $_POST['ecommerce_type'];
        $siteDb->webservice_address = $_POST['ecommerce_webservice_address'];
        $siteDb->user_name = $_POST['ecommerce_user_name'];
        $siteDb->user_password = $_POST['ecommerce_user_password'];
        $siteDb->price_level = $_POST['ecommerce_price_level'];
        $siteDb->filter_label = $_POST['ecommerce_filter_label'];
        $siteDb->filter_value = $_POST['ecommerce_filter_value'];
        $siteDb->fk_cat_societe = $_POST['ecommerce_fk_cat_societe'];
        $siteDb->fk_cat_product = $_POST['ecommerce_fk_cat_product'];
        $siteDb->fk_warehouse = $_POST['ecommerce_fk_warehouse'];
        $siteDb->stock_sync_direction = $_POST['ecommerce_stock_sync_direction'];
        $siteDb->last_update = $_POST['ecommerce_last_update'];
        //$siteDb->timeout = $_POST['ecommerce_timeout'];
        $siteDb->magento_use_special_price = ($_POST['ecommerce_magento_use_special_price'] ? 1 : 0);
        $siteDb->magento_price_type = $_POST['ecommerce_magento_price_type'];

        // TODO Save this into table of ecommerce_site, field fk_thirdparty instead of global var.
        dolibarr_set_const($db, 'ECOMMERCENG_USE_THIS_THIRDPARTY_FOR_NONLOGGED_CUSTOMER', GETPOST('ECOMMERCENG_USE_THIS_THIRDPARTY_FOR_NONLOGGED_CUSTOMER','int'));
        dolibarr_set_const($db, 'ECOMMERCENG_BANK_ID_FOR_PAYMENT', GETPOST('ECOMMERCENG_BANK_ID_FOR_PAYMENT','int'));

        $result = 0;
        if (intval($_POST['ecommerce_id']))
        {
            $siteDb->id = $_POST['ecommerce_id'];
            $result = $siteDb->update($user);
        } else
        {
            $result = $siteDb->create($user);
        }

        if ($result > 0)
        {
            $sites = $siteDb->listSites();  // Reload after create

            $eCommerceMenu = new eCommerceMenu($db, $siteDb);
            $eCommerceMenu->updateMenu();
            $db->commit();

            if (!empty($conf->global->PRODUIT_MULTIPRICES) && $siteDb->price_level != $last_price_level) {
                updatePriceLevel($siteDb);
            }

            setEventMessages($langs->trans('ECommerceSetupSaved'), null);
        } else
        {
            $db->rollback();
            setEventMessages($siteDb->error, $siteDb->errors, 'errors');
        }
    }
    else
    {
        setEventMessages('', $errors, 'errors');
    }
}
//DELETE
elseif ($_POST['site_form_detail_action'] == 'delete')
{
    $siteDb->id = $_POST['ecommerce_id'];
    $result = $siteDb->delete($user);
    if ($result < 0)
    {
        setEventMessages($langs->trans('ECommerceDeleteErrorDb'), null, 'errors');
    }
    else
    {
        $sites = $siteDb->listSites();  // Reload after delete

        $eCommerceMenu = new eCommerceMenu($db, $siteDb);
        $eCommerceMenu->updateMenu();
        $success[] = $langs->trans('ECommerceDeleteOk');
        $siteDb->id = null;
        unset($_POST);
    }
}



/*
 *  View
 */

$classCategorie = new Categorie($db);
$productCategories = $classCategorie->get_full_arbo('product');
$societeCategories = $classCategorie->get_full_arbo('customer');

if (!empty($conf->global->PRODUIT_MULTIPRICES)) {
    $priceLevels = array();
	for ($i = 1; $i <= $conf->global->PRODUIT_MULTIPRICES_LIMIT; $i++) {
        $keyforlabel = 'PRODUIT_MULTIPRICES_LABEL'.$i;
        $priceLevels[$i] = !empty($conf->global->$keyforlabel) ? $langs->trans($conf->global->$keyforlabel) : $i;
    }
}

//SET VARIABLES
$ecommerceId = ($_POST['ecommerce_id'] ? $_POST['ecommerce_id'] : $siteDb->id);
$ecommerceName = ($_POST['ecommerce_name'] ? $_POST['ecommerce_name'] : $siteDb->name);
$ecommerceType = ($_POST['ecommerce_type'] ? $_POST['ecommerce_type'] : intval($siteDb->type));
$ecommerceWebserviceAddress = ($_POST['ecommerce_webservice_address'] ? $_POST['ecommerce_webservice_address'] : $siteDb->webservice_address);
$ecommerceUserName = ($_POST['ecommerce_user_name'] ? $_POST['ecommerce_user_name'] : $siteDb->user_name);
$ecommerceUserPassword = ($_POST['ecommerce_user_password'] ? $_POST['ecommerce_user_password'] : $siteDb->user_password);
$ecommercePriceLevel = ($_POST['ecommerce_price_level'] ? $_POST['ecommerce_price_level'] : $siteDb->price_level);
$ecommerceFilterLabel = ($_POST['ecommerce_filter_label'] ? $_POST['ecommerce_filter_label'] : $siteDb->filter_label);
$ecommerceFilterValue = ($_POST['ecommerce_filter_value'] ? $_POST['ecommerce_filter_value'] : $siteDb->filter_value);
$ecommerceFkCatSociete = ($_POST['ecommerce_fk_cat_societe'] ? $_POST['ecommerce_fk_cat_societe'] : intval($siteDb->fk_cat_societe));
$ecommerceFkCatProduct = ($_POST['ecommerce_fk_cat_product'] ? $_POST['ecommerce_fk_cat_product'] : intval($siteDb->fk_cat_product));
$ecommerceFkWarehouse = ($_POST['ecommerce_fk_warehouse'] ? $_POST['ecommerce_fk_warehouse'] : intval($siteDb->fk_warehouse));
$ecommerceStockSyncDirection = ($_POST['ecommerce_stock_sync_direction'] ? $_POST['ecommerce_stock_sync_direction'] : $siteDb->stock_sync_direction);
$ecommerceMagentoUseSpecialPrice = ($_POST['ecommerce_magento_use_special_price'] ? $_POST['ecommerce_magento_use_special_price'] : intval($siteDb->magento_use_special_price));
$ecommerceMagentoPriceType = ($_POST['ecommerce_magento_price_type'] ? $_POST['ecommerce_magento_price_type'] : $siteDb->ecommerce_magento_price_type);
/*$ecommerceTimeout = 300;
if (isset($_POST['ecommerce_timeout']))
    $ecommerceTimeout = $_POST['ecommerce_timeout'];
elseif (isset($siteDb->timeout))
    $ecommerceTimeout = $siteDb->timeout;*/

$ecommerceLastUpdate = $siteDb->last_update;
$var = true;
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
$title = '';
if ($siteDb->name)
    $title = $langs->trans('ECommerceSetupSite') . ' ' . $siteDb->name;
else
    $title = $langs->trans('ECommerceCreateSite');

//SHOW PAGE
$urltpl=dol_buildpath('/ecommerceng/admin/tpl/eCommerceSetup.tpl.php',0);
include($urltpl);

$soapwsdlcacheon = ini_get('soap.wsdl_cache_enabled');
$soapwsdlcachedir = ini_get('soap.wsdl_cache_dir');
if ($soapwsdlcacheon)
{
    print img_warning('').' '.$langs->trans("WarningSoapCacheIsOn", $soapwsdlcachedir).' ';
    print $langs->trans("WarningSoapCacheIsOn2", $langs->transnoentitiesnoconv("ECommerceSiteAddress")).'<br>';
}
else
{
    print $langs->trans("SoapCacheIsOff", $soapwsdlcachedir).'<br>';
}

llxFooter();


$db->close();
clearstatcache();
