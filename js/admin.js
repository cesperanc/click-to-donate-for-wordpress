/**
 * @description Implement the rich layout for the module admin pages
 * @author Cláudio Esperança, Diogo Serra
 * @version 1.0
 */
var $j = jQuery.noConflict();

$j(function(){
    
    // Localize and set the common options for the calendars
    var calendarOptions = {
        closeText: ctdAdmin.closeText,
        currentText: ctdAdmin.currentText,
        dateFormat: ctdAdmin.dateFormat,
        dayNames: [
            ctdAdmin.dayNamesSunday,
            ctdAdmin.dayNamesMonday,
            ctdAdmin.dayNamesTuesday,
            ctdAdmin.dayNamesWednesday,
            ctdAdmin.dayNamesThursday,
            ctdAdmin.dayNamesFriday,
            ctdAdmin.dayNamesSaturday
        ],
        dayNamesMin: [
            ctdAdmin.dayNamesMinSu,
            ctdAdmin.dayNamesMinMo,
            ctdAdmin.dayNamesMinTu,
            ctdAdmin.dayNamesMinWe,
            ctdAdmin.dayNamesMinTh,
            ctdAdmin.dayNamesMinFr,
            ctdAdmin.dayNamesMinSa
        ],
        dayNamesShort: [
            ctdAdmin.dayNamesShortSun,
            ctdAdmin.dayNamesShortMon,
            ctdAdmin.dayNamesShortTue,
            ctdAdmin.dayNamesShortWed,
            ctdAdmin.dayNamesShortThu,
            ctdAdmin.dayNamesShortFri,
            ctdAdmin.dayNamesShortSat
        ],
        monthNames: [
            ctdAdmin.monthNamesJanuary,
            ctdAdmin.monthNamesFebruary,
            ctdAdmin.monthNamesMarch,
            ctdAdmin.monthNamesApril,
            ctdAdmin.monthNamesMay,
            ctdAdmin.monthNamesJune,
            ctdAdmin.monthNamesJuly,
            ctdAdmin.monthNamesAugust,
            ctdAdmin.monthNamesSeptember,
            ctdAdmin.monthNamesOctober,
            ctdAdmin.monthNamesNovember,
            ctdAdmin.monthNamesDecember
        ],
        monthNamesShort: [
            ctdAdmin.monthNamesShortJan,
            ctdAdmin.monthNamesShortFeb,
            ctdAdmin.monthNamesShortMar,
            ctdAdmin.monthNamesShortApr,
            ctdAdmin.monthNamesShortMay,
            ctdAdmin.monthNamesShortJun,
            ctdAdmin.monthNamesShortJul,
            ctdAdmin.monthNamesShortAug,
            ctdAdmin.monthNamesShortSep,
            ctdAdmin.monthNamesShortOct,
            ctdAdmin.monthNamesShortNov,
            ctdAdmin.monthNamesShortDec
        ],
        nextText: ctdAdmin.nextText,
        prevText: ctdAdmin.prevText,
        weekHeader: ctdAdmin.weekHeader,
        altFormat: "@",
        autoSize: true,
        changeMonth: true,
        changeYear: true
    };
    
    // Attach the spinner
    $j('#ctd-maximum-clicks-limit').spinner({
        min: 0, 
        increment: 'fast',
        showOn: 'both',
        mouseWheel: true,
        step: 100,
        largeStep: 1000
    });
    
    // Hide the hidden elements
    $j(".start-hidden").hide();
    
    // Set the CSS for the fieldset
    $j(".ctd-enable-container").css({
        'margin': '0 2px',
        'padding': '5px',
        'border': '0px none',
        'border-radius': '5px'
    });
    
    // Container function to show and style the fieldsets accordingly
    var showContainer = function(innerContainer, outerContainer, show){
        if(show){
            $j(innerContainer).show();
            $j(outerContainer).css({'margin': '10px 2px', 'border': '1px solid #ECECEC'});
        }else{
            $j(innerContainer).hide();
            $j(outerContainer).css({'margin': '0 2px', 'border': '0px none'});
        }
    };
    
    // Show the fieldset when the checkbox is checked
    $j("#ctd-enable-maxclicks").click(function(){
        showContainer("#ctd-maxclicks-container", "#ctd-enable-maxclicks-container", $j(this).is(":checked"));
    });
    $j("#ctd-enable-startdate").click(function(){
        showContainer("#ctd-startdate-container", "#ctd-enable-startdate-container", $j(this).is(":checked"));
        
        // Reset the minDate for the other calendar
        if(!$j(this).is(":checked")){
            $j("#ctd-enddate").datepicker("option", "minDate", null);
        }
    });
    $j("#ctd-enable-enddate").click(function(){
        showContainer("#ctd-enddate-container", "#ctd-enable-enddate-container", $j(this).is(":checked"));
        
        // Reset the minDate for the other calendar
        if(!$j(this).is(":checked")){
            $j("#ctd-startdate").datepicker("option", "maxDate", null);
        }
    });
    
    // Attach the date picker components and set their dates based on the timestamp values
    var startDate = $j.datepicker.parseDate("@", $j("#ctd-hidden-startdate").val()) || null;
    var endDate = $j.datepicker.parseDate("@", $j("#ctd-hidden-enddate").val()) || null;
    $j("#ctd-startdate").datepicker($j.extend(true, {}, calendarOptions, {
        defaultDate: "+1w",
        altField: "#ctd-hidden-startdate",
        maxDate: endDate,
        onSelect: function( selectedDate ) {
            var instance = $j(this).data( "datepicker" ), 
                date = $j.datepicker.parseDate(instance.settings.dateFormat || $j.datepicker._defaults.dateFormat, selectedDate, instance.settings );
            $j("#ctd-enddate").datepicker( "option", "minDate", date );
        }
    })).datepicker("setDate", startDate);
    
    $j("#ctd-enddate").datepicker($j.extend(true, {}, calendarOptions, {
        defaultDate: "+2w",
        altField: "#ctd-hidden-enddate",
        minDate: startDate,
        onSelect: function( selectedDate ) {
            var instance = $j(this).data( "datepicker" ), 
                date = $j.datepicker.parseDate(instance.settings.dateFormat || $j.datepicker._defaults.dateFormat, selectedDate, instance.settings );
            $j("#ctd-startdate").datepicker( "option", "maxDate", date );
        }
    })).datepicker("setDate", endDate);
    
    // Set the initial visibility of the fieldsets
    showContainer("#ctd-maxclicks-container", "#ctd-enable-maxclicks-container", $j("#ctd-enable-maxclicks").is(":checked"));
    showContainer("#ctd-startdate-container", "#ctd-enable-startdate-container", $j("#ctd-enable-startdate").is(":checked"));
    showContainer("#ctd-enddate-container", "#ctd-enable-enddate-container", $j("#ctd-enable-enddate").is(":checked"));
    
    
});