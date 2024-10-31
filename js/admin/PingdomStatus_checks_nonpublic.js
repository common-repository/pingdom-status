jQuery(document).ready(function(){
	// Make table "zebra"
	jQuery("#checks_list tr:odd").addClass("alternate");
	
	// Make table deleteable
	jQuery('#checks_list').deleterow({
		acceptColSelector: '.delete_button',
		beforeDeleteCallback : function(id){
				return true;
			},
		afterDeleteCallback : function(id, fnc){
			var p = {};
			p['id'] = id;
			p['group_id'] = jQuery("#select"+id).val();
			jQuery('div#progress p').text("Making public...");
			jQuery('div#progress').show();
			jQuery.post(ajax_makepublicurl, p, function(str) {				
	            jQuery('div#progress p').text('Check with id ' + str + ' is public');
	            fnc();
	        });
		}
	});
});