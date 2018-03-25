<?php

/* Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2011 Regis Houssin        <regis@dolibarr.fr>
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
 *      \file       htdocs/includes/triggers/interface_all_Demo.class.php
 *      \ingroup    core
 *      \brief      Fichier de demo de personalisation des actions du workflow
 *      \remarks    Son propre fichier d'actions peut etre cree par recopie de celui-ci:
 *                  - Le nom du fichier doit etre: interface_modMymodule_Mytrigger.class.php
 * 					                           ou: interface_all_Mytrigger.class.php
 *                  - Le fichier doit rester stocke dans includes/triggers
 *                  - Le nom de la classe doit etre InterfaceMytrigger
 *                  - Le nom de la methode constructeur doit etre InterfaceMytrigger
 *                  - Le nom de la propriete name doit etre Mytrigger
 * 		\version	$Id: interface_all_Demo.class.php-NORUN,v 1.30 2011/07/31 23:29:46 eldy Exp $
 */

/**
 *      Class of triggers for autoaddline module
 */
class InterfaceAutoAddLine
{

    var $db;

    /**
     *   Constructor.
     *
     *   @param      Database   $db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "system";
        $this->description = "Triggers of this module will check a project is linked to validated element. It may also replace _projectref_ with ref of linked project.";
        $this->picto = 'project';
        $this->db = $db;
        $this->version = 'dolibarr';            // 'development', 'experimental', 'dolibarr' or version
    }

    /**
     *   Return name of trigger file
     *   @return     string      Name of trigger file
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   Return description of trigger file
     *   @return     string      Description of trigger file
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Return version of trigger file
     *   @return     string      Version of trigger file
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development')
            return $langs->trans("Development");
        elseif ($this->version == 'experimental')
            return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr')
            return DOL_VERSION;
        elseif ($this->version)
            return $this->version;
        else
            return $langs->trans("Unknown");
    }

    /**
     *      Function called when a Dolibarrr business event is done.
     *      All functions "runTrigger" are triggered if file is inside directory htdocs/includes/triggers
     *
     *      @param      string		$action      Code de l'evenement
     *      @param      Object		$object      Objet concerne
     *      @param      User		$user        Objet user
     *      @param      Translate	$langs       Objet langs
     *      @param      Conf		$conf        Objet conf
     *      @return     int         			<0 if KO, 0 if no triggered ran, >0 if OK
     */
    function runTrigger($action, $object, $user, $langs, $conf)
    {
        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action
        // Users

        switch ($action)
        {
            case 'LINEBILL_UPDATE':
            case 'LINEBILL_INSERT':
            case 'LINEBILL_DELETE':
                dol_include_once('/autoaddline/class/autoaddline.class.php');

                // Prepare some data arrays: existing autoaddlines ids and products linked to
                $staticAutoAddLine = new AutoAddLine($this->db);
                $finalsData = $staticAutoAddLine->getFinalsData();
                $finalsByTargets = $staticAutoAddLine->getFinalsByTargets();
                $finalsIds = array_keys($finalsData);

                // Final lines data to add array
                $finalsLines = array();

                // Get line object fetched
                if (!($object->fetch($object->rowid) > 0))
                    $error++;

                // Si la ligne appelante est une ligne finale, STOP (pas de no_trigger dans les fonction DU CORE -_- ) - autrement loop infini pour delete
                if (in_array($object->fk_product, $finalsIds))
                    return 1;

                // Get invoice for current line calling
                $invoice = new Facture($this->db);
                if (!($invoice->fetch($object->fk_facture) > 0))
                    $error++;
                if (!($invoice->fetch_lines() > 0))
                    $error++;

                $test = 0;
                foreach ($invoice->lines as $invoiceLine)
                {
                    // If current service define as a final line exists in finals, REMOVE IT & DON NOT INCLUDE in calc
                    if (isset($invoiceLine->fk_product) && in_array($invoiceLine->fk_product, $finalsIds))
                    {
                        $tempInvoiceLine = new FactureLigne($this->db);
                        $tempInvoiceLine->rowid = $invoiceLine->rowid;
                        if (!($tempInvoiceLine->delete() > 0))
                            $error++;
                    } else
                    {
                        if (array_key_exists($invoiceLine->fk_product, $finalsByTargets))
                        {
                            foreach ($finalsByTargets[$invoiceLine->fk_product]['finals'] as $finalsToApply)
                            {
                                if (!array_key_exists($finalsToApply, $finalsLines))
                                    $finalsLines[$finalsToApply] = 0;

                                // Prepare line when service is a rate on price
                                if ($finalsData[$finalsToApply]['type'] == AutoAddLine::SERVICE_TYPE_RATEONPRICE)
                                    $finalsLines[$finalsToApply] += ($finalsData[$finalsToApply]['value'] / 100) * $invoiceLine->total_ht;
                            }
                        }
                    }
                }

                foreach ($finalsLines as $autoAddLineServiceId => $amount)
                    if (!($invoice->addline($invoice->id, $lineDesc, price2num($amount), 1, 0, '', '', $autoAddLineServiceId) > 0))
                        $error++;

                break;
        }

        if (!$error)
            return 1;

        return -1;
    }

}

