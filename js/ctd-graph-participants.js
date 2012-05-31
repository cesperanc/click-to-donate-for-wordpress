var $j=jQuery.noConflict();(function(c){var b={call:function(d,e){if(b[e]){return b[e].apply(d,Array.prototype.slice.call(arguments,2))}else{c.error(ctdGraphParticipantsL10n.privateMethodDoesNotExist.replace("{0}",e))}return null},init:function(){},drawGraph:function(g){var h=this;var f=google.visualization.arrayToDataTable(g);var d={backgroundColor:"transparent",animation:{duration:1000,easing:"out"},legend:"none"};var e=new google.visualization.BarChart(c(this).get(0));e.draw(f,d);c(this).removeClass("loading");return h},dataLoaded:function(d){var e=this;if(!c.isEmptyObject(d)){b.call(e,"drawGraph",d)}else{c(this).removeClass("loading").html(ctdGraphParticipantsL10n.withoutdata)}c(this).trigger("dataLoaded.ctdGraphVisitors",d);return this},loadData:function(d){var e=this;c(this).addClass("loading").html(ctdGraphParticipantsL10n.loading);c.post(ajaxurl,d,function(f){b.call(e,"dataLoaded",f)},"json");return e}};var a={loadData:function(d){return b.call(this,"loadData",d)},drawGraph:function(){return b.call(this,"drawGraph")}};c.fn.ctdGraphVisitors=function(d){if(a[d]){return a[d].apply(this,Array.prototype.slice.call(arguments,1))}else{if(typeof d==="object"||!d){return b.init.apply(this,arguments)}else{c.error(ctdGraphParticipantsL10n.methodDoesNotExist.replace("{0}",d))}}return null}})($j);$j(function(){var b={closeText:ctdGraphParticipantsL10n.closeText,currentText:ctdGraphParticipantsL10n.currentText,dateFormat:ctdGraphParticipantsL10n.dateFormat,dayNames:[ctdGraphParticipantsL10n.dayNamesSunday,ctdGraphParticipantsL10n.dayNamesMonday,ctdGraphParticipantsL10n.dayNamesTuesday,ctdGraphParticipantsL10n.dayNamesWednesday,ctdGraphParticipantsL10n.dayNamesThursday,ctdGraphParticipantsL10n.dayNamesFriday,ctdGraphParticipantsL10n.dayNamesSaturday],dayNamesMin:[ctdGraphParticipantsL10n.dayNamesMinSu,ctdGraphParticipantsL10n.dayNamesMinMo,ctdGraphParticipantsL10n.dayNamesMinTu,ctdGraphParticipantsL10n.dayNamesMinWe,ctdGraphParticipantsL10n.dayNamesMinTh,ctdGraphParticipantsL10n.dayNamesMinFr,ctdGraphParticipantsL10n.dayNamesMinSa],dayNamesShort:[ctdGraphParticipantsL10n.dayNamesShortSun,ctdGraphParticipantsL10n.dayNamesShortMon,ctdGraphParticipantsL10n.dayNamesShortTue,ctdGraphParticipantsL10n.dayNamesShortWed,ctdGraphParticipantsL10n.dayNamesShortThu,ctdGraphParticipantsL10n.dayNamesShortFri,ctdGraphParticipantsL10n.dayNamesShortSat],monthNames:[ctdGraphParticipantsL10n.monthNamesJanuary,ctdGraphParticipantsL10n.monthNamesFebruary,ctdGraphParticipantsL10n.monthNamesMarch,ctdGraphParticipantsL10n.monthNamesApril,ctdGraphParticipantsL10n.monthNamesMay,ctdGraphParticipantsL10n.monthNamesJune,ctdGraphParticipantsL10n.monthNamesJuly,ctdGraphParticipantsL10n.monthNamesAugust,ctdGraphParticipantsL10n.monthNamesSeptember,ctdGraphParticipantsL10n.monthNamesOctober,ctdGraphParticipantsL10n.monthNamesNovember,ctdGraphParticipantsL10n.monthNamesDecember],monthNamesShort:[ctdGraphParticipantsL10n.monthNamesShortJan,ctdGraphParticipantsL10n.monthNamesShortFeb,ctdGraphParticipantsL10n.monthNamesShortMar,ctdGraphParticipantsL10n.monthNamesShortApr,ctdGraphParticipantsL10n.monthNamesShortMay,ctdGraphParticipantsL10n.monthNamesShortJun,ctdGraphParticipantsL10n.monthNamesShortJul,ctdGraphParticipantsL10n.monthNamesShortAug,ctdGraphParticipantsL10n.monthNamesShortSep,ctdGraphParticipantsL10n.monthNamesShortOct,ctdGraphParticipantsL10n.monthNamesShortNov,ctdGraphParticipantsL10n.monthNamesShortDec],nextText:ctdGraphParticipantsL10n.nextText,prevText:ctdGraphParticipantsL10n.prevText,weekHeader:ctdGraphParticipantsL10n.weekHeader,altFormat:"@",autoSize:true,changeMonth:true,changeYear:true},a=null,c=null;if($j("#ctd-hidden-graphparticipants-startdate").val()){a=$j.datepicker.parseDate(b.altFormat,$j("#ctd-hidden-graphparticipants-startdate").val())||null}if($j("#ctd-hidden-graphparticipants-enddate").val()){c=$j.datepicker.parseDate(b.altFormat,$j("#ctd-hidden-graphparticipants-enddate").val())||null}$j("#ctd-graphparticipants-startdate").datepicker($j.extend(true,{},b,{defaultDate:"-1w",altField:"#ctd-hidden-graphparticipants-startdate",maxDate:c,onSelect:function(f){var d=$j(this).data("datepicker"),e=$j.datepicker.parseDate(d.settings.dateFormat||$j.datepicker._defaults.dateFormat,f,d.settings);$j("#ctd-graphparticipants-enddate").datepicker("option","minDate",e)}})).datepicker("setDate",a);$j("#ctd-graphparticipants-enddate").datepicker($j.extend(true,{},b,{defaultDate:"+0w",altField:"#ctd-hidden-graphparticipants-enddate",minDate:a,onSelect:function(f){var d=$j(this).data("datepicker"),e=$j.datepicker.parseDate(d.settings.dateFormat||$j.datepicker._defaults.dateFormat,f,d.settings);$j("#ctd-graphparticipants-startdate").datepicker("option","maxDate",e)}})).datepicker("setDate",c)});