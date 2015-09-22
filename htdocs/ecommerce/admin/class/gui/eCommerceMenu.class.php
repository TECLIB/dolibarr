<?php
/*
 * @module		ECommerce
 * @version		1.0
 * @copyright	Auguria
 * @author		<franck.charpentier@auguria.net>
 * @licence		GNU General Public License
 */

/**
 * Class for update menus
 */

dol_include_once('/ecommerce/core/modules/modECommerce.class.php');
dol_include_once('/ecommerce/class/data/eCommerceSite.class.php');

class eCommerceMenu
{
	private $module;
	private $db;
	private $siteDb;
	
    function eCommerceMenu($db, $siteDb=null, $modECommerce=null)
    {
    	$this->db = $db;
    	
    	if ($siteDb==null)
    		$this->siteDb = new eCommerceSite($this->db);
    	else
    		$this->siteDb = $siteDb;
    	
    	if ($modECommerce!=null)
        	$this->module = $modECommerce;
        
        return 1;
    }
    /**
     * @return array menu
     */
    function getMenu()
    {
    	$menu = array();
    	
    	//define top menu
    	$menu[0]=array(	'fk_menu'=>0,
    					'type'=>'top',
    					'titre'=>'ECommerceMenu',
    					'mainmenu'=>'ecommerce',
    					'leftmenu'=>'1',
    					'url'=>'/ecommerce/index.php',
    					'langs'=>'ecommerce@ecommerce',
    					'position'=>100,
    					'enabled'=>'$user->rights->ecommerce->read',
    					'perms'=>'1',
    					'target'=>'',
    					'user'=>2);
    	//define left menu
    	$menu[1]=array(	'fk_menu'=>'r=0',
    					'type'=>'left',
    					'titre'=>'ECommerceMenu',
    					'mainmenu'=>'ecommerce',
    					'url'=>'/ecommerce/index.php',
    					'langs'=>'ecommerce@ecommerce',
    					'position'=>100,
    					'enabled'=>'$user->rights->ecommerce->read',
    					'perms'=>'1',
    					'target'=>'',
    					'user'=>2);
    	
    	//add link to configuration
    	$menu[2]=array(	'fk_menu'=>'r=1',
    					'type'=>'left',
    					'titre'=>'ECommerceSetupSites',
    					'mainmenu'=>'ecommerce',
    					'url'=>'/ecommerce/admin/eCommerceSetup.php',
    					'langs'=>'ecommerce@ecommerce',
    					'position'=>100,
    					'enabled'=>'$user->rights->ecommerce->site',
    					'perms'=>'1',
    					'target'=>'',
    					'user'=>2);
    	
    	//add submenu foreach site
    	$sites = $this->siteDb->listSites();
    	if (count($this->siteDb))
    		foreach ($sites as $site)
    			$menu[]=array(	'fk_menu'=>'r=1',
    							'type'=>'left',
    							'titre'=>$site['name'],
    							'mainmenu'=>'ecommerce',
    							'url'=>'/ecommerce/site.php?id='.$site['id'],
    							'langs'=>'ecommerce@ecommerce',
    							'position'=>100,
    							'enabled'=>'$user->rights->ecommerce->read',
    							'perms'=>'1',
    							'target'=>'',
    							'user'=>2);
    	return $menu;
    }
    
    /**
     * Update menu into database
     */
    function updateMenu()
    {
    	if ($this->module == null)
        	$this->module = new modECommerce($this->db);
        	
    	$this->module->menu = $this->getMenu();
    	$this->module->db->begin();
		$this->module->delete_menus();
		$this->module->insert_menus();
		$this->module->db->commit();
		return 1;
    }
}

