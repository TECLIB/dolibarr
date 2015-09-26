<?php

llxHeader();

if (is_object($site))
{
    $linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
    
    print_fiche_titre($langs->trans('ECommerceSiteSynchro').' '.$site->name, $linkback, 'eCommerceTitle@ecommerce');
    
    print $langs->trans("ECommerceLastCompleteSync").': ';
    if ($site->last_update) print dol_print_date($site->last_update, 'dayhoursec');
    else print $langs->trans("ECommerceNoUpdateSite");
    print '<br><br>'."\n";
?>
	<script type="text/javascript" src="<?php print dol_buildpath('/ecommerce/js/form.js',1); ?>"></script>
	<table class="noborder" width="100%">
		<tr class="liste_titre">
			<td width="20%"><?php print $langs->trans('ECommerceObjectToUpdate') ?></td>
			<td width="20%"><?php print $langs->trans('NbInDolibarr') ?></td>
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
			<td><?php print $nbCategoriesInDolibarr; ?></td>
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
			<td><?php print $langs->trans('ECommerceProducts') ?></td>
			<td><?php print $nbProductInDolibarr; ?></td>
			<td><?php print $nbProductToUpdate;	?>
			</td>
			<?php if ($synchRights==true):?>
			<td>
				<?php
				if ($nbCategoriesToUpdate>0) {
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
			<td><?php print $nbSocieteInDolibarr; ?> *</td>
			<td>
				<?php
					print $nbSocieteToUpdate;
				?>**
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
    			<td><?php print $nbCommandeToUpdate; ?></td>
    			<?php if ($synchRights==true):?>
    			<td>
                        <?php
    				if ($nbSocieteToUpdate>0) { ?>
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
			<td>
				<?php
					print $nbFactureToUpdate;
				?>
			</td>
			<?php if ($synchRights==true):?>
			<td>
				<?php if ($nbSocieteToUpdate>0) { ?>
				<form name="form_synchro_commande" id="form_synchro_facture" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input type="hidden" name="to_date" value="<?php print $toDate ?>">
					<input type="submit" name="submit_synchro_commande" id="submit_synchro_facture" class="button" disabled="disabled" value="<?php print $langs->trans('ECommerceSynchronizeCommande').' ('.$langs->trans("SyncSocieteFirst").')'; ?>">
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
	$categorytmp=new Categorie($db);
	$categorytmp->fetch($site->fk_cat_societe);
	$tagname=$categorytmp->label;
	print '* '.$langs->trans("OnlyThirdPartyWithTags", $tagname).'<br>';
	print '** '.$langs->trans("WithMagentoThirdIsModifiedIfAddressModified").'<br>';
	?>
	
	
	<form name="form_reset_data" id="form_reset_data" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
		<input type="hidden" name="id" value="<?php print $site->id ?>">
		<input type="hidden" name="to_date" value="<?php print $toDate ?>">
		<input type="hidden" name="reset_data" id="form_reset_data_value" value="1" >
		<input type="hidden" name="confirm" id="confirm" value="<?php print $langs->trans('ECommerceConfirmReset') ?>">
		<div class="tabsAction" ><input type="submit" name="submit_reset" class="butActionDelete" value="<?php print $langs->trans('ECommerceReset') ?>"></div>
	</form>
<?php
}
else
{
	print_fiche_titre($langs->trans("ECommerceSiteSynchro"),$linkback,'eCommerceTitle@ecommerce');
	$errors[] = $langs->trans('ECommerceSiteError');
}

setEventMessages($error, $errors, 'errors');
setEventMessages(null, $success);

llxFooter();
