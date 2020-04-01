<?php
/* Copyright (C) 2019 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *   	\file       justificativedocument_card.php
 *		\ingroup    justificativedocuments
 *		\brief      Page to create/edit/view justificativedocument
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC','1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK','1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX','1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN",'1');						// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT','auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE','aloginmodule');		// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN',1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP','none');					// Disable all Content Security Policies


// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/justificativedocuments/class/justificativedocument.class.php');
dol_include_once('/justificativedocuments/lib/justificativedocuments_justificativedocument.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("justificativedocuments@justificativedocuments","other"));

// Get parameters
$id			= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage= GETPOST('contextpage', 'aZ')?GETPOST('contextpage', 'aZ'):'justificativedocumentcard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
//$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object=new JustificativeDocument($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->justificativedocuments->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('justificativedocumentcard','globalcard'));     // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options=$extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all=trim(GETPOST("search_all", 'alpha'));
$search=array();
foreach($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key, 'alpha')) $search[$key]=GETPOST('search_'.$key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action='view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once.

// Security check - Protection if external user
//if ($user->societe_id > 0) accessforbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$isdraft = (($object->statut == JustificativeDocument::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'justificativedocuments', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

$permissionnote = $user->rights->justificativedocuments->justificativedocument->write;		// Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->justificativedocuments->justificativedocument->write;	// Used by the include of actions_dellink.inc.php
$permissiontoadd = $user->rights->justificativedocuments->justificativedocument->write; 	// Used by the include of actions_addupdatedelete.inc.php// Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->justificativedocuments->justificativedocument->delete || ($permissiontoadd && $object->status == 0);
$permissiontoapprove = $user->rights->justificativedocuments->justificativedocument->approve;


/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
    $error=0;

    $backurlforlist = dol_buildpath('/justificativedocuments/justificativedocument_list.php', 1);

    if (empty($backtopage) || ($cancel && empty($id))) {
        if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
            if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
	    	else $backtopage = dol_buildpath('/justificativedocuments/justificativedocument_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
        }
    }
    $triggermodname = 'JUSTIFICATIVEDOCUMENTS_JUSTIFICATIVEDOCUMENT_MODIFY';	// Name of trigger action code to execute when we modify record

    // Actions cancel, add, update, confirm_validate, delete or clone
    include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

    // Actions when linking object each other
    include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

    // Actions when printing a doc from card
    include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

    // Action to move up and down lines of object
    //include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';	// Must be include, not include_once

    if ($action == 'set_thirdparty' && $permissiontoadd)
    {
    	$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, 'JUSTIFICATIVEDOCUMENT_MODIFY');
    }
    if ($action == 'classin' && $permissiontoadd)
    {
    	$object->setProject(GETPOST('projectid', 'int'));
    }

    // Action approve object
    if ($action == 'confirm_approve' && $confirm == 'yes' && $permissiontoadd)
    {
        $result = $object->approve($user);
        if ($result >= 0)
        {
            // Define output language
            if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
            {
                $outputlangs = $langs;
                $newlang = '';
                if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
                if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
                if (!empty($newlang)) {
                    $outputlangs = new Translate("", $conf);
                    $outputlangs->setDefaultLang($newlang);
                }
                $model = $object->modelpdf;
                $ret = $object->fetch($id); // Reload to get new records

                $object->generateDocument($model, $outputlangs, 0, 0, 0);
            }
        }
        else
        {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    }

    // Actions to send emails
    $trigger_name='JUSTIFICATIVEDOCUMENT_SENTBYMAIL';
    $autocopy='MAIN_MAIL_AUTOCOPY_JUSTIFICATIVEDOCUMENT_TO';
    $trackid='justificativedocument'.$object->id;
    include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 */

$form=new Form($db);
$formfile=new FormFile($db);

