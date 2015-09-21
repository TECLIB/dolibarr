<?php

/* Copyright (C) 2007-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *      \file       htdocs/stockManager/class/destruction.class.php
 *      \ingroup    stockManager
 *      \brief      This file is part of the Auguria's stockManager's core
 * 		\version    destruction.class.php,v 1.0
 * 		\author		Auguria
 */
// requires

/**
 *      \class      destruction
 *      \brief      Class to manage destructions in logistic hubs
 */
class AutoAddLine // extends CommonObject
{

    const SERVICE_TYPE_RATEONPRICE = 0;
    const NAMINGTYPE_ONLYLABELS = 0;
    const NAMINGTYPE_ONLYREFERENCE = 1;
    const NAMINGTYPE_BOTH = 2;

    var $db;       //!< To store db handler
    var $error;       //!< To return error code (or message)
    var $errors = array();    //!< To return several error codes (or messages)
    //var $element='stockmanager_destruction_list';			//!< Id that identify managed objects
    var $table_element = 'autoaddline'; //!< Name of table without prefix where object is stored
    var $id;
    var $type;
    var $value;
    var $lines;

    /**
     *      Constructor
     *      @param      DB      Database handler
     */
    function AutoAddLine($DB)
    {
        $this->db = $DB;
        return 1;
    }

    /**
     *      Create object into database
     *      @param      user        	User that create
     *      @param      notrigger	    0=launch triggers after, 1=disable triggers
     *      @return     int         	<0 if KO, Id of created object if OK
     */
    function create(User $user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        // Clean parameters
        // Check parameters
        // Put here code to add control on parameters values		        
        // Insert request
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "autoaddline(";
        $sql.= " label,";
        $sql.= " fk_product_base";
        if (isset($this->type))
            $sql.= ", final_service_type";
        if (isset($this->value))
            $sql.= ", final_service_value";
        $sql.= ") VALUES (";
        $sql.= "'".$this->db->escape($this->label)."', ";
        $sql.= $this->product_id;
        if (isset($this->type))
            $sql.= ", " . $this->type;
        if (isset($this->type))
            $sql.= ", " . $this->value;
        $sql.= ")";

        $this->db->begin();

        dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->id = $this->db->last_insert_id($resql);

            if (!$notrigger)
            {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }
        else
        {   
            $error++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        // Commit or rollback
        if ($error)
        {
            $this->db->rollback();
            return -1 * $error;
        }
        else
        {
            $this->db->commit();
            return $this->id;
        }
    }

    function create_lines(Array $servicesIdsToAdd, User $user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;


        // Check parameters
        // Put here code to add control on parameters values		        
        $insertValues = array();
        foreach ($servicesIdsToAdd as $serviceId)
            $insertValues[] = '(' . $this->id . ',' . $serviceId . ')';
        // Insert request
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "autoaddline_association(";
        $sql.= " fk_product_base";
        $sql.= ", fk_product_target";
        $sql.= ") VALUES " . implode(',', $insertValues);

        $this->db->begin();

        dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);

        if (!$resql)
        {
            $error++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (!$error)
        {

            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . 'autoaddline');

            if (!$notrigger)
            {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }

        // Commit or rollback
        if ($error)
        {
            foreach ($this->errors as $errmsg)
            {
                dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        } else
        {
            $this->db->commit();
            return $this->id;
        }
    }

    /**
     *    Load object in memory from database
     *    @param      id          id object
     *    @return     int         <0 if KO, >0 if OK
     */
    function fetch($idService)
    {
        global $langs;
        $sql = "SELECT";
        $sql.= " fk_product_base";
        $sql.= ", final_service_type";
        $sql.= ", final_service_value";

        $sql.= " FROM " . MAIN_DB_PREFIX . "autoaddline";
        $sql.= " WHERE fk_product_base = " . $idService;

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->fk_product_base;
                $this->type = $obj->final_service_type;
                $this->value = $obj->final_service_value;
            }
            $this->db->free($resql);

            return 1;
        } else
        {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
            return -1;
        }
    }

