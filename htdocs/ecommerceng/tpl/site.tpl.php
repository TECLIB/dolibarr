<?php

llxHeader();

if (is_object($site))
{
    $linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
    
    print_fiche_titre($langs->trans('ECommerceSiteSynchro').' '.$site->name, $linkback, 'eCommerceTitle@ecommerceng');
    
    print '<br>';

    dol_fiche_head('', '', 'Dolibarr <- Ecommerce');
    
    print '<table class="centpercent nobordernopadding"><tr><td>';
    
    print $langs->trans("ECommerceLastCompleteSync", $site->name).': ';
    if ($site->last_update) print '<strong>'.dol_print_date($site->last_update, 'dayhoursec').'</strong>';
    else print $langs->trans("ECommerceNoUpdateSite");
    print '<br>';
    
    $soapwsdlcacheon = ini_get('soap.wsdl_cache_enabled');
    $soapwsdlcachedir = ini_get('soap.wsdl_cache_dir');
    if ($soapwsdlcacheon)
    {
        print $langs->trans("WarningSoapCacheIsOn", $soapwsdlcachedir).'<br>';
    }
    else
    {
        print $langs->trans("SoapCacheIsOff", $soapwsdlcachedir).'<br>';
    }
    
    print '</td><td align="right">';
    
    $button.='<a href="'.$_SERVER["PHP_SELF"].'?id='.$site->id.'" class="butAction">'.$langs->trans('RefreshCount').'</a>';
    print $button;
    
    print '</td></tr></table>';
    
    
    print '<br>'."\n";
?>
	<table class="noborder" width="100%">
		<tr class="liste_titre">
			<td><?php print $langs->trans('ECommerceObjectToUpdate') ?></td>
			<td><?php print $langs->trans('NbInDolibarr') ?></td>
			<td><?php print $langs->trans('NbInDolibarrLinkedToE') ?></td>
			<td><?php print $langs->trans('ECommerceCountToUpdate') ?></td>
			<?php if ($synchRights==true):?>
			<td>&nbsp;</td>
			<?php endif; ?>
		</tr>

<?php
$var=!$var;
?>		
		<tr <?php print $bc[$var] ?>>
			<td><?php print $langs->trans('ECommerceCategoriesProducts') ?></td>
			<td><?php print $nbCategoriesInDolibarr; ?> *</td>
			<td><?php print $nbCategoriesInDolibarrLinkedToE; ?> *</td>
			<td>
				<?php
					print $nbCategoriesToUpdate;
				?>
			</td>
			<?php if ($synchRights==true):?>
			<td>
				<?php if ($nbCategoriesToUpdate>0) { ?>
				<form name="form_synchro_categories" id="form_synchro_category" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
					<input type="submit" name="submit_synchro_category" id="submit_synchro_category" class="button" value="<?php print $langs->trans('ECommerceSynchronizeCategoryProduct') ?>">
				</form>
				<?php } ?>
			</td>
			<?php endif; ?>
		</tr>
<?php
$var=!$var;
?>		
		<tr <?php print $bc[$var] ?>>
			<td><?php print $langs->trans('ProductsOrServices') ?></td>
			<td><?php print $nbProductInDolibarr; ?> **</td>
			<td><?php print $nbProductInDolibarrLinkedToE; ?> **</td>
			<td><?php print $nbProductToUpdate;	?>
			</td>
			<?php if ($synchRights==true):?>
			<td>
				<?php
				if ($nbProductToUpdate>0 && $nbCategoriesToUpdate>0) {
				    ?>
				<form name="form_synchro_product" id="form_synchro_product" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
					<input type="submit" name="submit_synchro_product" id="submit_synchro_product" class="button" disabled="disabled" value="<?php print $langs->trans('ECommerceSynchronizeProduct').' ('.$langs->trans("SyncCategFirst").")"; ?>">
				</form>
				<?php 
				}
				elseif ($nbProductToUpdate>0) { ?>
				<form name="form_synchro_product" id="form_synchro_product" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
					<input type="submit" name="submit_synchro_product" id="submit_synchro_product" class="button" value="<?php print $langs->trans('ECommerceSynchronizeProduct') ?>">
				</form>
				<?php } ?>
			</td>
			<?php endif; ?>
		</tr>
<?php
$var=!$var;
?>		
		<tr <?php print $bc[$var] ?>>
			<td><?php print $langs->trans('ECommerceSociete') ?></td>
			<td><?php print $nbSocieteInDolibarr; ?> ***</td>
			<td><?php print $nbSocieteInDolibarrLinkedToE; ?> ***</td>
			<td>
				<?php
					print $nbSocieteToUpdate;
				?> ****
			</td>
			<?php if ($synchRights==true):?>
			<td>
				<?php if ($nbSocieteToUpdate>0): ?>
				<form name="form_synchro_societe" id="form_synchro_societe" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
					<input type="submit" name="submit_synchro_societe" id="submit_synchro_societe" class="button" value="<?php print $langs->trans('ECommerceSynchronizeSociete') ?>">
				</form>
				<?php endif; ?>
			</td>
			<?php endif; ?>
		</tr>
