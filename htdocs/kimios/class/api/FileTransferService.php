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

class DataTransaction {
  public $compressed; // boolean
  public $hashMD5; // string
  public $hashSHA; // string
  public $size; // long
  public $uid; // long

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getChunck {
  public $sessionId; // string
  public $transactionId; // long
  public $offset; // long
  public $chunkSize; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getChunckResponse {
  public $return; // base64Binary

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class startUploadTransaction {
  public $sessionId; // string
  public $documentId; // long
  public $isCompressed; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class startUploadTransactionResponse {
  public $return; // DataTransaction

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class sendChunk {
  public $sessionId; // string
  public $transactionId; // long
  public $data; // base64Binary

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class sendChunkResponse {
}

class endUploadTransaction {
  public $sessionId; // string
  public $transactionId; // long
  public $md5; // string
  public $sha1; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class endUploadTransactionResponse {
}

class startDownloadTransaction {
  public $sessionId; // string
  public $documentVersionId; // long
  public $isCompressed; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class startDownloadTransactionResponse {
  public $return; // DataTransaction

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * FileTransferService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class FileTransferService extends SoapClient {

  private static $classmap = array(
                                    'DataTransaction' => 'DataTransaction',
                                    'DMServiceException' => 'DMServiceException',
                                    'getChunck' => 'getChunck',
                                    'getChunckResponse' => 'getChunckResponse',
                                    'startUploadTransaction' => 'startUploadTransaction',
                                    'startUploadTransactionResponse' => 'startUploadTransactionResponse',
                                    'sendChunk' => 'sendChunk',
                                    'sendChunkResponse' => 'sendChunkResponse',
                                    'endUploadTransaction' => 'endUploadTransaction',
                                    'endUploadTransactionResponse' => 'endUploadTransactionResponse',
                                    'startDownloadTransaction' => 'startDownloadTransaction',
                                    'startDownloadTransactionResponse' => 'startDownloadTransactionResponse',
                                   );

  public function FileTransferService($wsdl = "http://192.168.122.118:9999/kimios/services/FileTransferService?wsdl", $options = array()) {
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
   * @param endUploadTransaction $parameters
   * @return endUploadTransactionResponse
   */
  public function endUploadTransaction(endUploadTransaction $parameters) {
    return $this->__soapCall('endUploadTransaction', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getChunck $parameters
   * @return getChunckResponse
   */
  public function getChunck(getChunck $parameters) {
    return $this->__soapCall('getChunck', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param sendChunk $parameters
   * @return sendChunkResponse
   */
  public function sendChunk(sendChunk $parameters) {
    return $this->__soapCall('sendChunk', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param startUploadTransaction $parameters
   * @return startUploadTransactionResponse
   */
  public function startUploadTransaction(startUploadTransaction $parameters) {
    return $this->__soapCall('startUploadTransaction', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param startDownloadTransaction $parameters
   * @return startDownloadTransactionResponse
   */
  public function startDownloadTransaction(startDownloadTransaction $parameters) {
    return $this->__soapCall('startDownloadTransaction', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