    public function fetch_lines()
    {
        global $langs;

        $sql = "SELECT ";
        $sql.= " fk_product_target";

        $sql.= " FROM " . MAIN_DB_PREFIX . "autoaddline_association";

        $sql.= " WHERE fk_product_base = " . $this->id;

        dol_syslog(get_class($this) . "::getPallets sql=" . $sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);

            $this->lines = array();

            if ($num)
            {
                $ii = 0;
                while ($ii < $num) {
                    $obj = $this->db->fetch_object($resql);

                    $this->lines[] = $obj->fk_product_target;

                    $ii++;
                }
                $this->db->free($resql);
            }

            return 1;
        } else
        {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::getPallets " . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *      Update object into database
     *      @param      user        	User that modify
     *      @param      notrigger	    0=launch triggers after, 1=disable triggers
     *      @return     int         	<0 if KO, >0 if OK
     */
    function update($user = 0, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        ////IMPORTANT: si le client change, on reset les lignes
        // Clean parameters
        // Check parameters
        // Put here code to add control on parameters values
        // Update request        
        $sql = "UPDATE " . MAIN_DB_PREFIX . "autoaddline SET";
        $sql.= " final_service_type = " . $this->type;
        $sql.= ", final_service_value = " . $this->value;

        $sql.= " WHERE fk_product_base = " . $this->id;

        $this->db->begin();

        dol_syslog(get_class($this) . "::update sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $error++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (!$error)
        {
            if (!$notrigger)
            {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }

        // Commit or rollback
        if ($error)
        {
            foreach ($this->errors as $errmsg)
            {
                dol_syslog(get_class($this) . "::update " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        } else
        {
            $this->db->commit();
            return 1;
        }
    }

    /**
     *   Delete object in database
     * 	 @param     user        	User that delete
     *   @param     notrigger	    0=launch triggers after, 1=disable triggers
     *   @return	int				<0 if KO, >0 if OK
     */
    function delete($user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        $this->db->begin();

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "autoaddline";
        $sql.= " WHERE fk_product_base =" . $this->id;

        if (!$this->delete_lines())
            $error++;

        dol_syslog(get_class($this) . "::delete sql=" . $sql);
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $error++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (!$error)
        {
            if (!$notrigger)
            {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }

        // Commit or rollback
        if ($error)
        {
            foreach ($this->errors as $errmsg)
            {
                dol_syslog(get_class($this) . "::delete " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        } else
        {
            $this->db->commit();
            return 1;
        }
    }

    public function delete_lines(Array $multipleIds = array(), $targetId = '')
    {
        global $conf, $langs;
        $error = 0;

        $this->db->begin();

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "autoaddline_association";
        if (!$targetId)
        {
            $sql.= " WHERE fk_product_base =" . $this->id;
            if (!empty($multipleIds))
                $sql.= " AND fk_product_target IN (" . implode(',', $multipleIds) . ")";
        } else
            $sql.= " WHERE fk_product_target =" . $targetId;
        dol_syslog(get_class($this) . "::delete_lines sql=" . $sql);
        $resql = $this->db->query($sql);
        if (!$resql)
        {
            $error++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (!$error)
        {
            if (!$notrigger)
            {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }

        // Commit or rollback
        if ($error)
        {
            foreach ($this->errors as $errmsg)
            {
                dol_syslog(get_class($this) . "::delete " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        } else
        {
            $this->db->commit();
            return 1;
        }
    }

    /**
     * 		Load an object from its id and create a new one in database
     * 		@param      fromid     		Id of object to clone
     * 	 	@return		int				New id of clone
     */
    function createFromClone($fromid)
    {
        global $user, $langs;

        $error = 0;

        $object = new Stockmove($this->db);

        $this->db->begin();

        // Load source object
        $object->fetch($fromid);
        $object->id = 0;
        $object->statut = 0;

        // Clear fields
        // ...
        // Create clone
        $result = $object->create($user);

        // Other options
        if ($result < 0)
        {
            $this->error = $object->error;
            $error++;
        }

        if (!$error)
        {
            
        }

        // End
        if (!$error)
        {
            $this->db->commit();
            return $object->id;
        } else
        {
            $this->db->rollback();
            return -1;
        }
    }

    public function getTypes()
    {
        global $langs;
        return array(
                self::SERVICE_TYPE_RATEONPRICE => $langs->trans('RateOnPrice')
        );
    }
    
    public function getFinalsData()
    {
        global $langs;

        $sql = "SELECT ";
        $sql.= "  fk_product_base";
        $sql.= ", final_service_type";
        $sql.= ", final_service_value";

        $sql.= " FROM " . MAIN_DB_PREFIX . "autoaddline";


        dol_syslog(get_class($this) . "::getPallets sql=" . $sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $data = array();

            if ($num)
            {
                $ii = 0;
                while ($ii < $num) {
                    $obj = $this->db->fetch_object($resql);

                    $data[$obj->fk_product_base] = array(
                            'type' => $obj->final_service_type,
                            'value' => $obj->final_service_value
                    );
                    
                    $ii++;
                }
                $this->db->free($resql);
            }
            return $data;
        } else
        {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::getPallets " . $this->error, LOG_ERR);
            return -1;
        }
    }

    public function getFinalsByTargets()
    {
        global $langs;

        $sql = "SELECT ";
        $sql.= "  fl.fk_product_base";
        $sql.= ", fla.fk_product_target";

        $sql.= " FROM " . MAIN_DB_PREFIX . "autoaddline as fl";

        $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "autoaddline_association as fla";
        $sql.= " ON fl.fk_product_base = fla.fk_product_base";

        dol_syslog(get_class($this) . "::getPallets sql=" . $sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $finalsByTargets = array();

            if ($num)
            {
                $ii = 0;
                while ($ii < $num) {
                    $obj = $this->db->fetch_object($resql);

//                    $data[$obj->fk_product_base]['type'] = $obj->final_service_type;
//                    $data[$obj->fk_product_base]['value'] = $obj->final_service_value;
//                    if (isset($obj->fk_product_target))
//                        $data[$obj->fk_product_base]['targets'][] = $obj->fk_product_target;
                    if (isset($obj->fk_product_target))
                        $finalsByTargets[$obj->fk_product_target]['finals'][] = $obj->fk_product_base;

                    $ii++;
                }
                $this->db->free($resql);
            }
            return $finalsByTargets;
        } else
        {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::getPallets " . $this->error, LOG_ERR);
            return -1;
        }
    }

    public function getProducts()
    {
        global $langs;

        $sql = "SELECT ";
        $sql.= " pt.rowid";
        $sql.= ", pt.label";
        $sql.= ", pt.ref";
        $sql.= ", pt.fk_product_type";
        $sql.= ", fl.fk_product_base";
        $sql.= " FROM " . MAIN_DB_PREFIX . "product as pt";
        $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "autoaddline as fl";
        $sql.= " ON pt.rowid = fl.fk_product_base";

        dol_syslog(get_class($this) . "::getPallets sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $usableServices = array('labels' => array(), 'references' => array());
            $finalServices = array('labels' => array(), 'references' => array());
            $products = array('labels' => array(), 'references' => array());

            if ($num)
            {
                $ii = 0;
                while ($ii < $num) {
                    $obj = $this->db->fetch_object($resql);

                    if ($obj->fk_product_type == 1)
                    {
                        if ($obj->fk_product_base != null)
                        {
                            $finalServices['labels'][$obj->rowid] = $obj->label;
                            $finalServices['references'][$obj->rowid] = $obj->ref;
                        } 
                        else
                        {
                            $usableServices['labels'][$obj->rowid] = $obj->label;
                            $usableServices['references'][$obj->rowid] = $obj->ref;

                            $products['labels'][$obj->rowid] = $obj->label;
                            $products['references'][$obj->rowid] = $obj->ref;
                        }
                    } else
                    {
                        $products['labels'][$obj->rowid] = $obj->label;
                        $products['references'][$obj->rowid] = $obj->ref;
                    }
                    $ii++;
                }
                $this->db->free($resql);
            }

            // Finals
            $finalServicesEmpty[-1] = '';
            if (empty($finalServices['labels']))
            {
                $finalServicesEmpty[-1] = $langs->trans('NoServiceAvailable');
            }
            $finalServices['labels'] = $finalServicesEmpty + $finalServices['labels'];
            $finalServices['references'] = $finalServicesEmpty + $finalServices['references'];

            // Usables
            $usableServicesEmpty[-1] = '';
            if (empty($usableServices['labels']))
            {
                $usableServicesEmpty[-1] = $langs->trans('NoServiceAvailable');
            }
            $usableServices['labels'] = $usableServicesEmpty + $usableServices['labels'];
            $usableServices['references'] = $usableServicesEmpty + $usableServices['references'];

            // Products
            $productsEmpty[-1] = '';
            if (empty($products['labels']))
            {
                $productsEmpty[-1] = $langs->trans('NoProductAvailable');
            }
            $products['labels'] = $productsEmpty + $products['labels'];
            $products['references'] = $productsEmpty + $products['references'];

            return array(
                    'final_services' => $finalServices,
                    'usable_services' => $usableServices,
                    'products' => $products
            );
        } else
        {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::getPallets " . $this->error, LOG_ERR);
            return -1;
        }
    }

    public function getUsableServices($namingType = self::NAMINGTYPE_BOTH)
    {
        $products = $this->getProducts();

        switch ($namingType)
        {
            case self::NAMINGTYPE_BOTH:
                return $products['usable_services'];
                break;
            case self::NAMINGTYPE_ONLYREFERENCE:
                return $products['usable_services']['references'];
                break;
            case self::NAMINGTYPE_ONLYLABELS:
                return $products['usable_services']['labels'];
                break;
        }
    }

    public function getFinalServices($namingType = self::NAMINGTYPE_BOTH)
    {
        $products = $this->getProducts();

        switch ($namingType)
        {
            case self::NAMINGTYPE_BOTH:
                return $products['final_services'];
                break;
            case self::NAMINGTYPE_ONLYREFERENCE:
                return $products['final_services']['references'];
                break;
            case self::NAMINGTYPE_ONLYLABELS:
                return $products['final_services']['labels'];
                break;
        }
    }

    public function getTargetProducts($namingType = self::NAMINGTYPE_BOTH)
    {
        $products = $this->getProduct();

        switch ($namingType)
        {
            case self::NAMINGTYPE_BOTH:
                return $products['products'];
                break;
            case self::NAMINGTYPE_ONLYREFERENCE:
                return $products['products']['references'];
                break;
            case self::NAMINGTYPE_ONLYLABELS:
                return $products['products']['labels'];
                break;
        }
    }

}

?>
