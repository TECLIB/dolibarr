<?php
/*
 * @module		ECommerce
 * @version		1.21
 * @copyright	Auguria
 * @author		<franck.charpentier@auguria.net>
 * @licence		GNU General Public License
 */

$res=0;
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res && file_exists("../../../../main.inc.php")) $res=@include("../../../../main.inc.php");
if (! $res && file_exists("../../../../../main.inc.php")) $res=@include("../../../../../main.inc.php");
if (! $res && preg_match('/\/nltechno([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res && preg_match('/\/teclib([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res) die("Include of main fails");
dol_include_once("/ecommerce/class/data/eCommerceSite.class.php");
if (!defined('DOL_CLASS_PATH'))
	define('DOL_CLASS_PATH', null);
if (DOL_CLASS_PATH == null)
	dol_include_once('/ecommerce/inc/pre.inc.php');
set_time_limit(600);

$langs->load("admin");
$langs->load("ecommerce@ecommerce");
//$langs->load("companies");
//$langs->load("users");
//$langs->load("orders");
//$langs->load("bills");
//$langs->load("contracts");

/***************************************************
* Check access
****************************************************/
//CHECK ACCESS
// Protection if external user
if ($user->societe_id > 0 || !$user->rights->ecommerce->read)
{
	accessforbidden();
}

/***************************************************
* Define page variables
****************************************************/

$eCommerceSite = new eCommerceSite($db);
$sites = $eCommerceSite->listSites();

/***************************************************
* Show page
****************************************************/

$urltpl=dol_buildpath('/ecommerce/tpl/index.tpl.php',0);
include($urltpl);

$db->close();
