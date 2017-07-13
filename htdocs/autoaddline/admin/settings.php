<?php

/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis@dolibarr.fr>
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
 */

/**
 * 	\file       htdocs/admin/societe.php
 * 	\ingroup    company
 * 	\brief      Third party module setup page
 *
 *  To enable module into a dolibarr git instance, you can do into htdocs dir:
 *  ln -fs ../../teclib_dolibarr_modules/htdocs teclib_3.8
 *  Then complete the htdocs/conf.php file with
 *  $dolibarr_main_url_root_alt='/custom,/teclib_3.8';
 *  $dolibarr_main_document_root_alt='/home/login/git/dolibarr_3.8/htdocs/custom,/home/login/git/dolibarr_3.8/htdocs/teclib_3.8';
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
require_once(DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php');
dol_include_once('/autoaddline/class/autoaddline.class.php');

global $conf, $langs;

$langs->load("admin");
$langs->load("autoaddline@autoaddline");

if (!$user->admin)
    accessforbidden();

$action = GETPOST('action', 'alpha');
$seeDetail = false;

$autoAddLine = new AutoAddLine($db);


$db->begin();

$error = 0;

$id = GETPOST('id', 'int');

if (isset($_POST['submit_affect']))
{
    $serviceId = GETPOST('autoaddline_service', 'int', 2);
    $serviceType = GETPOST('autoaddline_type', 'int', 2);
    $serviceValue = GETPOST('autoaddline_value', '', 2);
    $serviceValue = str_replace(',', '.', $serviceValue);
    $serviceValue = is_numeric($serviceValue) ? $serviceValue : '';
    $rulename = GETPOST('rulename');

    if (! $rulename)
    {
        setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("RuleName")), null, 'errors');
        $error++;
    }
    if (! ($serviceId > 0))
    {
        setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Product")), null, 'errors');
        $error++;
    }
    if (GETPOST('autoaddline_value') === '')
    {
        setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("DefineValue")), null, 'errors');
        $error++;
    }

    if (! $error)
    {
        if ($serviceId > 0 && !empty($serviceValue))
        {
            $autoAddLine->label = $rulename;
            $autoAddLine->product_id = $serviceId;
            $autoAddLine->type = $serviceType;
            $autoAddLine->value = $serviceValue;

            if (!($autoAddLine->create($user) > 0))
            {
                setEventMessages($autoAddLine->error, $autoAddLine->errors, 'errors');
                $error++;
            }

            if(!$error)
                $id = $autoAddLine->id;
        }
    }
}

if ($id > 0)
{

    $autoAddLine->fetch($id);
    $autoAddLine->fetch_lines();
}

if (isset($_POST['submit_see_details']) && $id > 0)
{
    $seeDetail = true;
}

if (isset($_POST['submit_update']))
{
    // remove lines
    $idsToRemoveJson = GETPOST('target_json', '', 2);
    if (!empty($idsToRemoveJson))
        if (!($autoAddLine->delete_lines(json_decode($idsToRemoveJson)) > 0))
            $error++;

    // diff to add
    $idsToAddJson = GETPOST('linked_json', '', 2);
    $idsToAddJson = array_diff(json_decode($idsToAddJson), $autoAddLine->lines);

    if (!empty($idsToAddJson))
        if (!($autoAddLine->create_lines($idsToAddJson, $user) > 0))
            $error++;

    // add diffs

    $seeDetail = true;
}

if ($action == 'delete')
{
    if (!($autoAddLine->delete($user)))
        $error++;
}

if (!$error)
{
    $db->commit();
}
else
{
    $db->rollback();
}



/*
 * View
 */

$allProductsAndServices = $autoAddLine->getProducts();
$finals = $allProductsAndServices['final_services'];
$usables = $allProductsAndServices['usable_services'];
$products = $allProductsAndServices['products'];
$finalsTypes = $autoAddLine->getTypes();


$form = new Form($db);


$help_url = '';
llxHeader($head, $langs->trans("Configuration"), $help_url, '', '', '', array('/autoaddline/js/settings.js'), '', 0, 0);



$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans('AutoAddLineSettingsDescription'),$linkback,'setup');

print '<br>';

$h=0;
$head[$h][0] = $_SERVER["PHP_SELF"];
$head[$h][1] = $langs->trans("Setup");
$head[$h][2] = 'tabsetup';
$h++;

$head[$h][0] = 'about.php';
$head[$h][1] = $langs->trans("About");
$head[$h][2] = 'tababout';
$h++;

dol_fiche_head($head, 'tabsetup', '');


print '<br>';

print_fiche_titre('1 - '.$langs->trans('SetNewServiceData'), '', '');


print '<form name="'.$fieldset['formname'].'" action="'.$fieldset['formaction'].'" method="post">';


