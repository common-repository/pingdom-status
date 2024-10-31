jQuery(document).ready(function(){
	jQuery("input[@name='post_applies']").change(function(){
		var selected = jQuery(this).val();
		
		switch(selected){
			case "all_servers": 
				jQuery("#date_picker").hide();
				
				jQuery("#groups_panel").hide();
				jQuery("#servers_panel").hide();
				jQuery("#outages_panel").hide();
				break;
			case "server_group":
				jQuery("#date_picker").hide();
				
				jQuery("#groups_panel").show();
				jQuery("#servers_panel").hide();
				jQuery("#outages_panel").hide();
				break;
			case "server":
				jQuery("#date_picker").hide();
				
				jQuery("#groups_panel").hide();
				jQuery("#servers_panel").show();
				jQuery("#outages_panel").hide();
				break;
			case "outage":
				jQuery("#date_picker").show();
				
				jQuery("#groups_panel").hide();
				jQuery("#servers_panel").hide();
				jQuery("#outages_panel").show();
				break;
			default:
				break;
		}
	});
	
	//
	// Get variables from the server side
	var outageIdsString = jQuery("#selectedOutageIds").val();
	var dateString = jQuery("#selectedOutagesDate").val();
	
	// Start date
	var startDate = new Date();
	if(dateString != ''){
		dateValues = dateString.split("|");
		startDate = new Date(dateValues[0], dateValues[1] - 1, dateValues[2]);
	}
	
	// Selected outageIds
	var outageIds = Array();
	outageIdsString = outageIdsString.split("|");
	if(outageIdsString.length != null){
		for(var i = 0; i < outageIdsString.length - 1; i++){
			outageIds.push(outageIdsString[i]);	
		}
	}
	
	jQuery("#updateOutages").click(function(){
		getOutages();
	});
	
	// Setup date picker
	var minDate = new Date();
	minDate.setFullYear(1970, 0, 1);
	jQuery.pingdom_datepick({
		defaultDate: startDate,
		yearsSelector: "#selectYear",
		monthsSelector: "#selectMonth",
		daysSelector: "#selectDay",
		minDate : minDate,
		changeHandler: function(date){}
	});
	
	/**
	 * Fills outages list
	 */
	getOutages = function(){
		var p = {};
		p['year'] = jQuery("#selectYear").val();
		p['month'] = jQuery("#selectMonth").val();
		p['day'] = jQuery("#selectDay").val();
		jQuery('#progress1').show();
		jQuery.post(ajax_get_outagesurl, p, function(str) {				
            jQuery('#progress1').hide();
            
            jQuery("#selectOutages").removeOption(/./);
            
            var values = eval('(' + str + ')');
            if(values != null && values.length != null && values.length > 0){
            	for(var i = 0; i < values.length; i++){
            		
            		//If this id is inside outageIds, then it should be selected
            		var isSelected = false;
            		for(var j = 0; j < outageIds.length; j++){
            			if(outageIds[j] == values[i].id){
            				isSelected = true;
            				break;
            			}
            		}

           			jQuery("#selectOutages").addOption(values[i].id, values[i].text, isSelected);
            	}
            }
	    });
	}
	
	
	
	getOutages();
});