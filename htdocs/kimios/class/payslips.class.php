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
 
class KimiosPayslips extends KimiosDB{

   var $payslipsFields = array(
      'payslips_code','doliuserid',
      'last_send','last_sender', 'doliuseremail'
   );

   function getTable() {
      return MAIN_DB_PREFIX."kimios_payslips";
   }

   static function showStep( $step ) {
      call_user_func(array('KimiosPayslips','showStep'.$step));
   }

   function showStep1() {
      global $langs, $db;

      $formother = new FormOther($db);

      print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">'."\n";

      $month_start = GETPOST("remonth")?GETPOST("remonth"):date("m", time());
      $year_start = GETPOST("reyear")?GETPOST("reyear"):date("Y", time());

      $month_year = sprintf("%02d",$month_start).'-'.sprintf("%04d",$year_start);
      $year_month = sprintf("%04d",$year_start).'-'.sprintf("%02d",$month_start);

      print '<p>Commencez par choisir un mois et une année : </p>';

      print $formother->select_month($month_start,'remonth');

      print $formother->select_year($year_start,'reyear');

      print '<input type="submit" class="button" value="' . 
                        dol_escape_htmltag($langs->trans("Search")) . '" />';

      print '<input type="hidden" name="step" value="2" />';

      print '</form>';
   }

   function showStep2() {
      global $langs, $db, $bc;

      global $KimiosPhpSoap, $KimiosPayslips, $KimiosConfig, $sessionId;

      $initialPath = "/".$KimiosConfig->fields['initialPath']."/";
      if (strlen($initialPath) == 2) $initialPath = "/";

      $month_start = GETPOST("remonth")?GETPOST("remonth"):date("m", time());
      $year_start = GETPOST("reyear")?GETPOST("reyear"):date("Y", time());

      $SearchService = new SearchService(
         $KimiosPhpSoap->getWsdl(
            $KimiosConfig->fields['url'], 'SearchService'), 
         $KimiosPhpSoap->getArrayOptionsService(
            $KimiosConfig->fields['url'], 'SearchService')
      );

      $path = $initialPath;
      $path.= "TECLIB Groupe/Human Resources (RH)/Salaires Teclib' S.A.S/";
      $path.= $year_start."/";

      if ($year_start != "2009") {
         $path.= $KimiosPayslips->get_full_month_text($month_start)."/";
      }

      print '<p><b>Répertoire GED sélectionné :</b> '.$path.'</p>';

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
      } else {
         print '<p style="color:red;font-weight:bold;">Le répertoire dans la GED est inexistant !</p>';
      }

      $DocumentService = new DocumentService(
         $KimiosPhpSoap->getWsdl(
            $KimiosConfig->fields['url'], 'DocumentService'),
         $KimiosPhpSoap->getArrayOptionsService(
            $KimiosConfig->fields['url'], 'DocumentService')
      );

      $documents = new getDocuments(
         array('sessionId' => $sessionId,
               'folderId' => $documentId)
      );

      $documentsResp = $DocumentService->getDocuments($documents);

      $allDocuments = $documentsResp->return->Document;