llxHeader('', $langs->trans('JustificativeDocument'), '');

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
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("JustificativeDocument")));

	print '<span class="opacitymedium">'.$langs->trans("EnterHereOnlyJustificativeDocument").'</span><br><br>';

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	$object->fields = dol_sort_array($object->fields, 'position');

	foreach($object->fields as $key => $val)
	{
	    // Discard if extrafield is a hidden field on form
	    if (abs($val['visible']) != 1 && abs($val['visible']) != 3) continue;

	    if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! verifCond($val['enabled'])) continue;	// We don't want this field

	    // Show field for type
	    /*if ($key == 'fk_type')
	    {
    	    print '<tr id="field_'.$key.'">';
    	    print '<td class="titlefieldcreate fieldrequired">';
    	    print $langs->trans("Type");
    	    print '</td><td>';
    	    $array = array('ee'=>'rr');
    	    print $form->selectarray('fk_type', $array);
    	    print '</td>';
    	    print '</tr>';
	    }
	    else*/
	    if ($key == 'fk_user')
	    {
	        print '<tr id="field_'.$key.'">';
	        print '<td class="titlefieldcreate fieldrequired">';
	        print $langs->trans("User");
	        print '</td><td>';
	        //$array = array('ee'=>'rr');
	        $include = 'hierarchyme';
	        if (! empty($user->rights->justificativedocuments->justificativedocument->write_all)) $include = '';
	        print $form->select_dolusers($user->id, 'fk_user', 0, null, 0, $include);
	        print '</td>';
	        print '</tr>';
	    } else {
    	    print '<tr id="field_'.$key.'">';
    	    print '<td';
    	    print ' class="titlefieldcreate';
    	    if ($val['notnull'] > 0) print ' fieldrequired';
    	    if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
    	    print '"';
    	    print '>';
    	    if (! empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
    	    else print $langs->trans($val['label']);
    	    print '</td>';
    	    print '<td>';
    	    if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
    	    elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
    	    else $value = GETPOST($key, 'alpha');
    	    print $object->showInputField($val, $key, $value, '', '', '', 0);
    	    print '</td>';
    	    print '</tr>';
	    }
	}

	// Common attributes
	//include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

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

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("JustificativeDocument"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	dol_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	$object->fields = dol_sort_array($object->fields, 'position');

	foreach($object->fields as $key => $val)
	{
	    // Discard if extrafield is a hidden field on form
	    if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4) continue;

	    if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! verifCond($val['enabled'])) continue;	// We don't want this field

	    // Show field for type
	    /*if ($key == 'fk_type')
	    {
	        print '<tr id="field_'.$key.'">';
	        print '<td class="titlefieldcreate fieldrequired">';
	        print $langs->trans("Type");
	        print '</td><td>';
	        $array = array('ee'=>'rr');
	        print $form->selectarray('fk_type', $array);
	        print '</td>';
	        print '</tr>';
	    }
	    else */
	    if ($key == 'fk_user')
	    {
	        print '<tr id="field_'.$key.'">';
	        print '<td class="titlefieldcreate fieldrequired">';
	        print $langs->trans("User");
	        print '</td><td>';
	        //$array = array('ee'=>'rr');
	        $include = 'hierarchyme';
	        if (! empty($user->rights->justificativedocuments->justificativedocument->write_all)) $include = '';
	        print $form->select_dolusers($object->fk_user, 'fk_user', 0, null, 0, $include);
	        print '</td>';
	        print '</tr>';
	    } else {
    	    print '<tr><td';
    	    print ' class="titlefieldcreate';
    	    if ($val['notnull'] > 0) print ' fieldrequired';
    	    if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
    	    print '">';
    	    if (! empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
    	    else print $langs->trans($val['label']);
    	    print '</td>';
    	    print '<td>';
    	    if (in_array($val['type'], array('int', 'integer'))) $value = GETPOSTISSET($key)?GETPOST($key, 'int'):$object->$key;
    	    elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOSTISSET($key)?GETPOST($key, 'none'):$object->$key;
    	    else $value = GETPOSTISSET($key)?GETPOST($key, 'alpha'):$object->$key;
    	    //var_dump($val.' '.$key.' '.$value);
    	    if ($val['noteditable']) print $object->showOutputField($val, $key, $value, '', '', '', 0);
    	    else print $object->showInputField($val, $key, $value, '', '', '', 0);
    	    print '</td>';
    	    print '</tr>';
	    }
	}

	// Common attributes
	//include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

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

	$head = justificativedocumentPrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans("JustificativeDocument"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete')
	{
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteJustificativeDocument'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline')
	{
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneJustificativeDocument', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
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

	// Call Hook formConfirm
	$parameters = array('lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/justificativedocuments/justificativedocument_list.php', 1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref='<div class="refidno">';
	/*
	// Ref bis
	$morehtmlref.=$form->editfieldkey("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->justificativedocuments->justificativedocument->creer, 'string', '', 0, 1);
	$morehtmlref.=$form->editfieldval("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->justificativedocuments->justificativedocument->creer, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1);
	// Project
	if (! empty($conf->projet->enabled))
	{
	    $langs->load("projects");
	    $morehtmlref.='<br>'.$langs->trans('Project') . ' ';
	    if ($user->rights->justificativedocuments->justificativedocument->write)
	    {
	        if ($action != 'classify')
	            $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
            if ($action == 'classify') {
                //$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
                $morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
                $morehtmlref.='<input type="hidden" name="action" value="classin">';
                $morehtmlref.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
                $morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', 0, 0, 1, 0, 1, 0, 0, '', 1);
                $morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
                $morehtmlref.='</form>';
            } else {
                $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
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
	$keyforbreak='fk_user_valid';  	                    // We change column just after this field
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


	/*
	 * Lines
	 */

	if (! empty($object->table_element_line))
	{
    	// Show object lines
    	$result = $object->getLinesArray();

    	print '	<form name="addproduct" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#addline' : '#line_' . GETPOST('lineid', 'int')) . '" method="POST">
    	<input type="hidden" name="token" value="' . newToken() . '">
    	<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
    	<input type="hidden" name="mode" value="">
    	<input type="hidden" name="id" value="' . $object->id . '">
    	';

    	if (! empty($conf->use_javascript_ajax) && $object->status == 0) {
    	    include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
    	}

    	print '<div class="div-table-responsive-no-min">';
    	if (! empty($object->lines) && $object->status == 0 && $permissiontoadd && $action != 'selectlines' && $action != 'editline')
    	{
    	    print '<table id="tablelines" class="noborder noshadow" width="100%">';
    	}

    	if (! empty($object->lines))
    	{
    		$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
    	}

    	// Form to add new line
    	if ($object->status == 0 && $permissiontoadd && $action != 'selectlines')
    	{
    	    if ($action != 'editline')
    	    {
    	        // Add products/services form
    	        $object->formAddObjectLine(1, $mysoc, $soc);

    	        $parameters = array();
    	        $reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
    	    }
    	}

    	if (! empty($object->lines) && $object->status == 0 && $permissiontoadd && $action != 'selectlines' && $action != 'editline')
    	{
    	    print '</table>';
    	}
    	print '</div>';

    	print "</form>\n";
	}


	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
    	print '<div class="tabsAction">'."\n";
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
    	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

    	if (empty($reshook))
    	{
    	    // Send
            //print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

    	    // Back to draft
    	    if ($object->status == $object::STATUS_VALIDATED)
    	    {
    	        if ($permissiontoadd)
    	        {
    	            print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes">'.$langs->trans("SetToDraft").'</a>';
    	        }
    	    }
    	    if ($object->status == $object::STATUS_APPROVED)
    	    {
   	            if ($permissiontoapprove)
    	        {
    	            print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes">'.$langs->trans("SetToDraft").'</a>';
    	        }
    	    }

            // Modify
    	    if ($object->status == $object::STATUS_DRAFT || $user->rights->justificativedocuments->justificativedocument->approve)    // User with permission to approve must be able to edit/fix and set reimbursed amount.
    	    {
    	        if ($permissiontoadd)
        		{
        			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>'."\n";
        		}
        		else
        		{
        			print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
        		}
    	    }

    		// Validate
    		if ($object->status == $object::STATUS_DRAFT)
    		{
    		    if ($permissiontoadd)
    		    {
    		        $upload_dir = $conf->justificativedocuments->dir_output . "/justificativedocument/" . dol_sanitizeFileName($object->ref);
    		        // Force saving documents on main company 1
    		        $upload_dir = preg_replace('/\/[0-9]+\/justificativedocuments/', '/justificativedocuments', $conf->justificativedocuments->dir_output)."/justificativedocument/".dol_sanitizeFileName($object->ref);

    		        $nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
    		        $nbLinks=Link::count($db, $object->element, $object->id);

    		        if (($nbFiles + $nbLinks) > 0)
    		        {
    		            print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes">'.$langs->trans("Validate").'</a>';
    		        }
    		        else
    		        {
    		            print '<a class="butActionRefused" href="" title="'.$langs->trans("AddAtLeastOneLinkedFile").'">'.$langs->trans("Validate").'</a>';
    		        }
    		    }
    		}

    		// Approve
    		if ($object->status == $object::STATUS_VALIDATED)
    		{
    		    if ($permissiontoapprove)
    		    {
    		        $upload_dir = $conf->justificativedocuments->dir_output . "/justificativedocument/" . dol_sanitizeFileName($object->ref);
    		        // Force saving documents on main company 1
    		        $upload_dir = preg_replace('/\/[0-9]+\/justificativedocuments/', '/justificativedocuments', $conf->justificativedocuments->dir_output)."/justificativedocument/".dol_sanitizeFileName($object->ref);

    		        $nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
    		        $nbLinks=Link::count($db, $object->element, $object->id);

    		        if (($nbFiles + $nbLinks) > 0)
    		        {
    		            print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_approve&confirm=yes">'.$langs->trans("Approve").'</a>';
    		        }
    		        else
    		        {
    		            print '<a class="butActionRefused" href="" title="'.$langs->trans("AddAtLeastOneLinkedFile").'">'.$langs->trans("Approve").'</a>';
    		        }
    		    } else {
    		        print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Approve').'</a>'."\n";
    		    }
    		}

    		// Clone
    		if ($permissiontoadd)
    		{
    			//print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=clone&amp;object=order">' . $langs->trans("ToClone") . '</a>'."\n";
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
    		if (! empty($user->rights->justificativedocuments->justificativedocument->delete) || (! empty($object->fields['status']) && $object->status == $object::STATUS_DRAFT && ! empty($user->rights->justificativedocuments->justificativedocument->write)))
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
	    $filedir = $conf->justificativedocuments->dir_output . '/' . $objref;
	    $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	    $genallowed = $user->rights->justificativedocuments->justificativedocument->read;	// If you can read, you can build the PDF to read content
	    $delallowed = $user->rights->justificativedocuments->justificativedocument->create;	// If you can create/edit, you can remove a file on card
	    print $formfile->showdocuments('justificativedocuments', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
		*/

	    // Show links to link elements
	    $linktoelem = $form->showLinkToObjectBlock($object, null, array('justificativedocument'));
	    $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	    $MAXEVENT = 10;

	    $morehtmlright = '<a href="'.dol_buildpath('/justificativedocuments/justificativedocument_agenda.php', 1).'?id='.$object->id.'">';
	    $morehtmlright.= $langs->trans("SeeAll");
	    $morehtmlright.= '</a>';

	    // List of actions on element
	    include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	    $formactions = new FormActions($db);
	    $somethingshown = $formactions->showactions($object, $object->element, $socid, 1, '', $MAXEVENT, '', $morehtmlright);

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
