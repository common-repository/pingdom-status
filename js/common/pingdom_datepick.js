/**
 * Fills datepicker fields with values
 */
jQuery.pingdom_datepick = function(options) {
	var settings = {
		 defaultDate : new Date(),
		 minDate : (new Date()),
		 maxYear : (new Date()).getFullYear(),
		 yearsSelector : '',
		 monthsSelector : '',
		 daysSelector : '',
		 changeHandler: function(date){}    
	};
	
	if(options) {
        jQuery.extend(settings, options);
    };
    
    // Current selected date
    var currentDate = settings.defaultDate;
	
	// Fills year options field
	for(var i = settings.maxYear; i >= settings.minDate.getFullYear(); i--){
		var select = currentDate.getFullYear() == i ? true : false;
		jQuery(settings.yearsSelector).addOption(i, i, select);
	}
	
	// Fill months field
	for(var i = Date.monthNames.length - 1; i >= 0; i--){
		var select = currentDate.getMonth() == i ? true : false;
		jQuery(settings.monthsSelector).addOption(i, Date.monthNames[i], select);
	}

	
	// Fills days field
	fillDays();
	
	function fillDays(){
		jQuery(settings.daysSelector).removeOption(/./);
		
		var totalDays = currentDate.getDaysInMonth();
		for(var i = 1; i <= totalDays; i++){
			var select = currentDate.getDate() == i ? true : false;
			jQuery(settings.daysSelector).addOption(i, i, select);
		}		
	}
	
	function updateCurrentDate(){
		var year = jQuery(settings.yearsSelector).val();
		var month = jQuery(settings.monthsSelector).val();
		var day = jQuery(settings.daysSelector).val();
		
		currentDate.setFullYear(year, month, day);
	}
	
	/**
	 * Change handlers
	 */
	jQuery(settings.yearsSelector).change(function(){
		updateCurrentDate();
		fillDays();
		settings.changeHandler(currentDate);
	});
	jQuery(settings.monthsSelector).change(function(){
		updateCurrentDate();
		fillDays();
		settings.changeHandler(currentDate);
	});
	jQuery(settings.daysSelector).change(function(){
		updateCurrentDate();
		settings.changeHandler(currentDate);
	});
}

