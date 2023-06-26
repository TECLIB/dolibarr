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

include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

class modKimios extends DolibarrModules {

	function __construct($db) {
        global $langs,$conf;

        $this->db = $db;
		$this->numero = 107200;
		$this->rights_class = 'kimios';
		$this->family = "ecm";
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "Interface with GED Kimios";
        $this->editor_name = 'TecLib';
        $this->editor_url = 'http://www.teclib.com';
		$this->version = '1.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto='technic';
		$this->dirs = array("/kimios");
		$this->module_parts = array(
			//'hooks' => array('pdfgeneration')
		);
		$this->config_page_url = array("config.php@kimios");
		$this->phpmin = array(5,0);
		$this->need_dolibarr_version = array(3,0);
		$this->langfiles = array("kimios@kimios");

		$this->rights = array();
		$this->rights_class = 'kimios';
		$r=0;

		$r++;
		$this->rights[$r][0] = 107201;
		$this->rights[$r][1] = 'Envoyer les fiches de payes';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'send_payslips';
	}

	function init($options='') {
		$sql = array();

		$result=$this->load_tables();

		return $this->_init($sql, $options);
	}

	function remove($options='') {
		$sql = array();

		return $this->_remove($sql, $options);
	}

	function load_tables() {
		return $this->_load_tables('/kimios/sql/');
	}
}

?>
