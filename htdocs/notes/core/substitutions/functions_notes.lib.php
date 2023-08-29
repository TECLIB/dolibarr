<?php
/* Copyright (C) 2014 Laurent Destailleur         <eldy@users.sourceforge.net>
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

/**
 *	\file			htdocs/notes/core/substitutions/functions_notes.lib.php
 *	\brief			A set of functions for Dolibarr
 *					This file contains functions for plugin cabinetmed.
 */
dol_include_once("/notes/class/note.class.php");


/**
 * 		Function called to complete substitution array (before generating on ODT, or a personalized email)
 * 		functions xxx_completesubstitutionarray are called by make_substitutions() if file
 * 		is inside directory htdocs/core/substitutions
 *
 *		@param	array		&$substitutionarray	Array with substitution key=>val
 *		@param	Translate	$langs				Output langs
 *		@param	Object		$object				Object to use to get values
 * 		@return	void							The entry parameter $substitutionarray is modified
 */
function notes_completesubstitutionarray(&$substitutionarray,$langs,$object)
{
	global $conf,$db;

	//dol_include_once('/cabinetmed/class/cabinetmedcons.class.php');

    $isbio=0;
    $isother=0;

    $substitutionarray['NotesNbTeclib']=$langs->trans("Notes");

    if (!empty($object->element)) {
	    if ($object->element == 'societe')
	    {
	    	$note=new Note($db);
	    	$nbofnotes = $note->countNb($object->element, $object->id);
		    if ($nbofnotes > 0) $substitutionarray['NotesNbTeclib']=$langs->trans("Notes").' <span class="badge">'.$nbofnotes.'</span>';
	    }
	    if ($object->element == 'facture')
	    {
	    	$note=new Note($db);
	    	$nbofnotes = $note->countNb($object->element, $object->id);
		    if ($nbofnotes > 0) $substitutionarray['NotesNbTeclib']=$langs->trans("Notes").' <span class="badge">'.$nbofnotes.'</span>';
	    }
	    if ($object->element == 'propal')
	    {
	    	$note=new Note($db);
	    	$nbofnotes = $note->countNb($object->element, $object->id);
		    if ($nbofnotes > 0) $substitutionarray['NotesNbTeclib']=$langs->trans("Notes").' <span class="badge">'.$nbofnotes.'</span>';
	    }
	    if ($object->element == 'order')
	    {
	    	$note=new Note($db);
	    	$nbofnotes = $note->countNb($object->element, $object->id);
		    if ($nbofnotes > 0) $substitutionarray['NotesNbTeclib']=$langs->trans("Notes").' <span class="badge">'.$nbofnotes.'</span>';
	    }
	    if ($object->element == 'project')
	    {
	        $note=new Note($db);
	        $nbofnotes = $note->countNb($object->element, $object->id);
	            if ($nbofnotes > 0) $substitutionarray['NotesNbTeclib']=$langs->trans("Notes").' <span class="badge">'.$nbofnotes.'</span>';
	    }
	    if ($object->element == 'fichinter')
	    {
	        $note=new Note($db);
	        $nbofnotes = $note->countNb($object->element, $object->id);
	        if ($nbofnotes > 0) $substitutionarray['NotesNbTeclib']=$langs->trans("Notes").' <span class="badge">'.$nbofnotes.'</span>';
	    }
    }
}

