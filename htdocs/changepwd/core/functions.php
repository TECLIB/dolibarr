<?php

$message = array();
$message_css = "";

function changePassword($user,$oldPassword,$newPassword,$newPasswordCnf)
{
  global $message;
  global $message_css;

  $server = "dc1.teclib.infra";
  $dn = "ou=people,dc=teclib,dc=infra";

  error_reporting(0);
  $con = ldap_connect($server);
  ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);

  $user_search = ldap_search($con,$dn,"(|(uid=$user)(mail=$user))");
  $user_get = ldap_get_entries($con, $user_search);
  $user_entry = ldap_first_entry($con, $user_search);
  $user_dn = ldap_get_dn($con, $user_entry);
  $user_id = $user_get[0]["uid"][0];
  $user_givenName = $user_get[0]["givenName"][0];
  $user_search_arry = array( "*", "ou", "uid", "mail", "passwordRetryCount", "passwordhistory" );
  $user_search_filter = "(|(uid=$user_id)(mail=$user))";
  $user_search_opt = ldap_search($con,$user_dn,$user_search_filter,$user_search_arry);
  $user_get_opt = ldap_get_entries($con, $user_search_opt);

  $passwordRetryCount = $user_get_opt[0]["passwordRetryCount"][0];
  $passwordhistory = $user_get_opt[0]["passwordhistory"][0];

  if ( $passwordRetryCount == 3 ) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E099 - Votre compte est bloqué!!!</div>";
    return false;
  }

  if (ldap_bind($con, $user_dn, $oldPassword) === false) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E100 - Nom d'utilisateur ou mot de passe actuel invalide.</div>";
    return false;
  }

  if ($oldPassword == $newPassword ) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E111 - Votre nouveau mot de passe correspond à votre mot de passe actuel, vous devez en choisir un NOUVEAU!</div>";
    return false;
  }

  if ($newPassword != $newPasswordCnf ) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E101 - Vos nouveaux mots de passe ne sont pas les mêmes!</div>";
    return false;
  }

  $encoded_newPassword = "{SHA}" . base64_encode( pack( "H*", sha1( $newPassword ) ) );

  $history_arr = ldap_get_values($con, $user_dn, "passwordhistory");

  if ( $history_arr ) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E102 - Votre nouveau mot de passe correspond à un de vos 10 derniers mots de passe, vous devez en choisir un NOUVEAU!</div>";
    return false;
  }

  if (strlen($newPassword) < 10 ) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E103 - Votre nouveau mot de passe est trop court.<br/>Il doit faire au minimum 10 caractères.</div>";
    return false;
  }

  if (!preg_match("/[0-9]/", $newPassword)) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E104 - Votre nouveau mot de passe doit contenir au moins un chiffre.</div>";
    return false;
  }

  if (!preg_match("/[a-zA-Z]/", $newPassword)) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E105 - Votre nouveau mot de passe doit contenir au moins une lettre.</div>";
    return false;
  }

  if (!preg_match("/[A-Z]/", $newPassword)) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E106 - Votre nouveau mot de passe doit contenir au moins une majuscule.</div>";
    return false;
  }

  if (!preg_match("/[a-z]/", $newPassword)) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E107 - Votre nouveau mot de passe doit contenir au moins une minuscule.</div>";
    return false;
  }

  if (!preg_match("/[\W]/", $newPassword)) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E108 - Votre nouveau mot de passe doit contenir au moins un symbole non alphanumérique.</div>";
    return false;
  }

  if (!$user_get) {
    $message[] = "<div style='color:red; font-weight:bold;'>Error E200 - Impossible de se connecter au serveur, vous ne pouvez pour l'instant pas modifier votre mot de passe.</div>";
    return false;
  }

  $auth_entry = ldap_first_entry($con, $user_search);
  $mail_addresses = ldap_get_values($con, $auth_entry, "mail");
  $given_names = ldap_get_values($con, $auth_entry, "givenName");
  $password_history = ldap_get_values($con, $auth_entry, "passwordhistory");
  $mail_address = $mail_addresses[0];
  $first_name = $given_names[0];

  $entry = array();
  $entry["userPassword"] = "$encoded_newPassword";

  if (ldap_modify($con,$user_dn,$entry) === false){
    $error = ldap_error($con);
    $errno = ldap_errno($con);
    $message[] = "<div style='color:red; font-weight:bold;'>E201 - Votre mot de passe ne peut pas être changé, contactez un administrateur système.</div>";
    $message[] = "$errno - $error";
  } else {
    $message_css = "yes";
    $message[] = "<div style='color:green; font-weight:bold;'>Le mot de passe pour l'utilisateur $user_id a été changé.<br/>Votre nouveau mot de passe est maintenant actif.</div>";
  }
}
