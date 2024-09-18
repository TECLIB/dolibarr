<?php

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");


dol_include_once('/changepwd/core/functions.php');


/*
 * View
 */

llxHeader();

if ( $_SESSION['uid'] > 0 ) {
	header('Location : '.DOL_URL_ROOT.'/index.php');
	exit;
}

$login = $user->login;

echo '<div class="fiche ui-tabs ui-widget ui-widget-content ui-corner-all" style="width:600px; margin-top:20px;">';

echo '<div class="titre" style="font-size:16px;margin:10px 0px 0px 10px;">Changement de votre mot de passe LDAP teclib</div><br />';

?>

<div id="container" style="margin:10px 0px 0px 10px;">

 <p>Vous devez choisir <b>un mot de passe fort</b>, au minimum 10 caractères comportant :
 <ul>
   <li>au moins une majuscule [A B C D E F G H ... X Y Z] ;</li>
   <li>au moins une minuscule [ a b c d e f g h ... x y z ] ;</li>
   <li>au moins un chiffre [ 0 1 2 3 4 5 6 7 8 9 ] ;</li>
   <li>et au moins un symbole non alphanumérique [ ~ ! @ # $ % ^ & * ( ) - _ = + [ ] { } ; : , . < > / ? ].</li>
 </ul><br />
 Vous devez utiliser un <b>NOUVEAU</b> mot de passe, celui-ci ne doit pas être le même que l'actuel !<br /><br />
 Vous pouvez utiliser le site web suivant pour générer un mot de passe fort :
 <a href="http://www.generateurdemotdepasse.com">http://www.generateurdemotdepasse.com/</a><br /><br /></p>

 <?php

    if (GETPOST('submitted')) {

      changePassword($login, GETPOST('oldPassword'), GETPOST('newPassword1'), GETPOST('newPassword2'));

      global $message_css;

      if ($message_css == "yes") {

        echo '<div class="msg_yes">';

      } else {

        echo '<div class="msg_no">';

        $message[] = "<div style='color:red; font-weight:bold;'>Votre mot de passe n'a pas été changé.</div>";
      }

      foreach ( $message as $one ) {
      	echo "<p>$one</p>";
     	}

    	echo '</div>';

    }

?>

   <form action="<?php print $_SERVER['PHP_SELF']; ?>" name="passwordChange" method="post">

     <table style="width: 550px; margin:10px 0px 0px 10px;">

	     <tr>
	     		<th>Votre nom d'utilisateur :</th>
	     		<td><input name="username" type="text" size="20px" value="<?php echo $login; ?>" disabled /></td>
	     	</tr>
	     <tr>
	     		<th>Mot de passe actuel :</th>
	     		<td><input name="oldPassword" size="20px" type="password" value="<?php echo GETPOSTISSET('oldPassword') ? GETPOST('oldPassword') : ''; ?>" /></td>
	     	</tr>
	     <tr>
	     		<th>Nouveau mot de passe :</th>
	     		<td><input name="newPassword1" size="20px" type="password" /></td>
	     	</tr>
	     <tr>
	     		<th>Nouveau mot de passe (confirmation) :</th>
	     		<td><input name="newPassword2" size="20px" type="password" /></td>
	     	</tr>
	     <tr>
	     		<td colspan="2" style="text-align: center;" >
	     			<br /><br />
	     			<input name="submitted" type="submit" value="Valider" class="button" />
	     			<button class="button" onclick="$('frm').action='index.php';$('frm').submit();">Annuler</button>
	     		</td>
	     	</tr>

     </table>
   </form>
</div>

<?php

echo '</div>';

echo '</div>';

llxFooter();

$db->close();
