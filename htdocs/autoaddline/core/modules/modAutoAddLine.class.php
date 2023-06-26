<?php

/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 		\defgroup   mymodule     Module AutoAddLine
 *      \brief      Module AutoAddLine
 *      \file       htdocs/includes/modules/modStockManager.class.php
 *      \ingroup    mymodule
 *      \brief      Description and activation file for module MyModule
 */
include_once(DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php");


/**
 *      Description and activation class for module MyModule
 */
class modAutoAddLine extends DolibarrModules
{

    /**
     *   \brief      Constructor. Define names, constants, directories, boxes, permissions
     *   \param      DB      Database handler
     */
    function __construct($DB)
    {
        global $langs, $conf;

        $this->db = $DB;
        $this->numero = 107380;
        // Key text used to identify module (for permissions, menus, etc...)

        $this->family = "other";
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Possible values for version are: 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';

        // Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);

        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
        $this->picto = 'teclib@autoaddline';

        // Defined if the directory /mymodule/includes/triggers/ contains triggers or not
        $this->triggers = 0;

        // Defined all module parts (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = array('triggers' => 1,
        							'substitutions' => 0,
        							'models' => 0,
        							'css' => array(),
        							'hooks' => array()
        );

        // Data directories to create when module is enabled.
        $this->dirs = array();
        $r = 0;

        // Relative path to module style sheet if exists. Example: '/mymodule/css/mycss.css'.
//        $this->style_sheet = '/autoaddline/css/autoaddline.css.php';

        // Config pages. Put here list of php page names stored in admmin directory used to setup module.
        $this->config_page_url = array('settings.php@autoaddline');

        // Dependencies
        $this->depends = array('modService', 'modFacture');  // List of modules id that must be enabled if this module is enabled
        $this->requiredby = array(); // List of modules id to disable if this one is disabled
        $this->phpmin = array(5, 0);     // Minimum version of PHP required by module
        $this->need_dolibarr_version = array(3, 0); // Minimum version of Dolibarr required by module
        $this->langfiles = array('autoaddline@autoaddline');
        $this->description = 'Calcul et ajout automatique de lignes finales aux facture en fonction de services définis pour les produits associés (taxe carbone, réduction...)';
        $this->editor_name = 'TecLib';
        $this->editor_url = 'http://www.teclib.com';

        // Constants
        // List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
        // Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
        //                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0) );
        //                             2=>array('MAIN_MODULE_MYMODULE_NEEDSMARTY','chaine',1,'Constant to say module need smarty',1)
//        $this->const = array(
//                0 => array(// Reception name for documents rights access
//                        'STOCKMANAGER_RECEPTION_SUBPERMCATEGORY_FOR_DOCUMENTS',
//                        'chaine',
//                        'reception',
//                        'Document\'s generation required constant',
//                        0,
//                        0,
//                        0
//                )
//        );

        // Array to add new pages in new tabs
        // Example: $this->tabs = array('objecttype:+tabname1:Title1:@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  // To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:Title2:@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  // To add another new tab identified by code tabname2
        //                              'objecttype:-tabname');                                                     // To remove an existing tab identified by code tabname
        // where objecttype can be
        // 'thirdparty'       to add a tab in third party view
        // 'intervention'     to add a tab in intervention view
        // 'order_supplier'   to add a tab in supplier order view
        // 'invoice_supplier' to add a tab in supplier invoice view
        // 'invoice'          to add a tab in customer invoice view
        // 'order'            to add a tab in customer order view
        // 'product'          to add a tab in product view
        // 'stock'            to add a tab in stock view
        // 'propal'           to add a tab in propal view
        // 'member'           to add a tab in fundation member view
        // 'contract'         to add a tab in contract view
        // 'user'             to add a tab in user view
        // 'group'            to add a tab in group view
        // 'contact'          to add a tab in contact view
        // 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        $this->tabs = array();

        // Dictionnaries
        $this->dictionnaries = array();
        /*
          $this->dictionnaries=array(
          'langs'=>'cabinetmed@cabinetmed',
          'tabname'=>array(MAIN_DB_PREFIX."cabinetmed_diaglec",MAIN_DB_PREFIX."cabinetmed_examenprescrit",MAIN_DB_PREFIX."cabinetmed_motifcons"),
          'tablib'=>array("DiagnostiqueLesionnel","ExamenPrescrit","MotifConsultation"),
          'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_diaglec as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_examenprescrit as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_motifcons as f'),
          'tabsqlsort'=>array("label ASC","label ASC","label ASC"),
          'tabfield'=>array("code,label","code,label","code,label"),
          'tabfieldvalue'=>array("code,label","code,label","code,label"),
          'tabfieldinsert'=>array("code,label","code,label","code,label"),
          'tabrowid'=>array("rowid","rowid","rowid"),
          'tabcond'=>array($conf->cabinetmed->enabled,$conf->cabinetmed->enabled,$conf->cabinetmed->enabled)
          );
         */

        // Boxes
        // Add here list of php file(s) stored in includes/boxes that contains class to show a box.
        $this->boxes = array();   // List of boxes
        $r = 0;
        // Example:
        /*
          $this->boxes[$r][1] = "myboxa.php";
          $r++;
          $this->boxes[$r][1] = "myboxb.php";
          $r++;
         */

        // Permissions
        $this->rights = array();  // Permission array used by this module
        $this->rights_class = 'autoaddline';
        $this->const = array();

        // Add here list of permission defined by an id, a label, a boolean and two constant strings.
        // Example:
        // $this->rights[$r][0] = 2000; 				// Permission id (must not be already used)
        // $this->rights[$r][1] = 'Permision label';	// Permission label
        // $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
        // $this->rights[$r][4] = 'level1';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        // $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        // $r++;
        // Setting rights
        // Rights for receptions
//        $r = 0;
//        $this->rights [$r] = array(// Read
//                0 => 21101, // Permission id
//                1 => 'Consulter les réceptions', // Permission label
//                3 => 0, // Default for new user
//                4 => 'reception', // Generic attribute name in module (level one)
//                5 => 'read'                                    // Specific attribute name in module (level one)
//        );


        // Create documentsDirs

        // Exports
//        $r = 1;

        // Example:
        // $this->export_code[$r]=$this->rights_class.'_'.$r;
        // $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        // $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
        // $this->export_permission[$r]=array(array("facture","facture","export"));
        // $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.cp'=>'Zip','s.ville'=>'Town','s.fk_pays'=>'Country','s.tel'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
        // $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.cp'=>'company','s.ville'=>'company','s.fk_pays'=>'company','s.tel'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
        // $this->export_sql_start[$r]='SELECT DISTINCT ';
        // $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
        // $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
        // $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
        // $r++;
    }

    /**
     * 		Function called when module is enabled.
     * 		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     * 		It also creates data directories.
     *      @return     int             1 if OK, 0 if KO
     */
    function init($options = '')
    {
        $sql = array();

        $result = $this->load_tables();

        return $this->_init($sql);
    }

    /**
     * 		Function called when module is disabled.
     *      Remove from database constants, boxes and permissions from Dolibarr database.
     * 		Data directories are not deleted.
     *      @return     int             1 if OK, 0 if KO
     */
    function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql);
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
		return $this->_load_tables('/autoaddline/sql/');
	}
}

