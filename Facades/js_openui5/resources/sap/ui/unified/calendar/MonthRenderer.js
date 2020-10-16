/*!
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['sap/ui/unified/calendar/CalendarUtils','sap/ui/unified/calendar/CalendarDate','sap/ui/unified/CalendarLegend','sap/ui/unified/CalendarLegendRenderer','sap/ui/core/library','sap/ui/unified/library',"sap/base/Log",'sap/ui/core/InvisibleText'],function(C,a,b,c,d,l,L,I){"use strict";var e=l.CalendarDayType;var f=d.CalendarType;var M={apiVersion:2};M.render=function(r,m){var D=this.getStartDate(m),t=m.getTooltip_AsString(),g=sap.ui.getCore().getLibraryResourceBundle("sap.ui.unified"),i=m.getId(),A={value:"",append:true},s="",w=m.getWidth();r.openStart("div",m);this.getClass(r,m).forEach(function(h){r.class(h);});if(m._getSecondaryCalendarType()){r.class("sapUiCalMonthSecType");}this.addWrapperAdditionalStyles(r,m);if(t){r.attr("title",t);}if(m._getShowHeader()){A.value=A.value+" "+i+"-Head";}if(m._bCalendar){s+=" "+I.getStaticId("sap.ui.unified","CALENDAR_MONTH_PICKER_OPEN_HINT")+" "+I.getStaticId("sap.ui.unified","CALENDAR_YEAR_PICKER_OPEN_HINT");}if(w){r.style("width",w);}r.accessibilityState(m,{role:"grid",roledescription:g.getText("CALENDAR_DIALOG"),multiselectable:!m.getSingleSelection()||m.getIntervalSelection(),labelledby:A,describedby:s});r.openEnd();if(m.getIntervalSelection()){r.openStart("span",i+"-Start");r.style("display","none");r.openEnd();r.text(g.getText("CALENDAR_START_DATE"));r.close("span");r.openStart("span",i+"-End");r.style("display","none");r.openEnd();r.text(g.getText("CALENDAR_END_DATE"));r.close("span");}this.renderMonth(r,m,D);r.close("div");};M.addWrapperAdditionalStyles=function(){};M.getStartDate=function(m){return m._getDate();};M.getClass=function(r,m){var g=["sapUiCalMonthView"],s=m.getPrimaryCalendarType(),S=m.getShowWeekNumbers();if(s===f.Islamic||!S){g.push("sapUiCalNoWeekNum");}return g;};M.renderMonth=function(r,m,D){this.renderHeader(r,m,D);this.renderDays(r,m,D);};M.renderHeader=function(r,m,D){var o=m._getLocaleData();var F=m._getFirstDayOfWeek();this.renderHeaderLine(r,m,o,D);r.openStart("div");r.accessibilityState(null,{role:"row"});r.style("overflow","hidden");r.openEnd();this.renderDayNames(r,m,o,F,7,true,undefined);r.close("div");};M.renderHeaderLine=function(r,m,o,D){C._checkCalendarDate(D);if(m._getShowHeader()){var i=m.getId();var s=m.getPrimaryCalendarType();var g=o.getMonthsStandAlone("wide",s);r.openStart("div",i+"-Head");r.class("sapUiCalHeadText");r.openEnd();r.text(g[D.getMonth()]);r.close("div");}};M.renderDayNames=function(r,m,o,s,D,g,w){var F=m._getFirstDayOfWeek();var h=m.getId();var j="";var k=m.getPrimaryCalendarType();var W=[];if(m._bLongWeekDays||!m._bNamesLengthChecked){W=o.getDaysStandAlone("abbreviated",k);}else{W=o.getDaysStandAlone("narrow",k);}var n=o.getDaysStandAlone("wide",k);if(m.getShowWeekNumbers()){this.renderDummyCell(r,"sapUiCalWH",true,"columnheader");}for(var i=0;i<D;i++){if(g){j=h+"-WH"+((i+F)%7);}else{j=h+"-WH"+i;}r.openStart("div",j);r.class("sapUiCalWH");if(i===0){r.class("sapUiCalFirstWDay");}if(w){r.style("width",w);}r.accessibilityState(null,{role:"columnheader",label:n[(i+s)%7]});r.openEnd();r.text(W[(i+s)%7]);r.close("div");}};M.renderDays=function(r,m,D){var w,g,h,H,i,t,s;C._checkCalendarDate(D);if(!D){D=m._getFocusedDate();}t=D.toUTCJSDate().getTime();if(!t&&t!==0){throw new Error("Date is invalid "+m);}H=this.getDayHelper(m,D);g=m._getVisibleDays(D,true);s=m.getShowWeekNumbers();w=m.getPrimaryCalendarType()!==f.Islamic&&s;h=g.length;for(i=0;i<h;i++){if(i%7===0){r.openStart("div");r.attr("role","row");r.openEnd();if(w){this._renderWeekNumber(r,g[i],H);}}this.renderDay(r,m,g[i],H,true,w,-1);if(i%7===6){r.close("div");}}if(h===28){this.renderDummyCell(r,"sapUiCalItem",false,"");}};M.renderDummyCell=function(r,s,v,R){r.openStart("div");r.class(s);r.class("sapUiCalDummy");r.style("visibility",v?"visible":"hidden");r.attr("role",R);r.attr("tabindex","-1");r.openEnd();r.close('div');};M.getDayHelper=function(m,D){var o,s,g=m._getLocaleData(),h={sLocale:m._getLocale(),oLocaleData:g,iMonth:D.getMonth(),iYear:D.getYear(),iFirstDayOfWeek:m._getFirstDayOfWeek(),iWeekendStart:g.getWeekendStart(),iWeekendEnd:g.getWeekendEnd(),aNonWorkingDays:m._getNonWorkingDays(),sToday:g.getRelativeDay(0),oToday:a.fromLocalJSDate(new Date(),m.getPrimaryCalendarType()),sId:m.getId(),oFormatLong:m._getFormatLong(),sSecondaryCalendarType:m._getSecondaryCalendarType(),oLegend:undefined};s=m.getLegend();if(s&&typeof s==="string"){o=sap.ui.getCore().byId(s);if(o){if(!(o instanceof b)){throw new Error(o+" is not an sap.ui.unified.CalendarLegend. "+m);}h.oLegend=o;}else{L.warning("CalendarLegend "+s+" does not exist!",m);}}return h;};M.renderDay=function(r,m,D,h,o,w,n,W,g){C._checkCalendarDate(D);var s=new a(D,h.sSecondaryCalendarType),A={role:m._getAriaRole(),selected:false,label:"",describedby:""},B=D._bBeforeFirstYear,j="",k=h.oLegend,N;var y=m._oFormatYyyymmdd.format(D.toUTCJSDate(),true);var p=D.getDay();var S=m._checkDateSelected(D);var q=m._getDateTypes(D);var E=m._checkDateEnabled(D);var i=0;if(B){E=false;}r.openStart("div",h.sId+"-"+y);r.class("sapUiCalItem");r.class("sapUiCalWDay"+p);if(W){r.style("width",W);}if(p===h.iFirstDayOfWeek){r.class("sapUiCalFirstWDay");}if(o&&h.iMonth!==D.getMonth()){r.class("sapUiCalItemOtherMonth");A["disabled"]=true;}if(D.isSame(h.oToday)){r.class("sapUiCalItemNow");A["label"]=h.sToday+" ";}if(S>0){r.class("sapUiCalItemSel");A["selected"]=true;}else{A["selected"]=false;}if(S===2){r.class("sapUiCalItemSelStart");A["describedby"]=A["describedby"]+" "+h.sId+"-Start";}else if(S===3){r.class("sapUiCalItemSelEnd");A["describedby"]=A["describedby"]+" "+h.sId+"-End";}else if(S===4){r.class("sapUiCalItemSelBetween");}else if(S===5){r.class("sapUiCalItemSelStart");r.class("sapUiCalItemSelEnd");A["describedby"]=A["describedby"]+" "+h.sId+"-Start";A["describedby"]=A["describedby"]+" "+h.sId+"-End";}q.forEach(function(t){if(t.type!==e.None){if(t.type===e.NonWorking){r.class("sapUiCalItemWeekEnd");N=this._addNonWorkingDayText(A);return;}r.class("sapUiCalItem"+t.type);j=t.type;if(t.tooltip){r.attr('title',t.tooltip);}}}.bind(this));if(!N){if(h.aNonWorkingDays){h.aNonWorkingDays.forEach(function(t){if(D.getDay()===t){this._addNonWorkingDayText(A);}}.bind(this));}else if(D.getDay()===h.iWeekendStart||D.getDay()===h.iWeekendEnd){this._addNonWorkingDayText(A);}}if(((m.getParent()&&m.getParent().getMetadata().getName()==="sap.ui.unified.CalendarOneMonthInterval")||(m.getMetadata().getName()==="sap.ui.unified.calendar.OneMonthDatesRow"))&&m.getStartDate()&&D.getMonth()!==m.getStartDate().getMonth()){r.class("sapUiCalItemOtherMonth");}if(!E){r.class("sapUiCalItemDsbl");A["disabled"]=true;}if(h.aNonWorkingDays){for(i=0;i<h.aNonWorkingDays.length;i++){if(p===h.aNonWorkingDays[i]){r.class("sapUiCalItemWeekEnd");break;}}}else if((p>=h.iWeekendStart&&p<=h.iWeekendEnd)||(h.iWeekendEnd<h.iWeekendStart&&(p>=h.iWeekendStart||p<=h.iWeekendEnd))){r.class("sapUiCalItemWeekEnd");}r.attr("tabindex","-1");r.attr("data-sap-day",y);if(g){A["label"]=A["label"]+h.aWeekDaysWide[p]+" ";}A["label"]=A["label"]+h.oFormatLong.format(D.toUTCJSDate(),true);if(j!==""){c.addCalendarTypeAccInfo(A,j,k);}if(h.sSecondaryCalendarType){A["label"]=A["label"]+" "+m._oFormatSecondaryLong.format(s.toUTCJSDate(),true);}r.accessibilityState(null,A);r.openEnd();if(q[0]){r.openStart("div");r.class("sapUiCalSpecialDate");if(q[0].color){r.style("background-color",q[0].color);}r.openEnd();r.close("div");}r.openStart("span");r.class("sapUiCalItemText");if(!!q[0]&&q[0].color){r.class("sapUiCalItemTextCustomColor");}r.openEnd();if(!B){r.text(D.getDate());}r.close("span");if(g){r.openStart("span");r.class("sapUiCalDayName");r.openEnd();r.text(h.aWeekDays[p]);r.close("span");}if(h.sSecondaryCalendarType){r.openStart("span");r.class("sapUiCalItemSecText");r.openEnd();r.text(s.getDate());r.close("span");}r.close("div");};M._addNonWorkingDayText=function(A){var t=sap.ui.getCore().getLibraryResourceBundle("sap.ui.unified").getText("LEGEND_NON_WORKING_DAY")+" ";A["label"]+=t;return t;};M._renderWeekNumber=function(r,D,h){var w=C.calculateWeekNumber(D.toUTCJSDate(),h.iYear,h.sLocale,h.oLocaleData),i=h.sId+"-WNum-"+w;r.openStart("div",i);r.class("sapUiCalWeekNum");r.accessibilityState(null,{role:"rowheader",labelledby:I.getStaticId("sap.ui.unified","CALENDAR_WEEK")+" "+i});r.openEnd();r.text(w);r.close("div");};return M;},true);
