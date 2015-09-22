function eCommerceSubmitForm(id_form)
{
	document.getElementById(id_form).submit();
}

function eCommerceConfirmDelete(id_form, confirmation)
{
	if (confirm(confirmation))
	{
		document.getElementById(id_form+'_action').value = 'delete';
		eCommerceSubmitForm(id_form);
	}
}

jQuery(document).ready(function (){
	jQuery('#form_reset_data').submit(function() {
		return confirm(jQuery('#confirm').val());		
	});	
});