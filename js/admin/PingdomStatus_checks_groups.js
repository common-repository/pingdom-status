jQuery(document).ready(function(){	
	updateTable();
		
	// Add group handling
	jQuery('#submit').click(function(){
		var p = {};
		p['group_name'] = jQuery('#group_name').val();
		
		jQuery('div#progress p').text("Saving...");
		jQuery('div#progress').show();
		jQuery("#submit").attr('disabled', true);
		jQuery.post(ajax_addurl, p, function(str) {
			var row_data = eval('(' + str + ')');
			
            jQuery('div#progress p').text('Group ' + row_data.newName + ' is saved');
            jQuery("#submit").attr('disabled', false);
                        
            // Append new row to table
            var row = "<tr id='" + row_data.id + "'>";
			row += "<th scope='row'>" +  row_data.id + "</th>";
			row += "<td id='" + row_data.id + "'>" + row_data.newName + "</td>";
			row += "<td id='" + row_data.id + "' class='delete_button'><a href='#' rel='permalink' class='edit'>Delete </a></td>";
			row += "</tr>";
            jQuery('#groups_list').children('tbody').append(row);
            
            updateTable();
        });
	});
	
	/**
	 * Connects events to the table
	 */
	function updateTable(){
		// Make table "zebra"
		jQuery("#groups_list tr:odd").addClass("alternate");
		
		// Make table editable	
		jQuery('#groups_list').tEditable(ajax_editurl, {
			indicator: "Saving...",
			submit: "Save",
			type: 'text',
			acceptCol: [0]
		});
		
		// Make table deleteable
		jQuery('#groups_list').deleterow({
			acceptColSelector: '.delete_button',
			beforeDeleteCallback : function(id){
				return confirm('Are you sure that you want to delete group with id ' + id + '?');
			},
			afterDeleteCallback : function(id, fnc){
				var p = {};
				p['id'] = id;
				jQuery('div#progress p').text("Deleting...");
				jQuery('div#progress').show();
				jQuery.post(ajax_deleteurl, p, function(str) {	
					if(str != 'ERROR'){			
		            	jQuery('div#progress p').text('Group with id ' + str + ' is deleted');
		            	fnc();
		            	updateTable();
					}
					else{
						jQuery('div#progress').removeClass('updated').addClass('error');
						jQuery('div#progress p').text('Error deleting group. Group contains public checks');
					}
		        });
			}
		});
	}
});