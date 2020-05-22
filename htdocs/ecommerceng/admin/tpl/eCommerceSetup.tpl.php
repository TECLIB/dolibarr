<?php
/* Copyright (C) 2010 Franck Charpentier - Auguria <franck.charpentier@auguria.net>
 * Copyright (C) 2013 Laurent Destailleur          <eldy@users.sourceforge.net>
 * Copyright (C) 2017 Open-DSI                     <support@open-dsi.fr>
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

// Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

include_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';

$formproduct = new FormProduct($db);

llxHeader();

print_fiche_titre($langs->trans("ECommerceSetup"),$linkback,'setup');

?>
	<script type="text/javascript" src="<?php print dol_buildpath('/ecommerceng/js/form.js',1); ?>"></script>
	<br>
	<form id="site_form_select" name="site_form_select" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
		<?php echo $langs->trans("SelectYourSite").' : '; ?>
		<select class="flat" id="site_form_select_site" name="site_form_select_site" onchange="eCommerceSubmitForm('site_form_select')">
			<option value="0"><?php print $langs->trans('ECommerceAddNewSite') ?></option>
<?php
if (count($sites))
	foreach ($sites as $option)
	{
		print '
			<option';
		if ($ecommerceId == $option['id'])
			print ' selected="selected"';
		print ' value="'.$option['id'].'">'.$option['name'].'</option>';
	}
?>
		</select>
	</form>
	<br>

	<?php
	print '<div class="titre">'.$langs->trans("MainSyncSetup").'</div>';
	?>

	<form name="site_form_detail" id="site_form_detail" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
			<input type="hidden" name="token" value="<?php print $_SESSION['newtoken'] ?>">
			<input id="site_form_detail_action" type="hidden" name="site_form_detail_action" value="save">
			<input type="hidden" name="ecommerce_id" value="<?php print $ecommerceId ?>">
			<input type="hidden" name="ecommerce_last_update" value="<?php print $ecommerceLastUpdate ?>">

			<table class="noborder" width="100%">
				<tr class="liste_titre">
					<td width="20%"><?php print $langs->trans('Parameter') ?></td>
					<td><?php print $langs->trans('Value') ?></td>
					<td><?php print $langs->trans('Description') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span class="fieldrequired"><?php print $langs->trans('ECommerceSiteType') ?></span></td>
					<td>
						<select class="flat" name="ecommerce_type">
							<option value="0">&nbsp;</option>
							<?php
								if (count($siteTypesLabel))
								    foreach ($siteTypesLabel as $key=>$value)
									{
										print '
											<option';
										if ($ecommerceType == $key)
											print ' selected="selected"';
										print ' value="'.$key.'">'.$langs->trans($value).'</option>';
									}
?>
						</select>
							<?php
							// Check if SOAP is on for platform that need SOAP
							if ($ecommerceType && in_array($ecommerceType, array(eCommerceSite::TYPE_MAGENTO, eCommerceSite::TYPE_WOOCOMMERCE)) && ! extension_loaded('soap'))
                            {
                                print info_admin($langs->trans("ErrorModuleSoapRequired"), 0, 0, 'error');
                            }
    						?>
					</td>
					<td><?php print $langs->trans('ECommerceSiteTypeDescription') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span class="fieldrequired"><?php print $langs->trans('ECommerceSiteName') ?></span></td>
					<td>
						<input type="text" class="flat" name="ecommerce_name" value="<?php print $ecommerceName ?>" size="30">
					</td>
					<td><?php print $langs->trans('ECommerceSiteNameDescription') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span class="fieldrequired"><?php print $langs->trans('ECommerceSiteAddress') ?></span></td>
					<td>
						<input type="text" class="flat" name="ecommerce_webservice_address" value="<?php print $ecommerceWebserviceAddress ?>" size="60">
						<?php
						if ($ecommerceWebserviceAddress)
						    print '<br><a href="'.$ecommerceWebserviceAddress.'" target="_blank">'.$langs->trans("ECommerceClickUrlToTestUrl").'</a>';
						?>
					</td>
					<td><?php print $langs->trans('ECommerceSiteAddressDescription') ?></td>
				</tr>

				<tr class="oddeven">
					<td class="fieldrequired"><?php print $langs->trans('ECommerceUserName') ?></td>
					<td>
						<input type="text" class="flat" name="ecommerce_user_name" value="<?php print $ecommerceUserName ?>" size="20">
					</td>
					<td><?php print $langs->trans('ECommerceUserNameDescription') ?></td>
				</tr>

                <tr class="oddeven">
                  <td class="fieldrequired"><?php print $langs->trans('ECommerceUserPassword') ?></td>
                  <td>
                    <input type="password" class="flat" name="ecommerce_user_password" value="<?php print $ecommerceUserPassword ?>" size="20">
                  </td>
                  <td><?php print $langs->trans('ECommerceUserPasswordDescription') ?></td>
                </tr>

				<tr class="oddeven">
					<td><span class="fieldrequired"><?php print $langs->trans('ECommerceCatProduct') ?></span></td>
					<td>
						<select class="flat" name="ecommerce_fk_cat_product">
							<option value="0">&nbsp;</option>
							<?php
								if (count($productCategories))
									foreach ($productCategories as $productCategorie)
									{
										print '
											<option';
										if ($ecommerceFkCatProduct == $productCategorie['id'])
											print ' selected="selected"';
										print ' value="'.$productCategorie['id'].'">'.$productCategorie['label'].'</option>';
									}
								?>
						</select>
					</td>
					<td><?php print $langs->trans('ECommerceCatProductDescription') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span class="fieldrequired"><?php print $langs->trans('ECommerceCatSociete') ?></span></td>
					<td>
						<select class="flat" name="ecommerce_fk_cat_societe">
							<option value="0">&nbsp;</option>
							<?php
								if (count($productCategories))
									foreach ($societeCategories as $societeCategorie)
									{
										print '
											<option';
										if ($ecommerceFkCatSociete == $societeCategorie['id'])
											print ' selected="selected"';
										print ' value="'.$societeCategorie['id'].'">'.$societeCategorie['label'].'</option>';
									}
								?>
						</select>
					</td>
					<td><?php print $langs->trans('ECommerceCatSocieteDescription') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span><?php print $langs->trans('ThirdPartyForNonLoggedUsers') ?></span></td>
					<td>
						<?php
                            print $form->select_company($conf->global->ECOMMERCENG_USE_THIS_THIRDPARTY_FOR_NONLOGGED_CUSTOMER, 'ECOMMERCENG_USE_THIS_THIRDPARTY_FOR_NONLOGGED_CUSTOMER', '', 1);
						?>
					</td>
					<td><?php print $langs->trans('SynchUnkownCustomersOnThirdParty') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span><?php print $langs->trans('BankAccountForPayments') ?></span></td>
					<td>
						<?php
						$form->select_comptes($conf->global->ECOMMERCENG_BANK_ID_FOR_PAYMENT,'ECOMMERCENG_BANK_ID_FOR_PAYMENT',0,'',2);
						?>
					</td>
					<td><?php print $langs->trans('SynchPaymentsOnWichBankAccount') ?></td>
				</tr>

				<!-- Filter are not used at this time
				<tr class="oddeven">
					<td><?php print $langs->trans('ECommerceFilterLabel') ?></td>
					<td>
						<input type="text" class="flat" name="ecommerce_filter_label" value="<?php print $ecommerceFilterLabel ?>" size="30">
					</td>
					<td><?php print $langs->trans('ECommerceFilterLabelDescription') ?></td>
				</tr>

				<tr class="oddeven">
					<td><?php print $langs->trans('ECommerceFilterValue') ?></td>
					<td>
						<input type="text" class="flat" name="ecommerce_filter_value" value="<?php print $ecommerceFilterValue ?>" size="30">
					</td>
					<td><?php print $langs->trans('ECommerceFilterValueDescription') ?></td>
				</tr>
				-->
<?php
if (!empty($conf->global->PRODUIT_MULTIPRICES)) {
?>
  <tr class="oddeven">
    <td class="fieldrequired"><?php print $langs->trans('ECommercePriceLevel') ?></td>
    <td>
      <select class="flat" name="ecommerce_price_level">
        <?php
        foreach ($priceLevels as $idx => $priceLevel) {
          print '<option value="' . $idx . '"' . ($ecommercePriceLevel == $idx ? ' selected="selected"' : '') . '">' . $idx . '</option>';
        }
        ?>
      </select>
    </td>
    <td><?php print $langs->trans('ECommercePriceLevelDescription') ?></td>
  </tr>
  <script type="text/javascript">
    jQuery(document).ready(function (){
      eCommerceConfirmUpdatePriceLevel("site_form_detail", "<?php print $langs->transnoentities('ECommerceConfirmUpdatePriceLevel') ?>", <?php print $siteDb->price_level ?>);
    });
  </script>
<?php
}
/*
?>
				<tr class="oddeven">
					<td><span><?php print $langs->trans('ECommerceTimeout') ?></span></td>
					<td>
						<input type="text" class="flat" name="ecommerce_timeout" value="<?php print $ecommerceTimeout ?>" size="10">
					</td>
					<td><?php print $langs->trans('ECommerceTimeoutDescription') ?></td>
				</tr>
<?php
*/
/* TODO A activer et tester "special prices"
?>
				<tr class="oddeven">
					<td><?php print $langs->trans('ECommerceMagentoUseSpecialPrice') ?></td>
					<td>
						<input type="checkbox" class="flat" name="ecommerce_magento_use_special_price" <?php print ($ecommerceMagentoUseSpecialPrice ? 'checked' : '') ?> />
					</td>
					<td><?php print $langs->trans('ECommerceMagentoUseSpecialPriceDescription') ?></td>
				</tr>
<?php
*/
?>
				<tr class="oddeven">
					<td><?php print $langs->trans('ECommerceMagentoPriceType') ?></td>
					<td>
						<select class="flat" name="ecommerce_magento_price_type">
							<option value="HT" <?php print ($ecommerceMagentoPriceType == 'HT' ? 'selected="selected"' : '') ?>><?php print $langs->trans('ECommerceMagentoPriceTypeHT') ?></option>
							<option value="TTC"<?php print ($ecommerceMagentoPriceType == 'TTC' ? 'selected="selected"' : '') ?>><?php print $langs->trans('ECommerceMagentoPriceTypeTTC') ?></option>
						</select>
					</td>
					<td><?php print $langs->trans('ECommerceMagentoPriceTypeDescription') ?></td>
				</tr>

			</table>


			<br>

