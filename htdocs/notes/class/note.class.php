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

dol_include_once("/notes/class/common_db_note.class.php");

/**
 * Class to manage enhanced notes
 */
class Note extends Common_DB_Note
{
	function getTable()
	{
		return MAIN_DB_PREFIX."notes";
	}

	/**
	 *
	 */
	static function showEdit($id, $note_id, $type = 'socid', $mode='')
	{
		global $langs;

		print '<a class="teclibnoteeditbutton" href="'.$_SERVER['PHP_SELF'].'?action=edit_note&token='.newToken().'&note_id='.$note_id.'&'.$type.'='.$id.'&mode='.$mode.'">
					<img src="'.dol_buildpath('/notes/img/note_edit.png',1).'"
					alt="'.$langs->trans("EditNote").'" title="'.$langs->trans("EditNote").'" />
		</a>';
	}

	/**
	 *
	 */
	static function showDelete($id, $note_id, $type = 'socid', $mode='')
	{
		global $langs;

		print '<a class="teclibnotedeletebutton" id="supprimernote" href="'.$_SERVER['PHP_SELF'].'?action=del_note&token='.newToken().'&note_id='.$note_id.'&'.$type.'='.$id.'&mode='.$mode.'">
					<img src="'.dol_buildpath('/notes/img/note_delete.png',1).'"
					alt="'.$langs->trans("DeleteNote").'" title="'.$langs->trans("DeleteNote").'" />
		</a>';
	}

	/**
	 * Return nb of notes
	 *
	 * @param	string	$item		Item (societe, commande, ...)
	 * @param	int		$id			Id of object
	 * @return 	int					Number of notes
	 */
	function countNb($item, $id)
	{
		global $db;

		if ($id > 0) {
			$query = "SELECT COUNT(*) as nb FROM ".$this->getTable()." WHERE item_type = '".$item."' AND item_id = ".$id;

			if ($resql = $db->query($query)) {
				$obj = $db->fetch_object($resql);
				if ($obj) return $obj->nb;
			}
		}

		return 0;
	}
}
