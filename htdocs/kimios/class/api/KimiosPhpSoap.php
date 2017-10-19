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

class KimiosPhpSoap {

	private $sessionId = "";

	function connect($kimiosUrl,$userSource,$userName,$password) {
		$SecurityService = new SecurityService(
			$this->getWsdl($kimiosUrl, 'SecurityService'),
			$this->getArrayOptionsService($kimiosUrl, 'SecurityService')
		);

		$secStart = new startSession(
			array('userName'    => $userName,
					'userSource'  => $userSource,
					'password'    => $password)
		);

		$SecurityServiceResponse = $SecurityService->startSession($secStart);
		$this->sessionId = $SecurityServiceResponse->return;

		//TODO test sessionID
	}

	function getArrayOptionsService($serverUrl, $serviceName){
	    $context = stream_context_create([
	        'ssl' => [
	            // set some SSL/TLS specific options
	            'verify_peer' => false,
	            'verify_peer_name' => false,
	            'allow_self_signed' => true
	        ]
	    ]);

	    $options = array(
			'soap_version'	=> SOAP_1_1,
			'exceptions'	=> true,
			'trace'			=> 1,
			'location'		=> $serverUrl.'/services/'.$serviceName.'?wsdl',
		    'stream_context' => $context
		);

		return $options;
	}

	function getWsdl($serverUrl, $serviceName){
		$wsdlUrl = $this->getArrayOptionsService($serverUrl, $serviceName);
		return $wsdlUrl['location'];
	}

	function getSessionId() {
		return $this->sessionId;
	}

}