<?php
$var=!$var;
?>		
		<tr <?php print $bc[$var] ?>>
			<td><?php print $langs->trans('ECommerceCommande') ?></td>
			<?php 
            if (empty($conf->commande->enabled))
            {
                ?>
				<td colspan="3"><?php print $langs->trans("ModuleCustomerOrderDisabled"); ?></td>
                <?php 
            }
            else
            {
                ?>
    			<td><?php print $nbCommandeInDolibarr; ?></td>
    			<td><?php print $nbCommandeInDolibarrLinkedToE; ?></td>
    			<td><?php print $nbCommandeToUpdate; ?></td>
    			<?php if ($synchRights==true):?>
    			<td>
                        <?php
    				if ($nbCommandeToUpdate>0 && $nbSocieteToUpdate>0) { ?>
    				<form name="form_synchro_commande" id="form_synchro_commande" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    					<input type="hidden" name="id" value="<?php print $site->id ?>">
    					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
    					<input type="submit" name="submit_synchro_commande" id="submit_synchro_commande" class="button" disabled="disabled" value="<?php print $langs->trans('ECommerceSynchronizeCommande').' ('.$langs->trans("SyncSocieteFirst").')'; ?>">
    				</form>
    				<?php } elseif ($nbCommandeToUpdate>0) { ?>
    				<form name="form_synchro_commande" id="form_synchro_commande" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    					<input type="hidden" name="id" value="<?php print $site->id ?>">
    					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
    					<input type="submit" name="submit_synchro_commande" id="submit_synchro_commande" class="button" value="<?php print $langs->trans('ECommerceSynchronizeCommande') ?>">
    				</form>
    				<?php } ?>
    			</td>
    			<?php endif; 
            }
            ?>
		</tr>
<?php
$var=!$var;
?>		
		<tr <?php print $bc[$var] ?>>
			<td><?php print $langs->trans('ECommerceFacture') ?></td>
			<td><?php print $nbFactureInDolibarr; ?></td>
			<td><?php print $nbFactureInDolibarrLinkedToE; ?></td>
			<td>
				<?php
					print $nbFactureToUpdate;
				?>
			</td>
			<?php if ($synchRights==true):?>
			<td>
				<?php if ($nbFactureToUpdate>0 && $nbSocieteToUpdate>0) { ?>
				<form name="form_synchro_commande" id="form_synchro_facture" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
					<input type="submit" name="submit_synchro_commande" id="submit_synchro_facture" class="button" disabled="disabled" value="<?php print $langs->trans('ECommerceSynchronizeFacture').' ('.$langs->trans("SyncSocieteFirst").')'; ?>">
				</form>
				<?php } elseif ($nbFactureToUpdate>0) { ?>
				<form name="form_synchro_facture" id="form_synchro_facture" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
					<input type="submit" name="submit_synchro_facture" id="submit_synchro_facture" class="button" value="<?php print $langs->trans('ECommerceSynchronizeFacture') ?>">
				</form>
				<?php } ?>
			</td>
			<?php endif; ?>
		</tr>
	</table>
	<?php
	$categorytmpprod=new Categorie($db);
	$categorytmpprod->fetch($site->fk_cat_product);
	$tagnameprod=$categorytmpprod->label;
	$categorytmp=new Categorie($db);
	$categorytmp->fetch($site->fk_cat_societe);
	$tagname=$categorytmp->label;
	print '* '.$langs->trans("OnlyProductCategIn", $tagnameprod).'<br>';
	print '** '.$langs->trans("OnlyProductsIn", $tagnameprod, $tagnameprod).'<br>';
	//print '*** '.$langs->trans("OnlyThirdPartyWithTags", $tagname).'<br>';
	print '*** '.$langs->trans("OnlyThirdPartyIn", $tagname).'<br>';
	print '**** '.$langs->trans("WithMagentoThirdIsModifiedIfAddressModified").'<br>';

	dol_fiche_end();
	
	print '<br>';
	
    dol_fiche_head('', '', 'Dolibarr -> Ecommerce');
	
	print $langs->trans("SyncIsAutomaticInRealTime", $site->name)."\n";
	
	dol_fiche_end();
	
	
	if ($user->admin)
	{
		print '<br><br>';
   		dol_fiche_head('', '', $langs->trans('DangerZone'));
		print '<a style="color: #600" id="submit_reset" href="'.$_SERVER['PHP_SELF'].'?id='.$site->id.'&to_date='.$toDate.'&reset_data=all">'.$langs->trans('ECommerceReset').'</a>';
	    print '<br><br>';
	    print '<a style="color: #600" id="submit_reset" href="'.$_SERVER['PHP_SELF'].'?id='.$site->id.'&to_date='.$toDate.'&reset_data=links">'.$langs->trans('ECommerceResetLink').'</a>';
		dol_fiche_end();
	}
}
else
{
	print_fiche_titre($langs->trans("ECommerceSiteSynchro"),$linkback,'eCommerceTitle@ecommerceng');
	$errors[] = $langs->trans('ECommerceSiteError');
}

setEventMessages(null, $errors, 'errors');
setEventMessages(null, $success);

llxFooter();
