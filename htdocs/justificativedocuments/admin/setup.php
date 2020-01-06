<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2019 Alicealalalamdskfldmjgdfgdfhfghgfh Adminson <testldr9@dolicloud.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    justificativedocuments/admin/setup.php
 * \ingroup justificativedocuments
 * \brief   JustificativeDocuments setup page.
 */

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
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/justificativedocuments.lib.php';
require_once "../class/justificativedocument.class.php";

// Translations
$langs->loadLangs(array("admin", "justificativedocuments@justificativedocuments"));

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

/*$arrayofparameters=array(
	'JUSTIFICATIVEDOCUMENTS_MYPARAM1'=>array('css'=>'minwidth200','enabled'=>1),
	'JUSTIFICATIVEDOCUMENTS_MYPARAM2'=>array('css'=>'minwidth500','enabled'=>1)
);
*/


/*
 * Actions
 */

/*
 * Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

$value = GETPOST('value', 'alphanohtml');
$error = 0;

if ($action == 'updateMask')
{
    $maskconstjd = GETPOST('maskconstJustificativeDocument', 'alpha');
    $maskjd = GETPOST('maskJustificativeDocument', 'alpha');

    if ($maskconstjd) $res = dolibarr_set_const($db, $maskconstjd, $maskjd, 'chaine', 0, '', $conf->entity);

    if (!$res > 0) $error++;

    if (!$error)
    {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }
    else
    {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

elseif ($action == 'specimen')
{
    $modele = GETPOST('module', 'alpha');

    $justificativedocument = new JustificativeDocument($db);
    $justificativedocument->initAsSpecimen();

    // Search template files
    $file = ''; $classname = ''; $filefound = 0;
    $dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
    foreach ($dirmodels as $reldir)
    {
        $file = dol_buildpath($reldir."core/modules/justificativedocuments/doc/pdf_".$modele.".modules.php", 0);
        if (file_exists($file))
        {
            $filefound = 1;
            $classname = "pdf_".$modele;
            break;
        }
    }

    if ($filefound)
    {
        require_once $file;

        $module = new $classname($db);

        if ($module->write_file($justificativedocument, $langs) > 0)
        {
            header("Location: ".DOL_URL_ROOT."/document.php?modulepart=justificativedocument&file=SPECIMEN.pdf");
            return;
        }
        else
        {
            setEventMessages($module->error, null, 'errors');
            dol_syslog($module->error, LOG_ERR);
        }
    }
    else
    {
        setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
        dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
    }
}

// Activate a model
elseif ($action == 'set')
{
    $ret = addDocumentModel($value, $type, $label, $scandir);
}

elseif ($action == 'del')
{
    $ret = delDocumentModel($value, $type);
    if ($ret > 0)
    {
        if ($conf->global->JUSTIFICATIVEDOCUMENT_ADDON_PDF == "$value") dolibarr_del_const($db, 'JUSTIFICATIVEDOCUMENT_ADDON_PDF', $conf->entity);
    }
}

// Set default model
elseif ($action == 'setdoc')
{
    if (dolibarr_set_const($db, "JUSTIFICATIVEDOCUMENT_ADDON_PDF", $value, 'chaine', 0, '', $conf->entity))
    {
        // The constant that was read before the new set
        // We therefore requires a variable to have a coherent view
        $conf->global->JUSTIFICATIVEDOCUMENT_ADDON_PDF = $value;
    }

    // On active le modele
    $ret = delDocumentModel($value, $type);
    if ($ret > 0)
    {
        $ret = addDocumentModel($value, $type, $label, $scandir);
    }
}

elseif ($action == 'setmod')
{
    // TODO Check if numbering module chosen can be activated
    // by calling method canBeActivated

    dolibarr_set_const($db, "JUSTIFICATIVEDOCUMENT_ADDON", $value, 'chaine', 0, '', $conf->entity);
}

elseif ($action == 'set_JUSTIFICATIVEDOCUMENT_DRAFT_WATERMARK')
{
    $draft = GETPOST("JUSTIFICATIVEDOCUMENT_DRAFT_WATERMARK");
    $res = dolibarr_set_const($db, "JUSTIFICATIVEDOCUMENT_DRAFT_WATERMARK", trim($draft), 'chaine', 0, '', $conf->entity);

    if (!$res > 0) $error++;

    if (!$error)
    {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }
    else
    {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

elseif ($action == 'set_JUSTIFICATIVEDOCUMENT_FREE_TEXT')
{
    $freetext = GETPOST("JUSTIFICATIVEDOCUMENT_FREE_TEXT", 'none'); // No alpha here, we want exact string

    $res = dolibarr_set_const($db, "JUSTIFICATIVEDOCUMENT_FREE_TEXT", $freetext, 'chaine', 0, '', $conf->entity);

    if (!$res > 0) $error++;

    if (!$error)
    {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }
    else
    {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}


/*
 * View
 */

