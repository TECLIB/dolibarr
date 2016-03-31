<?php
/*
 LICENSE

 This file is part of the Kimios Dolibarr module.

 Kimios Dolibarr module is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Kimios Dolibarr module is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Kimios Dolibarr module. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   Kimios-Dolibarr
 @author    teclib (François Legastelois)
 @copyright Copyright (c) 2013 teclib'
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      http://www.teclib.com
 @since     2013
 ---------------------------------------------------------------------- */
 
define('_KIMIOS_EXEC',true);

class ActionsKimios {

	function afterPDFCreation($parameters, &$object, &$action, $hookmanager){
		global $user;

		return true;

		$KimiosPhpSoap = new KimiosPhpSoap();

		$file = $parameters['file'];
		$explode_file = explode("/",$file);
		$explode_number = count($explode_file);

		$KimiosConfig = new KimiosConfig();
		$KimiosConfig->getFromDB(1);

		// Get sessionId
		$sessionId = $this->connect();

		$path = "/".$KimiosConfig->fields['initialPath']."/";
		$path.= $parameters['object']->element."/";
		$path.= date('Y')."/";
		$path.= date('m')."/";
		$path.= $explode_file[$explode_number-1];

		$SearchService = new SearchService(
			$KimiosPhpSoap->getWsdl(
				$KimiosConfig->fields['url'], 'SearchService'), 
			$KimiosPhpSoap->getArrayOptionsService(
				$KimiosConfig->fields['url'], 'SearchService')
		);

		$DocumentService = new DocumentService(
			$KimiosPhpSoap->getWsdl(
				$KimiosConfig->fields['url'], 'DocumentService'), 
			$KimiosPhpSoap->getArrayOptionsService(
				$KimiosConfig->fields['url'], 'DocumentService')
		);

		$DocumentVersionService = new DocumentVersionService(
			$KimiosPhpSoap->getWsdl(
				$KimiosConfig->fields['url'], 'DocumentVersionService'), 
			$KimiosPhpSoap->getArrayOptionsService(
				$KimiosConfig->fields['url'], 'DocumentVersionService')
		);

		$getDMentityFromPath = new getDMentityFromPath(
			array('sessionId' => $sessionId,
				'path' => $path)
		);

		try{
			$SearchService->getDMentityFromPath($getDMentityFromPath);
			$documentExists = true;
		} catch(SoapFault $fault) {
			$documentExists = false;
		}

		if($documentExists) {
			$documentSearchResp = $SearchService->getDMentityFromPath($getDMentityFromPath);
			$documentId = $documentSearchResp->return->uid;

			$createDocumentVersion = new createDocumentVersion(
				array(	'sessionId'  => $sessionId,
						'documentId' => $documentId)
			);

			$DocumentVersionService->createDocumentVersion($createDocumentVersion);
		} else {
			$createDocumentFromFullPath = new createDocumentFromFullPath(
				array(	'sessionId' 			=> $sessionId,
						'path' 					=> $path,
						'isSecurityInherited' 	=> true)
			);

			$createDocFFPathResp = $DocumentService->createDocumentFromFullPath(
										$createDocumentFromFullPath);
			$documentId = $createDocFFPathResp->return;
		}

		$FileTransferService = new FileTransferService(
			$KimiosPhpSoap->getWsdl(
				$KimiosConfig->fields['url'],'FileTransferService'), 
			$KimiosPhpSoap->getArrayOptionsService(
				$KimiosConfig->fields['url'],'FileTransferService')
		);

		$uploadTransaction = new startUploadTransaction(
			array('sessionId' 	=> $sessionId,
				'documentId' 	=> $documentId,
				'isCompressed' 	=> false)
		);

		$startUploadTransactionResp = $FileTransferService->startUploadTransaction(
											$uploadTransaction);
		$transactionId = $startUploadTransactionResp->return->uid;

		$localDocument_filesize = filesize($file);
		$localDocument_md5		= md5_file($file);
		$localDocument_sha1		= sha1_file($file);

		$localDocument_handle 	= fopen($file, "r");
		$localDocument_content 	= fread($localDocument_handle, $localDocument_filesize);
		fclose($localDocument_handle);

		$sendChunk = new sendChunk(
			array(	'sessionId' 	=> $sessionId,
					'transactionId' => $transactionId,
					'data' 			=> $localDocument_content)
		);
		$FileTransferService->sendChunk($sendChunk);

		$endUploadTransaction = new endUploadTransaction(
			array('sessionId' 	=> $sessionId,
				'transactionId' => $transactionId,
				'md5' 			=> $localDocument_md5,
				'sha1' 			=> $localDocument_sha1)
		);

		$endUploadTransactionResp = $FileTransferService->endUploadTransaction(
										$endUploadTransaction);

        return 0;
    }

    function connect() {
    	global $user;
    	
    	$KimiosConfig = new KimiosConfig();
		$KimiosConfig->getFromDB(1);

		$UserDolibarr 		= $user->login;
		$KimiosService 		= $KimiosConfig->fields['userName'];
		$KimiosServiceKey 	= $KimiosConfig->fields['password'];

		$KimiosPasswordChain = $KimiosService."|||".$KimiosServiceKey;

		$KimiosPhpSoap = new KimiosPhpSoap();
		$KimiosPhpSoap->connect(
			$KimiosConfig->fields['url'],
			$KimiosConfig->fields['userSource'],
			$UserDolibarr,
			$KimiosPasswordChain
		);


		$sessionId = $KimiosPhpSoap->getSessionId();

		return $sessionId;
    }

}