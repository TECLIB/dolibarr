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
 *   \file       htdocs/notes/note_order.php
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
if (! $res && preg_match('/\/nltechno([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res && preg_match('/\/teclib([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res) die("Include of main fails");
require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");
require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/comm/propal/class/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
dol_include_once("/notes/class/note.class.php");

$action = GETPOST('action');

if (!$user->rights->commande->lire) accessforbidden();

$langs->load("companies");
$langs->load("bills");
$langs->load("orders");
$langs->load("notes@notes");

// Security check
$socid = GETPOST("socid");
if ($user->societe_id) $socid = $user->societe_id;
$id = GETPOST('id');


$item_type = GETPOST('mode');
if ($item_type == 'invoice') $item_type = 'facture';
elseif ($item_type == 'order') $item_type = 'commande';
elseif ($item_type == 'propal') $item_type = 'propal';

$result=restrictedArea($user,$item_type,$id,'');




/*
 * Actions
 */

if($action=="del_note")
{
	$notes = new Note();
	$notes->getFromDB($_GET['note_id']);

	if ($notes->deleteFromDB())
	{
		header('Location: '.$_SERVER["PHP_SELF"].'?id='.$id.'&mode='.$item_type);
		exit;
   	}
}

if($action=="edit_note_go")
{
	$notes = new Note();
	$notes->getFromDB($_POST['rowid']);

   $input = array();
   foreach($notes->fields as $key => $value) {
      $input[$key] = $_POST[$key];
   }

   if ($notes->update($input))
   {
   		header('Location: '.$_SERVER["PHP_SELF"].'?id='.$id.'&mode='.$item_type);
		exit;
   }
}

if($action=="add_note")
{
	$notes = new Note();
	$notes->fields['user_id'] = $user->id;
	$notes->fields['datetime'] = date('Y-m-d H:i:s');
	$notes->fields['item_type'] = $item_type;
	$notes->fields['item_id'] = $id;
	$notes->fields['note_value'] = $_POST['note_value'];
	$notes->fields['note_title'] = $_POST['note_title'];

	if ($notes->addToDB())
	{
		header('Location: '.$_SERVER["PHP_SELF"].'?id='.$id.'&mode='.$item_type);
		exit;
   	}
}


/*
 * View
 */

llxHeader();

if ($id > 0)
{

    if ($conf->notification->enabled) $langs->load("mails");

    if ($item_type == 'facture')
    {
		require_once(DOL_DOCUMENT_ROOT.'/core/lib/invoice.lib.php');
    	$object = new Facture($db);
    	$object->fetch($id);
    	$head = facture_prepare_head($object);
		$title = $langs->trans("CustomerInvoice");
		$picto='bill';
    }
    if ($item_type == 'propal')
    {
		require_once(DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php');
    	$object = new Propal($db);
    	$object->fetch($id);
    	$head = propal_prepare_head($object);
		$title = $langs->trans("Proposal");
		$picto='propal';
    }
    if ($item_type == 'commande')
    {
		require_once(DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php');
    	$object = new Commande($db);
    	$object->fetch($id);
    	$head = commande_prepare_head($object);
		$title = $langs->trans("CustomerOrder");
		$picto='order';
    }

	dol_fiche_head($head, 'noteteclib', $title, 0, $picto);

	$notes = new Note();
	$existing_notes = $notes->find("item_type = '".$item_type."' AND item_id = '".$id."'", "datetime DESC");

	print '<script src="'.dol_buildpath('/notes/lib/uniform/jquery.uniform.js',1).'" type="text/javascript" charset="utf-8"></script>';

	print '<script src="'.dol_buildpath('/notes/lib/jquery.easyconfirm.js',1).'" type="text/javascript" charset="utf-8"></script>';

$JS = <<<JS
jQuery(function() {
	$.fx.speeds._default = 100;

	jQuery( "#accordion" ).accordion({
		collapsible: true,
		active : 9999999,
		autoHeight: false,
		navigation: true
	});

	$( "#dialog" ).dialog({
		autoOpen: false,
		show: "blind",
		width: 680,
		height: 440,
		modal: true
	});

	$( "#opener" ).click(function() {
		$( "#dialog" ).dialog( "open" );
		return false;
	});

	$("#supprimernote").easyconfirm({locale: {
		title: '{$langs->trans("Confirm")}',
		text: '{$langs->trans("AreYouSureYouWantToDeleteThisNote")}',
		button: ['{$langs->trans("Cancel")}',' {$langs->trans("Confirm")}'],
		closeText: 'fermer'
	}});

});
JS;

	echo "<script type='text/javascript'>";
	echo $JS;
	echo "</script>";

	print '<div id="dialog" title="'.dol_escape_htmltag($langs->trans("Ajouter une note")).'">';
	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="id" value="'.$id.'" />';
	print '<input type="hidden" name="action" value="add_note" />';
	print '<input type="hidden" name="mode" value="'.$item_type.'" />';
	print '<p>'.$langs->trans("Title").' : <input type="text" name="note_title" size="90" /></p>';
	print '<p><textarea name="note_value" rows="20" cols="100"></textarea></p>';
	print '<p><input type="submit" value="'.$langs->trans("Save").'" class="button" /></p>';
	print '</form>';
	print '</div>'."\n";

	if($action=="edit_note")
	{
		$notes = new Note();
		$notes->getFromDB($_GET['note_id']);

		print '<div>';
		print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
		print '<input type="hidden" name="id" value="'.$id.'" />';
		print '<input type="hidden" name="mode" value="'.$item_type.'" />';
		print '<input type="hidden" name="rowid" value="'.$_GET['note_id'].'" />';
		print '<input type="hidden" name="user_id" value="'.$notes->fields['user_id'].'" />';
		print '<input type="hidden" name="datetime" value="'.$notes->fields['datetime'].'" />';
		print '<input type="hidden" name="item_type" value="'.$notes->fields['item_type'].'" />';
		print '<input type="hidden" name="item_id" value="'.$notes->fields['item_id'].'" />';

		print '<input type="hidden" name="action" value="edit_note_go" />';

		print '<p>Titre : <input type="text" name="note_title" size="90" value="' . $notes->fields['note_title'] . '" /></p>';

		print '<p>';
		$doleditor=new DolEditor('note_value',$notes->fields['note_value']);
		print $doleditor->Create();
		//print '<textarea name="note_value" rows="20" cols="100">'. $notes->fields['note_value'] . '</textarea>';
		print '</p>';

		print '<p><input type="submit" value="'.$langs->trans("Save").'" class="button" /></p>';
		print '</form>';
		print '</div>';
	}

	if($action!="edit_note")
	{
		print '<button id="opener" style="margin-bottom:5px;">'.$langs->trans("AddNote").'</button>';

		print '<div id="accordion" class="ui-accordion ui-widget ui-helper-reset">';

		if(count($existing_notes) > 0)
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

				print '<p style="border:1px dashed grey;padding-top:1px;padding-bottom:1px;	width:50px;text-align:center;margin-top:-10px;">';
				Note::showEdit($id,$note_infos['rowid'],'id',$item_type);
				Note::showDelete($id,$note_infos['rowid'],'id',$item_type);
				print '</p>';

				print $note_infos['note_value'];
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
