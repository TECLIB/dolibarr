<?php
/*
 * @module		ECommerce
 * @version		1.2
 * @copyright	Auguria
 * @author		<franck.charpentier@auguria.net>
 * @licence		GNU General Public License
 */
class eCommerceRemoteAccess
{
	private $site;
	private $className;
	private $dirName;
	private $class;
	private $db;
	
    /**
     * Class for access remote sites
     * Creates an instance of the appropriate class according to type of site
     * 
     * @param   Database            $db         Databse handler
     * @param   eCommerceSite       $site       eCommerceSite
     */
    function eCommerceRemoteAccess($db, $site)
    {
    	$this->db = $db;
        $this->site = $site;
        $this->setName();
        dol_include_once('/ecommerce/class/data/'.$this->dirName.'/eCommerceRemoteAccess'.$this->className.'.class.php');
        $this->setClass();        
        return 1;
    }
    
	private function setName()
	{
		$types = $this->site->getSiteTypes();
		$name = $types[$this->site->type];
		$this->className = str_replace(' ','',ucwords($name));
		$this->dirName = str_replace(' ','',strtolower($name));
	}

    private function setClass()
    {
    	$className = get_class($this).$this->className;
    	$class = new $className($this->db, $this->site);
    	$this->class = $class;
    }
    
    /**
     * Call the connect method of the class instantiated in the constructor
     */
    public function connect()
    {
		$result = $this->class->connect();
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
    	return $result;
    }
    
    /**
     * Get societe to update from instantiated class in the constructor
     * 
     * @param $fromDate datetime updates from this date
     * @param $toDate datetime updates to this date
     * @return array of remote societe
     */
    public function getSocieteToUpdate($fromDate, $toDate)
	{
	    $result = $this->class->getSocieteToUpdate($fromDate, $toDate);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}

    /**
     * Get product to update from instantiated class in the constructor
     * @param $fromDate datetime updates from this date
     * @param $toDate datetime updates to this date
     * @return array of remote product
     */
    public function getProductToUpdate($fromDate, $toDate)
	{
	    $result=$this->class->getProductToUpdate($fromDate, $toDate);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
    
    /**
     * Get commande to update from instantiated class in the constructor
     * @param $fromDate datetime updates from this date
     * @param $toDate datetime updates to this date
     * @return array of remote commande
     */
    public function getCommandeToUpdate($fromDate, $toDate)
	{
		$result=$this->class->getCommandeToUpdate($fromDate, $toDate);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
		return $result;
	}
    
    /**
     * Get facture to update from instantiated class in the constructor
     * @param $fromDate datetime updates from this date
     * @param $toDate datetime updates to this date
     * @return array of remote facture
     */
    public function getFactureToUpdate($fromDate, $toDate)
	{
	    $result=$this->class->getFactureToUpdate($fromDate, $toDate);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
    /**
     * Put the remote data into societe dolibarr data from instantiated class in the constructor
     * @param $remoteObject array
     * @return $dolibarrObject array
     */
    public function convertRemoteObjectIntoDolibarrSociete($remoteObject)
	{
	    $result=$this->class->convertRemoteObjectIntoDolibarrSociete($remoteObject);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
    /**
     * Put the remote data into societe dolibarr data from instantiated class in the constructor
     * @param $remoteObject array
     * @return $dolibarrObject array
     */
    public function convertRemoteObjectIntoDolibarrProduct($remoteObject)
	{
	    $result=$this->class->convertRemoteObjectIntoDolibarrProduct($remoteObject);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
    /**
     * Put the remote data into commande dolibarr data from instantiated class in the constructor
     * @param $remoteObject array
     * @return $dolibarrObject array
     */
    public function convertRemoteObjectIntoDolibarrCommande($remoteObject)
	{
	    $result=$this->class->convertRemoteObjectIntoDolibarrCommande($remoteObject);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
    /**
     * Put the remote data into facture dolibarr data from instantiated class in the constructor
     * @param $remoteObject array
     * @return $dolibarrObject array
     */
    public function convertRemoteObjectIntoDolibarrFacture($remoteObject)
	{
	    $result=$this->class->convertRemoteObjectIntoDolibarrFacture($remoteObject);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
    /**
     * Get a commande from instantiated class in the constructor
     * @param $remoteCommandeId string
     * @return $dolibarrObject array
     */
	public function getCommande($remoteCommandeId)
	{
	    $result=$this->class->getCommande($remoteCommandeId);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
	/**	\brief	Get a remote category tree from magento
	 * 	\return	array	An array containing magento's categories as arrays
	 */
	public function getRemoteCategoryTree()
	{
	    $result=$this->class->getRemoteCategoryTree();
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
	/**	\brief	Get a remote category tree from magento
	 * 	\return	array	An array containing magento's categories as arrays
	 */
	public function getCategoryData($category_id)
	{
	    $result=$this->class->getCategoryData($category_id);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
	
	
    /**
     * Create a remote livraison from instantiated class in the constructor
     * @param $livraison object livraison
     * @parame $remote_order_id string id remote order
     * @return bool
     */
	public function createRemoteLivraison($livraison, $remote_order_id)
	{
	    $result=$this->class->createRemoteLivraison($livraison, $remote_order_id);
		$this->error=$this->class->error;
		$this->errors=$this->class->errors;
	    return $result;
	}
}
