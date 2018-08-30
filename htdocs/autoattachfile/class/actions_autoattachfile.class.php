<?php
/* Copyright (C) 2011-2016	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2012		Regis Houssin		<regis.houssin@capnetworks.com>
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
 */

/**
 *	\file       htdocs/autoattachfile/class/actions_autoattachfile.class.php
 *	\ingroup    autoattachfile
 *	\brief      File to control actions
 */
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");


/**
 *	Class to manage hooks for module autoattachfile
 */
class ActionsAutoattachfile
{
    var $db;
    var $error;
    var $errors=array();

    /**
     *	Constructor
     *
     *  @param		DoliDB		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
    }


    /**
     * getFormMail
     */
    function getFormMail($parameters, &$object, &$action, $hookmanager)
    {
    	global $conf,$langs;
    	$langs->load('sendproductdoc@sendproductdoc');

    	$keytoavoidconflict = '';
    	include_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
    	if (versioncompare(versiondolibarrarray(),array(4,0,-3)) >= 0)
    	{
    	   $keytoavoidconflict = empty($parameters['trackid'])?'':'-'.$parameters['trackid'];
    	}

    	$nbFiles=0;

    	if ((GETPOST('action','aZ09') == 'presend' && GETPOST('mode') == 'init') || (GETPOST('modelmailselected','int') && ! GETPOST('removedfile','alpha')))
    	{
    		// Get current content of list of files
			$listofpaths = (! empty($_SESSION["listofpaths".$keytoavoidconflict])) ? explode(';',$_SESSION["listofpaths".$keytoavoidconflict]) : array();
			$listofnames = (! empty($_SESSION["listofnames".$keytoavoidconflict])) ? explode(';',$_SESSION["listofnames".$keytoavoidconflict]) : array();
			$listofmimes = (! empty($_SESSION["listofmimes".$keytoavoidconflict])) ? explode(';',$_SESSION["listofmimes".$keytoavoidconflict]) : array();

			if ($object->param['models'] == 'propal_send')
    		{
    			$nbFiles += $this->_addFiles($object, $listofpaths, $listofnames, $listofmimes, $conf->autoattachfile->dir_output.'/proposals');
    		}

    	    if ($object->param['models'] == 'order_send')
    		{
    			$nbFiles += $this->_addFiles($object, $listofpaths, $listofnames, $listofmimes, $conf->autoattachfile->dir_output.'/orders');
    		}

    	    if ($object->param['models'] == 'facture_send')
    		{
    			$nbFiles += $this->_addFiles($object, $listofpaths, $listofnames, $listofmimes, $conf->autoattachfile->dir_output.'/invoices');
    		}

    		// Now we saved back content of files to have into attachment
    		$_SESSION["listofpaths".$keytoavoidconflict]=join(';',$listofpaths);
    		$_SESSION["listofnames".$keytoavoidconflict]=join(';',$listofnames);
    		$_SESSION["listofmimes".$keytoavoidconflict]=join(';',$listofmimes);
    	}

    	return 0;
    }

	/**
	 * Add files from the list as e-mail attachments
	 */
	private function _addFiles($form, &$listofpaths, &$listofnames, &$listofmimes, $path)
	{
		global $conf,$langs,$user;

		include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		$fileList = dol_dir_list($path,'files',0);
		$nbFiles = 0;

		$vardir=$conf->user->dir_output."/".$user->id;
		$upload_dir_tmp = $vardir.'/temp';

		$result = dol_mkdir($upload_dir_tmp);

		foreach($fileList as $fileParams) {
			// Attachment in the e-mail
			$file = $fileParams['fullname'];
			$newfile = $upload_dir_tmp.'/'.basename($file);

			$result=dol_copy($file, $newfile, 0, 1);
			dol_syslog("result=".$result);
			if (! $result) dol_syslog(get_class($this).'::_addFiles failed to move file from '.$file.' to '.$newfile, LOG_ERR);

			if (! in_array($newfile, $listofpaths))
			{
				$listofpaths[] = $newfile;
				$listofnames[] = basename($newfile);
				$listofmimes[] = dol_mimetype($newfile);
				$nbFiles++;
			}
		}

		return $nbFiles;
	}

}

