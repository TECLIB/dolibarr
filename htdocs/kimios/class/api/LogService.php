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

class Log {
  public $date; // dateTime
  public $dmEntityType; // int
  public $dmEntityUid; // long
  public $operation; // int
  public $uid; // long
  public $user; // string
  public $userSource; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentLogs {
  public $sessionId; // string
  public $documentId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getDocumentLogsResponse {
  public $return; // ArrayOfLog

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkspaceLogs {
  public $sessionId; // string
  public $workspaceId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getWorkspaceLogsResponse {
  public $return; // ArrayOfLog

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getFolderLogs {
  public $sessionId; // string
  public $folderId; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getFolderLogsResponse {
  public $return; // ArrayOfLog

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * LogService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class LogService extends SoapClient {

  private static $classmap = array(
                                    'Log' => 'Log',
                                    'DMServiceException' => 'DMServiceException',
                                    'getDocumentLogs' => 'getDocumentLogs',
                                    'getDocumentLogsResponse' => 'getDocumentLogsResponse',
                                    'getWorkspaceLogs' => 'getWorkspaceLogs',
                                    'getWorkspaceLogsResponse' => 'getWorkspaceLogsResponse',
                                    'getFolderLogs' => 'getFolderLogs',
                                    'getFolderLogsResponse' => 'getFolderLogsResponse',
                                   );

  public function LogService($wsdl = "http://192.168.122.118:9999/kimios/services/LogService?wsdl", $options = array()) {
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
   * @param getDocumentLogs $parameters
   * @return getDocumentLogsResponse
   */
  public function getDocumentLogs(getDocumentLogs $parameters) {
    return $this->__soapCall('getDocumentLogs', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getWorkspaceLogs $parameters
   * @return getWorkspaceLogsResponse
   */
  public function getWorkspaceLogs(getWorkspaceLogs $parameters) {
    return $this->__soapCall('getWorkspaceLogs', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getFolderLogs $parameters
   * @return getFolderLogsResponse
   */
  public function getFolderLogs(getFolderLogs $parameters) {
    return $this->__soapCall('getFolderLogs', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