//---- Defining a service as a final one block ----//
$fieldset = array();
$fieldset['formname'] = 'affect_form';
$fieldset['formaction'] = $_SERVER['PHP_SELF'];
$fieldset['options'] = 'style="width:80%;padding-top:20px;"';

$fieldset['hiddens'] = array(
        array(
                'name' => '',
                'value' => ''
        )
);

// Set fieldset table's
$fieldset['tables'][] = array(
        'alt_lines_bg' => false,
        'print_lines_bg' => false,
        'options' => 'width="100%"',
        'lines' => array(
                array(
                        'options' => '',
                        'cells' => array(
                                array(
                                        'html' => $langs->trans('RuleName'),
                                        'options' => 'width="40%"'
                                ),
                                array(
                                        'html' => '<input type="text" name="rulename" value="'.GETPOST('rulename').'" />',
                                        'options' => ''
                                )
                        )
                ),
                array(
                        'options' => '',
                        'cells' => array(
                                array(
                                        'html' => $langs->trans('ChooseAService'),
                                        'options' => 'width="40%"'
                                ),
                                array(
                                        'html' => $form->select_produits_list(GETPOST('autoaddline_service'),'autoaddline_service'),
                                        'options' => ''
                                )
                        )
                ),
                array(
                        'options' => '',
                        'cells' => array(
                                array(
                                        'html' => $langs->trans('ChooseType'),
                                        'options' => 'width="40%"'
                                ),
                                array(
                                        'html' => $form->selectarray('autoaddline_type', $finalsTypes, 0, 0),
                                        'options' => ''
                                )
                        )
                ),
                array(
                        'options' => '',
                        'cells' => array(
                                array(
                                        'html' => $langs->trans('DefineValue'),
                                        'options' => 'width="40%"'
                                ),
                                array(
                                        'html' => '<input type="text" name="autoaddline_value" value="'.GETPOST('autoaddline_value').'" />',
                                        'options' => ''
                                )
                        )
                ),
                array(
                        'options' => '',
                        'cells' => array(
                                array(
                                        'html' => '<input type="submit" class="button" name="submit_affect" value="' . $langs->trans('Add') . '" />',
                                        'options' => 'align="right" colspan="2"'
                                )
                        )
                )
        )
);
include ('tpl/fieldset.tpl.php');

//---- Details and param of a final service ----//
print_fiche_titre('2 - '.$langs->trans('AutoAddLineSettingsDetails'), '', '');

$fieldset = array();
$fieldset['formname'] = 'show_form';
$fieldset['formaction'] = $_SERVER['PHP_SELF'];
$fieldset['options'] = 'style="width:80%;padding-top:20px;" id="selected_final_fieldset"';

$fieldset['hiddens'] = array(
        array(
                'name' => '',
                'value' => ''
        )
);

// Set fieldset table's
$fieldset['tables'][] = array(
        'alt_lines_bg' => false,
        'print_lines_bg' => false,
        'options' => 'width="100%"',
        'lines' => array(
                array(
                        'options' => '',
                        'cells' => array(
                                array(
                                        'html' => $langs->trans('SelectAFinalService'),
                                        'options' => 'width="40%"'
                                ),
                                array(
                                        'html' => $form->selectarray('id', $finals['labels'], ($seeDetail ? $id : -1), 0),
                                        'options' => 'align="left"'
                                )
                        )
                ),
                array(
                        'options' => '',
                        'cells' => array(
                                array(
                                        'html' => '<input type="submit" class="button" name="submit_see_details" value="' . $langs->trans('SeeDetails') . '" />',
                                        'options' => 'align="right" colspan="2"'
                                )
                        )
                )
        )
);
include ('tpl/fieldset.tpl.php');


