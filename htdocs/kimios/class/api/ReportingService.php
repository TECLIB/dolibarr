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

class getReportsList {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getReportsListResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getReport {
  public $sessionId; // string
  public $className; // string
  public $xmlParameters; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getReportResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getReportAttributes {
  public $sessionId; // string
  public $className; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getReportAttributesResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeGhostTransaction {
  public $sessionId; // string
  public $transactionId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class removeGhostTransactionResponse {
}


/**
 * ReportingService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class ReportingService extends SoapClient {

  private static $classmap = array(
                                    'DMServiceException' => 'DMServiceException',
                                    'getReportsList' => 'getReportsList',
                                    'getReportsListResponse' => 'getReportsListResponse',
                                    'getReport' => 'getReport',
                                    'getReportResponse' => 'getReportResponse',
                                    'getReportAttributes' => 'getReportAttributes',
                                    'getReportAttributesResponse' => 'getReportAttributesResponse',
                                    'removeGhostTransaction' => 'removeGhostTransaction',
                                    'removeGhostTransactionResponse' => 'removeGhostTransactionResponse',
                                   );

  public function ReportingService($wsdl = "http://192.168.122.118:9999/kimios/services/ReportingService?wsdl", $options = array()) {
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
   * @param getReport $parameters
   * @return getReportResponse
   */
  public function getReport(getReport $parameters) {
    return $this->__soapCall('getReport', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getReportsList $parameters
   * @return getReportsListResponse
   */
  public function getReportsList(getReportsList $parameters) {
    return $this->__soapCall('getReportsList', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getReportAttributes $parameters
   * @return getReportAttributesResponse
   */
  public function getReportAttributes(getReportAttributes $parameters) {
    return $this->__soapCall('getReportAttributes', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param removeGhostTransaction $parameters
   * @return removeGhostTransactionResponse
   */
  public function removeGhostTransaction(removeGhostTransaction $parameters) {
    return $this->__soapCall('removeGhostTransaction', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
