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

class DocumentType {
  public $documentTypeUid; // long
  public $name; // string
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class MetaFeed {
  public $className; // string
  public $name; // string
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Workflow {
  public $description; // string
  public $name; // string
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class WorkflowStatusManager {
  public $securityEntityName; // string
  public $securityEntitySource; // string
  public $securityEntityType; // int
  public $workflowStatusUid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowStatusManagers {
  public $sessionId; // string
  public $workflowStatusId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowStatusManagersResponse {
  public $return; // ArrayOfWorkflowStatusManager

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflows {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowsResponse {
  public $return; // ArrayOfWorkflow

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createWorkflow {
  public $sessionId; // string
  public $name; // string
  public $description; // string
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createWorkflowResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateEnumerationValues {
  public $sessionId; // string
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateEnumerationValuesResponse {
}

class createWorkflowStatusManager {
  public $sessionId; // string
  public $workflowStatusId; // long
  public $securityEntityName; // string
  public $securityEntitySource; // string
  public $securityEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createWorkflowStatusManagerResponse {
}

class deleteWorkflowStatusManager {
  public $sessionId; // string
  public $workflowStatusId; // long
  public $securityEntityName; // string
  public $securityEntitySource; // string
  public $securityEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteWorkflowStatusManagerResponse {
}

class createDocumentType {
  public $sessionId; // string
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createDocumentTypeResponse {
}

class updateWorkflow {
  public $sessionId; // string
  public $workflowId; // long
  public $name; // string
  public $description; // string
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateWorkflowResponse {
}

class searchMetaFeedValues {
  public $sessionId; // string
  public $metaFeedId; // long
  public $criteria; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class searchMetaFeedValuesResponse {
  public $return; // ArrayOfString

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentType {
  public $sessionId; // string
  public $documentTypeId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentTypeResponse {
  public $return; // DocumentType

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteWorkflow {
  public $sessionId; // string
  public $workflowId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteWorkflowResponse {
}

class createMetaFeed {
  public $sessionId; // string
  public $name; // string
  public $className; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createMetaFeedResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaFeed {
  public $sessionId; // string
  public $metaFeedId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaFeedResponse {
  public $return; // MetaFeed

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflow {
  public $sessionId; // string
  public $workflowId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowResponse {
  public $return; // Workflow

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowStatuses {
  public $sessionId; // string
  public $workflowId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowStatusesResponse {
  public $return; // ArrayOfWorkflowStatus

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowStatus {
  public $sessionId; // string
  public $workflowStatusId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkflowStatusResponse {
  public $return; // WorkflowStatus

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateMetaFeed {
  public $sessionId; // string
  public $metaFeedId; // long
  public $name; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateMetaFeedResponse {
}

class deleteDocumentType {
  public $sessionId; // string
  public $documentTypeId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteDocumentTypeResponse {
}

class getDocumentTypes {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentTypesResponse {
  public $return; // ArrayOfDocumentType

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentType {
  public $sessionId; // string
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentTypeResponse {
}

class deleteMetaFeed {
  public $sessionId; // string
  public $metaFeedId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteMetaFeedResponse {
}

class getAvailableMetaFeeds {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAvailableMetaFeedsResponse {
  public $return; // ArrayOfString

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createWorkflowStatus {
  public $sessionId; // string
  public $workflowId; // long
  public $name; // string
  public $successorId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createWorkflowStatusResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateWorkflowStatus {
  public $sessionId; // string
  public $workflowStatusId; // long
  public $workflowId; // long
  public $name; // string
  public $successorId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateWorkflowStatusResponse {
}

class getMetaFeeds {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaFeedsResponse {
  public $return; // ArrayOfMetaFeed

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaFeedValues {
  public $sessionId; // string
  public $metaFeedId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaFeedValuesResponse {
  public $return; // ArrayOfString

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteWorkflowStatus {
  public $sessionId; // string
  public $workflowStatusId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteWorkflowStatusResponse {
}


/**
 * StudioService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class StudioService extends SoapClient {

  private static $classmap = array(
                                    'DocumentType' => 'DocumentType',
                                    'WorkflowStatus' => 'WorkflowStatus',
                                    'MetaFeed' => 'MetaFeed',
                                    'Workflow' => 'Workflow',
                                    'WorkflowStatusManager' => 'WorkflowStatusManager',
                                    'DMServiceException' => 'DMServiceException',
                                    'getWorkflowStatusManagers' => 'getWorkflowStatusManagers',
                                    'getWorkflowStatusManagersResponse' => 'getWorkflowStatusManagersResponse',
                                    'getWorkflows' => 'getWorkflows',
                                    'getWorkflowsResponse' => 'getWorkflowsResponse',
                                    'createWorkflow' => 'createWorkflow',
                                    'createWorkflowResponse' => 'createWorkflowResponse',
                                    'updateEnumerationValues' => 'updateEnumerationValues',
                                    'updateEnumerationValuesResponse' => 'updateEnumerationValuesResponse',
                                    'createWorkflowStatusManager' => 'createWorkflowStatusManager',
                                    'createWorkflowStatusManagerResponse' => 'createWorkflowStatusManagerResponse',
                                    'deleteWorkflowStatusManager' => 'deleteWorkflowStatusManager',
                                    'deleteWorkflowStatusManagerResponse' => 'deleteWorkflowStatusManagerResponse',
                                    'createDocumentType' => 'createDocumentType',
                                    'createDocumentTypeResponse' => 'createDocumentTypeResponse',
                                    'updateWorkflow' => 'updateWorkflow',
                                    'updateWorkflowResponse' => 'updateWorkflowResponse',
                                    'searchMetaFeedValues' => 'searchMetaFeedValues',
                                    'searchMetaFeedValuesResponse' => 'searchMetaFeedValuesResponse',
                                    'getDocumentType' => 'getDocumentType',
                                    'getDocumentTypeResponse' => 'getDocumentTypeResponse',
                                    'deleteWorkflow' => 'deleteWorkflow',
                                    'deleteWorkflowResponse' => 'deleteWorkflowResponse',
                                    'createMetaFeed' => 'createMetaFeed',
                                    'createMetaFeedResponse' => 'createMetaFeedResponse',
                                    'getMetaFeed' => 'getMetaFeed',
                                    'getMetaFeedResponse' => 'getMetaFeedResponse',
                                    'getWorkflow' => 'getWorkflow',
                                    'getWorkflowResponse' => 'getWorkflowResponse',
                                    'getWorkflowStatuses' => 'getWorkflowStatuses',
                                    'getWorkflowStatusesResponse' => 'getWorkflowStatusesResponse',
                                    'getWorkflowStatus' => 'getWorkflowStatus',
                                    'getWorkflowStatusResponse' => 'getWorkflowStatusResponse',
                                    'updateMetaFeed' => 'updateMetaFeed',
                                    'updateMetaFeedResponse' => 'updateMetaFeedResponse',
                                    'deleteDocumentType' => 'deleteDocumentType',
                                    'deleteDocumentTypeResponse' => 'deleteDocumentTypeResponse',
                                    'getDocumentTypes' => 'getDocumentTypes',
                                    'getDocumentTypesResponse' => 'getDocumentTypesResponse',
                                    'updateDocumentType' => 'updateDocumentType',
                                    'updateDocumentTypeResponse' => 'updateDocumentTypeResponse',
                                    'deleteMetaFeed' => 'deleteMetaFeed',
                                    'deleteMetaFeedResponse' => 'deleteMetaFeedResponse',
                                    'getAvailableMetaFeeds' => 'getAvailableMetaFeeds',
                                    'getAvailableMetaFeedsResponse' => 'getAvailableMetaFeedsResponse',
                                    'createWorkflowStatus' => 'createWorkflowStatus',
                                    'createWorkflowStatusResponse' => 'createWorkflowStatusResponse',
                                    'updateWorkflowStatus' => 'updateWorkflowStatus',
                                    'updateWorkflowStatusResponse' => 'updateWorkflowStatusResponse',
                                    'getMetaFeeds' => 'getMetaFeeds',
                                    'getMetaFeedsResponse' => 'getMetaFeedsResponse',
                                    'getMetaFeedValues' => 'getMetaFeedValues',
                                    'getMetaFeedValuesResponse' => 'getMetaFeedValuesResponse',
                                    'deleteWorkflowStatus' => 'deleteWorkflowStatus',
                                    'deleteWorkflowStatusResponse' => 'deleteWorkflowStatusResponse',
                                   );

  public function StudioService($wsdl = "http://192.168.122.118:9999/kimios/services/StudioService?wsdl", $options = array()) {
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
   * @param getWorkflowStatusManagers $parameters
   * @return getWorkflowStatusManagersResponse
   */
  public function getWorkflowStatusManagers(getWorkflowStatusManagers $parameters) {
    return $this->__soapCall('getWorkflowStatusManagers', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getWorkflows $parameters
   * @return getWorkflowsResponse
   */
  public function getWorkflows(getWorkflows $parameters) {
    return $this->__soapCall('getWorkflows', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createWorkflow $parameters
   * @return createWorkflowResponse
   */
  public function createWorkflow(createWorkflow $parameters) {
    return $this->__soapCall('createWorkflow', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateEnumerationValues $parameters
   * @return updateEnumerationValuesResponse
   */
  public function updateEnumerationValues(updateEnumerationValues $parameters) {
    return $this->__soapCall('updateEnumerationValues', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createWorkflowStatusManager $parameters
   * @return createWorkflowStatusManagerResponse
   */
  public function createWorkflowStatusManager(createWorkflowStatusManager $parameters) {
    return $this->__soapCall('createWorkflowStatusManager', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteWorkflowStatusManager $parameters
   * @return deleteWorkflowStatusManagerResponse
   */
  public function deleteWorkflowStatusManager(deleteWorkflowStatusManager $parameters) {
    return $this->__soapCall('deleteWorkflowStatusManager', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createDocumentType $parameters
   * @return createDocumentTypeResponse
   */
  public function createDocumentType(createDocumentType $parameters) {
    return $this->__soapCall('createDocumentType', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateWorkflow $parameters
   * @return updateWorkflowResponse
   */
  public function updateWorkflow(updateWorkflow $parameters) {
    return $this->__soapCall('updateWorkflow', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDocumentType $parameters
   * @return getDocumentTypeResponse
   */
  public function getDocumentType(getDocumentType $parameters) {
    return $this->__soapCall('getDocumentType', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param searchMetaFeedValues $parameters
   * @return searchMetaFeedValuesResponse
   */
  public function searchMetaFeedValues(searchMetaFeedValues $parameters) {
    return $this->__soapCall('searchMetaFeedValues', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createMetaFeed $parameters
   * @return createMetaFeedResponse
   */
  public function createMetaFeed(createMetaFeed $parameters) {
    return $this->__soapCall('createMetaFeed', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteWorkflow $parameters
   * @return deleteWorkflowResponse
   */
  public function deleteWorkflow(deleteWorkflow $parameters) {
    return $this->__soapCall('deleteWorkflow', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaFeed $parameters
   * @return getMetaFeedResponse
   */
  public function getMetaFeed(getMetaFeed $parameters) {
    return $this->__soapCall('getMetaFeed', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getWorkflow $parameters
   * @return getWorkflowResponse
   */
  public function getWorkflow(getWorkflow $parameters) {
    return $this->__soapCall('getWorkflow', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getWorkflowStatuses $parameters
   * @return getWorkflowStatusesResponse
   */
  public function getWorkflowStatuses(getWorkflowStatuses $parameters) {
    return $this->__soapCall('getWorkflowStatuses', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getWorkflowStatus $parameters
   * @return getWorkflowStatusResponse
   */
  public function getWorkflowStatus(getWorkflowStatus $parameters) {
    return $this->__soapCall('getWorkflowStatus', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteDocumentType $parameters
   * @return deleteDocumentTypeResponse
   */
  public function deleteDocumentType(deleteDocumentType $parameters) {
    return $this->__soapCall('deleteDocumentType', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateMetaFeed $parameters
   * @return updateMetaFeedResponse
   */
  public function updateMetaFeed(updateMetaFeed $parameters) {
    return $this->__soapCall('updateMetaFeed', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDocumentTypes $parameters
   * @return getDocumentTypesResponse
   */
  public function getDocumentTypes(getDocumentTypes $parameters) {
    return $this->__soapCall('getDocumentTypes', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateDocumentType $parameters
   * @return updateDocumentTypeResponse
   */
  public function updateDocumentType(updateDocumentType $parameters) {
    return $this->__soapCall('updateDocumentType', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteMetaFeed $parameters
   * @return deleteMetaFeedResponse
   */
  public function deleteMetaFeed(deleteMetaFeed $parameters) {
    return $this->__soapCall('deleteMetaFeed', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAvailableMetaFeeds $parameters
   * @return getAvailableMetaFeedsResponse
   */
  public function getAvailableMetaFeeds(getAvailableMetaFeeds $parameters) {
    return $this->__soapCall('getAvailableMetaFeeds', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createWorkflowStatus $parameters
   * @return createWorkflowStatusResponse
   */
  public function createWorkflowStatus(createWorkflowStatus $parameters) {
    return $this->__soapCall('createWorkflowStatus', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaFeeds $parameters
   * @return getMetaFeedsResponse
   */
  public function getMetaFeeds(getMetaFeeds $parameters) {
    return $this->__soapCall('getMetaFeeds', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateWorkflowStatus $parameters
   * @return updateWorkflowStatusResponse
   */
  public function updateWorkflowStatus(updateWorkflowStatus $parameters) {
    return $this->__soapCall('updateWorkflowStatus', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteWorkflowStatus $parameters
   * @return deleteWorkflowStatusResponse
   */
  public function deleteWorkflowStatus(deleteWorkflowStatus $parameters) {
    return $this->__soapCall('deleteWorkflowStatus', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaFeedValues $parameters
   * @return getMetaFeedValuesResponse
   */
  public function getMetaFeedValues(getMetaFeedValues $parameters) {
    return $this->__soapCall('getMetaFeedValues', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
