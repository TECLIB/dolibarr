<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *   	\file       advanceddiscount_card.php
 *		\ingroup    advanceddiscount
 *		\brief      Page to create/edit/view advanceddiscount
 */

//if (! defined('NOREQUIREUSER'))          define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))            define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))           define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))          define('NOREQUIRETRAN','1');
//if (! defined('NOSCANGETFORINJECTION'))  define('NOSCANGETFORINJECTION','1');			// Do not check anti CSRF attack test
//if (! defined('NOSCANPOSTFORINJECTION')) define('NOSCANPOSTFORINJECTION','1');		// Do not check anti CSRF attack test
//if (! defined('NOCSRFCHECK'))            define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test done when option MAIN_SECURITY_CSRF_WITH_TOKEN is on.
//if (! defined('NOSTYLECHECK'))           define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL'))         define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))          define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))          define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))          define('NOREQUIREAJAX','1');         // Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php');
dol_include_once('/advanceddiscount/class/advanceddiscount.class.php');
dol_include_once('/advanceddiscount/lib/advanceddiscount.lib.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("advanceddiscount@advanceddiscount","other"));

// Get parameters
$id			= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object=new AdvancedDiscount($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->advanceddiscount->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('advanceddiscountcard'));     // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options=$extrafields->getOptionalsFromPost($object->table_element,'','search_');

// Initialize array of search criterias
$search_all=trim(GETPOST("search_all",'alpha'));
$search=array();
foreach($object->fields as $key => $val)
{
    if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action='view';

// Security check - Protection if external user
//if ($user->societe_id > 0) accessforbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$result = restrictedArea($user, 'advanceddiscount', $id);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

$permissiontoadd = $user->rights->advanceddiscount->write;
$permissiontodelete = $user->rights->advanceddiscount->delete;


/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	$error=0;

	if (empty($backtopage)) $backtopage = dol_buildpath('/advanceddiscount/advanceddiscount_card.php',1).'?id='.($id?$id:'__ID__');
	$backurlforlist = dol_buildpath('/advanceddiscount/advanceddiscount_list.php',1);
	$triggermodname = 'ADVANCEDDISCOUNT_ADVANCEDDISCOUNT_MODIFY';	// Name of trigger action code to execute when we modify record

	// Actions cancel, add, update or delete
	//include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';	// v7 is not working
	include_once './core/actions_addupdatedelete.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emails
	$trigger_name='ADVANCEDDISCOUNT_SENTBYMAIL';
	$autocopy='MAIN_MAIL_AUTOCOPY_ADVANCEDDISCOUNT_TO';
	$trackid='advanceddiscount'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';

	if ($action == 'addrule' && ! empty($user->rights->advanceddiscount->write))
	{
		if (! GETPOST('type_rule','alpha') || GETPOST('type_rule','alpha') == '-1')
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Type")), null, 'errors');
			$error++;
		}
		if (GETPOST('value_rule','alpha') == '')
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Value")), null, 'errors');
			$error++;
		}

		if (! $error)
		{
			$sqlinsert = 'INSERT INTO '.MAIN_DB_PREFIX.'advanceddiscount_rules (fk_advanceddiscount, type_rule, value_rule)';
			$sqlinsert.= ' VALUES ('.$object->id.", '".$db->escape(GETPOST('type_rule','alpha'))."', '".$db->escape(GETPOST('value_rule','alpha'))."'";
			$sqlinsert.= ')';
			$resql = $db->query($sqlinsert);
			if (! $resql)
			{
				dol_print_error($db);
			}
			else
			{
				header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
				exit;
			}
		}
	}

	if ($action == 'addaction' && ! empty($user->rights->advanceddiscount->write))
	{
		if (! GETPOST('type_action','alpha') || GETPOST('type_action','alpha') == '-1')
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Type")), null, 'errors');
			$error++;
		}
		if (GETPOST('value_action','alpha') == '')
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Value")), null, 'errors');
			$error++;
		}
		if (in_array(GETPOST('type_action','alpha'), array('itemfixeddiscount', 'itempercentagediscount')))
		{
			$rulenotfound=1;
			foreach($object->arrayofrules as $rule)
			{
				if (in_array($rule['type'], array('containsproduct')))
				{
					$rulenotfound=0;
					break;
				}
			}
			if ($rulenotfound)
			{
				setEventMessages($langs->trans("ErrorCanAddItemActionIfNoItemRule"), null, 'errors');
				$error++;
			}
		}
		if (GETPOST('value_action','alpha') < 0)
		{
			setEventMessages($langs->trans("ErrorValueCantBeNegative"), null, 'errors');
			$error++;
		}
		if (! $error)
		{
			$sqlinsert = 'INSERT INTO '.MAIN_DB_PREFIX.'advanceddiscount_actions (fk_advanceddiscount, type_action, value_action)';
			$sqlinsert.= ' VALUES ('.$object->id.", '".$db->escape(GETPOST('type_action','alpha'))."', '".$db->escape(GETPOST('value_action','alpha'))."'";
			$sqlinsert.= ')';
			$resql = $db->query($sqlinsert);
			if (! $resql)
			{
				dol_print_error($db);
			}
			else
			{
				header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
				exit;
			}
		}
	}


	if ($action == 'deleterule' && ! empty($user->rights->advanceddiscount->write))
	{
		$sqldelete = 'DELETE FROM '.MAIN_DB_PREFIX.'advanceddiscount_rules WHERE rowid = '.GETPOST('ruleid','int');
		$resql = $db->query($sqldelete);
		if (! $resql)
		{
			dol_print_error($db);
		}
		else
		{
			header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
			exit;
		}
	}

	if ($action == 'deleteaction' && ! empty($user->rights->advanceddiscount->write))
	{
		$sqldelete = 'DELETE FROM '.MAIN_DB_PREFIX.'advanceddiscount_actions WHERE rowid = '.GETPOST('actionid','int');
		$resql = $db->query($sqldelete);
		if (! $resql)
		{
			dol_print_error($db);
		}
		else
		{
			header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
			exit;
		}
	}
}



