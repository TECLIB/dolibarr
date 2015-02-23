<?php
/* Copyright (C) 2011   François Legastelois <flegastelois@teclib.com>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/*
 * @version $Id: commondbtm.class.php 14498 2011-05-20 13:39:24Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

class Common_DB_Note
{

   var $updates = array();
   var $oldvalues = array();
   var $input = array();
   var $history_blacklist = array();

   /**
    * Add an item to the database
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @return new ID of the item is insert successfull else false
   **/
   function addToDB() {
      global $db;

      //unset($this->fields["id"]);
      $nb_fields = count($this->fields);
      if ($nb_fields>0) {
         // Build query
         $query = "INSERT INTO `".$this->getTable()."` (";

         $i = 0;
         foreach ($this->fields as $key => $val) {
            $fields[$i] = $key;
            $values[$i] = $val;
            $i++;
         }

         for ($i=0 ; $i<$nb_fields; $i++) {
            $query .= "`".$fields[$i]."`";
            if ($i!=$nb_fields-1) {
               $query .= ",";
            }
         }

         $query .= ") VALUES (";
         for ($i=0 ; $i<$nb_fields ; $i++) {

            if ($values[$i]=='NULL') {
               $query .= $values[$i];
            } else {
               $query .= "'".$db->escape($values[$i])."'";
            }

            if ($i!=$nb_fields-1) {
               $query .= ",";
            }

         }
         $query .= ")";

         dol_syslog("Common_DB::addToDB::".$query, LOG_INFO);

         if ($result=$db->query($query))
         {
            $this->fields['rowid'] = $db->last_insert_id($this->getTable());
            return $this->fields['rowid'];
         }

      }
      return false;
   }

   /**
    * Retrieve an item from the database
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @param $ID ID of the item to get
    *
    * @return true if succeed else false
   **/
   function getFromDB ($ID) {
      global $db;
      // Make new database object and fill variables

      // != 0 because 0 is consider as empty
      if (strlen($ID)==0) {
         return false;
      }

      $query = "SELECT * FROM `".$this->getTable()."` WHERE `".$this->getIndexName()."` = '$ID'";

      dol_syslog("Common_DB::getFromDB::".$query, LOG_INFO);

      if ($result = $db->query($query))
      {
         if ($db->num_rows($result)==1)
         {
            $this->fields = $db->fetch_array($result);

            $this->post_getFromDB();

            return true;
         }
      }

      return false;
   }

   /**
    * Get the name of the index field
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @return name of the index field
   **/
   function getIndexName() {
      return "rowid";
   }

   /**
    * Retrieve all items from the database
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @param $condition condition used to search if needed (empty get all)
    * @param $order order field if needed
    * @param $limit limit retrieved datas if needed
    *
    * @return true if succeed else false
    */
   function find ($condition="", $order="", $limit="")
   {
      global $db;
      // Make new database object and fill variables

      $query = "SELECT * FROM `".$this->getTable()."`";

      if (!empty($condition)) {
         $query .= " WHERE $condition";
      }

      if (!empty($order)) {
         $query .= " ORDER BY $order";
      }

      if (!empty($limit)) {
         $query .= " LIMIT ".intval($limit);
      }

      dol_syslog("Common_DB::find::".$query, LOG_INFO);

      $data = array();
      if ($result = $db->query($query)) {
         if ($db->num_rows($result)) {
            while ($line = $db->fetch_array($result)) {

               $indexKey = $this->getIndexName();
               $data[$line[$indexKey]] = $line;
            }
         }
      }

      return $data;
   }

   /**
    * Actions done at the end of the getFromDB function
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @return nothing
   **/
   function post_getFromDB () {
   }

   /**
    * Actions done at the end of the deleteFromDB function
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @return nothing
   **/
   function post_deleteFromDB () {
   }

   /**
    * Actions done at the end of the update function
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @return nothing
   **/
   function post_updateItem () {
   }

   /**
    * Return a field Value if exists
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @param $field field name
    *
    * @return value of the field / false if not exists
   **/
   function getField ($field) {

      if (array_key_exists($field,$this->fields)) {
         return $this->fields[$field];
      }
      return false;
   }

   /**
    * Update some elements of an item in the database.
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @param $input array : the _POST vars returned by the item form when press update
    *
    * @return boolean : true on success
   **/
   function update($input) {
      global $db;

      $indexName = $this->getIndexName();

      if (!$this->getFromDB($input[$indexName])) {
         return false;
      }

      $this->input = $input;

      $x = 0;

      foreach ($this->input as $key => $val) {
         if (array_key_exists($key,$this->fields)) {

            // Prevent history for date statement (for date for example)
            if (is_null($this->fields[$key]) && $this->input[$key]=='NULL') {
               $this->fields[$key] = 'NULL';
            }

            if ($this->fields[$key] != $this->input[$key]) {
               if ($key!=$indexName) {
                  // Store old values
                  if (!in_array($key,$this->history_blacklist)) {
                     $this->oldvalues[$key] = $this->fields[$key];
                  }

                  $this->fields[$key] = $this->input[$key];
                  $this->updates[$x]  = $key;
                  $x++;
               }
            }

         }
      }

      if (count($this->updates)) {
         if ($this->updateInDB($this->updates,$this->oldvalues)) {
            $this->post_updateItem();
            return true;
         }
      }

      return false;
   }

   /**
    * Update the item in the database
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @param $updates fields to update
    * @param $oldvalues old values of the updated fields
    *
    * @return nothing
   **/
   function updateInDB($updates, $oldvalues=array()) {
      global $db;

      $indexName = $this->getIndexName();

      foreach ($updates as $field) {
         if (isset($this->fields[$field])) {
            $query  = "UPDATE `".$this->getTable()."` SET `".$field."`";

            if ($this->fields[$field]=="NULL") {
               $query .= " = ".$this->fields[$field];

            } else {
               $query .= " = '".$db->escape($this->fields[$field])."'";
            }

            $query .= " WHERE `".$indexName."` = '".$this->fields[$indexName]."'";

            dol_syslog("Common_DB::updateInDB::".$query, LOG_INFO);

            if (!$db->query($query)) {
               if (isset($oldvalues[$field])) {
                  unset($oldvalues[$field]);
               }
            }

         } else {
            // Clean oldvalues
            if (isset($oldvalues[$field])) {
               unset($oldvalues[$field]);
            }
         }

      }

      return true;
   }

   /**
    * Mark deleted or purge an item in the database
    *
    * FROM GLPI-PROJECT.org : See the license at the top of this file
    *
    * @param $force force the purge of the item (not used if the table do not have a deleted field)
    *
    * @return true if succeed else false
   **/
   function deleteFromDB() {
      global $db;

      $indexName = $this->getIndexName();

      $query = "DELETE FROM `".$this->getTable()."` WHERE `".$indexName."` = '".$this->fields[$indexName]."'";

      if ($result = $db->query($query)) {
         $this->post_deleteFromDB();
         return true;
      }

      return false;
   }

	function select($name='',$class='',$preselected,$value_field,$display_field) {
      $tab = $this->find();

      $out = '<select name="'.$name.'" class="'.$class.'">';
      $out.= '<option value=""></option>';
      foreach($tab as $key => $val){

         if(is_array($display_field)) {
            $display_fields = array();
            foreach($display_field['fields'] as $val){
               $display_fields[] = $tab[$key][$val];
            }
            $to_display = implode($display_field['separator'],$display_fields);
         }else{
            $to_display = $tab[$key][$display_field];
         }

         $selected = ($preselected==$tab[$key][$value_field])?"selected='selected'":"";
         $out.= '<option value="'.$tab[$key][$value_field].'" '.$selected.'>'.$to_display.'</option>';
      }
      $out.= '</select>';

      return $out;
	}

}

?>