$form = new Form($db);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

llxHeader("", $langs->trans("JustificativeDocumentSetup"));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("JustificativeDocumentsSetup"), $linkback, 'title_setup');

$head = justificativedocumentsAdminPrepareHead();

dol_fiche_head($head, 'settings', '', -1, 'justificativedocument');

/*
 * justificativedocuments Numbering model
 */

print load_fiche_titre($langs->trans("NumberingModules"), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td class="nowrap">'.$langs->trans("Example").'</td>';
print '<td class="center" width="60">'.$langs->trans("Status").'</td>';
print '<td class="center" width="16">'.$langs->trans("ShortInfo").'</td>';
print '</tr>'."\n";

clearstatcache();

foreach ($dirmodels as $reldir)
{
    $dir = dol_buildpath($reldir."core/modules/justificativedocuments/");

    if (is_dir($dir))
    {
        $handle = opendir($dir);
        if (is_resource($handle))
        {
            while (($file = readdir($handle)) !== false)
            {
                if (strpos($file, 'mod_justificativedocument_') === 0 && substr($file, dol_strlen($file) - 3, 3) == 'php')
                {
                    $file = substr($file, 0, dol_strlen($file) - 4);

                    require_once $dir.$file.'.php';

                    $module = new $file($db);

                    // Show modules according to features level
                    if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
                    if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

                    if ($module->isEnabled())
                    {
                        print '<tr class="oddeven"><td>'.$module->name."</td><td>\n";
                        print $module->info();
                        print '</td>';

                        // Show example of numbering model
                        print '<td class="nowrap">';
                        $tmp = $module->getExample();
                        if (preg_match('/^Error/', $tmp)) print '<div class="error">'.$langs->trans($tmp).'</div>';
                        elseif ($tmp == 'NotConfigured') print $langs->trans($tmp);
                        else print $tmp;
                        print '</td>'."\n";

                        print '<td class="center">';
                        if ($conf->global->JUSTIFICATIVEDOCUMENT_ADDON == $file)
                        {
                            print img_picto($langs->trans("Activated"), 'switch_on');
                        }
                        else
                        {
                            print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?action=setmod&amp;value='.$file.'">';
                            print img_picto($langs->trans("Disabled"), 'switch_off');
                            print '</a>';
                        }
                        print '</td>';

                        $justificativedocument = new JustificativeDocument($db);
                        $justificativedocument->initAsSpecimen();

                        // Info
                        $htmltooltip = '';
                        $htmltooltip .= ''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
                        $justificativedocument->type = 0;
                        $nextval = $module->getNextValue($mysoc, $justificativedocument);
                        if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
                            $htmltooltip .= ''.$langs->trans("NextValue").': ';
                            if ($nextval) {
                                if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured')
                                    $nextval = $langs->trans($nextval);
                                    $htmltooltip .= $nextval.'<br>';
                            } else {
                                $htmltooltip .= $langs->trans($module->error).'<br>';
                            }
                        }

                        print '<td class="center">';
                        print $form->textwithpicto('', $htmltooltip, 1, 0);
                        print '</td>';

                        print "</tr>\n";
                    }
                }
            }
            closedir($handle);
        }
    }
}
print "</table><br>\n";


/*
 * Document templates generators
 */

