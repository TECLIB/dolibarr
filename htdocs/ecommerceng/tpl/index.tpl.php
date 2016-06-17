<?php
llxHeader();

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("ECommerceDashboard"), $linkback, 'eCommerceTitle@ecommerceng');

print '<br>';

if (count($sites)): ?>
	<table class="noborder" width="100%">
		<tr class="liste_titre">
			<td><?php print $langs->trans('ECommerceSite') ?></td>
			<td><?php print $langs->trans('ECommerceLastUpdate') ?></td>
			<td colspan="2">&nbsp;</td>
		</tr>
<?php
$var=!$var;
	foreach ($sites as $site)
	{ ?>		
		<tr <?php print $bc[$var] ?>>
			<td><?php print $site->name ?></td>
			<td>
				<?php
					if ($site->last_update)
					{
					    //print $site['last_update'];
						print dol_print_date($site->last_update, 'dayhour');
					}
					else
						print $langs->trans('ECommerceNoUpdateSite');
				?>
			</td>
			<td><div style="inline-block">
				<form class="inline-block" style="pdding-right: 10px" name="form_index" id="form_detailed" action="<?php print dol_buildpath('/ecommerceng/site.php',1); ?>?id=<?php echo $site->id ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input class="button" type="submit" name="submit_detailed" value="<?php print $langs->trans('ECommerceUpdateSite') ?>">					
				</form>
				<form class="inline-block" name="form_index" id="form_global" action="<?php print dol_buildpath('/ecommerceng/site.php', 1); ?>?id=<?php echo $site->id; ?>" method="post">
					<input type="hidden" name="id" value="<?php print $site->id ?>">
					<input class="button" type="submit" name="submit_synchro_all" value="<?php print $langs->trans('ECommerceUpdateAll') ?>">
				</form>
				</div>
			</td>			
		</tr>
	<?php } ?>
	</table>
<?php else: ?>
<p><?php $langs->trans('ECommerceNoSite') ?></p>
<?php endif;
llxFooter();