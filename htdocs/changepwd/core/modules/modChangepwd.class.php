<?php

include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

class modChangepwd extends DolibarrModules {

	function __construct($db) {
        global $langs,$conf;

        $this->db = $db;
		$this->numero = 1191391519;
		$this->rights_class = 'teclib-changepwd';
		$this->family = "base";
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "teclib - Changement mot de passe LDAP";
		$this->version = '1.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto='group';
		$this->dirs = array();
		$this->module_parts = array(
		);
		$this->config_page_url = array();
		$this->phpmin = array(5,0);
		$this->need_dolibarr_version = array(3,0);
		$this->langfiles = array("main","users","companies");

		$this->rights = array();
		$this->rights_class = 'user';
	}

	function init($options='') {
		$sql = array();

		$result=$this->load_tables();

		return $this->_init($sql, $options);
	}
	
}

?>