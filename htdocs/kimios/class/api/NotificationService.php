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

class DocumentWorkflowStatusRequest {
  public $comment; // string
  public $date; // dateTime
  public $documentUid; // long
  public $status; // int
  public $userName; // string
  public $userSource; // string
  public $validationDate; // dateTime
  public $validatorUserName; // string
  public $validatorUserSource; // string
  public $workflowStatusUid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentWorkflowStatusRequest {
  public $sessionId; // string
  public $documentId; // long
  public $workflowStatusId; // long
  public $userName; // string
  public $userSource; // string
  public $requestDate; // dateTime

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentWorkflowStatusRequestResponse {
  public $return; // DocumentWorkflowStatusRequest

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentWorkflowStatusRequestComment {
  public $sessionId; // string
  public $documentId; // long
  public $workflowStatusId; // long
  public $userName; // string
  public $userSource; // string
  public $requestDate; // dateTime
  public $newComment; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentWorkflowStatusRequestCommentResponse {
}

class createRequest {
  public $sessionId; // string
  public $documentId; // long
  public $workflowStatusId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createRequestResponse {
}

class rejectRequest {
  public $sessionId; // string
  public $documentId; // long
  public $workflowStatusId; // long
  public $userName; // string
  public $userSource; // string
  public $statusDate; // dateTime
  public $comment; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class rejectRequestResponse {
}

class getPendingRequests {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getPendingRequestsResponse {
  public $return; // ArrayOfDocumentWorkflowStatusRequest

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class cancelWorkflow {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class cancelWorkflowResponse {
}

class acceptRequest {
  public $sessionId; // string
  public $documentId; // long
  public $workflowStatusId; // long
  public $userName; // string
  public $userSource; // string
  public $statusDate; // dateTime
  public $comment; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class acceptRequestResponse {
}

class getRequests {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRequestsResponse {
  public $return; // ArrayOfDocumentWorkflowStatusRequest

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * NotificationService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class NotificationService extends SoapClient {

  private static $classmap = array(
                                    'WorkflowStatus' => 'WorkflowStatus',
                                    'DocumentWorkflowStatusRequest' => 'DocumentWorkflowStatusRequest',
                                    'DMServiceException' => 'DMServiceException',
                                    'getLastWorkflowStatus' => 'getLastWorkflowStatus',
                                    'getLastWorkflowStatusResponse' => 'getLastWorkflowStatusResponse',
                                    'getDocumentWorkflowStatusRequest' => 'getDocumentWorkflowStatusRequest',
                                    'getDocumentWorkflowStatusRequestResponse' => 'getDocumentWorkflowStatusRequestResponse',
                                    'updateDocumentWorkflowStatusRequestComment' => 'updateDocumentWorkflowStatusRequestComment',
                                    'updateDocumentWorkflowStatusRequestCommentResponse' => 'updateDocumentWorkflowStatusRequestCommentResponse',
                                    'createRequest' => 'createRequest',
                                    'createRequestResponse' => 'createRequestResponse',
                                    'rejectRequest' => 'rejectRequest',
                                    'rejectRequestResponse' => 'rejectRequestResponse',
                                    'getPendingRequests' => 'getPendingRequests',
                                    'getPendingRequestsResponse' => 'getPendingRequestsResponse',
                                    'cancelWorkflow' => 'cancelWorkflow',
                                    'cancelWorkflowResponse' => 'cancelWorkflowResponse',
                                    'acceptRequest' => 'acceptRequest',
                                    'acceptRequestResponse' => 'acceptRequestResponse',
                                    'getRequests' => 'getRequests',
                                    'getRequestsResponse' => 'getRequestsResponse',
                                   );

  public function NotificationService($wsdl = "http://192.168.122.118:9999/kimios/services/NotificationService?wsdl", $options = array()) {
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
   * @param getDocumentWorkflowStatusRequest $parameters
   * @return getDocumentWorkflowStatusRequestResponse
   */
  public function getDocumentWorkflowStatusRequest(getDocumentWorkflowStatusRequest $parameters) {
    return $this->__soapCall('getDocumentWorkflowStatusRequest', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getLastWorkflowStatus $parameters
   * @return getLastWorkflowStatusResponse
   */
  public function getLastWorkflowStatus(getLastWorkflowStatus $parameters) {
    return $this->__soapCall('getLastWorkflowStatus', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateDocumentWorkflowStatusRequestComment $parameters
   * @return updateDocumentWorkflowStatusRequestCommentResponse
   */
  public function updateDocumentWorkflowStatusRequestComment(updateDocumentWorkflowStatusRequestComment $parameters) {
    return $this->__soapCall('updateDocumentWorkflowStatusRequestComment', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createRequest $parameters
   * @return createRequestResponse
   */
  public function createRequest(createRequest $parameters) {
    return $this->__soapCall('createRequest', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getPendingRequests $parameters
   * @return getPendingRequestsResponse
   */
  public function getPendingRequests(getPendingRequests $parameters) {
    return $this->__soapCall('getPendingRequests', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param rejectRequest $parameters
   * @return rejectRequestResponse
   */
  public function rejectRequest(rejectRequest $parameters) {
    return $this->__soapCall('rejectRequest', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param cancelWorkflow $parameters
   * @return cancelWorkflowResponse
   */
  public function cancelWorkflow(cancelWorkflow $parameters) {
    return $this->__soapCall('cancelWorkflow', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param acceptRequest $parameters
   * @return acceptRequestResponse
   */
  public function acceptRequest(acceptRequest $parameters) {
    return $this->__soapCall('acceptRequest', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getRequests $parameters
   * @return getRequestsResponse
   */
  public function getRequests(getRequests $parameters) {
    return $this->__soapCall('getRequests', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
