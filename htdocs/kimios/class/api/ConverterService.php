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

class Locale {
  public $ISO3Country; // string
  public $ISO3Language; // string
  public $country; // string
  public $displayCountry; // string
  public $displayLanguage; // string
  public $displayName; // string
  public $displayVariant; // string
  public $language; // string
  public $variant; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Response {
  public $allowedMethods; // ArrayOfString
  public $cookies; // string2NewCookieMap
  public $date; // dateTime
  public $entity; // anyType
  public $entityTag; // EntityTag
  public $headers; // string2ArrayOfAnyTypeMultivaluedMap
  public $language; // Locale
  public $lastModified; // dateTime
  public $length; // int
  public $links; // ArrayOfLink
  public $location; // anyURI
  public $mediaType; // MediaType
  public $metadata; // string2ArrayOfAnyTypeMultivaluedMap
  public $status; // int
  public $statusInfo; // StatusType
  public $stringHeaders; // string2ArrayOfAnyTypeMultivaluedMap

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class UriBuilder {
}

class MediaType {
  public $parameters; // string2stringMap
  public $subtype; // string
  public $type; // string
  public $wildcardSubtype; // boolean
  public $wildcardType; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class StatusType {
  public $family; // Family
  public $reasonPhrase; // string
  public $statusCode; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Link {
  public $params; // string2stringMap
  public $rel; // string
  public $title; // string
  public $type; // string
  public $uri; // anyURI
  public $uriBuilder; // UriBuilder

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class NewCookie {
  public $comment; // string
  public $maxAge; // int
  public $secure; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class EntityTag {
  public $value; // string
  public $weak; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Family {
  const INFORMATIONAL = 'INFORMATIONAL';
  const SUCCESSFUL = 'SUCCESSFUL';
  const REDIRECTION = 'REDIRECTION';
  const CLIENT_ERROR = 'CLIENT_ERROR';
  const SERVER_ERROR = 'SERVER_ERROR';
  const OTHER = 'OTHER';

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Cookie {
  public $domain; // string
  public $name; // string
  public $path; // string
  public $value; // string
  public $version; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class string2ArrayOfAnyTypeMultivaluedMap {
  public $entry; // entry

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class entry {
  public $key; // string
  public $value; // ArrayOfAnyType

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class string2stringMap {
  public $entry; // entry

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}}

class entry {
  public $key; // string
  public $value; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class string2NewCookieMap {
  public $entry; // entry

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class entry {
  public $key; // string
  public $value; // NewCookie

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocumentVersions {
  public $sessionId; // string
  public $versionId; // ArrayOfLong
  public $converterImpl; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocumentVersionsResponse {
  public $return; // Response

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocument {
  public $sessionId; // string
  public $documentId; // long
  public $converterImpl; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocumentResponse {
  public $return; // Response

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocumentVersion {
  public $sessionId; // string
  public $versionId; // long
  public $converterImpl; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocumentVersionResponse {
  public $return; // Response

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocuments {
  public $sessionId; // string
  public $documentId; // ArrayOfLong
  public $converterImpl; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class convertDocumentsResponse {
  public $return; // Response

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * ConverterService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class ConverterService extends SoapClient {

  private static $classmap = array(
                                    'Locale' => 'Locale',
                                    'DMServiceException' => 'DMServiceException',
                                    'Response' => 'Response',
                                    'UriBuilder' => 'UriBuilder',
                                    'MediaType' => 'MediaType',
                                    'StatusType' => 'StatusType',
                                    'Link' => 'Link',
                                    'NewCookie' => 'NewCookie',
                                    'EntityTag' => 'EntityTag',
                                    'Family' => 'Family',
                                    'Cookie' => 'Cookie',
                                    'string2ArrayOfAnyTypeMultivaluedMap' => 'string2ArrayOfAnyTypeMultivaluedMap',
                                    'entry' => 'entry',
                                    'string2stringMap' => 'string2stringMap',
                                    'entry' => 'entry',
                                    'string2NewCookieMap' => 'string2NewCookieMap',
                                    'entry' => 'entry',
                                    'convertDocumentVersions' => 'convertDocumentVersions',
                                    'convertDocumentVersionsResponse' => 'convertDocumentVersionsResponse',
                                    'convertDocument' => 'convertDocument',
                                    'convertDocumentResponse' => 'convertDocumentResponse',
                                    'convertDocumentVersion' => 'convertDocumentVersion',
                                    'convertDocumentVersionResponse' => 'convertDocumentVersionResponse',
                                    'convertDocuments' => 'convertDocuments',
                                    'convertDocumentsResponse' => 'convertDocumentsResponse',
                                   );

  public function ConverterService($wsdl = "http://192.168.122.118:9999/kimios/services/ConverterService?wsdl", $options = array()) {
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
   * @param convertDocumentVersions $parameters
   * @return convertDocumentVersionsResponse
   */
  public function convertDocumentVersions(convertDocumentVersions $parameters) {
    return $this->__soapCall('convertDocumentVersions', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param convertDocument $parameters
   * @return convertDocumentResponse
   */
  public function convertDocument(convertDocument $parameters) {
    return $this->__soapCall('convertDocument', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param convertDocumentVersion $parameters
   * @return convertDocumentVersionResponse
   */
  public function convertDocumentVersion(convertDocumentVersion $parameters) {
    return $this->__soapCall('convertDocumentVersion', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param convertDocuments $parameters
   * @return convertDocumentsResponse
   */
  public function convertDocuments(convertDocuments $parameters) {
    return $this->__soapCall('convertDocuments', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
