<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/forceproject/core/modules/project/mod_project_teclib.php
 *	\ingroup    project
 *	\brief      Fichier contenant la classe du modele de numerotation de reference de projet Universal
 */

require_once(DOL_DOCUMENT_ROOT ."/core/modules/project/modules_project.php");


/**
 * 	Classe du modele de numerotation de reference de projet Teclib
 */
class mod_project_teclib extends ModeleNumRefProjects
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $error = '';
	var $nom = 'Teclib';


    /**
     *  \brief      Renvoi la description du modele de numerotation
     *  \return     string      Texte descripif
     */
	function info()
    {
		return "Modèle de numérotation projet de Teclib {cccc}-{00} ou ccc=code client et 00 est un numéro propre à chaque client.";
    }

    /**
     *  \brief      Renvoi un exemple de numerotation
     *  \return     string      Example
     */
    function getExample()
    {
		return "CCCC-01";
    }

   /**
	*  Return next value
	*
	*  @param	Societe		$objsoc		Object third party
	*  @param   Project		$project	Object project
	*  @return  string					Value if OK, 0 if KO
	*/
    function getNextValue($objsoc=0,$project='')
    {
		global $db,$conf;

		if (is_object($objsoc) && $objsoc->id > 0)
		{
			$sql = " SELECT ref FROM llx_projet WHERE fk_soc = ".$objsoc->id." ORDER BY ref DESC";

			dol_syslog("mod_project_teclib::getNextValue sql=".$sql);

			$result = $db->query($sql);
			if ($result) {
				$num = $db->num_rows($result);

				if ($num > 0 && $objsoc->code_client)
				{
					$obj = $db->fetch_object($result);

					$nextRef = explode("-",$obj->ref);
					$nextRef = $nextRef[1]+1;
					$nextRef = (strlen($nextRef)==1)?"0".$nextRef:$nextRef;
					return $objsoc->code_client."-".$nextRef;
				} elseif(!empty($objsoc->code_client)) {
					return $objsoc->code_client."-01";
				} else {
					return "####-01";	// Happened if customer code not defined
				}
			}
			else dol_print_error($db);
      	}
      	else return "####-00";
	}


    /**
     *  Return next reference not yet used as a reference
     *
     *  @param	Societe		$objsoc     Object third party
     *  @param  Project		$project	Object project
     *  @return string      			Next not used reference
     */
    function project_get_num($objsoc=0,$project='')
    {
        return $this->getNextValue($objsoc,$project);
    }
}

?>