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

class Folder {
  public $creationDate; // dateTime
  public $name; // string
  public $owner; // string
  public $ownerSource; // string
  public $parentType; // int
  public $parentUid; // long
  public $path; // string
  public $uid; // long
  public $updateDate; // dateTime

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteFolder {
  public $sessionId; // string
  public $folderId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteFolderResponse {
}

class createFolder {
  public $sessionId; // string
  public $name; // string
  public $parentId; // long
  public $parentType; // int
  public $isSecurityInherited; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createFolderResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateFolder {
  public $sessionId; // string
  public $folderId; // long
  public $name; // string
  public $parentId; // long
  public $parentType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateFolderResponse {
}

class getFolders {
  public $sessionId; // string
  public $parentId; // long
  public $parentType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getFoldersResponse {
  public $return; // ArrayOfFolder

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getFolder {
  public $sessionId; // string
  public $folderId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getFolderResponse {
  public $return; // Folder

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * FolderService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class FolderService extends SoapClient {

  private static $classmap = array(
                                    'Folder' => 'Folder',
                                    'DMServiceException' => 'DMServiceException',
                                    'deleteFolder' => 'deleteFolder',
                                    'deleteFolderResponse' => 'deleteFolderResponse',
                                    'createFolder' => 'createFolder',
                                    'createFolderResponse' => 'createFolderResponse',
                                    'updateFolder' => 'updateFolder',
                                    'updateFolderResponse' => 'updateFolderResponse',
                                    'getFolders' => 'getFolders',
                                    'getFoldersResponse' => 'getFoldersResponse',
                                    'getFolder' => 'getFolder',
                                    'getFolderResponse' => 'getFolderResponse',
                                   );

  public function FolderService($wsdl = "http://192.168.122.118:9999/kimios/services/FolderService?wsdl", $options = array()) {
    foreach(self::$classmap as $key => $value) {
      if(!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    parent::__construct($wsdl, $options);
  }

  /**
   *  
   *
   * @param deleteFolder $parameters
   * @return deleteFolderResponse
   */
  public function deleteFolder(deleteFolder $parameters) {
    return $this->__soapCall('deleteFolder', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createFolder $parameters
   * @return createFolderResponse
   */
  public function createFolder(createFolder $parameters) {
    return $this->__soapCall('createFolder', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateFolder $parameters
   * @return updateFolderResponse
   */
  public function updateFolder(updateFolder $parameters) {
    return $this->__soapCall('updateFolder', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getFolder $parameters
   * @return getFolderResponse
   */
  public function getFolder(getFolder $parameters) {
    return $this->__soapCall('getFolder', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getFolders $parameters
   * @return getFoldersResponse
   */
  public function getFolders(getFolders $parameters) {
    return $this->__soapCall('getFolders', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
