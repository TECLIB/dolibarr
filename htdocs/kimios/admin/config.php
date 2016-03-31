<?php
/*
 LICENSE

 This file is part of the Kimios Dolibarr module.

 Kimios Dolibarr module is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Kimios Dolibarr module is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Kimios Dolibarr module. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   Kimios-Dolibarr
 @author    teclib (François Legastelois)
 @copyright Copyright (c) 2013 teclib'
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      http://www.teclib.com
 @since     2013
 ---------------------------------------------------------------------- */
 
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/kimios/class/db.class.php');
dol_include_once('/kimios/class/config.class.php');

$langs->load("admin");
$langs->load("kimios@kimios");

if (!$user->admin) accessforbidden();

$var = true;

llxHeader();

$form 			= new Form($db);
$KimiosConfig 	= new KimiosConfig();

if(isset($_POST['update'])) {
	$input = array('rowid' => 1);
	foreach($KimiosConfig->configFields as $configKey) {
		$input[$configKey] = $_POST[$configKey];
	}
	$KimiosConfig->update($input);
}

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'
				. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans("KimiosSetup"), $linkback, 'setup');
echo "</br >";

$KimiosConfig->getFromDB(1);

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?leftmenu=setup" name="config">';

echo '<input type="hidden" name="update" value="1" />';

echo '<table class="noborder" width="100%">';
	echo '<tr class="liste_titre">';
		echo '<td class="liste_titre" width="40%">' 
				. $langs->trans('KimiosSetupHead1') . '</td>';
		echo '<td class="liste_titre" width="20%">' 
				. $langs->trans('KimiosSetupHead2') . '</td>';
	echo '</tr>';

	foreach($KimiosConfig->configFields as $configKey) {
		$var=!$var;
		echo '<tr '.$bc[$var].'>'."\n";
			echo '<td style="padding:5px; width: 40%;">' 
					. $langs->trans('KimiosSetupKey'.$configKey) . '</td>' . "\n";
			echo '<td style="padding:5px;">';
				echo '<input class="flat" type="text" name="' 
					. $configKey . '" value="' . $KimiosConfig->fields[$configKey] . '" size="50" />';
			echo '</td>'."\n";
		echo '</tr>'."\n";
	}

	echo '<tr>';
		echo '<td>&nbsp;</td>';
		echo '<td><br /><input type="submit" value="' 
				. $langs->trans('KimiosSetupSubmit').'" class="button"/>
		<br /><br /></td>';
	echo '</tr>';

echo '</table>';
echo '</form>';

dol_fiche_end();

llxFooter();

$db->close();