<?php
/* Copyright (C) 2001-2003,2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2014      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006      Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2010           Juanjo Menent        <jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *   \file       htdocs/notes/socnote.php
 *   \brief      Tab for notes on third party
 *   \ingroup    actions
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
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
dol_include_once("/notes/class/note.class.php");

$action = GETPOST('action','aZ09');

$langs->load("companies");
$langs->load("notes@notes");

// Security check
$socid = GETPOST("socid");
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'societe', $socid, 'societe');

$item_type = 'societe';

$societe = new Societe($db);
if ($socid > 0)
{
    $societe->fetch($socid);
}



/*
 * Actions
 */

if ($action=="del_note" && ! GETPOST('cancel')) 
{
	$notes = new Note();
	$notes->getFromDB($_GET['note_id']);

	if($notes->deleteFromDB()) {
		header('Location: socnote.php?socid='.$socid);
		exit;
	}
}

if($action=="edit_note_go" && ! GETPOST('cancel')) {
	$notes = new Note();
	$notes->getFromDB($_POST['rowid']);

	$input = array();
	foreach($notes->fields as $key => $value) {
		$input[$key] = $_POST[$key];
	}

	if($notes->update($input)) {
		header('Location: socnote.php?socid='.$socid);
		exit;
	}
}

if ($action=="add_note") 
{
	$notes = new Note();
	$notes->fields['user_id'] = $user->id;
	$notes->fields['datetime'] = date('Y-m-d H:i:s');
	$notes->fields['item_type'] = $item_type;
	$notes->fields['item_id'] = $socid;
	$notes->fields['note_value'] = $_POST['note_value'];
	$notes->fields['note_title'] = $_POST['note_title'];

	if($notes->addToDB()) {
		header('Location: socnote.php?socid='.$socid);
		exit;
	}
}


/*
 * View
 */

$title=$langs->trans("ThirdParty");
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/thirdpartynameonly/',$conf->global->MAIN_HTML_TITLE) && $societe->name) $title=$societe->name;

llxHeader('', $title);

if ($socid > 0)
{
	if ($conf->notification->enabled) $langs->load("mails");

	$head = societe_prepare_head($societe);

	dol_fiche_head($head, 'noteteclib', $langs->trans("ThirdParty"), 0, 'company');

	$notes = new Note();
	$existing_notes = $notes->find("item_type = '".$item_type."' AND item_id = '".$socid."'", "datetime DESC");

	//print '<script src="'.dol_buildpath('/notes/lib/uniform/jquery.uniform.js',1).'" type="text/javascript" charset="utf-8"></script>';

	print '<script src="'.dol_buildpath('/notes/lib/jquery.easyconfirm.js',1).'" type="text/javascript" charset="utf-8"></script>';

	$JS = <<<JS
jQuery(function() {
	$.fx.speeds._default = 100;

	jQuery( "#accordion" ).accordion({
		collapsible: true,
		active : 9999999,
		autoHeight: false,
		navigation: true,
		disabled: false
	});

	$( "#dialog" ).dialog({
		autoOpen: false,
		show: "blind",
		width: 740,
		height: 450,
		modal: true
	});

	$( "#opener" ).click(function() {
		$( "#dialog" ).dialog( "open" );
		return false;
	});

	$(".teclibnotedeletebutton").easyconfirm({locale: {
		title: '{$langs->trans("DeleteNote")}?',
		text: '{$langs->trans("AreYouSure")}?',
		button: ['{$langs->trans("Cancel")}',' {$langs->trans("Confirm")}'],
		closeText: 'fermer'
	}});

});
JS;

	echo "<script type='text/javascript'>";
	echo $JS;
	echo "</script>";

	print '<div id="dialog" title="'.dol_escape_htmltag($langs->trans("AddNote")).'">';
	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="socid" value="'.$socid.'" />';
	print '<input type="hidden" name="action" value="add_note" />';
	print '<p>'.$langs->trans("Title").' : <input type="text" name="note_title" size="90" /></p>';
	//print '<p>';
	//$doleditor=new DolEditor('note_value_add',$notes->fields['note_value_add'],'',240,'dolibarr_notes');     WYSIWYG does not work into a dialog.
	//print $doleditor->Create();
	print '<textarea id="noteteclib" name="note_value" rows="20" style="width: 98%"></textarea>';
	//print '</p>';
	print '<div class="center"><input type="submit" value="'.$langs->trans("Save").'" class="button" /></div>';
	print '</form>';
	print '</div>'."\n";

	if($action=="edit_note") {
		$notes = new Note();
		$notes->getFromDB($_GET['note_id']);

		print '<div>';
		print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
		print '<input type="hidden" name="socid" value="'.$socid.'" />';

		print '<input type="hidden" name="rowid" value="'.$_GET['note_id'].'" />';
		print '<input type="hidden" name="user_id" value="'.$notes->fields['user_id'].'" />';
		print '<input type="hidden" name="datetime" value="'.$notes->fields['datetime'].'" />';
		print '<input type="hidden" name="item_type" value="'.$notes->fields['item_type'].'" />';
		print '<input type="hidden" name="item_id" value="'.$notes->fields['item_id'].'" />';

		print '<input type="hidden" name="action" value="edit_note_go" />';

		print '<p>'.$langs->trans("Title").' : <input type="text" name="note_title" size="90"
		value="' . $notes->fields['note_title'] . '" /></p>';

		$doleditor=new DolEditor('note_value',$notes->fields['note_value'],'',180,'dolibarr_notes');
		print $doleditor->Create();
		//print '<textarea name="note_value" rows="20" cols="100"></textarea></p>';
		print '<p><div class="center"><input type="submit" value="'.$langs->trans("Save").'" class="button" /> &nbsp; <input type="submit" name="cancel" value="'.$langs->trans("Cancel").'" class="button" /></div></p>';
		print '</form>';
		print '</div>';
	}

	if($action!="edit_note") {
		print '<button id="opener" style="margin-bottom:5px;">'.$langs->trans("AddNote").'</button>';

		print '<div id="accordion" class="ui-accordion ui-widget ui-helper-reset">';

		if (count($existing_notes) > 0)
		{
			foreach($existing_notes as $note_infos)
			{

				$user = new User($db);
				$user->fetch($note_infos['user_id']);
				$auteur = $user->getFullName($langs);

				
			 	print'<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top">';
			 	print '<a href="#">';
			 	print 'n°'.$note_infos['rowid'].' - '.$note_infos['datetime'].' - '.$auteur;
			 	print ' : '.$note_infos['note_title'];
			 	print '</a>';
			 	print '</h3>';
			 	
			 	print '<div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active">';
			 	print '<p style="float: right; width:50px; text-align:center;margin-bottom:0px; margin-top: 0px;">';
			 	Note::showEdit($socid,$note_infos['rowid']);       // Show button EditNote
			 	Note::showDelete($socid,$note_infos['rowid']);     // Show button DeleteNote
			 	print '</p>';
			 	print dol_htmlentitiesbr($note_infos['note_value']);
			 	print '</div>';
			}
		}
		else
		{
			print $langs->trans('NoNotes');
		}

		print '</div>';
	}

}

llxFooter();

$db->close();
