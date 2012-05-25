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
            var data = new google.visualization.DataTable();
            data.addColumn('string', ctdGraphL10n.day);
            data.addColumn('number', ctdGraphL10n.totalVisits);
            data.addRows(rows);
            /*var data = google.visualization.arrayToDataTable(data/*[
            ['Year', 'Sales', 'Expenses'],
            ['2004',  1000,      400],
            ['2005',  1170,      460],
            ['2006',  660,       1120],
            ['2007',  1030,      540]
            ]*///);

            var options = {
                backgroundColor: 'transparent',
                animation:{
                    duration: 1000,
                    easing: 'out'
                }/*,
                isStacked: true*/
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
        closeText: ctdAdminL10n.closeText,
        currentText: ctdAdminL10n.currentText,
        dateFormat: ctdAdminL10n.dateFormat,
        dayNames: [
            ctdAdminL10n.dayNamesSunday,
            ctdAdminL10n.dayNamesMonday,
            ctdAdminL10n.dayNamesTuesday,
            ctdAdminL10n.dayNamesWednesday,
            ctdAdminL10n.dayNamesThursday,
            ctdAdminL10n.dayNamesFriday,
            ctdAdminL10n.dayNamesSaturday
        ],
        dayNamesMin: [
            ctdAdminL10n.dayNamesMinSu,
            ctdAdminL10n.dayNamesMinMo,
            ctdAdminL10n.dayNamesMinTu,
            ctdAdminL10n.dayNamesMinWe,
            ctdAdminL10n.dayNamesMinTh,
            ctdAdminL10n.dayNamesMinFr,
            ctdAdminL10n.dayNamesMinSa
        ],
        dayNamesShort: [
            ctdAdminL10n.dayNamesShortSun,
            ctdAdminL10n.dayNamesShortMon,
            ctdAdminL10n.dayNamesShortTue,
            ctdAdminL10n.dayNamesShortWed,
            ctdAdminL10n.dayNamesShortThu,
            ctdAdminL10n.dayNamesShortFri,
            ctdAdminL10n.dayNamesShortSat
        ],
        monthNames: [
            ctdAdminL10n.monthNamesJanuary,
            ctdAdminL10n.monthNamesFebruary,
            ctdAdminL10n.monthNamesMarch,
            ctdAdminL10n.monthNamesApril,
            ctdAdminL10n.monthNamesMay,
            ctdAdminL10n.monthNamesJune,
            ctdAdminL10n.monthNamesJuly,
            ctdAdminL10n.monthNamesAugust,
            ctdAdminL10n.monthNamesSeptember,
            ctdAdminL10n.monthNamesOctober,
            ctdAdminL10n.monthNamesNovember,
            ctdAdminL10n.monthNamesDecember
        ],
        monthNamesShort: [
            ctdAdminL10n.monthNamesShortJan,
            ctdAdminL10n.monthNamesShortFeb,
            ctdAdminL10n.monthNamesShortMar,
            ctdAdminL10n.monthNamesShortApr,
            ctdAdminL10n.monthNamesShortMay,
            ctdAdminL10n.monthNamesShortJun,
            ctdAdminL10n.monthNamesShortJul,
            ctdAdminL10n.monthNamesShortAug,
            ctdAdminL10n.monthNamesShortSep,
            ctdAdminL10n.monthNamesShortOct,
            ctdAdminL10n.monthNamesShortNov,
            ctdAdminL10n.monthNamesShortDec
        ],
        nextText: ctdAdminL10n.nextText,
        prevText: ctdAdminL10n.prevText,
        weekHeader: ctdAdminL10n.weekHeader,
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