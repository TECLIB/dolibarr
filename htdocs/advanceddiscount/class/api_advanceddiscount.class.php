<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2018 SuperAdmin
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

use Luracast\Restler\RestException;

dol_include_once('/advanceddiscount/class/advanceddiscount.class.php');



/**
 * \file    advanceddiscount/class/api_advanceddiscount.class.php
 * \ingroup advanceddiscount
 * \brief   File for API management of advanceddiscount.
 */

/**
 * API class for advanceddiscount advanceddiscount
 *
 * @smart-auto-routing false
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class AdvancedDiscountApi extends DolibarrApi
{
    /**
     * @var array   $FIELDS     Mandatory fields, checked when create and update object
     */
    static $FIELDS = array(
        'name'
    );


    /**
     * @var AdvancedDiscount $advanceddiscount {@type AdvancedDiscount}
     */
    public $advanceddiscount;

    /**
     * Constructor
     *
     * @url     GET /
     *
     */
    function __construct()
    {
		global $db, $conf;
		$this->db = $db;
        $this->advanceddiscount = new AdvancedDiscount($this->db);
    }

    /**
     * Get properties of a advanceddiscount object
     *
     * Return an array with advanceddiscount informations
     *
     * @param 	int 	$id ID of advanceddiscount
     * @return 	array|mixed data without useless information
	 *
     * @url	GET advanceddiscount/{id}
     * @throws 	RestException
     */
    function get($id)
    {
		if (!DolibarrApiAccess::$user->hasRight('advanceddiscount', 'read')) {
			throw new RestException(401);
		}

        $result = $this->advanceddiscount->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'AdvancedDiscount not found');
        }

		if( ! DolibarrApi::_checkAccessToResource('advanceddiscount',$this->advanceddiscount->id)) {
			throw new RestException(403, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		return $this->_cleanObjectDatas($this->advanceddiscount);
    }


    /**
     * List advanceddiscount
     *
     * Get a list of advanceddiscount
     *
     * @param string	       $sortfield	        Sort field
     * @param string	       $sortorder	        Sort order
     * @param int		       $limit		        Limit for list
     * @param int		       $page		        Page number
     * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
     * @return  array                               Array of order objects
     *
     * @throws RestException
     *
     * @url	GET /advanceddiscount/
     */
    function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '') {
        global $db, $conf;

        $obj_ret = array();

        $socid = DolibarrApiAccess::$user->socid ? DolibarrApiAccess::$user->socid : '';

        $restictonsocid = 0;	// Set to 1 if there is a field socid in table of object

        // If the internal user must only see his customers, force searching by him
        if ($restictonsocid && ! DolibarrApiAccess::$user->hasRight('societe', 'client', 'voir') && !$socid) $search_sale = DolibarrApiAccess::$user->id;

        $sql = "SELECT t.rowid";
        if ($restictonsocid && (!DolibarrApiAccess::$user->hasRight('societe', 'client', 'voir') && !$socid) || $search_sale > 0) $sql .= ", sc.fk_soc, sc.fk_user"; // We need these fields in order to filter by sale (including the case where the user can only see his prospects)
        $sql.= " FROM ".MAIN_DB_PREFIX."advanceddiscount_advanceddiscount as t";

        if ($restictonsocid && (!DolibarrApiAccess::$user->hasRight('societe', 'client', 'voir') && !$socid) || $search_sale > 0) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc"; // We need this table joined to the select in order to filter by sale
        $sql.= " WHERE 1 = 1";

		// Example of use $mode
        //if ($mode == 1) $sql.= " AND s.client IN (1, 3)";
        //if ($mode == 2) $sql.= " AND s.client IN (2, 3)";
        $tmpobject = new AdvancedDiscount($db);
        if ($tmpobject->ismultientitymanaged) $sql.= ' AND t.entity IN ('.getEntity('advanceddiscount').')';

        if ($restictonsocid && (!DolibarrApiAccess::$user->hasRight('societe', 'client', 'voir') && !$socid) || $search_sale > 0) $sql.= " AND t.fk_soc = sc.fk_soc";
        if ($restictonsocid && $socid) $sql.= " AND t.fk_soc = ".((int) $socid);
        if ($restictonsocid && $search_sale > 0) $sql.= " AND t.rowid = sc.fk_soc";		// Join for the needed table to filter by sale
        // Insert sale filter
        if ($restictonsocid && $search_sale > 0)
        {
            $sql .= " AND sc.fk_user = ".$search_sale;
        }
        if ($sqlfilters)
        {
            if (! DolibarrApi::_checkFilters($sqlfilters))
            {
                throw new RestException(503, 'Error when validating parameter sqlfilters '.$sqlfilters);
            }
	        $regexstring='\(([^:\'\(\)]+:[^:\'\(\)]+:[^:\(\)]+)\)';
            $sql.=" AND (".preg_replace_callback('/'.$regexstring.'/', 'DolibarrApi::_forge_criteria_callback', $sqlfilters).")";
        }

        $sql.= $db->order($sortfield, $sortorder);
        if ($limit)	{
            if ($page < 0)
            {
                $page = 0;
            }
            $offset = $limit * $page;

            $sql.= $db->plimit($limit + 1, $offset);
        }

        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            while ($i < $num)
            {
                $obj = $db->fetch_object($result);
                $advanceddiscount_static = new AdvancedDiscount($db);
                if($advanceddiscount_static->fetch($obj->rowid)) {
                    $obj_ret[] = $this->_cleanObjectDatas($advanceddiscount_static);
                }
                $i++;
            }
        }
        else {
            throw new RestException(503, 'Error when retrieve advanceddiscount list');
        }
        if( ! count($obj_ret)) {
            throw new RestException(404, 'No AdvancedDiscount found');
        }
		return $obj_ret;
    }

    /**
     * Create advanceddiscount object
     *
     * @param array $request_data   Request datas
     * @return int  ID of advanceddiscount
     *
     * @url	POST advanceddiscount/
     */
    function post($request_data = NULL)
    {
        if(! DolibarrApiAccess::$user->hasRight('advanceddiscount', 'create')) {
			throw new RestException(401);
		}
        // Check mandatory fields
        $result = $this->_validate($request_data);

        foreach($request_data as $field => $value) {
            $this->advanceddiscount->$field = $value;
        }
        if( ! $this->advanceddiscount->create(DolibarrApiAccess::$user)) {
            throw new RestException(500);
        }
        return $this->advanceddiscount->id;
    }

    /**
     * Update advanceddiscount
     *
     * @param int   $id             Id of advanceddiscount to update
     * @param array $request_data   Datas
     * @return int
     *
     * @url	PUT advanceddiscount/{id}
     */
    function put($id, $request_data = NULL)
    {
        if(! DolibarrApiAccess::$user->hasRight('advanceddiscount', 'create')) {
			throw new RestException(401);
		}

        $result = $this->advanceddiscount->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'AdvancedDiscount not found');
        }

		if( ! DolibarrApi::_checkAccessToResource('advanceddiscount',$this->advanceddiscount->id)) {
			throw new RestException(403, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

        foreach($request_data as $field => $value) {
            $this->advanceddiscount->$field = $value;
        }

        if($this->advanceddiscount->update($id, DolibarrApiAccess::$user))
            return $this->get($id);

        return false;
    }

    /**
     * Delete advanceddiscount
     *
     * @param   int     $id   AdvancedDiscount ID
     * @return  array
     *
     * @url	DELETE advanceddiscount/{id}
     */
    function delete($id)
    {
        if (! DolibarrApiAccess::$user->hasRight('advanceddiscount', 'delete')) {
			throw new RestException(401);
		}
        $result = $this->advanceddiscount->fetch($id);
        if (! $result) {
            throw new RestException(404, 'AdvancedDiscount not found');
        }

		if (! DolibarrApi::_checkAccessToResource('advanceddiscount',$this->advanceddiscount->id)) {
			throw new RestException(403, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		if (! $this->advanceddiscount->delete(DolibarrApiAccess::$user, 0))
        {
        	throw new RestException(500, 'Error '.$this->advanceddiscount->error);
        }

         return array(
            'success' => array(
                'code' => 200,
                'message' => 'AdvancedDiscount deleted'
            )
        );

    }


    /**
     * Clean sensible object datas
     *
     * @param   object  $object    Object to clean
     * @return    array    Array of cleaned object properties
     */
    function _cleanObjectDatas($object)
    {
    	$object = parent::_cleanObjectDatas($object);

    	unset($object->linkedObjectsIds);
    	unset($object->array_options);
    	unset($object->canvas);
    	unset($object->fk_project);
    	unset($object->contact_id);
    	unset($object->contact);
    	unset($object->thirdparty);
    	unset($object->user);
    	unset($object->origin);
    	unset($object->origin_id);
    	unset($object->ref_ext);
    	unset($object->statut);
    	unset($object->country);
    	unset($object->country_id);
    	unset($object->country_code);
    	unset($object->barcode_type);
    	unset($object->barcode_type_code);
    	unset($object->barcode_type_label);
    	unset($object->barcode_type_coder);
    	unset($object->mode_reglement_id);
    	unset($object->cond_reglement_id);
    	unset($object->cond_reglement);
    	unset($object->note_public);
    	unset($object->isextrafieldmanaged);
    	unset($object->fk_account);
    	unset($object->note_private);
    	unset($object->model_pdf);
    	unset($object->shipping_method_id);
    	unset($object->fk_delivery_address);
    	unset($object->total_ht);
    	unset($object->total_ttc);
    	unset($object->note);
    	unset($object->total_localtax1);
    	unset($object->total_localtax2);
    	unset($object->total_tva);
    	unset($object->lines);
    	unset($object->fk_incoterms);
    	unset($object->location_incoterms);
    	unset($object->libelle_incoterms);
    	unset($object->name);
    	unset($object->firstname);
    	unset($object->lastname);
    	unset($object->civility_id);

    	return $object;
    }

    /**
     * Validate fields before create or update object
     *
     * @param array $data   Data to validate
     * @return array
     *
     * @throws RestException
     */
    function _validate($data)
    {
        $advanceddiscount = array();
        foreach (AdvancedDiscountApi::$FIELDS as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
            $advanceddiscount[$field] = $data[$field];
        }
        return $advanceddiscount;
    }
}
