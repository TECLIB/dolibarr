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
if (!defined('DOL_CLASS_PATH'))
	define('DOL_CLASS_PATH', null);
if (DOL_CLASS_PATH == null)
	dol_include_once('/ecommerce/inc/pre.inc.php');
dol_include_once("/ecommerce/class/business/eCommerceSynchro.class.php");
$langs->load("ecommerce@ecommerce");
$errors = array();
$success = array();
$site = null;

$langs->load("admin");
$langs->load("ecommerce");

//CHECK ACCESS
// || $user->getrights('magento')!=1
// Protection if external user
if ($user->societe_id > 0 || !$user->rights->ecommerce->read)
{
	accessforbidden();
}

$id=GETPOST('id','int');
$error=0;


/*******************************************************************
* ACTIONS
********************************************************************/

if ($id)
{
	try
	{
		$site= new eCommerceSite($db);
		$site->fetch($id);
		if (isset($site->timeout))
			set_time_limit($site->timeout);
		
		$synchro = new eCommerceSynchro($db, $site);
		if (! is_object($synchro) || count($synchro->errors))
		{
		    $error++;
		}
		
		$result=0;
		
		if (! $error)
		{
		  $result=$synchro->checkAnonymous();
		}

		if ($result <= 0)
		{
			$errors = $synchro->errors;
			$error = $synchro->error;
		}
		else
		{
			//synch only with write rights
			if ($user->rights->ecommerce->write)
			{
				if ($_POST['reset_data'])
				{
					$synchro->dropImportedAndSyncData();
				}
				elseif ($_POST['submit_synchro_societe'])
				{
					$synchro->synchSociete();
				}
				elseif ($_POST['submit_synchro_commande'])
				{
					$synchro->synchCommande();
				}
				elseif ($_POST['submit_synchro_product'])
				{
					$synchro->synchProduct();
				}
				elseif ($_POST['submit_synchro_facture']||$_REQUEST['submit_synchro_all'])
				{
					$synchro->synchFacture();
				}
			}

			
        	/***************************************************
        	* PAGE
        	*
        	* Put here all vars to build tpl page
        	****************************************************/
			if (! $error) $nbSocieteToUpdate = $synchro->getNbSocieteToUpdate(true);
			if ($nbSocieteToUpdate < 0) $error++;
			if (! $error) $nbProductToUpdate = $synchro->getNbProductToUpdate(true);
			if ($nbProductToUpdate < 0) $error++;
			if (! $error) $nbCommandeToUpdate = $synchro->getNbCommandeToUpdate(true);
			if ($nbCommandeToUpdate < 0) $error++;
			if (! $error) $nbFactureToUpdate = $synchro->getNbFactureToUpdate(true);
			if ($nbFactureToUpdate < 0) $error++;

			if ($user->rights->ecommerce->write)
				$synchRights = true;
			
			if (count($synchro->success))
				$success = $synchro->success;
			
			if (count($synchro->errors))
				$errors = $synchro->errors;
		}
	}
	catch (Exception $e)
	{
		$errors[] = $langs->trans('ECommerceSiteErrorConnect');
	}
}

/***************************************************
* Show page
****************************************************/
$urltpl=dol_buildpath('/ecommerce/tpl/site.tpl.php',0);
include($urltpl);

$db->close();
