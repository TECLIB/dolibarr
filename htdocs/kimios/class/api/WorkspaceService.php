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

class Workspace {
  public $creationDate; // dateTime
  public $name; // string
  public $owner; // string
  public $ownerSource; // string
  public $path; // string
  public $uid; // long
  public $updateDate; // dateTime

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkspaces {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkspacesResponse {
  public $return; // ArrayOfWorkspace

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkspace {
  public $sessionId; // string
  public $workspaceId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkspaceResponse {
  public $return; // Workspace

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteWorkspace {
  public $sessionId; // string
  public $workspaceId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteWorkspaceResponse {
}

class createWorkspace {
  public $sessionId; // string
  public $name; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createWorkspaceResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateWorkspace {
  public $sessionId; // string
  public $workspaceId; // long
  public $name; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateWorkspaceResponse {
}


/**
 * WorkspaceService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class WorkspaceService extends SoapClient {

  private static $classmap = array(
                                    'Workspace' => 'Workspace',
                                    'DMServiceException' => 'DMServiceException',
                                    'getWorkspaces' => 'getWorkspaces',
                                    'getWorkspacesResponse' => 'getWorkspacesResponse',
                                    'getWorkspace' => 'getWorkspace',
                                    'getWorkspaceResponse' => 'getWorkspaceResponse',
                                    'deleteWorkspace' => 'deleteWorkspace',
                                    'deleteWorkspaceResponse' => 'deleteWorkspaceResponse',
                                    'createWorkspace' => 'createWorkspace',
                                    'createWorkspaceResponse' => 'createWorkspaceResponse',
                                    'updateWorkspace' => 'updateWorkspace',
                                    'updateWorkspaceResponse' => 'updateWorkspaceResponse',
                                   );

  public function WorkspaceService($wsdl = "http://192.168.122.118:9999/kimios/services/WorkspaceService?wsdl", $options = array()) {
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
   * @param getWorkspaces $parameters
   * @return getWorkspacesResponse
   */
  public function getWorkspaces(getWorkspaces $parameters) {
    return $this->__soapCall('getWorkspaces', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getWorkspace $parameters
   * @return getWorkspaceResponse
   */
  public function getWorkspace(getWorkspace $parameters) {
    return $this->__soapCall('getWorkspace', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteWorkspace $parameters
   * @return deleteWorkspaceResponse
   */
  public function deleteWorkspace(deleteWorkspace $parameters) {
    return $this->__soapCall('deleteWorkspace', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createWorkspace $parameters
   * @return createWorkspaceResponse
   */
  public function createWorkspace(createWorkspace $parameters) {
    return $this->__soapCall('createWorkspace', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateWorkspace $parameters
   * @return updateWorkspaceResponse
   */
  public function updateWorkspace(updateWorkspace $parameters) {
    return $this->__soapCall('updateWorkspace', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