if ($seeDetail)
{
    $autoAddLine->fetch($id);
    $autoAddLine->fetch_lines();
    // Prepare target/linked options html
    $targetServicesOptions = '';
    $linkedServicesOptions = '';

    $linkedHiddenArray = array();
    $targetHiddenArray = array();
    foreach (array_diff_key($autoAddLine->lines, array(-1 => '')) as $linkedId)
    {
        $linkedServicesOptions.= '<option value="' . $linkedId . '">' . $products['labels'][$linkedId] . '</option>';
        $linkedHiddenArray[] = $linkedId;
    }
    foreach (array_diff_key($products['labels'], array_flip($autoAddLine->lines), array(-1 => '')) as $targetId => $targetLabel)
    {
        $targetServicesOptions.= '<option value="' . $targetId . '">' . $targetLabel . '</option>';
        $targetHiddenArray[] = $targetId;
    }

//---- Hidden fieldset containing details  ----//
    $fieldset = array();
    $fieldset['formname'] = 'details_form';
    $fieldset['formaction'] = $_SERVER['PHP_SELF'];
    $fieldset['options'] = 'style="width:80%;padding-top:20px;" id="details_final_fieldset" ';
    $fieldset['legend'] = array(
            'options' => '',
            'html' => $langs->trans('ServiceDataDetails')
    );
    $fieldset['hiddens'] = array(
            array(
                    'name' => 'id',
                    'value' => $autoAddLine->id
            ),
            array(
                    'name' => 'linked_json',
                    'value' => htmlentities(json_encode($linkedHiddenArray))
            ),
            array(
                    'name' => 'target_json',
                    'value' => htmlentities(json_encode($targetHiddenArray))
            )
    );

    // Details table
    $fieldset['tables'][] = array(
            'alt_lines_bg' => false,
            'print_lines_bg' => false,
            'options' => 'width="30%"',
            'labels' => array(
                    array(
                            'html' => $langs->trans('FinalDetails'),
                            'options' => 'width="100%" colspan="2"'
                    )
            ),
            'lines' => array(
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => $langs->trans('ServiceRef'),
                                            'options' => 'height="60px" width="60%"'
                                    ),
                                    array(
                                            'html' => $finals['references'][$autoAddLine->id],
                                            'options' => 'align="right"'
                                    )
                            )
                    ),
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => $langs->trans('ServiceLabel'),
                                            'options' => 'height="60px width="60%"'
                                    ),
                                    array(
                                            'html' => $finals['labels'][$autoAddLine->id],
                                            'options' => 'align="right"'
                                    )
                            )
                    ),
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => $langs->trans('ServiceType'),
                                            'options' => 'height="60px width="60%"'
                                    ),
                                    array(
                                            'html' => $finalsTypes[$autoAddLine->type],
                                            'options' => 'align="right"'
                                    )
                            )
                    ),
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => $langs->trans('ServiceValue'),
                                            'options' => 'height="60px width="60%"'
                                    ),
                                    array(
                                            'html' => $autoAddLine->value,
                                            'options' => 'align="right"'
                                    )
                            )
                    )
            )
    );

    include ('tpl/fieldset.tpl.php');

    $fieldset=array();

    // Currently associated select multiple
    $fieldset['tables'][] = array(
            'alt_lines_bg' => false,
            'print_lines_bg' => false,
            'options' => 'style="display: inline-block; width: 25% !important;"',
            'labels' => array(
                    array(
                            'html' => $langs->trans('LinkedServices'),
                            'options' => 'width="100%" colspan="2" align="center"'
                    )
            ),
            'lines' => array(
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => '<select id="select_linked" style="height:500px;width:100%" multiple="multiple" name="linked_products">' . $linkedServicesOptions . '</select>',
                                            'options' => 'align="right"'
                                    )
                            )
                    )
            )
    );

    // To link select multiple
    $fieldset['tables'][] = array(
            'alt_lines_bg' => false,
            'print_lines_bg' => false,
            'options' => ' width="10%" style="display: inline-block" height="500px"',
            'lines' => array(
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => '<span id="toggler_button">' . img_picto($langs->trans("Remove"), 'next') . '</span>',
                                            'options' => 'align="center"  valign="bottom"'
                                    )
                            )
                    ),
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => '<span id="add_button">' . img_picto($langs->trans("Add"), 'previous') . '</span>', //'<input type="button" name="add_selected" value="'. $langs->trans('Add').'"/>',
                                            'options' => 'align="center"  valign="top"'
                                    )
                            )
                    )
            )
    );



    // Currently associated select multiple
    $fieldset['tables'][] = array(
            'alt_lines_bg' => false,
            'print_lines_bg' => false,
            'options' => 'style="display: inline-block; width: 25% !important;"',
            'labels' => array(
                    array(
                            'html' => $langs->trans('TargetServices'),
                            'options' => 'width="100%" colspan="2" align="center"'
                    )
            ),
            'lines' => array(
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => '<select id="select_target" style="height:500px;width:100%" multiple="multiple" name="target_products">' . $targetServicesOptions . '</select>',
                                            'options' => 'align="left"'
                                    )
                            )
                    ),
            )
    );

    // Buttons
    $fieldset['tables'][] = array(
            'alt_lines_bg' => false,
            'print_lines_bg' => false,
            'options' => 'style="margin-top:15px; width: 100% !important;"',
            'lines' => array(
                    array(
                            'options' => '',
                            'cells' => array(
                                    array(
                                            'html' => '<input class="button" type="submit" name="submit_update" value="' . $langs->trans('Apply') . '" /> &nbsp; ' . '<a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $autoAddLine->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a>' ,
                                            'options' => 'align="center"'
                                    )
                            )
                    ),
            )
    );

    print '<div class="centpercent">';
    include ('tpl/fieldset.tpl.php');
    print '</div>';
}
dol_fiche_end();
//var_dump('<pre>');
//var_dump($autoAddLine->getFinalsByTargets());
//var_dump($autoAddLine->getFinalsData());
//var_dump($db);
//var_dump('</pre>');


print '</form>';

dol_fiche_end();


llxFooter();

$db->close();