/*

print load_fiche_titre($langs->trans("JustificativeDocumentsModelModule"), '', '');

// Load array def with activated templates
$def = array();
$sql = "SELECT nom";
$sql .= " FROM ".MAIN_DB_PREFIX."document_model";
$sql .= " WHERE type = '".$type."'";
$sql .= " AND entity = ".$conf->entity;
$resql = $db->query($sql);
if ($resql)
{
    $i = 0;
    $num_rows = $db->num_rows($resql);
    while ($i < $num_rows)
    {
        $array = $db->fetch_array($resql);
        array_push($def, $array[0]);
        $i++;
    }
}
else
{
    dol_print_error($db);
}

print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td class="center" width="60">'.$langs->trans("Status")."</td>\n";
print '<td class="center" width="60">'.$langs->trans("Default")."</td>\n";
print '<td class="center" width="38">'.$langs->trans("ShortInfo").'</td>';
print '<td class="center" width="38">'.$langs->trans("Preview").'</td>';
print "</tr>\n";

clearstatcache();

foreach ($dirmodels as $reldir)
{
    foreach (array('', '/doc') as $valdir)
    {
        $dir = dol_buildpath($reldir."core/modules/$justificativedocuments".$valdir);

        if (is_dir($dir))
        {
            $handle = opendir($dir);
            if (is_resource($handle))
            {
                while (($file = readdir($handle)) !== false)
                {
                    $filelist[] = $file;
                }
                closedir($handle);
                arsort($filelist);

                foreach ($filelist as $file)
                {
                    if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file))
                    {
                        if (file_exists($dir.'/'.$file))
                        {
                            $name = substr($file, 4, dol_strlen($file) - 16);
                            $classname = substr($file, 0, dol_strlen($file) - 12);

                            require_once $dir.'/'.$file;
                            $module = new $classname($db);

                            $modulequalified = 1;
                            if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) $modulequalified = 0;
                            if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modulequalified = 0;

                            if ($modulequalified)
                            {
                                $var = !$var;
                                print '<tr class="oddeven"><td width="100">';
                                print (empty($module->name) ? $name : $module->name);
                                print "</td><td>\n";
                                if (method_exists($module, 'info')) print $module->info($langs);
                                else print $module->description;
                                print '</td>';

                                // Active
                                if (in_array($name, $def))
                                {
                                    print '<td class="center">'."\n";
                                    print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&value='.$name.'">';
                                    print img_picto($langs->trans("Enabled"), 'switch_on');
                                    print '</a>';
                                    print '</td>';
                                }
                                else
                                {
                                    print '<td class="center">'."\n";
                                    print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'">'.img_picto($langs->trans("Disabled"), 'switch_off').'</a>';
                                    print "</td>";
                                }

                                // Default
                                print '<td class="center">';
                                if ($conf->global->JUSTIFICATIVEDOCUMENT_ADDON_PDF == $name)
                                {
                                    print img_picto($langs->trans("Default"), 'on');
                                }
                                else
                                {
                                    print '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"), 'off').'</a>';
                                }
                                print '</td>';

                                // Info
                                $htmltooltip = ''.$langs->trans("Name").': '.$module->name;
                                $htmltooltip .= '<br>'.$langs->trans("Type").': '.($module->type ? $module->type : $langs->trans("Unknown"));
                                if ($module->type == 'pdf')
                                {
                                    $htmltooltip .= '<br>'.$langs->trans("Width").'/'.$langs->trans("Height").': '.$module->page_largeur.'/'.$module->page_hauteur;
                                }
                                $htmltooltip .= '<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
                                $htmltooltip .= '<br>'.$langs->trans("MultiLanguage").': '.yn($module->option_multilang, 1, 1);
                                $htmltooltip .= '<br>'.$langs->trans("WatermarkOnDrafts").': '.yn($module->option_draft_watermark, 1, 1);


                                print '<td class="center">';
                                print $form->textwithpicto('', $htmltooltip, 1, 0);
                                print '</td>';

                                // Preview
                                print '<td class="center">';
                                if ($module->type == 'pdf')
                                {
                                    print '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'">'.img_object($langs->trans("Preview"), 'bill').'</a>';
                                }
                                else
                                {
                                    print img_object($langs->trans("PreviewNotAvailable"), 'generic');
                                }
                                print '</td>';

                                print "</tr>\n";
                            }
                        }
                    }
                }
            }
        }
    }
}

print '</table>';
print "<br>";

*/

// End of page
llxFooter();
$db->close();