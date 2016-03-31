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

class DMEntityAttribute {
  public $indexed; // boolean
  public $name; // string
  public $value; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getEntityAttributeValue {
  public $sessionId; // string
  public $dmEntityId; // long
  public $attributeName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getEntityAttributeValueResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getEntityAttributes {
  public $sessionId; // string
  public $dmEntityId; // long
  public $attributeName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getEntityAttributesResponse {
  public $return; // ArrayOfDMEntityAttribute

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class setEntityAttribute {
  public $sessionId; // string
  public $dmEntityId; // long
  public $attributeValue; // string
  public $attributeName; // string
  public $isIndexed; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class setEntityAttributeResponse {
}

class generatePasswordForUser {
  public $sessionId; // string
  public $userId; // string
  public $userSource; // string
  public $sendMail; // boolean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class generatePasswordForUserResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getEntityAttribute {
  public $sessionId; // string
  public $dmEntityId; // long
  public $attributeName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getEntityAttributeResponse {
  public $return; // DMEntityAttribute

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}


/**
 * ExtensionService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class ExtensionService extends SoapClient {

  private static $classmap = array(
                                    'DMEntityAttribute' => 'DMEntityAttribute',
                                    'DMServiceException' => 'DMServiceException',
                                    'getEntityAttributeValue' => 'getEntityAttributeValue',
                                    'getEntityAttributeValueResponse' => 'getEntityAttributeValueResponse',
                                    'getEntityAttributes' => 'getEntityAttributes',
                                    'getEntityAttributesResponse' => 'getEntityAttributesResponse',
                                    'setEntityAttribute' => 'setEntityAttribute',
                                    'setEntityAttributeResponse' => 'setEntityAttributeResponse',
                                    'generatePasswordForUser' => 'generatePasswordForUser',
                                    'generatePasswordForUserResponse' => 'generatePasswordForUserResponse',
                                    'getEntityAttribute' => 'getEntityAttribute',
                                    'getEntityAttributeResponse' => 'getEntityAttributeResponse',
                                   );

  public function ExtensionService($wsdl = "http://192.168.122.118:9999/kimios/services/ExtensionService?wsdl", $options = array()) {
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
   * @param getEntityAttributeValue $parameters
   * @return getEntityAttributeValueResponse
   */
  public function getEntityAttributeValue(getEntityAttributeValue $parameters) {
    return $this->__soapCall('getEntityAttributeValue', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getEntityAttributes $parameters
   * @return getEntityAttributesResponse
   */
  public function getEntityAttributes(getEntityAttributes $parameters) {
    return $this->__soapCall('getEntityAttributes', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param generatePasswordForUser $parameters
   * @return generatePasswordForUserResponse
   */
  public function generatePasswordForUser(generatePasswordForUser $parameters) {
    return $this->__soapCall('generatePasswordForUser', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param setEntityAttribute $parameters
   * @return setEntityAttributeResponse
   */
  public function setEntityAttribute(setEntityAttribute $parameters) {
    return $this->__soapCall('setEntityAttribute', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getEntityAttribute $parameters
   * @return getEntityAttributeResponse
   */
  public function getEntityAttribute(getEntityAttribute $parameters) {
    return $this->__soapCall('getEntityAttribute', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
