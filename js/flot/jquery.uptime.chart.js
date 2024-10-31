jQuery(document).ready(function(jQuery){
		jQuery.ajax({
  				url: uptimeChartData,
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
						data2[i] = [];
						data2[i][0] = data[i][0];
						data2[i][1] = (data[i][1] == -1) ? 0 : decimals(100 - data[i][1], 2);
					}
					
	  				// Draw Flot Plot!
					var plot = jQuery.plot(
					  	jQuery("#uptimeChart"), 
					  	[{ data: data }, { data: data2 }], 
					  	{ 
					  		yaxis: { 
					  			tickSize: 25,
					  			min: 0,
					  			max: 100,
					  			tickFormatter: function(v, axis){ return v+'%'; }
					  		}, 
					  		xaxis: { 
					  			//tickSize: [14, "day"],
					  			ticks: [data[0][0], data[6][0], data[12][0], data[18][0], data[24][0], data[data.length-1][0]],
					  			tickLength: 5,
		   						min: data[0][0]-(12*60*60*1000),
		    					max: data[data.length-1][0]+(12*60*60*1000),
					  			mode: "time",
					  			timeformat: "%y/%m/%d",
					  		} ,
					  		series:{
					  			stack: 0,
					  			lines: {show: false},
					  			bars: {show: true, lineWidth: 0, fill: 0.5, barWidth: (24 * 60 * 60 * 1000)*0.9, align: 'center'},
								points: {show: false}
					  		},
					  		colors: uptimeColors,
					  		grid:{
					  			hoverable: true,
					  			borderWidth: 0.5
					  		},
					  		legend: { position: 'sw' }
					  	}
					);
	  			}
  		});
  		
  	function showTooltip(x, y, contents, classes) {
        jQuery('<div id="tooltip">' + contents + '</div>').addClass('uptimeChartHover '+classes).css( { top: y + 5, left: x + 5 }).appendTo("body").fadeIn(200);
    }
    var previousPoint = null;
    jQuery("#uptimeChart").bind("plothover", function (event, pos, item) {
        jQuery("#x").text(pos.x.toFixed(2));
        jQuery("#y").text(pos.y.toFixed(2));
            if (item) {
                if (previousPoint != item.datapoint) {
                    previousPoint = item.datapoint;
                    
                    
                    jQuery("#tooltip").remove();

					var x = item.dataIndex+1;
					var y, t, c;
					if(item.seriesIndex == 1){
						y = decimals(item.datapoint[1]-item.datapoint[2], 2);
						t = 'Downtime the ';
						c = 'uptimeChartHoverDown';
					}
					else{
						y = item.datapoint[1];
						t = 'Uptime the ';
						c = '';
					}
                        
                    showTooltip(item.pageX,
                    			item.pageY,
                                t + nSuffix(x) +":<br /><strong>" + y + "%</strong>",
                                c);
                }
            }else {
                jQuery("#tooltip").remove();
                previousPoint = null;            
            }
    });
    
    function nSuffix(n){
    	var nStr = n.toString();
    	var n2 = 0;
    	if(nStr.charAt(nStr.length-1) == ''){ 
    		n2 = parseInt(n);
    	}else{
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
    function decimals(num, dec) {
		return Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
	}
});