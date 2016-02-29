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
 @copyright Copyright (c) 2016 teclib'
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
if (! $res && preg_match('/\/nltechno([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res && preg_match('/\/teclib([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res) die("Include of main fails");
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';

dol_include_once('/kimios/class/db.class.php');
dol_include_once('/kimios/class/config.class.php');
dol_include_once('/kimios/class/api/bootstrap.php');
dol_include_once('/kimios/class/actions_kimios.class.php');
dol_include_once('/kimios/class/payslips.class.php');

define('_KIMIOS_EXEC',true);

if (!$user->rights->kimios->send_payslips) accessforbidden();

llxHeader();

$step = GETPOST("step")?GETPOST("step"):1;

$h = 0;
for ($i = 1; $i < $step+1; $i++) {
   $head[$h][0] = DOL_URL_ROOT.'/kimios/payslips.php';
   $head[$h][1] = $langs->trans("Step")." ".$i;
   if ($i == $step) $hselected=$h;
   $h++;
}

dol_fiche_head($head, $hselected, "Envoi fiches de paie");

$KimiosPhpSoap = new KimiosPhpSoap();
$ActionsKimios = new ActionsKimios();
$KimiosPayslips = new KimiosPayslips();
$KimiosConfig  = new KimiosConfig();
$KimiosConfig->getFromDB(1);

$sessionId = $ActionsKimios->connect();

print '<table class="notopnoleftnoright" width="100%">';
   KimiosPayslips::showStep($step);
print '</table>';

llxFooter();
