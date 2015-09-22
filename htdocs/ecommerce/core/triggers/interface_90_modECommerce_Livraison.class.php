<?php
/*
 * @module		ECommerce
 * @version		1.2
 * @copyright	Auguria
 * @author		<franck.charpentier@auguria.net>
 * @licence		GNU General Public License
 */
if (!defined('DOL_CLASS_PATH'))
	define('DOL_CLASS_PATH', null);

dol_include_once('/ecommerce/class/business/eCommerceSynchro.class.php');

require_once(DOL_DOCUMENT_ROOT.'/expedition/'.DOL_CLASS_PATH.'expedition.class.php');

class InterfaceLivraison
{
    private $db;
    private $name;
    private $description;
    private $version;
    
    public $family;
    public $errors;
    
    /**
     *   This class is a trigger on delivery to update delivery on eCommerce Site
     *   @param      DB      Handler database access
     */
    function InterfaceLivraison($DB)
    {
        $this->db = $DB ;
    
        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "eCommerce";
        $this->description = "Triggers of this module update delivery on eCommerce Site according to order status.";
        $this->version = '1.0';
    }
    
    
    /**
     *   Renvoi nom du lot de triggers
     *   @return     string      Nom du lot de triggers
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     *   Renvoi descriptif du lot de triggers
     *   @return     string      Descriptif du lot de triggers
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Renvoi version du lot de triggers
     *   @return     string      Version du lot de triggers
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }
    
    /**
     *      Fonction appelee lors du declenchement d'un evenement Dolibarr.
     *      D'autres fonctions run_trigger peuvent etre presentes dans includes/triggers
     *      @param      action      Code de l'evenement
     *      @param      object      Objet concerne
     *      @param      user        Objet user
     *      @param      lang        Objet lang
     *      @param      conf        Objet conf
     *      @return     int         <0 if fatal error, 0 si nothing done, >0 if ok
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
        if ($action == 'SHIPPING_VALIDATE')
        {
        	try
        	{
	            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
	            
        		//retrieve shipping id
        		$object->load_object_linked('','shipping',$object->id,'delivery');        		
        		$shippingId = $object->linked_object['shipping'][0];        		
        	
        		//retrieve order id
        		$shipping = new Expedition($this->db);     
        		//for before 2.9 compatibility  		
        		if (intval($shippingId)==0)
        			$shippingId = $shipping->fetch($object->id);        		
        		$shipping->fetch($shippingId);
        		
        		$shipping->load_object_linked('','commande',$shipping->id,'shipping');
        		$orderId = $shipping->linked_object['commande'][0];    
        		//for before 2.9 compatibility  		
        		if (intval($orderId)==0 && count($shipping->linked_object))
        			foreach ($shipping->linked_object as $linkArray)
        			{
        				if ($linkArray['type']=='commande')
        					$orderId = $linkArray['linkid'];
        			}
        		//load eCommerce Commande by order id
	            $eCommerceCommande = new eCommerceCommande($this->db);
	            $eCommerceCommande->fetchByCommandeId($orderId);
	            
	            if (isset($eCommerceCommande->id) &&  $eCommerceCommande->id > 0)
	            {
		            //set eCommerce site
		            $eCommerceSite = new eCommerceSite($this->db);
		            $eCommerceSite->fetch($eCommerceCommande->fk_site);
		            
		            $synchro = new eCommerceSynchro($this->db, $eCommerceSite);
		            $synchro->synchLivraison($object, $eCommerceCommande->remote_id);
					return 1;
	            }
        	}
        	catch (Exception $e)
        	{
            	$this->errors = 'Trigger exception : '.$e;
	            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id." ".$this->errors);
            	return -1;
        	}
        }
		return 0;
    }

}
?>