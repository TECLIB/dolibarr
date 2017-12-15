<?php
/* Copyright (C) 2010 Franck Charpentier - Auguria <franck.charpentier@auguria.net>
 * Copyright (C) 2013 Laurent Destailleur          <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */


require_once(DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');

dol_include_once('/ecommerceng/admin/class/gui/eCommerceMenu.class.php');
dol_include_once('/ecommerceng/admin/class/data/eCommerceDict.class.php');
dol_include_once('/ecommerceng/class/data/eCommerceSite.class.php');


/**
 *  Description and activation class for module ECommerce
 */
class modECommerceNg extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param    DoliDB      $db      Database handler
	 */
	function __construct($db)
	{
	    global $conf;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 107100;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'ecommerceng';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "other";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = 'EcommerceNg';        //  Must be same than value used for if $conf->ecommerceng->enabled
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Module to synchronise Dolibarr with ECommerce platform (currently ecommerce supported: Magento, WooCommerce)";
		$this->descriptionlong = "See page https://wiki.dolibarr.org/index.php/Module_Magento_EN for more information";
		$this->editor_name = 'TecLib, Open-Dsi';
		$this->editor_url = 'http://www.teclib.com';

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '4.0.3';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 1;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/images directory, use this->picto=DOL_URL_ROOT.'/module/images/file.png'
		$this->picto='eCommerce.png@ecommerceng';

        // Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 // Set this to 1 if module has its own trigger directory
		//							'login' => 0,                                    // Set this to 1 if module has its own login method directory
		//							'substitutions' => 0,                            // Set this to 1 if module has its own substitution function file
		//							'menus' => 0,                                    // Set this to 1 if module has its own menus handler directory
		//							'barcode' => 0,                                  // Set this to 1 if module has its own barcode directory
		//							'models' => 0,                                   // Set this to 1 if module has its own models directory
		//							'css' => '/mymodule/css/mymodule.css.php',       // Set this to relative path of css if module has its own css file
		//							'hooks' => array('hookcontext1','hookcontext2')  // Set here all hooks context managed by module
		//							'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE')) // Set here all workflow context managed by module
		//                        );
		$this->module_parts = array(
            'triggers' => 1,
			'hooks' => array('expeditioncard','invoicecard','productdocuments'),
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();
		$r=0;

		// Relative path to module style sheet if exists. Example: '/mymodule/mycss.css'.
		$this->style_sheet = '';

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array('eCommerceSetup.php@ecommerceng');

		// Dependencies
		$this->depends = array("modSociete","modProduct","modCategorie","modWebServices");		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,9);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("ecommerce@ecommerceng", "woocommerce@ecommerceng");

		// Constants
		// List of particular constants to add when module is enabled
		$this->const = array(
		    0=>array('ECOMMERCENG_SHOW_DEBUG_TOOLS', 'chaine', '1', 'Enable button to clean database for debug purpose', 1, 'allentities', 1),
		    1=>array('ECOMMERCENG_DEBUG', 'chaine', '0', 'This is to enable ECommerceng log of web services requests', 1, 'allentities', 0),
		    2=>array('ECOMMERCENG_MAXSIZE_MULTICALL', 'chaine', '400', 'Max size for multicall', 1, 'allentities', 0),
			3=>array('ECOMMERCENG_MAXRECORD_PERSYNC', 'chaine', '2000', 'Max nb of record per synch', 1, 'allentities', 0),
			4=>array('ECOMMERCENG_ENABLE_LOG_IN_NOTE', 'chaine', '0', 'Store into private note the last full response returned by web service', 1, 'allentities', 0),
		);

		// Array to add new pages in new tabs
		//$this->tabs = array('entity:Title:@mymodule:/mymodule/mynewtab.php?id=__ID__');
		// where entity can be
		// 'thirdparty'       to add a tab in third party view
		// 'intervention'     to add a tab in intervention view
		// 'supplier_order'   to add a tab in supplier order view
		// 'supplier_invoice' to add a tab in supplier invoice view
		// 'invoice'          to add a tab in customer invoice view
		// 'order'            to add a tab in customer order view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'member'           to add a tab in fundation member view
		// 'contract'         to add a tab in contract view

        if (! isset($conf->ecommerceng) || ! isset($conf->ecommerceng->enabled))
        {
            $conf->ecommerceng=new stdClass();
            $conf->ecommerceng->enabled=0;
        }

        $eCommerceSite = new eCommerceSite($this->db);

        // Dictionaries
		$this->dictionaries=array(
		    'langs'=>'woocommerce@ecommerceng',
            'tabname'=>array(MAIN_DB_PREFIX."c_ecommerceng_tax_class"),
            'tablib'=>array("ECommercengWoocommerceDictTaxClass"),
            'tabsql'=>array('SELECT f.rowid as rowid, f.site_id, f.code, f.label, f.entity, f.active FROM '.MAIN_DB_PREFIX.'c_ecommerceng_tax_class as f WHERE f.entity='.$conf->entity),
            'tabsqlsort'=>array("site_id ASC, label ASC"),
            'tabfield'=>array("code,label,site_id"),
            'tabfieldvalue'=>array("code,label,site_id"),
            'tabfieldinsert'=>array("code,label,site_id"),
            'tabrowid'=>array("rowid"),
            'tabcond'=>array($conf->ecommerceng->enabled && $eCommerceSite->hasTypeSite(2))
        );

        /* Example:
        $this->dictionaries=array(
            'langs'=>'mylangfile@mymodule',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->mymodule->enabled,$conf->mymodule->enabled,$conf->mymodule->enabled)												// Condition to show each dictionary
        );
        */

		// Boxes
		$this->boxes = array();			// List of boxes
		$r=0;

		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
		// Example:
		//$this->boxes[$r][1] = "myboxa.php";
		//$r++;
		//$this->boxes[$r][1] = "myboxb.php";
		//$r++;

		// Cronjobs
		//------------
		$this->cronjobs = array(
			0=>array('label'=>'AutoSyncEcommerceNg', 'jobtype'=>'method', 'class'=>'ecommerceng/class/business/eCommerceUtils.class.php', 'objectname'=>'eCommerceUtils', 'method'=>'synchAll', 'parameters'=>'100', 'comment'=>'Synchronize all data from eCommerce to Dolibarr. Parameter is max nb of record to do per synchronization run.', 'frequency'=>1, 'unitfrequency'=>86400, 'priority'=>90, 'status'=>0, 'test'=>true),
		);

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$this->rights_class = 'ecommerceng';
		$r=0;

		$r++;
		$this->rights[$r][0] = 107101;
		$this->rights[$r][1] = 'See synchronization status';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'read';

		$r++;
		$this->rights[$r][0] = 107102;
		$this->rights[$r][1] = 'Synchronize';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'write';

		$r++;
		$this->rights[$r][0] = 107103;
		$this->rights[$r][1] = 'Configure websites';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'site';

		$r=0;

		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
		// Example:
		// $this->rights[$r][0] = 2000; 				// Permission id (must not be already used)
		// $this->rights[$r][1] = 'Permision label';	// Permission label
		// $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		// $this->rights[$r][4] = 'level1';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $r++;


		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus
		//if (! empty($conf->modules['ecommerceng']))     // Do not run this code if module is not yet enabled (tables does not exists yet)
		//{
    		$eCommerceMenu = new eCommerceMenu($this->db,null,$this);
	        $this->menu = $eCommerceMenu->getMenu();
		//}

		// Exports
		$r=1;

		// Example:
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		// $this->export_permission[$r]=array(array("facture","facture","export"));
		// $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.cp'=>'Zip','s.ville'=>'Town','s.fk_pays'=>'Country','s.tel'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.cp'=>'company','s.ville'=>'company','s.fk_pays'=>'company','s.tel'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		// $this->export_alias_array[$r]=array('s.rowid'=>"socid",'s.nom'=>'soc_name','s.address'=>'soc_adres','s.cp'=>'soc_zip','s.ville'=>'soc_ville','s.fk_pays'=>'soc_pays','s.tel'=>'soc_tel','s.siren'=>'soc_siren','s.siret'=>'soc_siret','s.ape'=>'soc_ape','s.idprof4'=>'soc_idprof4','s.code_compta'=>'soc_customer_accountancy','s.code_compta_fournisseur'=>'soc_supplier_accountancy','f.rowid'=>"invoiceid",'f.facnumber'=>"ref",'f.datec'=>"datecreation",'f.datef'=>"dateinvoice",'f.total'=>"totalht",'f.total_ttc'=>"totalttc",'f.tva'=>"totalvat",'f.paye'=>"paid",'f.fk_statut'=>'status','f.note'=>"note",'fd.rowid'=>'lineid','fd.description'=>"linedescription",'fd.price'=>"lineprice",'fd.total_ht'=>"linetotalht",'fd.total_tva'=>"linetotaltva",'fd.total_ttc'=>"linetotalttc",'fd.tva_tx'=>"linevatrate",'fd.qty'=>"lineqty",'fd.date_start'=>"linedatestart",'fd.date_end'=>"linedateend",'fd.fk_product'=>'productid','p.ref'=>'productref');
		// $this->export_sql_start[$r]='SELECT DISTINCT ';
		// $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		// $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		// $r++;
	}

	/**
	 *	Function called when module is enabled.
	 *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *	It also creates data directories.
	 *
	 *  @param     string  $options    Options
	 *  @return    int                 1 if OK, 0 if KO
	 */
	function init($options = '')
	{
		$sql = array();

		$result=$this->load_tables($options);
		$this->addSettlementTerms();
		$this->addAnonymousCompany();
        $this->addFiles();
		return $this->_init($sql, $options);
	}

	/**
	 *	Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted.
	 *
	 *  @param     string  $options    Options
	 *  @return    int                 1 if OK, 0 if KO
	 */
	function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/ecommerceng/sql/');
	}

	/**
	 * Add anonymous company for anonymous orders
	 */
	private function addAnonymousCompany()
	{
	    global $user;

		$idCompany = dolibarr_get_const($this->db, 'ECOMMERCE_COMPANY_ANONYMOUS');

		// Check for const existing but company deleted from dB
		if ($idCompany)
		{
			$dBSociete = new Societe($this->db);
			$idCompany = $dBSociete->fetch($idCompany) < 0 ? null:$idCompany ;
		}

		if ($idCompany == null)
		{
			$dBSociete = new Societe($this->db);
			$dBSociete->nom = 'Anonymous';
			$dBSociete->client = 3;//for client/prospect
			$dBSociete->create($user);

			if (dolibarr_set_const($this->db, 'ECOMMERCE_COMPANY_ANONYMOUS', $dBSociete->id) < 0)
			{
				dolibarr_print_error($this->db);
			}
		}
	}

	/**
	 * Add settlement terms if not exists
	 */
	private function AddSettlementTerms()
	{
		$table = MAIN_DB_PREFIX."c_payment_term";
		$eCommerceDict = new eCommerceDict($this->db, $table);
		$cashExists = $eCommerceDict->fetchByCode('CASH');
		if ($cashExists == array())
		{
			// Get free rowid to insert
			$newid = 0;
			$sql = "SELECT max(rowid) newid from ".$table;
			$maxId = $this->db->query($sql);
			if ($maxId)
			{
				$obj = $this->db->fetch_object($maxId);
				$newid = ($obj->newid + 1);
			}
			else
			{
				dol_print_error($this->db);
			}

			// Get free sortorder to insert
			$newSort = 0;
			$sql = "SELECT max(sortorder) newsortorder from ".$table;
			$maxSort = $this->db->query($sql);
			if ($maxSort)
			{
				$obj = $this->db->fetch_object($maxSort);
				$newSort = ($obj->newsortorder + 1);
			}
			else
			{
				dol_print_error($this->db);
			}

			if ($newid != 0 && $newSort != 0)
			{
			    if ((float) DOL_VERSION < 5.0)
			    {
    				$sql = "INSERT INTO ".$table."
    							(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour, decalage)
    						VALUES
    							(".$newid.", 'CASH', ".$newSort.", 1, 'Au comptant', 'A la commande', 0, 0, NULL)";
    				$insert = $this->db->query($sql);
			    }
			}
		}
	}

    /**
   	 * Add files need for dolibarr
   	 */
   	private function addFiles()
   	{
        $srcFile = dol_buildpath('/ecommerceng/patchs/dolibarr/includes/OAuth/OAuth2/Service/WordPress.php');
        $destFile = DOL_DOCUMENT_ROOT . '/includes/OAuth/OAuth2/Service/WordPress.php';

        if (dol_copy($srcFile, $destFile) < 0) {
            setEventMessages("Error copy file '$srcFile' to '$destFile'", null, 'errors');
        }
   	}
}

