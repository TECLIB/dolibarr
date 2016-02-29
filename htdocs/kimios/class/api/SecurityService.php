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

class DMEntitySecurity {
  public $dmEntityType; // int
  public $dmEntityUid; // long
  public $fullAccess; // boolean
  public $fullName; // string
  public $name; // string
  public $read; // boolean
  public $source; // string
  public $type; // int
  public $write; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class isAdmin {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class isAdminResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class canCreateWorkspace {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class canCreateWorkspaceResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getGroups {
  public $sessionId; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getGroupsResponse {
  public $return; // ArrayOfGroup

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class isSessionAlive {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class isSessionAliveResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUsers {
  public $sessionId; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUsersResponse {
  public $return; // ArrayOfUser

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDMEntitySecurities {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int
  public $xmlStream; // string
  public $isRecursive; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDMEntitySecuritiesResponse {
}

class canWrite {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class canWriteResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUser {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUserResponse {
  public $return; // User

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getGroup {
  public $sessionId; // string
  public $groupId; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getGroupResponse {
  public $return; // Group

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDMEntitySecurities {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDMEntitySecuritiesResponse {
  public $return; // ArrayOfDMEntitySecurity

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class startSession {
  public $userName; // string
  public $userSource; // string
  public $password; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class startSessionResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class hasReportingAccess {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class hasReportingAccessResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class hasStudioAccess {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class hasStudioAccessResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class hasFullAccess {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class hasFullAccessResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAuthenticationSources {
}

class getAuthenticationSourcesResponse {
  public $return; // ArrayOfAuthenticationSource

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class canRead {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class canReadResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * SecurityService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class SecurityService extends SoapClient {

  private static $classmap = array(
                                    'DMEntitySecurity' => 'DMEntitySecurity',
                                    'User' => 'User',
                                    'Group' => 'Group',
                                    'AuthenticationSource' => 'AuthenticationSource',
                                    'DMServiceException' => 'DMServiceException',
                                    'isAdmin' => 'isAdmin',
                                    'isAdminResponse' => 'isAdminResponse',
                                    'canCreateWorkspace' => 'canCreateWorkspace',
                                    'canCreateWorkspaceResponse' => 'canCreateWorkspaceResponse',
                                    'getGroups' => 'getGroups',
                                    'getGroupsResponse' => 'getGroupsResponse',
                                    'isSessionAlive' => 'isSessionAlive',
                                    'isSessionAliveResponse' => 'isSessionAliveResponse',
                                    'getUsers' => 'getUsers',
                                    'getUsersResponse' => 'getUsersResponse',
                                    'updateDMEntitySecurities' => 'updateDMEntitySecurities',
                                    'updateDMEntitySecuritiesResponse' => 'updateDMEntitySecuritiesResponse',
                                    'canWrite' => 'canWrite',
                                    'canWriteResponse' => 'canWriteResponse',
                                    'getUser' => 'getUser',
                                    'getUserResponse' => 'getUserResponse',
                                    'getGroup' => 'getGroup',
                                    'getGroupResponse' => 'getGroupResponse',
                                    'getDMEntitySecurities' => 'getDMEntitySecurities',
                                    'getDMEntitySecuritiesResponse' => 'getDMEntitySecuritiesResponse',
                                    'startSession' => 'startSession',
                                    'startSessionResponse' => 'startSessionResponse',
                                    'hasReportingAccess' => 'hasReportingAccess',
                                    'hasReportingAccessResponse' => 'hasReportingAccessResponse',
                                    'hasStudioAccess' => 'hasStudioAccess',
                                    'hasStudioAccessResponse' => 'hasStudioAccessResponse',
                                    'hasFullAccess' => 'hasFullAccess',
                                    'hasFullAccessResponse' => 'hasFullAccessResponse',
                                    'getAuthenticationSources' => 'getAuthenticationSources',
                                    'getAuthenticationSourcesResponse' => 'getAuthenticationSourcesResponse',
                                    'canRead' => 'canRead',
                                    'canReadResponse' => 'canReadResponse',
                                   );

  public function SecurityService($wsdl = "http://192.168.122.118:9999/kimios/services/SecurityService?wsdl", $options = array()) {
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
   * @param isAdmin $parameters
   * @return isAdminResponse
   */
  public function isAdmin(isAdmin $parameters) {
    return $this->__soapCall('isAdmin', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param canCreateWorkspace $parameters
   * @return canCreateWorkspaceResponse
   */
  public function canCreateWorkspace(canCreateWorkspace $parameters) {
    return $this->__soapCall('canCreateWorkspace', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getGroups $parameters
   * @return getGroupsResponse
   */
  public function getGroups(getGroups $parameters) {
    return $this->__soapCall('getGroups', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getUsers $parameters
   * @return getUsersResponse
   */
  public function getUsers(getUsers $parameters) {
    return $this->__soapCall('getUsers', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param isSessionAlive $parameters
   * @return isSessionAliveResponse
   */
  public function isSessionAlive(isSessionAlive $parameters) {
    return $this->__soapCall('isSessionAlive', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param canWrite $parameters
   * @return canWriteResponse
   */
  public function canWrite(canWrite $parameters) {
    return $this->__soapCall('canWrite', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateDMEntitySecurities $parameters
   * @return updateDMEntitySecuritiesResponse
   */
  public function updateDMEntitySecurities(updateDMEntitySecurities $parameters) {
    return $this->__soapCall('updateDMEntitySecurities', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getGroup $parameters
   * @return getGroupResponse
   */
  public function getGroup(getGroup $parameters) {
    return $this->__soapCall('getGroup', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getUser $parameters
   * @return getUserResponse
   */
  public function getUser(getUser $parameters) {
    return $this->__soapCall('getUser', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDMEntitySecurities $parameters
   * @return getDMEntitySecuritiesResponse
   */
  public function getDMEntitySecurities(getDMEntitySecurities $parameters) {
    return $this->__soapCall('getDMEntitySecurities', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param startSession $parameters
   * @return startSessionResponse
   */
  public function startSession(startSession $parameters) {
    return $this->__soapCall('startSession', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param hasReportingAccess $parameters
   * @return hasReportingAccessResponse
   */
  public function hasReportingAccess(hasReportingAccess $parameters) {
    return $this->__soapCall('hasReportingAccess', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param hasStudioAccess $parameters
   * @return hasStudioAccessResponse
   */
  public function hasStudioAccess(hasStudioAccess $parameters) {
    return $this->__soapCall('hasStudioAccess', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAuthenticationSources $parameters
   * @return getAuthenticationSourcesResponse
   */
  public function getAuthenticationSources(getAuthenticationSources $parameters) {
    return $this->__soapCall('getAuthenticationSources', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param hasFullAccess $parameters
   * @return hasFullAccessResponse
   */
  public function hasFullAccess(hasFullAccess $parameters) {
    return $this->__soapCall('hasFullAccess', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param canRead $parameters
   * @return canReadResponse
   */
  public function canRead(canRead $parameters) {
    return $this->__soapCall('canRead', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
