/*!
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['sap/ui/core/Renderer','sap/ui/unified/calendar/CalendarDate','./MonthRenderer',"sap/ui/core/CalendarType"],function(R,C,M,a){"use strict";var D=R.extend(M);D.apiVersion=2;D.getStartDate=function(d){return d._getStartDate();};D.getClass=function(r,d){var c=["sapUiCalDatesRow","sapUiCalRow"];if(!d.getShowDayNamesLine()){c.push("sapUiCalNoNameLine");}return c;};D.addWrapperAdditionalStyles=function(r,d){if(d._iTopPosition){r.style("top",d._iTopPosition+"px");}};D.renderMonth=function(r,d,o){M.renderMonth.apply(this,arguments);this.renderWeekNumbers(r,d);};D.renderWeekNumbers=function(r,d){var o,i,b,w;if(d.getShowWeekNumbers()&&d.getPrimaryCalendarType()===a.Gregorian){o=sap.ui.getCore().getLibraryResourceBundle("sap.ui.unified");r.openStart("div",d.getId()+"-weeks");r.class("sapUiCalRowWeekNumbers");r.openEnd();i=d.getDays();b=100/i;w=d.getWeekNumbers();w.forEach(function(W){r.openStart("div");r.class('sapUiCalRowWeekNumber');r.style("width",W.len*b+"%");r.attr("data-sap-ui-week",W.number);r.openEnd();r.text(o.getText('CALENDAR_DATES_ROW_WEEK_NUMBER',[W.number]));r.close("div");});r.close("div");}};D.renderDummyCell=function(){};D.renderHeader=function(r,d,o){var l=d._getLocaleData();var i=d.getId();var b=d.getDays();var w="";if(d._getShowHeader()){r.openStart("div",i+"-Head");r.openEnd();this.renderHeaderLine(r,d,l,o);r.close("div");}w=(100/b)+"%";if(d.getShowDayNamesLine()){r.openStart("div",i+"-Names");r.style("display","inline");r.openEnd();this.renderDayNames(r,d,l,o.getDay(),b,false,w);r.close("div");}};D.renderHeaderLine=function(r,d,l,o){var I=d.getId();var b=d.getDays();var c=new C(o,d.getPrimaryCalendarType());var w="";var m=0;var e=[];var i=0;for(i=0;i<b;i++){m=c.getMonth();if(e.length>0&&e[e.length-1].iMonth==m){e[e.length-1].iDays++;}else{e.push({iMonth:m,iDays:1});}c.setDate(c.getDate()+1);}var f=l.getMonthsStandAlone("wide");for(i=0;i<e.length;i++){var g=e[i];w=(100/b*g.iDays)+"%";r.openStart("div",I+"-Head"+i);r.class("sapUiCalHeadText");r.style("width",w);r.openEnd();r.text(f[g.iMonth]);r.close("div");}};D.renderDays=function(r,d,o){var b=d.getDays();var w=(100/b)+"%";var s=d.getShowDayNamesLine();if(!o){o=d._getFocusedDate();}var h=this.getDayHelper(d,o);if(!s){if(d._bLongWeekDays||!d._bNamesLengthChecked){h.aWeekDays=h.oLocaleData.getDaysStandAlone("abbreviated");}else{h.aWeekDays=h.oLocaleData.getDaysStandAlone("narrow");}h.aWeekDaysWide=h.oLocaleData.getDaysStandAlone("wide");}var c=new C(o,d.getPrimaryCalendarType());for(var i=0;i<b;i++){this.renderDay(r,d,c,h,false,false,i,w,!s);c.setDate(c.getDate()+1);}};return D;},true);
