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

class SymbolicLink {
  public $creationDate; // dateTime
  public $creatorName; // string
  public $creatorSource; // string
  public $dmEntityType; // int
  public $dmEntityUid; // long
  public $name; // string
  public $parentType; // int
  public $parentUid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Bookmark {
  public $dmEntityType; // int
  public $dmEntityUid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getBookmarks {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getBookmarksResponse {
  public $return; // ArrayOfBookmark

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addSymbolicLink {
  public $sessionId; // string
  public $name; // string
  public $dmEntityId; // long
  public $dmEntityType; // int
  public $parentId; // long
  public $parentType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addSymbolicLinkResponse {
}

class deleteDocument {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteDocumentResponse {
}

class getMyCheckedOutDocuments {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMyCheckedOutDocumentsResponse {
  public $return; // ArrayOfDocument

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeRelatedDocument {
  public $sessionId; // string
  public $documentId; // long
  public $relatedDocumentUid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeRelatedDocumentResponse {
}

class removeSymbolicLink {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int
  public $parentId; // long
  public $parentType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeSymbolicLinkResponse {
}

class getRecentItems {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRecentItemsResponse {
  public $return; // ArrayOfBookmark

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addBookmark {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addBookmarkResponse {
}

class checkinDocument {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class checkinDocumentResponse {
}

class getSymbolicLinksCreated {
  public $sessionId; // string
  public $targetId; // long
  public $targetType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getSymbolicLinksCreatedResponse {
  public $return; // ArrayOfSymbolicLink

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRelatedDocuments {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRelatedDocumentsResponse {
  public $return; // ArrayOfDocument

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createDocumentFromFullPath {
  public $sessionId; // string
  public $path; // string
  public $isSecurityInherited; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createDocumentFromFullPathResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateSymbolicLink {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int
  public $parentId; // long
  public $parentType; // int
  public $newParentId; // long
  public $newParentType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateSymbolicLinkResponse {
}

class checkoutDocument {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class checkoutDocumentResponse {
}

class getDocuments {
  public $sessionId; // string
  public $folderId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentsResponse {
  public $return; // ArrayOfDocument

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getChildSymbolicLinks {
  public $sessionId; // string
  public $parentId; // long
  public $parentType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getChildSymbolicLinksResponse {
  public $return; // ArrayOfSymbolicLink

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocument {
  public $sessionId; // string
  public $documentId; // long
  public $name; // string
  public $extension; // string
  public $mimeType; // string
  public $folderId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentResponse {
}

class getDocument {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentResponse {
  public $return; // Document

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeBookmark {
  public $sessionId; // string
  public $dmEntityId; // long
  public $dmEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeBookmarkResponse {
}

class createDocument {
  public $sessionId; // string
  public $name; // string
  public $extension; // string
  public $mimeType; // string
  public $folderId; // long
  public $isSecurityInherited; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createDocumentResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addRelatedDocument {
  public $sessionId; // string
  public $documentId; // long
  public $relatedDocumentUid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addRelatedDocumentResponse {
}


/**
 * DocumentService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class DocumentService extends SoapClient {

  private static $classmap = array(
                                    'WorkflowStatus' => 'WorkflowStatus',
                                    'SymbolicLink' => 'SymbolicLink',
                                    'Document' => 'Document',
                                    'Bookmark' => 'Bookmark',
                                    'DMServiceException' => 'DMServiceException',
                                    'getBookmarks' => 'getBookmarks',
                                    'getBookmarksResponse' => 'getBookmarksResponse',
                                    'addSymbolicLink' => 'addSymbolicLink',
                                    'addSymbolicLinkResponse' => 'addSymbolicLinkResponse',
                                    'deleteDocument' => 'deleteDocument',
                                    'deleteDocumentResponse' => 'deleteDocumentResponse',
                                    'getMyCheckedOutDocuments' => 'getMyCheckedOutDocuments',
                                    'getMyCheckedOutDocumentsResponse' => 'getMyCheckedOutDocumentsResponse',
                                    'removeRelatedDocument' => 'removeRelatedDocument',
                                    'removeRelatedDocumentResponse' => 'removeRelatedDocumentResponse',
                                    'removeSymbolicLink' => 'removeSymbolicLink',
                                    'removeSymbolicLinkResponse' => 'removeSymbolicLinkResponse',
                                    'getRecentItems' => 'getRecentItems',
                                    'getRecentItemsResponse' => 'getRecentItemsResponse',
                                    'getLastWorkflowStatus' => 'getLastWorkflowStatus',
                                    'getLastWorkflowStatusResponse' => 'getLastWorkflowStatusResponse',
                                    'addBookmark' => 'addBookmark',
                                    'addBookmarkResponse' => 'addBookmarkResponse',
                                    'checkinDocument' => 'checkinDocument',
                                    'checkinDocumentResponse' => 'checkinDocumentResponse',
                                    'getSymbolicLinksCreated' => 'getSymbolicLinksCreated',
                                    'getSymbolicLinksCreatedResponse' => 'getSymbolicLinksCreatedResponse',
                                    'getRelatedDocuments' => 'getRelatedDocuments',
                                    'getRelatedDocumentsResponse' => 'getRelatedDocumentsResponse',
                                    'createDocumentFromFullPath' => 'createDocumentFromFullPath',
                                    'createDocumentFromFullPathResponse' => 'createDocumentFromFullPathResponse',
                                    'updateSymbolicLink' => 'updateSymbolicLink',
                                    'updateSymbolicLinkResponse' => 'updateSymbolicLinkResponse',
                                    'checkoutDocument' => 'checkoutDocument',
                                    'checkoutDocumentResponse' => 'checkoutDocumentResponse',
                                    'getDocuments' => 'getDocuments',
                                    'getDocumentsResponse' => 'getDocumentsResponse',
                                    'getChildSymbolicLinks' => 'getChildSymbolicLinks',
                                    'getChildSymbolicLinksResponse' => 'getChildSymbolicLinksResponse',
                                    'updateDocument' => 'updateDocument',
                                    'updateDocumentResponse' => 'updateDocumentResponse',
                                    'getDocument' => 'getDocument',
                                    'getDocumentResponse' => 'getDocumentResponse',
                                    'removeBookmark' => 'removeBookmark',
                                    'removeBookmarkResponse' => 'removeBookmarkResponse',
                                    'createDocument' => 'createDocument',
                                    'createDocumentResponse' => 'createDocumentResponse',
                                    'addRelatedDocument' => 'addRelatedDocument',
                                    'addRelatedDocumentResponse' => 'addRelatedDocumentResponse',
                                   );

  public function DocumentService($wsdl = "http://192.168.122.118:9999/kimios/services/DocumentService?wsdl", $options = array()) {
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
   * @param getBookmarks $parameters
   * @return getBookmarksResponse
   */
  public function getBookmarks(getBookmarks $parameters) {
    return $this->__soapCall('getBookmarks', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param addSymbolicLink $parameters
   * @return addSymbolicLinkResponse
   */
  public function addSymbolicLink(addSymbolicLink $parameters) {
    return $this->__soapCall('addSymbolicLink', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteDocument $parameters
   * @return deleteDocumentResponse
   */
  public function deleteDocument(deleteDocument $parameters) {
    return $this->__soapCall('deleteDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMyCheckedOutDocuments $parameters
   * @return getMyCheckedOutDocumentsResponse
   */
  public function getMyCheckedOutDocuments(getMyCheckedOutDocuments $parameters) {
    return $this->__soapCall('getMyCheckedOutDocuments', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeRelatedDocument $parameters
   * @return removeRelatedDocumentResponse
   */
  public function removeRelatedDocument(removeRelatedDocument $parameters) {
    return $this->__soapCall('removeRelatedDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getRecentItems $parameters
   * @return getRecentItemsResponse
   */
  public function getRecentItems(getRecentItems $parameters) {
    return $this->__soapCall('getRecentItems', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeSymbolicLink $parameters
   * @return removeSymbolicLinkResponse
   */
  public function removeSymbolicLink(removeSymbolicLink $parameters) {
    return $this->__soapCall('removeSymbolicLink', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param addBookmark $parameters
   * @return addBookmarkResponse
   */
  public function addBookmark(addBookmark $parameters) {
    return $this->__soapCall('addBookmark', array($parameters),       array(
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
   * @param checkinDocument $parameters
   * @return checkinDocumentResponse
   */
  public function checkinDocument(checkinDocument $parameters) {
    return $this->__soapCall('checkinDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getSymbolicLinksCreated $parameters
   * @return getSymbolicLinksCreatedResponse
   */
  public function getSymbolicLinksCreated(getSymbolicLinksCreated $parameters) {
    return $this->__soapCall('getSymbolicLinksCreated', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createDocumentFromFullPath $parameters
   * @return createDocumentFromFullPathResponse
   */
  public function createDocumentFromFullPath(createDocumentFromFullPath $parameters) {
    return $this->__soapCall('createDocumentFromFullPath', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getRelatedDocuments $parameters
   * @return getRelatedDocumentsResponse
   */
  public function getRelatedDocuments(getRelatedDocuments $parameters) {
    return $this->__soapCall('getRelatedDocuments', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param checkoutDocument $parameters
   * @return checkoutDocumentResponse
   */
  public function checkoutDocument(checkoutDocument $parameters) {
    return $this->__soapCall('checkoutDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateSymbolicLink $parameters
   * @return updateSymbolicLinkResponse
   */
  public function updateSymbolicLink(updateSymbolicLink $parameters) {
    return $this->__soapCall('updateSymbolicLink', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDocuments $parameters
   * @return getDocumentsResponse
   */
  public function getDocuments(getDocuments $parameters) {
    return $this->__soapCall('getDocuments', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getChildSymbolicLinks $parameters
   * @return getChildSymbolicLinksResponse
   */
  public function getChildSymbolicLinks(getChildSymbolicLinks $parameters) {
    return $this->__soapCall('getChildSymbolicLinks', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateDocument $parameters
   * @return updateDocumentResponse
   */
  public function updateDocument(updateDocument $parameters) {
    return $this->__soapCall('updateDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDocument $parameters
   * @return getDocumentResponse
   */
  public function getDocument(getDocument $parameters) {
    return $this->__soapCall('getDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createDocument $parameters
   * @return createDocumentResponse
   */
  public function createDocument(createDocument $parameters) {
    return $this->__soapCall('createDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeBookmark $parameters
   * @return removeBookmarkResponse
   */
  public function removeBookmark(removeBookmark $parameters) {
    return $this->__soapCall('removeBookmark', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param addRelatedDocument $parameters
   * @return addRelatedDocumentResponse
   */
  public function addRelatedDocument(addRelatedDocument $parameters) {
    return $this->__soapCall('addRelatedDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
