jQuery(document).ready(function(jQuery){
	
	// Make table "zebra"
	jQuery("#checks_list tr:odd").addClass("alternate");
	
	// Get available groups

	/* var groups = eval('(' + jQuery("#sensorGroups").val() + ')'); */

	
	// Make table editable

/*

	jQuery('#checks_list').tEditable(ajax_editurl, {
		indicator: "Saving...",
		submit: "Save",
		type: 'select',
		acceptCol: [3],
		options : groups
	});
*/


	jQuery('#checks_list tbody tr td input.PsAdminSave').click(function(){
		var id = jQuery(this).parent().parent().parent().attr('id');
		var selectId = jQuery(this).parent().children('select').val();
		var e = jQuery(this);
  		
  		jQuery.post(ajax_editurl, { id: id, selectId: selectId}, function(data){
			jQuery(e).parent().parent().children('p.PsAdminEdit').html(data + " <a class='PsAdminEdit' href='#edit'>Edit</a>");
			jQuery(e).parent().parent().children('a.PsAdminEdit').click(function(){
				jQuery(this).parent().click();
			});
			jQuery(e).parent().slideUp();
  		});
  		
  		return false;
	});

	jQuery('#checks_list tbody tr td p.PsAdminEdit').click(function(){
		jQuery(this).parent().children('div.PsAdminEditBox').slideToggle();
		return false;
	});
	
	// Make table deleteable1
	jQuery('#checks_list').deleterow({
		acceptColSelector: '.delete_button',
		beforeDeleteCallback : function(id){
				return true;
			},
		afterDeleteCallback : function(id, fnc){
			var p = {};
			p['id'] = id;
			jQuery('div#progress p').text("Deleting check...");
			jQuery('div#progress').show();
			jQuery.post(ajax_makenonpublicurl, p, function(str) {				
	            jQuery('div#progress p').text('Check with id ' + id + ' is deleted');
	            fnc();
	        });
		}
	});
	
	// Make table deleteable
	jQuery('#checks_list').deleterow({
		acceptColSelector: '.unpublic_button',
		beforeDeleteCallback : function(id){
				return true;
			},
		afterDeleteCallback : function(id, fnc){
			var p = {};
			p['id'] = id;
			jQuery('div#progress p').text("Removing from public...");
			jQuery('div#progress').show();
			jQuery.post(ajax_makenonpublicurl, p, function(str) {				
	            jQuery('div#progress p').text('Check with id ' + id + ' is non-public');
	            fnc();
	        });
		}
	});
});