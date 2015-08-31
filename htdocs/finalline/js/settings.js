/**
 *      \@author    Auguria
 *      \@version   1.0
 */
function addToHiddens(selectLinked, selectTarget)
{    
    var jsonLinked = new Array();
    var jsonTarget = new Array();
    
    selectLinked.children().each(function() {            
        jsonLinked.push($(this).val());          
    });
    selectTarget.children().each(function() {            
        jsonTarget.push($(this).val());  
    });
    
    $('input[name="linked_json"]').val('[' + jsonLinked.join() + ']');
    $('input[name="target_json"]').val('[' + jsonTarget.join() + ']');
}

$(document).ready(function() {
    var selectLinked = $('#select_linked');
    var selectTarget = $('#select_target');
    
    var hiddenLinked = $('input[name="linked_json"]');
    var hiddenTarget = $('input[name="target_json"]');
        
    $('#rem_button').click(function() {        
        selectLinked.children(':selected').each(function() {            
            selectTarget.append($(this));            
        });
        addToHiddens(selectLinked, selectTarget);        
    });
    $('#add_button').click(function() {
        selectTarget.children(':selected').each(function() {            
            selectLinked.append($(this));
        });
        addToHiddens(selectLinked, selectTarget);
    })
});