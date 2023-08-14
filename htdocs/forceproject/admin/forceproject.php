<?php
/* Copyright (C) 2008-2013	Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012		Regis Houssin        <regis.houssin@capnetworks.com>
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
 */

/**
 *	    \file       htdocs/forceproject/admin/forceproject.php
 *      \ingroup    forceproject
 *      \brief      Page to setup module ForceProject
 */

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
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php');

$langs->load("admin");
$langs->load("other");
$langs->load("forceproject@forceproject");

$def = array();
$action=GETPOST('action', 'alpha');
$confirm=GETPOST('confirm', 'alpha');
$actionsave=GETPOST('save', 'alpha');

$modules = array();
//if ($conf->fournisseur->enabled) $modules['supplier_orders']='SuppliersOrders';
//if ($conf->fournisseur->enabled) $modules['supplier_invoices']='SuppliersInvoices';

if (!$user->admin) {
	accessforbidden();
}


/*
 * Actions
 */

$reg = array();
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_set_const($db, $code, 1, 'chaine', 0, '', 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}


/*
 * View
 */

$form=new Form($db);
$formfile=new FormFile($db);

$help = '';

llxHeader('','ForceProject',$help);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ForceProjectSetup"),$linkback,'setup');
print '<br>';

clearstatcache();


$h=0;
$head[$h][0] = $_SERVER["PHP_SELF"];
$head[$h][1] = $langs->trans("Setup");
$head[$h][2] = 'tabsetup';
$h++;

$head[$h][0] = 'about.php';
$head[$h][1] = $langs->trans("About");
$head[$h][2] = 'tababout';
$h++;

dol_fiche_head($head, 'tabsetup', '', -1);

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print "<td>&nbsp;</td>\n";
print '<td align="center" width="60">'.$langs->trans("Value").'</td>';
print "</tr>\n";
$var=true;


// Vars
$listofparams=array('FORCEPROJECT_ON_PROPOSAL','FORCEPROJECT_ON_ORDER','FORCEPROJECT_ON_ORDER_SUPPLIER','FORCEPROJECT_ON_INVOICE','FORCEPROJECT_ON_PROPOSAL_SUPPLIER','FORCEPROJECT_ON_INVOICE_SUPPLIER');
foreach($listofparams as $paramname)
{
	$var=!$var;
	print '<tr '.$bc[$var].'><td>';
	print $langs->trans($paramname).'</td><td>&nbsp</td><td align="center">';
	if (! empty($conf->use_javascript_ajax))
	{
		print ajax_constantonoff($paramname);
	}
	else
	{
		if (empty($conf->global->$paramname))
		{
			print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_'.$paramname.'&amp;value=1">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
		}
		else
		{
			print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_'.$paramname.'&amp;value=0">'.img_picto($langs->trans("Enabled"),'switch_on').'</a>';
		}
	}
	print '</td></tr>';
}

print '</table>';


dol_fiche_end();



// Footer
llxFooter();
// Close database handler
$db->close();

