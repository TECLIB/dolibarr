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

class RuleImplP {
  public $dmsEvent; // int
  public $dmsEventStatus; // int

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class Rule {
  public $beans; // ArrayOfRuleImplP
  public $javaClassName; // string
  public $toto; // anyType

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class RuleBean {
  public $id; // long
  public $javaClass; // string
  public $name; // string
  public $path; // string
  public $ruleCreationDate; // dateTime
  public $ruleOwner; // string
  public $ruleOwnerSource; // string
  public $ruleUpdateDate; // dateTime

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRuleParam {
  public $sessionId; // string
  public $javaClassName; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRuleParamResponse {
  public $return; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createRule {
  public $sessionId; // string
  public $conditionJavaClass; // string
  public $path; // string
  public $ruleName; // string
  public $xmlStream; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class createRuleResponse {
}

class getRuleItems {
  public $arg0; // ArrayOfRule

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getRuleItemsResponse {
  public $return; // ArrayOfRule
}
  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getBeans {
}

class getBeansResponse {
  public $return; // ArrayOfRuleBean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class sendList {
  public $arg0; // ArrayOfRuleBean

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class sendListResponse {
}

class getAvailablesRules {
  public $sessionId; // string

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

class getAvailablesRulesResponse {
  public $return; // ArrayOfString

  function __construct( $config ) {
    foreach($config as $key => $value) {
      $this->$key = $value;
    }
  }
}

/**
 * RuleService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class RuleService extends SoapClient {

  private static $classmap = array(
                                    'RuleImplP' => 'RuleImplP',
                                    'Rule' => 'Rule',
                                    'RuleBean' => 'RuleBean',
                                    'DMServiceException' => 'DMServiceException',
                                    'getRuleParam' => 'getRuleParam',
                                    'getRuleParamResponse' => 'getRuleParamResponse',
                                    'createRule' => 'createRule',
                                    'createRuleResponse' => 'createRuleResponse',
                                    'getRuleItems' => 'getRuleItems',
                                    'getRuleItemsResponse' => 'getRuleItemsResponse',
                                    'getBeans' => 'getBeans',
                                    'getBeansResponse' => 'getBeansResponse',
                                    'sendList' => 'sendList',
                                    'sendListResponse' => 'sendListResponse',
                                    'getAvailablesRules' => 'getAvailablesRules',
                                    'getAvailablesRulesResponse' => 'getAvailablesRulesResponse',
                                   );

  public function RuleService($wsdl = "http://192.168.122.118:9999/kimios/services/RuleService?wsdl", $options = array()) {
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
   * @param getRuleParam $parameters
   * @return getRuleParamResponse
   */
  public function getRuleParam(getRuleParam $parameters) {
    return $this->__soapCall('getRuleParam', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param createRule $parameters
   * @return createRuleResponse
   */
  public function createRule(createRule $parameters) {
    return $this->__soapCall('createRule', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getBeans $parameters
   * @return getBeansResponse
   */
  public function getBeans(getBeans $parameters) {
    return $this->__soapCall('getBeans', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getRuleItems $parameters
   * @return getRuleItemsResponse
   */
  public function getRuleItems(getRuleItems $parameters) {
    return $this->__soapCall('getRuleItems', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param sendList $parameters
   * @return sendListResponse
   */
  public function sendList(sendList $parameters) {
    return $this->__soapCall('sendList', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getAvailablesRules $parameters
   * @return getAvailablesRulesResponse
   */
  public function getAvailablesRules(getAvailablesRules $parameters) {
    return $this->__soapCall('getAvailablesRules', array($parameters),       array(
            'uri' => 'http://kimios.org',
            'soapaction' => ''
           )
      );
  }

}

?>
