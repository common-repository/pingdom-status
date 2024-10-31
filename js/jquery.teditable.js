jQuery.fn.tEditable = function(url, options) {
	 var settings = {
        url    : url,
        name   : 'value',
        id     : 'id',
        type   : 'textarea',
        rowID  : 'row',
        colID  : 'col',
        width  : 'auto',
        height : 'auto',
        event  : 'click',
        onblur : 'cancel',
        submit : 'OK',
        indicator : '',
        acceptCol : [],
        options : {}, // Valid if type is "select"
        selectId : 'selectId' // Valid if type is "select". This is parameter name that is sent to sensor 
        
    };
	
	if(options) {
        jQuery.extend(settings, options);
    };
	
	var j = jQuery;
	tbody = j(this).children('tbody');      // get table body
	rows = j(tbody).children('tr');         // get rows	
	cols = j(rows).children('td');       // get cols
	
	var numcols = cols.length/rows.length;  // get column count
	j(cols).each(function(i) {
		var row = i/numcols;
		var col = i%numcols;
		
		// If this column is accepted	
		var accepted = false;
		if(settings.acceptCol.length != null){
			for(var k=0; k<settings.acceptCol.length; k++){
		        if(settings.acceptCol[k]==col){
		       		accepted = true;
		       		break;
		        }
		    }
		}
		if(!accepted){
			return;
		}
		
		j(this)[settings.event](function() {
			/* save this to self because this changes when scope changes */
	        var self = this;
	
	        /* prevent throwing an exeption if edit field is clicked again */
	        if (self.editing) {
	            return;
	        }
	
	        /* figure out how wide and tall we are */
	        var width = 
	            ('auto' == settings.width)  ? jQuery(self).css('width')  : settings.width;
	        var height = 
	            ('auto' == settings.height) ? jQuery(self).css('height') : settings.height;
	
	        self.editing    = true;
	        self.revert     = jQuery(self).html();
	        self.innerHTML  = '';
	
	        /* create the form object */
	        var f = document.createElement('form');
	
	        /*  main input element */
	        var i;
	        switch (settings.type) {
	            case 'textarea':
	                i = document.createElement('textarea');
	                if (settings.rows) {
	                    i.rows = settings.rows;
	                } else {
	                    jQuery(i).css('height', height);
	                }
	                if (settings.cols) {
	                    i.cols = settings.cols;
	                } else {
	                    jQuery(i).css('width', width);
	                }  	
	                break;
	            case 'select':
	                i = document.createElement('select');
	                j(i).addOption(settings.options, false);
	                
	                break;
	            default:
	                i = document.createElement('input');
	                i.type  = settings.type;
	                jQuery(i).css('width', width);
	                jQuery(i).css('height', height);
	                /* https://bugzilla.mozilla.org/show_bug.cgi?id=236791 */
	                i.setAttribute('autocomplete','off');
	        }
	        
	        /* set input content via POST, GET, given data or existing value */
	        /* this looks weird because it is for maintaining bc */
	        var url;
	        var type;
	                
	        if (settings.getload) {	         
				url = settings.getload;
	            type = 'GET';
	        } else if (settings.postload) {	
	            url = settings.postload;
	            type = 'POST';      
	        }
	        else if(settings.url) {
	        	url = settings.url;
	        	type = 'POST';
	        }
	
			i.value = self.revert;
	        i.name  = settings.name;
	        f.appendChild(i);
	
	        if (settings.submit) {
	            var b = document.createElement('input');
	            b.type = 'submit';
	            b.value = settings.submit;
	            f.appendChild(b);
	        }
	
	        if (settings.cancel) {
	            var b = document.createElement('input');
	            b.type = 'button';
	            b.value = settings.cancel;
	            f.appendChild(b);
	        }
	
	        /* add created form to self */
	        self.appendChild(f);
	
	        i.focus();
	 
	        /* discard changes if pressing esc */
	        jQuery(i).keydown(function(e) {
	            if (e.keyCode == 27) {
	                e.preventDefault();
	                reset();
	            }
	        });
	
	        /* discard, submit or nothing with changes when clicking outside */
	        /* do nothing is usable when navigating with tab */
	        var t;
	        if ('cancel' == settings.onblur) {
	            jQuery(i).blur(function(e) {
	                t = setTimeout(reset, 500)
	            });
	        /* TODO: does not currently work */
	        } else if ('submit' == settings.onblur) {
            jQuery(i).blur(function(e) {
                jQuery(f).submit();
            });
	        } else {
	            jQuery(i).blur(function(e) {
	              /* TODO: maybe something here */
	            });
	        }
	
	        jQuery(f).submit(function(e) {
	
	            if (t) { 
	                clearTimeout(t);
	            }
	
	            /* do no submit */
	            e.preventDefault(); 
	
	            /* add edited content and id of edited element to POST */           
	            var p = {};
	            p[i.name] = jQuery(i).val();
	            p[settings.id] = self.id;
	            p[settings.selectId] = j(i).val();
				p[settings.rowID] = row; // provide updated row and column
				p[settings.colID] = col; 
				
	
	            /* show the saving indicator */
	            if (settings.indicator)
					jQuery(self).html(settings.indicator);
				else 
					jQuery(self).html("Saving...");
				
				

	            jQuery.post(settings.url, p, function(str) {
	                self.innerHTML = str;
	                self.editing = false;
	            });
	            return false;
	        });
		
			
	        function reset() {
	            self.innerHTML = self.revert;
	            self.editing   = false;
	        }
				
		});
	});		
	return(this);
}

