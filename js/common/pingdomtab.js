/**
 * Implements tab functionality
 * Example of usage
 * js:
 * jQuery.pingdomtab({
 *		bindings:[
 *			{tabSelector: '#tab_div_daily', tabContentSelector: '#div_daily', selectedTabClass : 'tab_a', unselectedTabClass : ''},
 *			{tabSelector: '#tab_div_hourly', tabContentSelector: '#div_hourly', selectedTabClass : 'tab_a', unselectedTabClass : ''}
 *		],
 * 		fxFadeIn: true,
 * 		placeholder: null
 * });
 * 
 * html:
 * <div class="tabs">
 * <ul>
 *		<li id="tab_div_daily" class="tab_a"><a href="javascript:;">Daily</a></li>
 *		<li id="tab_div_hourly"><a href="javascript:;">Hourly</a></li>
 * </ul>
 * </div>
 * For example on how to use it with placeholder, see how it is used on uptime reports page.
 * 
 * @requires jquery.js
 * @author Aleksandar Vucetic
 */
jQuery.pingdomtab = function(parameters){
	/**
	 * Default settings
	 */
    var settings = {
    	/**
    	 * Bindings is array of objects in format
    	 * 	{
    	 * 		tabSelector: <selector of tab element>, 
    	 * 		tabContentSelector: <selector of tab content element>, 
    	 * 		onShow : <on show callback function>,
    	 * 		selectedTabClass : <tab class for selected state>,
    	 * 		unselectedTabClass : <tab class for unselected state>,
    	 * 		selectedImageSource : <if tab is img tag, this is image source for selected state. If this is set, selectedTabClass should be set to null>,
    	 * 		unselectedImageSource : <if tab is img tag, this is image source for unselected state. If this is set, unselectedTabClass should be set to null>
    	 * 	}
    	 */
		bindings : [],
		
		fxFadeIn : true, // For flash div-s this can be done only with false
		placeholder : null // If there is a placeholder, fxFadeIn has to be put to false
    };
    jQuery.extend(settings, parameters);  
    
    /**
     * 
     */
	// If I have a placeholder, I need to position all other elements according to that placeholder
	if(settings.placeholder != null){
		for(var i = 0; i < settings.bindings.length; i++){
			// And hide all but first
			if(jQuery(settings.bindings[i].tabContentSelector)[0] != null){
    			if(i != 0){
					jQuery(settings.bindings[i].tabContentSelector)[0].style.visibility = 'hidden';
				}
				
				jQuery(settings.bindings[i].tabContentSelector)[0].style.position = 'absolute';
				jQuery(settings.bindings[i].tabContentSelector)[0].style.left = '0px';
				jQuery(settings.bindings[i].tabContentSelector)[0].style.top = '0px';
			}
		}
	}
	
	// Attach events to tabs			
	for(var i = 0; i < settings.bindings.length; i++){
		jQuery(settings.bindings[i].tabSelector).bind('click', function(){
			var toShowId = 0;
			for(var j = 0; j < settings.bindings.length; j++){
				if(jQuery(this)[0] != jQuery(settings.bindings[j].tabSelector)[0]){
					//
					// Set class/image for unselected tab(s)
					if(settings.bindings[j].selectedTabClass != null && settings.bindings[j].unselectedTabClass != null){
						jQuery(settings.bindings[j].tabSelector).removeClass(settings.bindings[j].selectedTabClass);
						jQuery(settings.bindings[j].tabSelector).addClass(settings.bindings[j].unselectedTabClass);
					}
					else{
						jQuery(settings.bindings[j].tabSelector).attr('src', settings.bindings[j].unselectedImageSource);
					}
					
					//
					// Do hiding of content pane
					if(settings.fxFadeIn){
						jQuery(settings.bindings[j].tabContentSelector).hide();
					}
					else{
						// For flash div-s this can be done only this way
						jQuery(settings.bindings[j].tabContentSelector)[0].style.visibility = 'hidden';
					}
				}
				else{
					toShowId = j;
				}
			}
			
			
			//
			// Set class/image for selected tab
			if(settings.bindings[toShowId].selectedTabClass != null && settings.bindings[toShowId].unselectedTabClass != null){
				jQuery(settings.bindings[toShowId].tabSelector).removeClass(settings.bindings[toShowId].unselectedTabClass);
				jQuery(settings.bindings[toShowId].tabSelector).addClass(settings.bindings[toShowId].selectedTabClass);
			}
			else{
				jQuery(settings.bindings[toShowId].tabSelector).attr('src', settings.bindings[toShowId].selectedImageSource);
			}
			
			//
			// Do showing of content pane
			if(settings.fxFadeIn){
				jQuery(settings.bindings[toShowId].tabContentSelector).fadeIn("slow");
			}
			else{
				jQuery(settings.bindings[toShowId].tabContentSelector)[0].style.visibility = 'visible';
			}
			
			//
			// Call callback function
			if(settings.bindings[toShowId].onShow != null){
				settings.bindings[toShowId].onShow();
			}
		});
	}
};




