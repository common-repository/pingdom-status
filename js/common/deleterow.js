jQuery.fn.deleterow = function(options) {
	 var settings = {
        acceptColSelector : '.deleteable',
        beforeDeleteCallback : function(id){return true;}, // Called before delete, if true is returned, deletion is performed
        afterDeleteCallback : function(id){} // Called after deletion         
    };
	
	if(options) {
        jQuery.extend(settings, options);
    };
	
	var j = jQuery;
	tbody = j(this).children('tbody');      // get table body
	rows = j(tbody).children('tr');         // get rows	
	cols = j(rows).children('td' + settings.acceptColSelector);       // get cols
	
	var numcols = cols.length / rows.length;  // get column count
	j(cols).each(function(i) {	
		j(this).unbind('click');
		j(this).click(function() {
			/* save this to self because this changes when scope changes */
	        var self = this;
	        
	        // Find row with specified id
	        var id = j(self).attr('id');
	        
	        if(settings.beforeDeleteCallback(id)){
	        	settings.afterDeleteCallback(id, function(){
	        		j(tbody).children('tr#' + id).remove();
	        	});
	        }	
		});
	});		
	return(this);
}

