<?php
/*
 * Kimios - Document Management System Software
 * Copyright (C) 2012-2013  DevLib'
 * Copyright (C) 2013 - François Legastelois (flegastelois@teclib.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_KIMIOS_EXEC') or die;

class Document {
  public $checkedOut; // boolean
  public $checkoutDate; // dateTime
  public $checkoutUser; // string
  public $checkoutUserSource; // string
  public $creationDate; // dateTime
  public $documentTypeName; // string
  public $documentTypeUid; // long
  public $extension; // string
  public $folderUid; // long
  public $length; // long
  public $mimeType; // string
  public $name; // string
  public $outOfWorkflow; // boolean
  public $owner; // string
  public $ownerSource; // string
  public $path; // string
  public $uid; // long
  public $updateDate; // dateTime
  public $versionCreationDate; // dateTime
  public $versionUpdateDate; // dateTime
  public $workflowStatusName; // string
  public $workflowStatusUid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

?>