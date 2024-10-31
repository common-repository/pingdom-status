jQuery(document).ready(function(){
	jQuery("#submit").click(function(){	
		var p = {};
		p['username'] = jQuery('#username').val();
		p['password'] = jQuery('#password').val();
		p['api_key'] = jQuery('#api_key').val();
		p['threshold'] = jQuery('#threshold').val();
		
		jQuery('div#progress p').text('Saving...');
		jQuery('div#progress').show();
		jQuery("#submit").attr('disabled', true);
		jQuery.post(ajax_saveurl, p, function(str) {
			jQuery('div#progress p').text('Settings are saved');
			jQuery("#submit").attr('disabled', false);
		});
		jQuery("#syncnow").trigger('click');
	});
	
	jQuery('a.showlog').click(function(){
		jQuery('p.log').slideToggle(100);
	});
	
	jQuery("#syncnow").click(function(){	
		jQuery('div#progress p').text('Synchronizing...');
		jQuery('div#progress').show();
		jQuery("#syncnow").attr('disabled', true);
		jQuery.post(ajax_syncurl, null, function(str) {
			jQuery('div#progress p').text('Data synchronized successfully');
			jQuery("#syncnow").attr('disabled', false);
			jQuery('a.showlog').fadeIn(800);
			jQuery('p.log').hide();
			jQuery('p.log').html(nl2br(str, false));
	    });
	});
	function nl2br (str, is_xhtml) {
	    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
	    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
	}
});
