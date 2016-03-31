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

class DMEntity {
  public $creationDate; // dateTime
  public $name; // string
  public $owner; // string
  public $ownerSource; // string
  public $path; // string
  public $type; // int
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Criteria {
  public $facetField; // string
  public $facetRange; // boolean
  public $facetRangeGap; // string
  public $facetRangeMax; // string
  public $facetRangeMin; // string
  public $faceted; // boolean
  public $fieldName; // string
  public $filtersValues; // ArrayOfString
  public $level; // int
  public $metaId; // long
  public $metaType; // long
  public $position; // int
  public $query; // string
  public $rangeMax; // string
  public $rangeMin; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class SearchResponse {
  public $documentIds; // ArrayOfLong
  public $results; // int
  public $rows; // ArrayOfDocument

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class SearchRequest {
  public $criteriaList; // ArrayOfCriteria
  public $criteriasListJson; // string
  public $id; // long
  public $name; // string
  public $owner; // string
  public $ownerSource; // string
  public $sortDir; // string
  public $sortField; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class saveSearchQuery {
  public $sessionId; // string
  public $id; // long
  public $name; // string
  public $criterias; // ArrayOfCriteria
  public $sortField; // string
  public $sortDir; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class saveSearchQueryResponse {
}

class getDMentityFromPath {
  public $sessionId; // string
  public $path; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDMentityFromPathResponse {
  public $return; // DMEntity

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class listSearchQueries {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class listSearchQueriesResponse {
  public $return; // ArrayOfSearchRequest

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class advancedSearch {
  public $sessionId; // string
  public $xmlStream; // string
  public $dmEntityId; // long
  public $dmEntityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class advancedSearchResponse {
  public $return; // ArrayOfDocument

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteSearchQuery {
  public $sessionId; // string
  public $searchQueryId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class deleteSearchQueryResponse {
}

class quickSearch {
  public $sessionId; // string
  public $query; // string
  public $dmEntityId; // long
  public $dmEntityType; // int
  public $start; // int
  public $pageSize; // int
  public $sortField; // string
  public $sortDir; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class quickSearchResponse {
  public $return; // SearchResponse

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class loadSearchQuery {
  public $sessionId; // string
  public $searchQueryId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class loadSearchQueryResponse {
  public $return; // SearchRequest

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getPathFromDMEntity {
  public $sessionId; // string
  public $entityId; // long
  public $entityType; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getPathFromDMEntityResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}
class advancedSearchDocuments {
  public $sessionId; // string
  public $criterias; // ArrayOfCriteria
  public $start; // int
  public $pageSize; // int
  public $sortField; // string
  public $sortDir; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class advancedSearchDocumentsResponse {
  public $return; // SearchResponse

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class executeSearchQuery {
  public $sessionId; // string
  public $searchQueryId; // long
  public $start; // int
  public $pageSize; // int
  public $sortField; // string
  public $sortDir; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class executeSearchQueryResponse {
  public $return; // SearchResponse

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * SearchService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class SearchService extends SoapClient {

  private static $classmap = array(
                                    'Document' => 'Document',
                                    'DMEntity' => 'DMEntity',
                                    'Criteria' => 'Criteria',
                                    'SearchResponse' => 'SearchResponse',
                                    'SearchRequest' => 'SearchRequest',
                                    'DMServiceException' => 'DMServiceException',
                                    'saveSearchQuery' => 'saveSearchQuery',
                                    'saveSearchQueryResponse' => 'saveSearchQueryResponse',
                                    'getDMentityFromPath' => 'getDMentityFromPath',
                                    'getDMentityFromPathResponse' => 'getDMentityFromPathResponse',
                                    'listSearchQueries' => 'listSearchQueries',
                                    'listSearchQueriesResponse' => 'listSearchQueriesResponse',
                                    'advancedSearch' => 'advancedSearch',
                                    'advancedSearchResponse' => 'advancedSearchResponse',
                                    'deleteSearchQuery' => 'deleteSearchQuery',
                                    'deleteSearchQueryResponse' => 'deleteSearchQueryResponse',
                                    'quickSearch' => 'quickSearch',
                                    'quickSearchResponse' => 'quickSearchResponse',
                                    'loadSearchQuery' => 'loadSearchQuery',
                                    'loadSearchQueryResponse' => 'loadSearchQueryResponse',
                                    'getPathFromDMEntity' => 'getPathFromDMEntity',
                                    'getPathFromDMEntityResponse' => 'getPathFromDMEntityResponse',
                                    'advancedSearchDocuments' => 'advancedSearchDocuments',
                                    'advancedSearchDocumentsResponse' => 'advancedSearchDocumentsResponse',
                                    'executeSearchQuery' => 'executeSearchQuery',
                                    'executeSearchQueryResponse' => 'executeSearchQueryResponse',
                                   );

  public function SearchService($wsdl = "http://192.168.122.118:9999/kimios/services/SearchService?wsdl", $options = array()) {
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
   * @param getDMentityFromPath $parameters
   * @return getDMentityFromPathResponse
   */
  public function getDMentityFromPath(getDMentityFromPath $parameters) {
    return $this->__soapCall('getDMentityFromPath', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param saveSearchQuery $parameters
   * @return saveSearchQueryResponse
   */
  public function saveSearchQuery(saveSearchQuery $parameters) {
    return $this->__soapCall('saveSearchQuery', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param listSearchQueries $parameters
   * @return listSearchQueriesResponse
   */
  public function listSearchQueries(listSearchQueries $parameters) {
    return $this->__soapCall('listSearchQueries', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param advancedSearch $parameters
   * @return advancedSearchResponse
   */
  public function advancedSearch(advancedSearch $parameters) {
    return $this->__soapCall('advancedSearch', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param deleteSearchQuery $parameters
   * @return deleteSearchQueryResponse
   */
  public function deleteSearchQuery(deleteSearchQuery $parameters) {
    return $this->__soapCall('deleteSearchQuery', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param loadSearchQuery $parameters
   * @return loadSearchQueryResponse
   */
  public function loadSearchQuery(loadSearchQuery $parameters) {
    return $this->__soapCall('loadSearchQuery', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param quickSearch $parameters
   * @return quickSearchResponse
   */
  public function quickSearch(quickSearch $parameters) {
    return $this->__soapCall('quickSearch', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getPathFromDMEntity $parameters
   * @return getPathFromDMEntityResponse
   */
  public function getPathFromDMEntity(getPathFromDMEntity $parameters) {
    return $this->__soapCall('getPathFromDMEntity', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param advancedSearchDocuments $parameters
   * @return advancedSearchDocumentsResponse
   */
  public function advancedSearchDocuments(advancedSearchDocuments $parameters) {
    return $this->__soapCall('advancedSearchDocuments', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param executeSearchQuery $parameters
   * @return executeSearchQueryResponse
   */
  public function executeSearchQuery(executeSearchQuery $parameters) {
    return $this->__soapCall('executeSearchQuery', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
