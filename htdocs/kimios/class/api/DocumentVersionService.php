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

class MetaValue {
  public $documentVersionId; // long
  public $meta; // Meta
  public $metaId; // long
  public $value; // anyType

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class DocumentVersion {
  public $author; // string
  public $authorSource; // string
  public $creationDate; // dateTime
  public $documentTypeName; // string
  public $documentTypeUid; // long
  public $documentUid; // long
  public $hashMd5; // string
  public $hashSha; // string
  public $length; // long
  public $modificationDate; // dateTime
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Meta {
  public $documentTypeUid; // long
  public $metaFeedUid; // long
  public $metaType; // int
  public $name; // string
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class DocumentComment {
  public $authorName; // string
  public $authorSource; // string
  public $comment; // string
  public $date; // dateTime
  public $documentVersionUid; // long
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUnheritedMetas {
  public $sessionId; // string
  public $documentTypeId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getUnheritedMetasResponse {
  public $return; // ArrayOfMeta

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentComments {
  public $sessionId; // string
  public $documentVersionId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentCommentsResponse {
  public $return; // ArrayOfDocumentComment

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaNumber {
  public $sessionId; // string
  public $documentVersionId; // long
  public $metaId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaNumberResponse {
  public $return; // double

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentVersion {
  public $sessionId; // string
  public $documentId; // long
  public $documentTypeId; // long
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentVersionResponse {
}

class removeDocumentComment {
  public $sessionId; // string
  public $commentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeDocumentCommentResponse {
}

class getMeta {
  public $sessionId; // string
  public $metaId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaResponse {
  public $return; // Meta

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentVersion {
  public $sessionId; // string
  public $documentVersionId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentVersionResponse {
  public $return; // DocumentVersion

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetas {
  public $sessionId; // string
  public $documentTypeId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetasResponse {
  public $return; // ArrayOfMeta

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getLastDocumentVersion {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getLastDocumentVersionResponse {
  public $return; // DocumentVersion

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaBoolean {
  public $sessionId; // string
  public $documentVersionId; // long
  public $metaId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaBooleanResponse {
  public $return; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaValues {
  public $sessionId; // string
  public $documentVersionId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaValuesResponse {
  public $return; // ArrayOfMetaValue

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentComment {
  public $sessionId; // string
  public $documentVersionId; // long
  public $commentId; // long
  public $newComment; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateDocumentCommentResponse {
}

class createDocumentVersionFromLatest {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createDocumentVersionFromLatestResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addDocumentComment {
  public $sessionId; // string
  public $documentVersionId; // long
  public $comment; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class addDocumentCommentResponse {
}

class getMetaString {
  public $sessionId; // string
  public $documentVersionId; // long
  public $metaId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaStringResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentVersions {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentVersionsResponse {
  public $return; // ArrayOfDocumentVersion

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateMetas {
  public $sessionId; // string
  public $documentVersionId; // long
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class updateMetasResponse {
}

class getDocumentComment {
  public $sessionId; // string
  public $commentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentCommentResponse {
  public $return; // DocumentComment

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createDocumentVersion {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createDocumentVersionResponse {
  public $return; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaDate {
  public $sessionId; // string
  public $documentVersionId; // long
  public $metaId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getMetaDateResponse {
  public $return; // dateTime

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * DocumentVersionService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class DocumentVersionService extends SoapClient {

  private static $classmap = array(
                                    'MetaValue' => 'MetaValue',
                                    'DocumentVersion' => 'DocumentVersion',
                                    'Meta' => 'Meta',
                                    'DocumentComment' => 'DocumentComment',
                                    'DMServiceException' => 'DMServiceException',
                                    'getUnheritedMetas' => 'getUnheritedMetas',
                                    'getUnheritedMetasResponse' => 'getUnheritedMetasResponse',
                                    'getDocumentComments' => 'getDocumentComments',
                                    'getDocumentCommentsResponse' => 'getDocumentCommentsResponse',
                                    'getMetaNumber' => 'getMetaNumber',
                                    'getMetaNumberResponse' => 'getMetaNumberResponse',
                                    'updateDocumentVersion' => 'updateDocumentVersion',
                                    'updateDocumentVersionResponse' => 'updateDocumentVersionResponse',
                                    'removeDocumentComment' => 'removeDocumentComment',
                                    'removeDocumentCommentResponse' => 'removeDocumentCommentResponse',
                                    'getMeta' => 'getMeta',
                                    'getMetaResponse' => 'getMetaResponse',
                                    'getDocumentVersion' => 'getDocumentVersion',
                                    'getDocumentVersionResponse' => 'getDocumentVersionResponse',
                                    'getMetas' => 'getMetas',
                                    'getMetasResponse' => 'getMetasResponse',
                                    'getLastDocumentVersion' => 'getLastDocumentVersion',
                                    'getLastDocumentVersionResponse' => 'getLastDocumentVersionResponse',
                                    'getMetaBoolean' => 'getMetaBoolean',
                                    'getMetaBooleanResponse' => 'getMetaBooleanResponse',
                                    'getMetaValues' => 'getMetaValues',
                                    'getMetaValuesResponse' => 'getMetaValuesResponse',
                                    'updateDocumentComment' => 'updateDocumentComment',
                                    'updateDocumentCommentResponse' => 'updateDocumentCommentResponse',
                                    'createDocumentVersionFromLatest' => 'createDocumentVersionFromLatest',
                                    'createDocumentVersionFromLatestResponse' => 'createDocumentVersionFromLatestResponse',
                                    'addDocumentComment' => 'addDocumentComment',
                                    'addDocumentCommentResponse' => 'addDocumentCommentResponse',
                                    'getMetaString' => 'getMetaString',
                                    'getMetaStringResponse' => 'getMetaStringResponse',
                                    'getDocumentVersions' => 'getDocumentVersions',
                                    'getDocumentVersionsResponse' => 'getDocumentVersionsResponse',
                                    'updateMetas' => 'updateMetas',
                                    'updateMetasResponse' => 'updateMetasResponse',
                                    'getDocumentComment' => 'getDocumentComment',
                                    'getDocumentCommentResponse' => 'getDocumentCommentResponse',
                                    'createDocumentVersion' => 'createDocumentVersion',
                                    'createDocumentVersionResponse' => 'createDocumentVersionResponse',
                                    'getMetaDate' => 'getMetaDate',
                                    'getMetaDateResponse' => 'getMetaDateResponse',
                                   );

  public function DocumentVersionService($wsdl = "http://192.168.122.118:9999/kimios/services/DocumentVersionService?wsdl", $options = array()) {
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
   * @param getDocumentComments $parameters
   * @return getDocumentCommentsResponse
   */
  public function getDocumentComments(getDocumentComments $parameters) {
    return $this->__soapCall('getDocumentComments', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getUnheritedMetas $parameters
   * @return getUnheritedMetasResponse
   */
  public function getUnheritedMetas(getUnheritedMetas $parameters) {
    return $this->__soapCall('getUnheritedMetas', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaNumber $parameters
   * @return getMetaNumberResponse
   */
  public function getMetaNumber(getMetaNumber $parameters) {
    return $this->__soapCall('getMetaNumber', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeDocumentComment $parameters
   * @return removeDocumentCommentResponse
   */
  public function removeDocumentComment(removeDocumentComment $parameters) {
    return $this->__soapCall('removeDocumentComment', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateDocumentVersion $parameters
   * @return updateDocumentVersionResponse
   */
  public function updateDocumentVersion(updateDocumentVersion $parameters) {
    return $this->__soapCall('updateDocumentVersion', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMeta $parameters
   * @return getMetaResponse
   */
  public function getMeta(getMeta $parameters) {
    return $this->__soapCall('getMeta', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDocumentVersion $parameters
   * @return getDocumentVersionResponse
   */
  public function getDocumentVersion(getDocumentVersion $parameters) {
    return $this->__soapCall('getDocumentVersion', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetas $parameters
   * @return getMetasResponse
   */
  public function getMetas(getMetas $parameters) {
    return $this->__soapCall('getMetas', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getLastDocumentVersion $parameters
   * @return getLastDocumentVersionResponse
   */
  public function getLastDocumentVersion(getLastDocumentVersion $parameters) {
    return $this->__soapCall('getLastDocumentVersion', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaBoolean $parameters
   * @return getMetaBooleanResponse
   */
  public function getMetaBoolean(getMetaBoolean $parameters) {
    return $this->__soapCall('getMetaBoolean', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaValues $parameters
   * @return getMetaValuesResponse
   */
  public function getMetaValues(getMetaValues $parameters) {
    return $this->__soapCall('getMetaValues', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createDocumentVersionFromLatest $parameters
   * @return createDocumentVersionFromLatestResponse
   */
  public function createDocumentVersionFromLatest(createDocumentVersionFromLatest $parameters) {
    return $this->__soapCall('createDocumentVersionFromLatest', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateDocumentComment $parameters
   * @return updateDocumentCommentResponse
   */
  public function updateDocumentComment(updateDocumentComment $parameters) {
    return $this->__soapCall('updateDocumentComment', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param addDocumentComment $parameters
   * @return addDocumentCommentResponse
   */
  public function addDocumentComment(addDocumentComment $parameters) {
    return $this->__soapCall('addDocumentComment', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaString $parameters
   * @return getMetaStringResponse
   */
  public function getMetaString(getMetaString $parameters) {
    return $this->__soapCall('getMetaString', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDocumentVersions $parameters
   * @return getDocumentVersionsResponse
   */
  public function getDocumentVersions(getDocumentVersions $parameters) {
    return $this->__soapCall('getDocumentVersions', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param updateMetas $parameters
   * @return updateMetasResponse
   */
  public function updateMetas(updateMetas $parameters) {
    return $this->__soapCall('updateMetas', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createDocumentVersion $parameters
   * @return createDocumentVersionResponse
   */
  public function createDocumentVersion(createDocumentVersion $parameters) {
    return $this->__soapCall('createDocumentVersion', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getDocumentComment $parameters
   * @return getDocumentCommentResponse
   */
  public function getDocumentComment(getDocumentComment $parameters) {
    return $this->__soapCall('getDocumentComment', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getMetaDate $parameters
   * @return getMetaDateResponse
   */
  public function getMetaDate(getMetaDate $parameters) {
    return $this->__soapCall('getMetaDate', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
