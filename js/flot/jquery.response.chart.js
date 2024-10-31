jQuery(document).ready(function(jQuery){
		jQuery.ajax({
  				url: responseChartData,
  				cache: false,
  				dataType: 'json',

	  			success: function(data) {
	  				if(data.length === 0){
	  					return;
	  				}
	  				/* 
						-1 = Non existant data
	  				*/
	  				var data2 = [];
	
					for(var i in data){
						if(data[i][1] != -1){
							data2[i] = [];
							data2[i][0] = data[i][0];
							data2[i][1] = data[i][1];
						}
					}
	  				// Draw Flot Plot!
					var plot = jQuery.plot(
					  	jQuery("#responseChart"), 
					  	[data2 ], 
					  	{ 
					  		yaxis: { 
								min: 0,
								tickFormatter: function(v, axis){ return v+'ms'; }
					  		}, 
					  		xaxis: { 
					  			ticks: [data[0][0], data[6][0], data[12][0], data[18][0], data[24][0], data[data.length-1][0]],
		   						min: data[0][0],
		    					max: data[data.length-1][0],
					  			mode: "time",
					  			timeformat: "%y/%m/%d"
					  		} ,
					  		series:{
					  			lines: {show: true, fill: 0.5},
								points: {show: true, radius: 2, fill: true, fillColor: responseColors[0]},
								shadowSize: 0
					  		},
					  		colors: responseColors,
					  		grid:{
					  			hoverable: true,
					  			borderWidth: 0.5
					  		}
					  		
					  	}
					);
	  			}
  		});
  		
  	function showTooltip(x, y, contents, classes) {
        jQuery('<div id="tooltip">' + contents + '</div>').addClass('uptimeChartHover '+classes).css( { top: y + 5, left: x + 5 }).appendTo("body").fadeIn(200);
    }
    var previousPoint = null;
    jQuery("#responseChart").bind("plothover", function (event, pos, item) {
        jQuery("#x").text(pos.x.toFixed(2));
        jQuery("#y").text(pos.y.toFixed(2));
            if (item) {
                if (previousPoint != item.datapoint) {
                    previousPoint = item.datapoint;
                    
                    jQuery("#tooltip").remove();
                    var x = item.dataIndex+1,
                    	y = item.datapoint[1];                    
     
                        
                    showTooltip(item.pageX, item.pageY,
                                "Average Response time the " + nSuffix(x) +":<br /> <strong>" + y + "ms</strong>",
                                "");
                }
            }
            else {
                jQuery("#tooltip").remove();
                previousPoint = null;            
            }
    });
    
    function nSuffix(n){
    	var nStr = n.toString();
    	var n2 = 0;
    	if(nStr.charAt(nStr.length-1) == ''){ n2 = parseInt(n); }
    	else{
    		// Exception to the rule, 11-19 is always 11th etc
    		if(nStr.length == 2 && nStr.charAt(0) == '1'){ return n+'th';  }
    		n2 = parseInt(nStr.charAt(nStr.length-1));
    	}
    	switch(n2){
    		case 1 :
    			return n+'st';
    		break;
    		case 2 :
    			return n+'nd';
    		break;
    		case 3 :
    			return n+'rd';
    		break;
    		case 0 :
    		case 4 : 
    		case 5 : 
    		case 6 :
    		case 7 :
    		case 8 :
    		case 9 :
    			return n+'th';
    		break;
    		default:
    			return n;
    	}
    }
});