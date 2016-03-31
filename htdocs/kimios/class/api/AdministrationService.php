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

class Role {
  public $role; // int
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Session {
  public $lastUse; // dateTime
  public $metaDatas; // string
  public $sessionUid; // string
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeUserFromGroup {
  public $sessionId; // string
  public $uid; // string
  public $gid; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeUserFromGroupResponse {
}

class deleteUser {
  public $sessionId; // string
  public $uid; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteUserResponse {
}

class updateGroup {
  public $sessionId; // string
  public $gid; // string
  public $name; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateGroupResponse {
}

class getManageableUsers {
  public $sessionId; // string
  public $gid; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getManageableUsersResponse {
  public $return; // ArrayOfUser

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUserRoles {
  public $sessionId; // string
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUserRolesResponse {
  public $return; // ArrayOfRole

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAuthenticationSource {
  public $sessionId; // string
  public $name; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAuthenticationSourceResponse {
  public $return; // AuthenticationSource

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class clearLock {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class clearLockResponse {
}

class createGroup {
  public $sessionId; // string
  public $gid; // string
  public $name; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createGroupResponse {
}

class getEnabledSessions {
  public $sessionId; // string
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getEnabledSessionsResponse {
  public $return; // ArrayOfSession

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAuthenticationSourceParams {
  public $sessionId; // string
  public $name; // string
  public $className; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAuthenticationSourceParamsResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class reindex {
  public $sessionId; // string
  public $path; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class reindexResponse {
}

class deleteAuthenticationSource {
  public $sessionId; // string
  public $className; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteAuthenticationSourceResponse {
}

class updateAuthenticationSource {
  public $sessionId; // string
  public $currentName; // string
  public $newName; // string
  public $className; // string
  public $xmlParameters; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateAuthenticationSourceResponse {
}

class getManageableGroups {
  public $sessionId; // string
  public $userId; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getManageableGroupsResponse {
  public $return; // ArrayOfGroup

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUserByAttribute {
  public $sessionId; // string
  public $userSource; // string
  public $attributeName; // string
  public $attributeValue; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUserByAttributeResponse {
  public $return; // User

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createRole {
  public $sessionId; // string
  public $role; // int
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createRoleResponse {
}

class getAllEnabledSessions {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAllEnabledSessionsResponse {
  public $return; // ArrayOfSession

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUserAttribute {
  public $sessionId; // string
  public $userId; // string
  public $userSource; // string
  public $attributeName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUserAttributeResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getCheckedOutDocuments {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getCheckedOutDocumentsResponse {
  public $return; // ArrayOfDocument

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRoles {
  public $sessionId; // string
  public $role; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRolesResponse {
  public $return; // ArrayOfRole

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getManageableGroup {
  public $sessionId; // string
  public $gid; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getManageableGroupResponse {
  public $return; // Group

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteRole {
  public $sessionId; // string
  public $role; // int
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteRoleResponse {
}

class getManageableUser {
  public $sessionId; // string
  public $arg1; // string
  public $arg2; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getManageableUserResponse {
  public $return; // User

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAvailableAuthenticationSource {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAvailableAuthenticationSourceResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getReindexProgress {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getReindexProgressResponse {
  public $return; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createAuthenticationSource {
  public $sessionId; // string
  public $name; // string
  public $className; // string
  public $xmlParameters; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createAuthenticationSourceResponse {
}

class getConnectedUsers {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getConnectedUsersResponse {
  public $return; // ArrayOfUser

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteGroup {
  public $sessionId; // string
  public $gid; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteGroupResponse {
}

class updateUser {
  public $sessionId; // string
  public $uid; // string
  public $userName; // string
  public $mail; // string
  public $password; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateUserResponse {
}

class createUser {
  public $sessionId; // string
  public $uid; // string
  public $userName; // string
  public $mail; // string
  public $password; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createUserResponse {
}

class removeEnabledSession {
  public $sessionId; // string
  public $sessionIdToRemove; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeEnabledSessionResponse {
}

class getAvailableAuthenticationSourceParams {
  public $sessionId; // string
  public $className; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAvailableAuthenticationSourceParamsResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class changeOwnership {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class changeOwnershipResponse {
}

class removeEnabledSessions {
  public $sessionId; // string
  public $userName; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeEnabledSessionsResponse {
}

class addUserToGroup {
  public $sessionId; // string
  public $uid; // string
  public $gid; // string
  public $authenticationSourceName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addUserToGroupResponse {
}

class setUserAttribute {
  public $sessionId; // string
  public $userId; // string
  public $userSource; // string
  public $attributeName; // string
  public $attributeValue; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class setUserAttributeResponse {
}


/**
 * AdministrationService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class AdministrationService extends SoapClient {

  private static $classmap = array(
                                    'Role' => 'Role',
                                    'User' => 'User',
                                    'Session' => 'Session',
                                    'Group' => 'Group',
                                    'AuthenticationSource' => 'AuthenticationSource',
                                    'Document' => 'Document',
                                    'DMServiceException' => 'DMServiceException',
                                    'removeUserFromGroup' => 'removeUserFromGroup',
                                    'removeUserFromGroupResponse' => 'removeUserFromGroupResponse',
                                    'deleteUser' => 'deleteUser',
                                    'deleteUserResponse' => 'deleteUserResponse',
                                    'updateGroup' => 'updateGroup',
                                    'updateGroupResponse' => 'updateGroupResponse',
                                    'getManageableUsers' => 'getManageableUsers',
                                    'getManageableUsersResponse' => 'getManageableUsersResponse',
                                    'getUserRoles' => 'getUserRoles',
                                    'getUserRolesResponse' => 'getUserRolesResponse',
                                    'getAuthenticationSource' => 'getAuthenticationSource',
                                    'getAuthenticationSourceResponse' => 'getAuthenticationSourceResponse',
                                    'clearLock' => 'clearLock',
                                    'clearLockResponse' => 'clearLockResponse',
                                    'createGroup' => 'createGroup',
                                    'createGroupResponse' => 'createGroupResponse',
                                    'getEnabledSessions' => 'getEnabledSessions',
                                    'getEnabledSessionsResponse' => 'getEnabledSessionsResponse',
                                    'getAuthenticationSourceParams' => 'getAuthenticationSourceParams',
                                    'getAuthenticationSourceParamsResponse' => 'getAuthenticationSourceParamsResponse',
                                    'reindex' => 'reindex',
                                    'reindexResponse' => 'reindexResponse',
                                    'deleteAuthenticationSource' => 'deleteAuthenticationSource',
                                    'deleteAuthenticationSourceResponse' => 'deleteAuthenticationSourceResponse',
                                    'updateAuthenticationSource' => 'updateAuthenticationSource',
                                    'updateAuthenticationSourceResponse' => 'updateAuthenticationSourceResponse',
                                    'getManageableGroups' => 'getManageableGroups',
                                    'getManageableGroupsResponse' => 'getManageableGroupsResponse',
                                    'getUserByAttribute' => 'getUserByAttribute',
                                    'getUserByAttributeResponse' => 'getUserByAttributeResponse',
                                    'createRole' => 'createRole',
                                    'createRoleResponse' => 'createRoleResponse',
                                    'getAllEnabledSessions' => 'getAllEnabledSessions',
                                    'getAllEnabledSessionsResponse' => 'getAllEnabledSessionsResponse',
                                    'getUserAttribute' => 'getUserAttribute',
                                    'getUserAttributeResponse' => 'getUserAttributeResponse',
                                    'getCheckedOutDocuments' => 'getCheckedOutDocuments',
                                    'getCheckedOutDocumentsResponse' => 'getCheckedOutDocumentsResponse',
                                    'getRoles' => 'getRoles',
                                    'getRolesResponse' => 'getRolesResponse',
                                    'getManageableGroup' => 'getManageableGroup',
                                    'getManageableGroupResponse' => 'getManageableGroupResponse',
                                    'deleteRole' => 'deleteRole',
                                    'deleteRoleResponse' => 'deleteRoleResponse',
                                    'getManageableUser' => 'getManageableUser',
                                    'getManageableUserResponse' => 'getManageableUserResponse',
                                    'getAvailableAuthenticationSource' => 'getAvailableAuthenticationSource',
                                    'getAvailableAuthenticationSourceResponse' => 'getAvailableAuthenticationSourceResponse',
                                    'getReindexProgress' => 'getReindexProgress',
                                    'getReindexProgressResponse' => 'getReindexProgressResponse',
                                    'createAuthenticationSource' => 'createAuthenticationSource',
                                    'createAuthenticationSourceResponse' => 'createAuthenticationSourceResponse',
                                    'getConnectedUsers' => 'getConnectedUsers',
                                    'getConnectedUsersResponse' => 'getConnectedUsersResponse',
                                    'deleteGroup' => 'deleteGroup',
                                    'deleteGroupResponse' => 'deleteGroupResponse',
                                    'updateUser' => 'updateUser',
                                    'updateUserResponse' => 'updateUserResponse',
                                    'createUser' => 'createUser',
                                    'createUserResponse' => 'createUserResponse',
                                    'removeEnabledSession' => 'removeEnabledSession',
                                    'removeEnabledSessionResponse' => 'removeEnabledSessionResponse',
                                    'getAvailableAuthenticationSourceParams' => 'getAvailableAuthenticationSourceParams',
                                    'getAvailableAuthenticationSourceParamsResponse' => 'getAvailableAuthenticationSourceParamsResponse',
                                    'changeOwnership' => 'changeOwnership',
                                    'changeOwnershipResponse' => 'changeOwnershipResponse',
                                    'removeEnabledSessions' => 'removeEnabledSessions',
                                    'removeEnabledSessionsResponse' => 'removeEnabledSessionsResponse',
                                    'addUserToGroup' => 'addUserToGroup',
                                    'addUserToGroupResponse' => 'addUserToGroupResponse',
                                    'setUserAttribute' => 'setUserAttribute',
                                    'setUserAttributeResponse' => 'setUserAttributeResponse',
                                   );

  public function AdministrationService($wsdl = "http://192.168.122.118:9999/kimios/services/AdministrationService?wsdl", $options = array()) {
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
   * @param deleteUser $parameters
   * @return deleteUserResponse
   */
  public function deleteUser(deleteUser $parameters) {
    return $this->__soapCall('deleteUser', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeUserFromGroup $parameters
   * @return removeUserFromGroupResponse
   */
  public function removeUserFromGroup(removeUserFromGroup $parameters) {
    return $this->__soapCall('removeUserFromGroup', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getManageableUsers $parameters
   * @return getManageableUsersResponse
   */
  public function getManageableUsers(getManageableUsers $parameters) {
    return $this->__soapCall('getManageableUsers', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateGroup $parameters
   * @return updateGroupResponse
   */
  public function updateGroup(updateGroup $parameters) {
    return $this->__soapCall('updateGroup', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAuthenticationSource $parameters
   * @return getAuthenticationSourceResponse
   */
  public function getAuthenticationSource(getAuthenticationSource $parameters) {
    return $this->__soapCall('getAuthenticationSource', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getUserRoles $parameters
   * @return getUserRolesResponse
   */
  public function getUserRoles(getUserRoles $parameters) {
    return $this->__soapCall('getUserRoles', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param clearLock $parameters
   * @return clearLockResponse
   */
  public function clearLock(clearLock $parameters) {
    return $this->__soapCall('clearLock', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createGroup $parameters
   * @return createGroupResponse
   */
  public function createGroup(createGroup $parameters) {
    return $this->__soapCall('createGroup', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getEnabledSessions $parameters
   * @return getEnabledSessionsResponse
   */
  public function getEnabledSessions(getEnabledSessions $parameters) {
    return $this->__soapCall('getEnabledSessions', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAuthenticationSourceParams $parameters
   * @return getAuthenticationSourceParamsResponse
   */
  public function getAuthenticationSourceParams(getAuthenticationSourceParams $parameters) {
    return $this->__soapCall('getAuthenticationSourceParams', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param reindex $parameters
   * @return reindexResponse
   */
  public function reindex(reindex $parameters) {
    return $this->__soapCall('reindex', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteAuthenticationSource $parameters
   * @return deleteAuthenticationSourceResponse
   */
  public function deleteAuthenticationSource(deleteAuthenticationSource $parameters) {
    return $this->__soapCall('deleteAuthenticationSource', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateAuthenticationSource $parameters
   * @return updateAuthenticationSourceResponse
   */
  public function updateAuthenticationSource(updateAuthenticationSource $parameters) {
    return $this->__soapCall('updateAuthenticationSource', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getManageableGroups $parameters
   * @return getManageableGroupsResponse
   */
  public function getManageableGroups(getManageableGroups $parameters) {
    return $this->__soapCall('getManageableGroups', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getUserByAttribute $parameters
   * @return getUserByAttributeResponse
   */
  public function getUserByAttribute(getUserByAttribute $parameters) {
    return $this->__soapCall('getUserByAttribute', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createRole $parameters
   * @return createRoleResponse
   */
  public function createRole(createRole $parameters) {
    return $this->__soapCall('createRole', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAllEnabledSessions $parameters
   * @return getAllEnabledSessionsResponse
   */
  public function getAllEnabledSessions(getAllEnabledSessions $parameters) {
    return $this->__soapCall('getAllEnabledSessions', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getCheckedOutDocuments $parameters
   * @return getCheckedOutDocumentsResponse
   */
  public function getCheckedOutDocuments(getCheckedOutDocuments $parameters) {
    return $this->__soapCall('getCheckedOutDocuments', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getUserAttribute $parameters
   * @return getUserAttributeResponse
   */
  public function getUserAttribute(getUserAttribute $parameters) {
    return $this->__soapCall('getUserAttribute', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteRole $parameters
   * @return deleteRoleResponse
   */
  public function deleteRole(deleteRole $parameters) {
    return $this->__soapCall('deleteRole', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getManageableGroup $parameters
   * @return getManageableGroupResponse
   */
  public function getManageableGroup(getManageableGroup $parameters) {
    return $this->__soapCall('getManageableGroup', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getRoles $parameters
   * @return getRolesResponse
   */
  public function getRoles(getRoles $parameters) {
    return $this->__soapCall('getRoles', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getManageableUser $parameters
   * @return getManageableUserResponse
   */
  public function getManageableUser(getManageableUser $parameters) {
    return $this->__soapCall('getManageableUser', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAvailableAuthenticationSource $parameters
   * @return getAvailableAuthenticationSourceResponse
   */
  public function getAvailableAuthenticationSource(getAvailableAuthenticationSource $parameters) {
    return $this->__soapCall('getAvailableAuthenticationSource', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getReindexProgress $parameters
   * @return getReindexProgressResponse
   */
  public function getReindexProgress(getReindexProgress $parameters) {
    return $this->__soapCall('getReindexProgress', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createAuthenticationSource $parameters
   * @return createAuthenticationSourceResponse
   */
  public function createAuthenticationSource(createAuthenticationSource $parameters) {
    return $this->__soapCall('createAuthenticationSource', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteGroup $parameters
   * @return deleteGroupResponse
   */
  public function deleteGroup(deleteGroup $parameters) {
    return $this->__soapCall('deleteGroup', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getConnectedUsers $parameters
   * @return getConnectedUsersResponse
   */
  public function getConnectedUsers(getConnectedUsers $parameters) {
    return $this->__soapCall('getConnectedUsers', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createUser $parameters
   * @return createUserResponse
   */
  public function createUser(createUser $parameters) {
    return $this->__soapCall('createUser', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateUser $parameters
   * @return updateUserResponse
   */
  public function updateUser(updateUser $parameters) {
    return $this->__soapCall('updateUser', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeEnabledSession $parameters
   * @return removeEnabledSessionResponse
   */
  public function removeEnabledSession(removeEnabledSession $parameters) {
    return $this->__soapCall('removeEnabledSession', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAvailableAuthenticationSourceParams $parameters
   * @return getAvailableAuthenticationSourceParamsResponse
   */
  public function getAvailableAuthenticationSourceParams(getAvailableAuthenticationSourceParams $parameters) {
    return $this->__soapCall('getAvailableAuthenticationSourceParams', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param changeOwnership $parameters
   * @return changeOwnershipResponse
   */
  public function changeOwnership(changeOwnership $parameters) {
    return $this->__soapCall('changeOwnership', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param addUserToGroup $parameters
   * @return addUserToGroupResponse
   */
  public function addUserToGroup(addUserToGroup $parameters) {
    return $this->__soapCall('addUserToGroup', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeEnabledSessions $parameters
   * @return removeEnabledSessionsResponse
   */
  public function removeEnabledSessions(removeEnabledSessions $parameters) {
    return $this->__soapCall('removeEnabledSessions', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param setUserAttribute $parameters
   * @return setUserAttributeResponse
   */
  public function setUserAttribute(setUserAttribute $parameters) {
    return $this->__soapCall('setUserAttribute', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