      if (count($allDocuments) == 0) {

         print '<p style="color:red;font-weight:bold;">Le répertoire de la GED ne contient aucune fiche à envoyer.</p>';

      } else {

         print '<p>Vérifiez les informations, puis sélectionnez les fiches à envoyer.</p>';

         $payslips_code = $month_start."_".$year_start; 

         print '<script type="text/javascript">
            function checkAll(ref, name) {
               var form = ref;
             
               while (form.parentNode && form.nodeName.toLowerCase() != \'form\'){ 
                  form = form.parentNode; 
               }
             
               var elements = form.getElementsByTagName(\'input\');
             
               for (var i = 0; i < elements.length; i++) {
                  if (elements[i].type == \'checkbox\' && elements[i].name == name) {
                     elements[i].checked = ref.checked;
                  }
               }
            }
         </script>';

         print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">'."\n";

         print '<table class="liste" width="80%">'."\n";

         print '<tr class="liste_titre">';
            print '<td class="liste_titre">Num.</td>';
            print '<td class="liste_titre">Fichier PDF</td>';
            print '<td class="liste_titre">Utilisateur ERP</td>';
            print '<td class="liste_titre">Email</td>';
            print '<td class="liste_titre">Dernier envoi</td>';
            print '<td class="liste_titre">Dernier expediteur</td>';
            print '<td class="liste_titre" style="text-align:center;">Sélectionner<br />
            <input name="coche" type="checkbox" onclick="checkAll(this, \'KimiosPayslips_rowid[]\');"  title="Tout / Rien"/></td>';
         print '</tr>';

         $var=True; $total = 0;

         foreach($allDocuments as $documentObject) {

            if ($doliUserId = $KimiosPayslips->get_userid_by_filename($documentObject->name)) {
               $var=!$var;

               $total++;

               $userDoli = new User($db);
               $userDoli->fetch($doliUserId);

               $KimiosPayslips_f = $KimiosPayslips->find("`payslips_code` = '" . $payslips_code . "' 
                                          AND `doliuserid` = '" . $doliUserId . "'");
               if (count($KimiosPayslips_f) == 0) {
                  $KimiosPayslips->fields = array(
                     'doliuserid' => $doliUserId,
                     'payslips_code' => $payslips_code,
                     'doliuseremail' => $userDoli->email,
                     'kimios_docuid' => $documentObject->uid,
                     'kimios_docpath' => addslashes($documentObject->path),
                     'kimios_docmime' => addslashes($documentObject->mimeType),
                     'kimios_docname' => $documentObject->name.".".$documentObject->extension,
                     'last_send' => NULL,
                     'last_sender' => NULL
                  );
                  $KimiosPayslips->addToDB();
                  
                  $KimiosPayslips_f = $KimiosPayslips->find("`payslips_code` = '" . $payslips_code . "' 
                                             AND `doliuserid` = '" . $doliUserId . "'");
               }

               $KimiosPayslips_f = $KimiosPayslips_f[key($KimiosPayslips_f)];

               print '<tr '.$bc[$var].'>';
                  print '<td>'.$total.'</td>';
                  print '<td><a href="'.DOL_URL_ROOT.'/teclib/kimios/payslips.php?action=download&payslips_rowid='
                                 . $KimiosPayslips_f['rowid'] . '" target="_blank">' . $documentObject->name . '</a></td>';
                  print '<td><a href="'.DOL_URL_ROOT.'/user/card.php?id=' .
                           $doliUserId.'" target="_blank">' .
                           img_object($langs->trans("ShowUser"),"user").' ' .
                           $userDoli->firstname.' '.$userDoli->lastname.'</a>';
                  print '</td>';
                  print '<td>'.$userDoli->email.'</td>';
                  if ($KimiosPayslips_f['last_send'] != 0) {
                     print '<td>'.date('d-m-Y', strtotime($KimiosPayslips_f['last_send'])).'</td>';               
                  } else {
                     print '<td>Jamais envoyée.</td>';
                  }

                  if ($KimiosPayslips_f['last_sender'] != 0) {
                     $doliSenderId = $KimiosPayslips_f['last_sender'];
                     $userDoliSender = new User($db);
                     $userDoliSender->fetch($doliSenderId);
                     print '<td><a href="'.DOL_URL_ROOT.'/user/card.php?id=' .
                              $doliSenderId.'" target="_blank">' .
                              img_object($langs->trans("ShowUser"),"user").' ' .
                              $userDoliSender->firstname.' '.$userDoliSender->lastname.'</a>';
                     print '</td>';
                  } else {
                     print '<td>Jamais envoyée.</td>';
                  }

                  print '<td style="text-align:center;">';
                     print '<input class="flat" name="KimiosPayslips_rowid[]" type="checkbox" value="'
                                                . $KimiosPayslips_f['rowid'] . '" size="1">';
                  print '</td>';
               print '</tr>';
            }
         }

         print '<tr class="liste_total">';
            print '<td class="liste_titre" style="text-align:left;" colspan="7"><b>Nombre de fiches de paie : </b>'.$total.'</td>';
         print '</tr>';

         print '</table>';

         print '</br ><h3>Après avoir vérifié puis sélectionné les fiches à envoyer, cliquez sur : <input type="submit" class="button" value="' . 
                           dol_escape_htmltag($langs->trans("Valider")) . '" /><h3>';

         print '<input type="hidden" name="step" value="3" />';

         print '</form>';
      
      }//end ELSE
   }

   function showStep3() {
      global $langs, $db, $bc;

      global $KimiosPhpSoap, $KimiosPayslips, $KimiosConfig, $sessionId;

      $KimiosPayslips_rowid = GETPOST("KimiosPayslips_rowid")?GETPOST("KimiosPayslips_rowid"):0;

      print '<p><b>Afin d\'éviter toute erreur, nous vous demandons une nouvelle confirmation.</b></p>';

      print '<p>Vous êtes sur le point d\'envoyer les fiches suivantes : </p>';

      if ($KimiosPayslips_rowid != 0) {

         print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">'."\n";

         print '<table class="liste" width="80%">'."\n";

         print '<tr class="liste_titre">';
            print '<td class="liste_titre">Kimios doc id</td>';
            print '<td class="liste_titre">Kimios doc path</td>';
            print '<td class="liste_titre">Utilisateur ERP</td>';
            print '<td class="liste_titre">Email</td>';
         print '</tr>';

         $var=True;

         foreach($KimiosPayslips_rowid as $rowid) {
            $var!=$var;

            $KimiosPayslips->getFromDB($rowid);

            $doliUser = new User($db);
            $doliUser->fetch($KimiosPayslips->fields['doliuserid']);
            
            print '<tr '.$bc[$var].'>';

               print '<input name="KimiosPayslips_rowid[]" type="hidden" value="'.$rowid.'">';

               print '<td>'.$KimiosPayslips->fields['kimios_docuid'].'</td>';
               print '<td><a href="'.DOL_URL_ROOT.'/teclib/kimios/payslips.php?action=download&payslips_rowid='.$rowid.'">' 
                           . $KimiosPayslips->fields['kimios_docpath'].'</a>';
               print '</td>';
               print '<td><a href="'.DOL_URL_ROOT.'/user/card.php?id=' .
                        $KimiosPayslips->fields['doliuserid'].'" target="_blank">' .
                        img_object($langs->trans("ShowUser"),"user").' ' .
                        $doliUser->firstname.' '.$doliUser->lastname.'</a>';
               print '</td>';
               print '<td>'.$KimiosPayslips->fields['doliuseremail'].'</td>';

            print '</tr>';

         }

         print '<table>';

         print '<br /><h3>Plus le nombre de fiches sélectionnées est élevé, plus le temps d\'envoi est long !';
         print '<br />Dès que vous le souhaitez, cliquer sur : <input type="submit" class="button" value="' . 
                           dol_escape_htmltag($langs->trans("Envoyer")) . '" /><h3>';

         print '<input type="hidden" name="step" value="4" />';

         print '</form>';
      }

   }

   function showStep4() {
      global $langs, $db, $bc, $user;

      global $KimiosPhpSoap, $KimiosPayslips, $KimiosConfig, $sessionId;

      $KimiosPayslips_rowid = GETPOST("KimiosPayslips_rowid")?GETPOST("KimiosPayslips_rowid"):0;

      if ($KimiosPayslips_rowid != 0) {

         foreach($KimiosPayslips_rowid as $rowid) {
            $KimiosPayslips->getFromDB($rowid);

            $doliUser = new User($db);
            $doliUser->fetch($KimiosPayslips->fields['doliuserid']);

            $payslips_code = explode("_", $KimiosPayslips->fields['payslips_code']);
            $month = KimiosPayslips::get_month_text($payslips_code[0]);
            $year = $payslips_code[1];

            $mimetype = $KimiosPayslips->fields['kimios_docmime'];
            $filename = $KimiosPayslips->fields['kimios_docname'];
            $fullpath = DOL_DATA_ROOT.'/kimios/'.$filename;

            $documentId = $KimiosPayslips->fields['kimios_docuid'];

            $DocumentService = new DocumentService(
               $KimiosPhpSoap->getWsdl(
                  $KimiosConfig->fields['url'], 'DocumentService'), 
               $KimiosPhpSoap->getArrayOptionsService(
                  $KimiosConfig->fields['url'], 'DocumentService'));
            $getDocument = new getDocument(
               array('sessionId' => $sessionId,
                  'documentId' => $documentId)
            );
            $documentResp = $DocumentService->getDocument($getDocument);
            $Document = $documentResp->return;
            $fileName = $Document->name.".".$Document->extension;
            $mimeType = $Document->mimeType;

            $DocumentVersionService = new DocumentVersionService(
               $KimiosPhpSoap->getWsdl(
                  $KimiosConfig->fields['url'], 'DocumentVersionService'), 
               $KimiosPhpSoap->getArrayOptionsService(
                  $KimiosConfig->fields['url'], 'DocumentVersionService')
            );
            $lastDocumentVersion = new getLastDocumentVersion(
               array('sessionId' => $sessionId,
                  'documentId' => $documentId)
            );
            $lastDvResp = $DocumentVersionService->getLastDocumentVersion($lastDocumentVersion);
            $dvUid = $lastDvResp->return->uid;

            $FileTransferService = new FileTransferService(
               $KimiosPhpSoap->getWsdl(
                  $KimiosConfig->fields['url'],'FileTransferService'), 
               $KimiosPhpSoap->getArrayOptionsService(
                  $KimiosConfig->fields['url'],'FileTransferService'));
            $downloadTransaction = new startDownloadTransaction(
               array('sessionId'       => $sessionId,
                  'documentVersionId' => $dvUid,
                  'isCompressed'       => false)
            );
            $stDlResp = $FileTransferService->startDownloadTransaction($downloadTransaction);
            $dTxUid  = $stDlResp->return->uid; 
            $fileSize   = $stDlResp->return->size;

            $chunkSize  = 1024;
            $offset  = 0;
            $chk = new getChunck(
               array('transactionId'   => $dTxUid,
                  'sessionId'       => $sessionId,
                  'chunkSize'       => $chunkSize,
                  'offset'          => $offset)
            );

            while($offset < $fileSize){
               if(($offset + $chunkSize)>$fileSize){
                  $chunkSize = ($fileSize - $offset);
               }
               $chk->offset   = $offset;
               $chk->chunkSize = $chunkSize;
               $chkResp       = $FileTransferService->getChunck($chk);
               file_put_contents($fullpath, $chkResp->return, FILE_APPEND | LOCK_EX);
               $offset     = $offset + $chunkSize;
            }

            $formmail = new FormMail($db);
            $filepath = array($fullpath);
            $filename = array($filename);
            $mimetype = array($mimetype);

            $subject = "Fiche de paie - $month $year";
            $sendto = $KimiosPayslips->fields['doliuseremail'];
            $from = "administration@teclib.com";

            $message = "Bonjour {$doliUser->firstname},\n\n";
            $message.= "Veuillez trouver en pièce jointe votre fiche de paie du mois de {$month} {$year}.\n\n";
            $message.= "Bonne réception,\nteclib'";

            $sendtocc = "administration@teclib.com";
            $deliveryreceipt = 0;

            $mailfile = new CMailFile($subject,$sendto,$from,$message,$filepath,$mimetype,$filename,$sendtocc,'',$deliveryreceipt,-1);

            if ($mailfile->error) {
               print '<p>Erreur : '.$mailfile->error.'</p>';
            
               unlink($fullpath);
            
            } else {
               $result=$mailfile->sendfile();
               if ($result) {

                  unlink($fullpath);

                  $input['rowid'] = $rowid;
                  $input['last_send'] = date('Y-m-d h:i:s', time());
                  $input['last_sender'] = $user->id;
                  $KimiosPayslips->update($input);

                  print '<p>La fiche de paie de <b>' . $doliUser->firstname 
                              . ' ' . $doliUser->lastname
                              . '</b> du mois de <b>' . $month . ' ' . $year 
                              . '</b> a correctement été envoyée à l\'adresse email : <b>' 
                              . $doliUser->email . '</b>.</p>';
               }
            }

         }//end foreach
      }
   }

   function download($payslips_rowid) {
      global $langs, $db, $bc, $user;

      global $KimiosPhpSoap, $KimiosPayslips, $KimiosConfig, $sessionId;

      $KimiosPayslips->getFromDB($payslips_rowid);
      $documentId = $KimiosPayslips->fields['kimios_docuid'];

      $DocumentService = new DocumentService(
         $KimiosPhpSoap->getWsdl(
            $KimiosConfig->fields['url'], 'DocumentService'), 
         $KimiosPhpSoap->getArrayOptionsService(
            $KimiosConfig->fields['url'], 'DocumentService'));
      $getDocument = new getDocument(
         array('sessionId' => $sessionId,
            'documentId' => $documentId)
      );
      $documentResp = $DocumentService->getDocument($getDocument);
      $Document = $documentResp->return;
      $fileName = $Document->name.".".$Document->extension;
      $mimeType = $Document->mimeType;

      $DocumentVersionService = new DocumentVersionService(
         $KimiosPhpSoap->getWsdl(
            $KimiosConfig->fields['url'], 'DocumentVersionService'), 
         $KimiosPhpSoap->getArrayOptionsService(
            $KimiosConfig->fields['url'], 'DocumentVersionService')
      );
      $lastDocumentVersion = new getLastDocumentVersion(
         array('sessionId' => $sessionId,
            'documentId' => $documentId)
      );
      $lastDvResp = $DocumentVersionService->getLastDocumentVersion($lastDocumentVersion);
      $dvUid = $lastDvResp->return->uid;

      $FileTransferService = new FileTransferService(
         $KimiosPhpSoap->getWsdl(
            $KimiosConfig->fields['url'],'FileTransferService'), 
         $KimiosPhpSoap->getArrayOptionsService(
            $KimiosConfig->fields['url'],'FileTransferService'));
      $downloadTransaction = new startDownloadTransaction(
         array('sessionId'       => $sessionId,
            'documentVersionId' => $dvUid,
            'isCompressed'       => false)
      );
      $stDlResp = $FileTransferService->startDownloadTransaction($downloadTransaction);
      $dTxUid  = $stDlResp->return->uid; 
      $fileSize   = $stDlResp->return->size;

      $chunkSize  = 1024;
      $offset  = 0;
      $chk = new getChunck(
         array('transactionId'   => $dTxUid,
            'sessionId'       => $sessionId,
            'chunkSize'       => $chunkSize,
            'offset'          => $offset)
      );

      header("Content-Type: ".$mimeType."; name=\"".$fileName."\"");
      header("Content-Transfer-Encoding: binary");
      header("Content-Length: ".$fileSize."");
      header("Content-Disposition: attachment; filename=\"".$fileName."\"");
      header("Expires: 0");
      header("Cache-Control: no-cache, must-revalidate");
      header("Pragma: no-cache");

      while($offset < $fileSize){
         if(($offset + $chunkSize)>$fileSize){
            $chunkSize = ($fileSize - $offset);
         }
         $chk->offset   = $offset;
         $chk->chunkSize = $chunkSize;
         $chkResp       = $FileTransferService->getChunck($chk);
         file_put_contents('php://output', $chkResp->return);
         $offset     = $offset + $chunkSize;
      }
   }

   static function get_full_month_text($month_number) {
      switch ($month_number) {
         case '1' : return "01_Janvier"; break;
         case '2' : return "02_Février"; break;
         case '3' : return "03_Mars"; break;
         case '4' : return "04_Avril"; break;
         case '5' : return "05_Mai"; break;
         case '6' : return "06_Juin"; break;
         case '7' : return "07_Juillet"; break;
         case '8' : return "08_Août"; break;
         case '9' : return "09_Septembre"; break;
         case '10' : return "10_Octobre"; break;
         case '11' : return "11_Novembre"; break;
         case '12' : return "12_Décembre"; break;
      }
   }

   static function get_month_text($month_number) {
      switch ($month_number) {
         case '1' : return "Janvier"; break;
         case '2' : return "Février"; break;
         case '3' : return "Mars"; break;
         case '4' : return "Avril"; break;
         case '5' : return "Mai"; break;
         case '6' : return "Juin"; break;
         case '7' : return "Juillet"; break;
         case '8' : return "Août"; break;
         case '9' : return "Septembre"; break;
         case '10' : return "Octobre"; break;
         case '11' : return "Novembre"; break;
         case '12' : return "Décembre"; break;
      }
   }

   function get_userid_by_filename($filename) {
      global $db;

      //NOM Prénom_XXXX.pdf
      $userinfos = explode("_", $filename);
      $userinfos = explode(" ", $userinfos[0]);

      if (count($userinfos) == 3) {
         $firstname = strtoupper($userinfos[2]);
         $lastname = strtoupper($userinfos[0])." ".strtoupper($userinfos[1]);
      } else {
         $firstname = strtoupper($userinfos[1]);
         $lastname = strtoupper($userinfos[0]);
      }

      $sql = "SELECT rowid 
               FROM `llx_user` 
               WHERE `lastname` LIKE '%$lastname%'
               AND `firstname` LIKE '%$firstname%'";

      $result = $db->query($sql);

      if ($db->num_rows($result) == 0) {
         //NOM_XXX.pdf
         $userinfos = explode("_", $filename);
         $lastname = strtoupper($userinfos[0]);

         $sql = "SELECT rowid 
                  FROM `llx_user` 
                  WHERE `lastname` = UPPER(\"$lastname\")";

         $result = $db->query($sql);
      }

      while($obj = $db->fetch_object($result)) {
         return $obj->rowid;
      }
   }

}