/*
 * View
 *
 * Put here all code to build page
 */

$form=new Form($db);
$formfile=new FormFile($db);

llxHeader('','AdvancedDiscount','');

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("AdvancedDiscount")));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage?"submit":"button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage?'':' onclick="javascript:history.go(-1)"').'>';	// Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("AdvancedDiscount"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	dol_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
    $res = $object->fetch_optionals();

	$head = advanceddiscountPrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans("AdvancedDiscount"), -1, 'advanceddiscount@advanceddiscount');

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete')
	{
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteAdvancedDiscount'), $langs->trans('ConfirmDeleteAdvancedDiscount'), 'confirm_delete', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('CloneAdvancedDiscount'), $langs->trans('ConfirmCloneAdvancedDiscount', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx')
	{
	    $formquestion=array();
	    /*
	     $forcecombo=0;
	     if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
	     $formquestion = array(
	     // 'text' => $langs->trans("ConfirmClone"),
	     // array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
	     // array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
	     // array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
	     );
	     */
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	if (! $formconfirm) {
	    $parameters = array('lineid' => $lineid);
	    $reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	    if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
	    elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/advanceddiscount/advanceddiscount_list.php', 1).'?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref='<div class="refidno">';
	/*
	// Ref bis
	$morehtmlref.=$form->editfieldkey("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->advanceddiscount->creer, 'string', '', 0, 1);
	$morehtmlref.=$form->editfieldval("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->advanceddiscount->creer, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $soc->getNomUrl(1);
	// Project
	if (! empty($conf->projet->enabled))
	{
	    $langs->load("projects");
	    $morehtmlref.='<br>'.$langs->trans('Project') . ' ';
	    if ($permissiontoadd)
	    {
	        if ($action != 'classify')
	        {
	            $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
	            if ($action == 'classify') {
	                //$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	                $morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	                $morehtmlref.='<input type="hidden" name="action" value="classin">';
	                $morehtmlref.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	                $morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	                $morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	                $morehtmlref.='</form>';
	            } else {
	                $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	            }
	        }
	    } else {
	        if (! empty($object->fk_project)) {
	            $proj = new Project($db);
	            $proj->fetch($object->fk_project);
	            $morehtmlref.=$proj->getNomUrl();
	        } else {
	            $morehtmlref.='';
	        }
	    }
	}
	*/
	$morehtmlref.='</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	dol_fiche_end();


	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
    	print '<div class="tabsAction">'."\n";
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
    	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

    	if (empty($reshook))
    	{
    	    // Send
            //print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

    		if ($user->rights->advanceddiscount->write)
    		{
    			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>'."\n";
    		}
    		else
    		{
    			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
    		}

    		// Clone
    		if ($user->rights->advanceddiscount->write)
    		{
    			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=clone&amp;object=order">' . $langs->trans("ToClone") . '</a></div>';
    		}

    		/*
    		if ($permissiontoadd)
    		{
    			if ($object->status == 1)
    		 	{
    		 		print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=disable">'.$langs->trans("Disable").'</a>'."\n";
    		 	}
    		 	else
    		 	{
    		 		print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=enable">'.$langs->trans("Enable").'</a>'."\n";
    		 	}
    		}
    		*/

    		// Delete (need delete permission, or if draft, just need create/modify permission)
    		if ($permissiontodelete)
    		{
    			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>'."\n";
    		}
    		else
    		{
    			print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
    		}
    	}
    	print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
	    $action = 'presend';
	}

	if ($action != 'presend')
	{
	    print '<div class="fichecenter"><div class="fichehalfleft">';
	    print '<a name="builddoc"></a>'; // ancre

	    // Documents
	    /*$objref = dol_sanitizeFileName($object->ref);
	    $relativepath = $comref . '/' . $comref . '.pdf';
	    $filedir = $conf->advanceddiscount->dir_output . '/' . $objref;
	    $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	    $genallowed = $user->rights->advanceddiscount->read;	// If you can read, you can build the PDF to read content
	    $delallowed = $user->rights->advanceddiscount->create;	// If you can create/edit, you can remove a file on card
	    print $formfile->showdocuments('advanceddiscount', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
		*/

	    // Show links to link elements
	    //$linktoelem = $form->showLinkToObjectBlock($object, null, array('advanceddiscount'));
	    //$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	    //$buttontoadd = '<a href="'.$_SERVER["PHP_SELF"].'?action=addrule&id='.$id.'">'.$langs->trans("Add").'</a>';
	    $buttontoadd = '';

	    print '<!-- rules -->'."\n";
	    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	    print '<input type="hidden" name="action" value="addrule">';
	    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	    print '<input type="hidden" name="id" value="'.$object->id.'">';

	    print load_fiche_titre($langs->trans("Rules"), $buttontoadd, '', 0, 0, '', '');

	    print '<div class="div-table-responsive-no-min">';
	    print '<table class="noborder allwidth">';
	    print '<tr class="liste_titre">';
	    print '<td>'.$langs->trans("Type").'</td><td>'.$langs->trans("Value").'</td><td></td>';
	    print '</tr>';

    	print '<tr class="liste_titre">';
    	print '<td>';
    	$arrayforselectrule=array(
    		'customercountry'=>'CustomerCountryIs',
    		'customercategory'=>'CustomerHasTag',
    		'containsproduct'=>'ContainsProduct',
    		'totalgreaterorequal'=>'TotalGreaterThanOrEqualTo'
    	);
    	if (! empty($conf->commande->enabled))
    	{
    		//$arrayforselect['HasAlreadyNOrder']='norder';
    	}
    	print $form->selectarray('type_rule', $arrayforselectrule, GETPOST('type_rule'), 1, 0, 0, '', 1);
    	print '</td>';
    	print '<td>';
    	print '<input type="text" class="maxwidth100" name="value_rule" value="'.GETPOST('value_rule','alpha').'">';
    	print '</td>';
    	print '<td>';
    	print '<input type="submit" class="button" name="addrule"'.(empty($user->rights->advanceddiscount->write)?' disabled':'').' value="'.$langs->trans("Add").'">';
    	print '</td>';
    	print '</tr>';

	    foreach($object->arrayofrules as $objrule)
		{
			print '<tr>';
			print '<td>';
			print $langs->trans($arrayforselectrule[$objrule['type']]);
			print '</td>';
			print '<td>';
			print $objrule['value'];
			print '</td>';
			print '<td class="right">';
			print '<a href="'.$_SERVER["PHP_SELF"].'?action=deleterule&ruleid='.$objrule['id'].'&id='.$object->id.'">'.img_delete().'</a>';
			print '</td>';
			print '</tr>';
		}
		print '</table>';
		print '</div>';

		print '</form>';

	    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	    /*$MAXEVENT = 10;

	    $morehtmlright = '<a href="'.dol_buildpath('/advanceddiscount/advanceddiscount_info.php', 1).'?id='.$object->id.'">';
	    $morehtmlright.= $langs->trans("SeeAll");
	    $morehtmlright.= '</a>';

	    // List of actions on element
	    include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	    $formactions = new FormActions($db);
	    //$somethingshown = $formactions->showactions($object, 'advanceddiscount', $socid, 1, '', $MAXEVENT, '', $morehtmlright);
		*/

	    //$buttontoadd = '<a href="'.$_SERVER["PHP_SELF"].'?action=addaction&id='.$id.'">'.$langs->trans("Add").'</a>';
	    $buttontoadd = '';

	    print '<!-- actions -->'."\n";
	    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	    print '<input type="hidden" name="action" value="addaction">';
	    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	    print '<input type="hidden" name="id" value="'.$object->id.'">';

	    print load_fiche_titre($langs->trans("Actions"), $buttontoadd, '', 0, 0, '', '');

	    print '<div class="div-table-responsive-no-min">';
	    print '<table class="noborder allwidth">';
	    print '<tr class="liste_titre">';
	    print '<td>'.$langs->trans("Type").'</td><td>'.$langs->trans("Value").'</td><td></td>';
	    print '</tr>';

    	print '<tr class="liste_titre">';
    	print '<td>';
    	$arrayforselectaction=array(
    		'objectfixeddiscount'=>'ObjectFixedDiscount',
    		'objectpercentagediscount'=>'ObjectPercentDiscount',
    		//'itemfixeddiscount'=>'ItemFixedDiscount',
    		'itempercentagediscount'=>'ItemPercentDiscount'
    	);
    	if (! empty($conf->commande->enabled))
    	{
    		//$arrayforselect['HasAlreadyNOrder']='norder';
    	}
    	print $form->selectarray('type_action', $arrayforselectaction, GETPOST('type_action'), 1, 0, 0, '', 1);
    	print '</td>';
    	print '<td>';
    	print '<input type="text" class="maxwidth100" name="value_action" value="'.GETPOST('value_action','alpha').'">';
    	print '</td>';
    	print '<td>';
    	print '<input type="submit" class="button" name="addaction"'.(empty($user->rights->advanceddiscount->write)?' disabled':'').' value="'.$langs->trans("Add").'">';
    	print '</td>';
    	print '</tr>';

	    foreach($object->arrayofactions as $objaction)
	    {
	    	print '<tr>';
	    	print '<td>';
	    	print $langs->trans($arrayforselectaction[$objaction['type']]);
	    	print '</td>';
	    	print '<td>';
	    	print $objaction['value'];
	    	print '</td>';
	    	print '<td class="right">';
	    	print '<a href="'.$_SERVER["PHP_SELF"].'?action=deleteaction&actionid='.$objaction['id'].'&id='.$object->id.'">'.img_delete().'</a>';
	    	print '</td>';
	    	print '</tr>';
	    }
	    print '</table>';
		print '</div>';

		print '<br><span class="opacitymedium">'.$langs->trans("NoteWhenDiscountApplied").'</span>';

		print '</form>';
		
	    print '</div></div></div>';
	}

	//Select mail models is same action as presend
	/*
	 if (GETPOST('modelselected')) $action = 'presend';

	 // Presend form
	 $modelmail='inventory';
	 $defaulttopic='InformationMessage';
	 $diroutput = $conf->product->dir_output.'/inventory';
	 $trackid = 'stockinv'.$object->id;

	 include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
	 */
}


// End of page
llxFooter();
$db->close();
