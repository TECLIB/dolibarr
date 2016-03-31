<?php

function __autoload($className) {
   $classFile = dol_include_once("/kimios/class/api/" . $className . '.php');
   if (file_exists($classFile)) {
      require_once($classFile);
      return true;
   }
   return false;
}