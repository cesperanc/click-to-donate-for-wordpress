/**
 * @description Implement the rich layout for the module admin pages
 * @author Cláudio Esperança, Diogo Serra
 * @version 1.0
 */
var $j = jQuery.noConflict();

$j(function(){
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
        weekHeader: ctdAdmin.weekHeader
    };
    
    $j(".start-hidden").hide();
    
    $j(".ctd-enable-container").css({
        'margin': '10px 2px',
        'padding': '5px',
        'border': '0px none',
        'border-radius': '5px'
    });
    
    var containerEnabledStyle = {
        'border': '1px solid #ECECEC'
    };
    
    var showContainer = function(innerContainer, outerContainer, show){
        if(show){
            $j(innerContainer).show();
            $j(outerContainer).css({'border': '1px solid #ECECEC'});
        }else{
            $j(innerContainer).hide();
            $j(outerContainer).css({'border': '0px none'});
        }
    };
    
    $j("#ctd-enable-maxclicks").click(function(){
        showContainer("#ctd-maxclicks-container", "#ctd-enable-maxclicks-container", $j(this).is(":checked"));
    });
    
    $j("#ctd-enable-startdate").click(function(){
        showContainer("#ctd-startdate-container", "#ctd-enable-startdate-container", $j(this).is(":checked"));
    });
    
    $j("#ctd-enable-enddate").click(function(){
        showContainer("#ctd-enddate-container", "#ctd-enable-enddate-container", $j(this).is(":checked"));
    });
    
    showContainer("#ctd-maxclicks-container", "#ctd-enable-maxclicks-container", $j("#ctd-enable-maxclicks").is(":checked"));
    showContainer("#ctd-startdate-container", "#ctd-enable-startdate-container", $j("#ctd-enable-startdate").is(":checked"));
    showContainer("#ctd-enddate-container", "#ctd-enable-enddate-container", $j("#ctd-enable-enddate").is(":checked"));
    
    
    $j("#ctd-startdate").datepicker($j.extend(true, {}, calendarOptions, {
        
    }));
    
    $j("#ctd-enddate").datepicker($j.extend(true, {}, calendarOptions, {
        
    }));
    
    //$j(".ctd-calendars").datepicker(calendarOptions);
});