<?php
if ($conf->stock->enabled)
{
    print '<div class="titre">'.$langs->trans("StockSyncSetup").'</div>';
?>
			<table class="noborder" width="100%">

				<tr class="liste_titre">
					<td width="20%"><?php print $langs->trans('Parameter') ?></td>
					<td><?php print $langs->trans('Value') ?></td>
					<td><?php print $langs->trans('Description') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span><?php print $langs->trans('ECommerceStockSyncDirection') ?></span></td>
					<td>
						<?php
                            $array=array('none'=>$langs->trans('None'), 'ecommerce2dolibarr'=>'eCommerce to Dolibarr', 'dolibarr2ecommerce'=>'Dolibarr to eCommerce');
							print $form->selectarray('ecommerce_stock_sync_direction', $array, $ecommerceStockSyncDirection);
						?>
					</td>
					<td><?php print $langs->trans('ECommerceStockSyncDirectionDescription') ?></td>
				</tr>

				<tr class="oddeven">
					<td><span><?php print $langs->trans('ECommerceStockProduct') ?></span></td>
					<td>
							<?php
								print $formproduct->selectWarehouses($ecommerceFkWarehouse, 'ecommerce_fk_warehouse', 0, 1);
							?>
					</td>
					<td><?php
					print $langs->trans('ECommerceStockProductDescription', $langs->transnoentitiesnoconv('ECommerceStockSyncDirection'));
					print '<br>';
					print $langs->trans('ECommerceStockProductDescription2', $langs->transnoentitiesnoconv('ECommerceStockSyncDirection'));
					?></td>
				</tr>
<?php
}
?>
			</table>




			<br>
			<center>
<?php
if ($siteDb->id)
{
?>
				<input type="submit" name="save_site" class="button" value="<?php print $langs->trans('Save') ?>">
				<a class="button" href='javascript:eCommerceConfirmDelete("site_form_detail", "<?php print $langs->trans('ECommerceConfirmDelete') ?>")'><?php print $langs->trans('Delete') ?></a>
<?php
}
else
{
?>
				<input type="submit" name="save_site" class="button" value="<?php print $langs->trans('Add') ?>">
<?php
}
?>
			</center>
		</form>

<?php
if ($success != array())
	foreach ($success as $succes)
		print '<p class="ok">'.$succes.'</p>';
?>
		<br>
