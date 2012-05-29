/**
 * @description Implement the graph layout for the module admin pages
 * @author Cláudio Esperança, Diogo Serra
 * @version 1.0
 */

var $j = jQuery.noConflict();

// jQuery plugin for the graph
(function($) {
    /* Private methods */
    var privateMethods = {
	/* Call private handler for internal use (no external access) */
	call : function(context, method){
	    if ( privateMethods[method] ) {
		return privateMethods[ method ].apply( context, Array.prototype.slice.call( arguments, 2 ));
	    } else {
		$.error( ctdGraphL10n.privateMethodDoesNotExist.replace("{0}", method) );
	    }
	    return null;
	},
        
        /* Plugin initialization method */
	init : function() {
        },
        
        drawGraph : function(rows){
            var containerElement = this;
            var data = google.visualization.arrayToDataTable(rows);
            var options = {
                backgroundColor: 'transparent',
                animation:{
                    duration: 1000,
                    easing: 'out'
                },
                isStacked: true
            };

            var chart = new google.visualization.ColumnChart($(this).get(0));
            chart.draw(data, options);

            $(this).removeClass("loading");
            
            return containerElement;
        },
        
        dataLoaded : function(data){
            var containerElement = this;
            privateMethods.call(containerElement, 'drawGraph', data);
            
            $(this).trigger('dataLoaded.ctdGraph', data);
            return this;
        },
	
	/* Return the data from the table */
	loadData : function(args){
            var containerElement = this;
            
            $(this).addClass("loading").html(ctdGraphL10n.loading);
            
            $.post( 
                ajaxurl, 
                args, 
                function(data) {
                    privateMethods.call(containerElement, 'dataLoaded', data);
                }, "json" 
            );
                
	    return containerElement;
	}
    };
    
    /* Public methods */
    var publicMethods = {
	/* Get number of data columns */
	loadData : function(args){ 
	    return privateMethods.call(this, 'loadData', args);
	},
	drawGraph : function(){ 
	    return privateMethods.call(this, 'drawGraph');
	}
    };
    
    /*  */
    $.fn.ctdGraph = function(method) {
	/* Method calling logic */
	if ( publicMethods[method] ) {
	    return publicMethods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
	} else if ( typeof method === 'object' || ! method ) {
	    return privateMethods.init.apply( this, arguments );
	} else {
	    $.error( ctdGraphL10n.methodDoesNotExist.replace("{0}", method) );
	}
	return null;
    };
})($j);


// View code
$j(function(){
    var calendarOptions = {
        closeText: ctdGraphL10n.closeText,
        currentText: ctdGraphL10n.currentText,
        dateFormat: ctdGraphL10n.dateFormat,
        dayNames: [
            ctdGraphL10n.dayNamesSunday,
            ctdGraphL10n.dayNamesMonday,
            ctdGraphL10n.dayNamesTuesday,
            ctdGraphL10n.dayNamesWednesday,
            ctdGraphL10n.dayNamesThursday,
            ctdGraphL10n.dayNamesFriday,
            ctdGraphL10n.dayNamesSaturday
        ],
        dayNamesMin: [
            ctdGraphL10n.dayNamesMinSu,
            ctdGraphL10n.dayNamesMinMo,
            ctdGraphL10n.dayNamesMinTu,
            ctdGraphL10n.dayNamesMinWe,
            ctdGraphL10n.dayNamesMinTh,
            ctdGraphL10n.dayNamesMinFr,
            ctdGraphL10n.dayNamesMinSa
        ],
        dayNamesShort: [
            ctdGraphL10n.dayNamesShortSun,
            ctdGraphL10n.dayNamesShortMon,
            ctdGraphL10n.dayNamesShortTue,
            ctdGraphL10n.dayNamesShortWed,
            ctdGraphL10n.dayNamesShortThu,
            ctdGraphL10n.dayNamesShortFri,
            ctdGraphL10n.dayNamesShortSat
        ],
        monthNames: [
            ctdGraphL10n.monthNamesJanuary,
            ctdGraphL10n.monthNamesFebruary,
            ctdGraphL10n.monthNamesMarch,
            ctdGraphL10n.monthNamesApril,
            ctdGraphL10n.monthNamesMay,
            ctdGraphL10n.monthNamesJune,
            ctdGraphL10n.monthNamesJuly,
            ctdGraphL10n.monthNamesAugust,
            ctdGraphL10n.monthNamesSeptember,
            ctdGraphL10n.monthNamesOctober,
            ctdGraphL10n.monthNamesNovember,
            ctdGraphL10n.monthNamesDecember
        ],
        monthNamesShort: [
            ctdGraphL10n.monthNamesShortJan,
            ctdGraphL10n.monthNamesShortFeb,
            ctdGraphL10n.monthNamesShortMar,
            ctdGraphL10n.monthNamesShortApr,
            ctdGraphL10n.monthNamesShortMay,
            ctdGraphL10n.monthNamesShortJun,
            ctdGraphL10n.monthNamesShortJul,
            ctdGraphL10n.monthNamesShortAug,
            ctdGraphL10n.monthNamesShortSep,
            ctdGraphL10n.monthNamesShortOct,
            ctdGraphL10n.monthNamesShortNov,
            ctdGraphL10n.monthNamesShortDec
        ],
        nextText: ctdGraphL10n.nextText,
        prevText: ctdGraphL10n.prevText,
        weekHeader: ctdGraphL10n.weekHeader,
        altFormat: "@",
        autoSize: true,
        changeMonth: true,
        changeYear: true
    },
    startDate = null,
    endDate = null;
    
    // Attach the date picker components and set their dates based on the timestamp values
    if($j("#ctd-hidden-graph-startdate").val()){
        startDate = $j.datepicker.parseDate(calendarOptions.altFormat, $j("#ctd-hidden-graph-startdate").val()) || null;
    }
    if($j("#ctd-hidden-graph-enddate").val()){
        endDate = $j.datepicker.parseDate(calendarOptions.altFormat, $j("#ctd-hidden-graph-enddate").val()) || null;
    }
    
    $j("#ctd-graph-startdate").datepicker($j.extend(true, {}, calendarOptions, {
        defaultDate: "-1w",
        altField: "#ctd-hidden-graph-startdate",
        maxDate: endDate,
        onSelect: function( selectedDate ) {
            var instance = $j(this).data( "datepicker" ), 
            date = $j.datepicker.parseDate(instance.settings.dateFormat || $j.datepicker._defaults.dateFormat, selectedDate, instance.settings );

            $j("#ctd-graph-enddate").datepicker( "option", "minDate", date );
        }
    })).datepicker("setDate", startDate);
    
    $j("#ctd-graph-enddate").datepicker($j.extend(true, {}, calendarOptions, {
        defaultDate: "+0w",
        altField: "#ctd-hidden-graph-enddate",
        minDate: startDate,
        onSelect: function( selectedDate ) {
            var instance = $j(this).data( "datepicker" ), 
                date = $j.datepicker.parseDate(instance.settings.dateFormat || $j.datepicker._defaults.dateFormat, selectedDate, instance.settings );
            $j("#ctd-graph-startdate").datepicker( "option", "maxDate", date );
        }
    })).datepicker("setDate", endDate);
});