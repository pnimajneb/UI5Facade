/*
 * ! OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/m/Button","sap/m/ButtonRenderer","sap/ui/base/ManagedObjectObserver"],function(B,a,M){"use strict";var R,L,b,S,c,I,D,r;var C=B.extend("sap.ui.mdc.chart.ChartTypeButton",{constructor:function(o){this.oChartModel=o.getManagedObjectModel();var s={type:"Transparent",press:function(e){this.displayChartTypes(e.getSource(),o);}.bind(this),id:o.getId()+"-btnChartType",icon:'{$chart>/getTypeInfo/icon}',tooltip:'{$chart>/getTypeInfo/text}'};this.oChart=o;B.apply(this,[s]);this.setModel(this.oChartModel,"$chart");this._oObserver=new M(function(){this.oChartModel.checkUpdate(true);}.bind(this));this._oObserver.observe(this.oChart,{aggregations:["items"],properties:["chartType"]});},renderer:a.render});C.mMatchingIcon={"bar":"sap-icon://horizontal-bar-chart","bullet":"sap-icon://horizontal-bullet-chart","bubble":"sap-icon://bubble-chart","column":"sap-icon://vertical-bar-chart","combination":"sap-icon://business-objects-experience","dual_bar":"sap-icon://horizontal-bar-chart","dual_column":"sap-icon://vertical-bar-chart","dual_combination":"sap-icon://business-objects-experience","dual_horizontal_combination":"sap-icon://business-objects-experience","dual_horizontal_stacked_combination":"sap-icon://business-objects-experience","dual_line":"sap-icon://line-chart","dual_stacked_bar":"sap-icon://full-stacked-chart","dual_stacked_column":"sap-icon://vertical-stacked-chart","dual_stacked_combination":"sap-icon://business-objects-experience","donut":"sap-icon://donut-chart","heatmap":"sap-icon://heatmap-chart","horizontal_stacked_combination":"sap-icon://business-objects-experience","line":"sap-icon://line-chart","pie":"sap-icon://pie-chart","scatter":"sap-icon://scatter-chart","stacked_bar":"sap-icon://full-stacked-chart","stacked_column":"sap-icon://vertical-stacked-chart","stacked_combination":"sap-icon://business-objects-experience","treemap":"sap-icon://Chart-Tree-Map","vertical_bullet":"sap-icon://vertical-bullet-chart","100_dual_stacked_bar":"sap-icon://full-stacked-chart","100_dual_stacked_column":"sap-icon://vertical-stacked-chart","100_stacked_bar":"sap-icon://full-stacked-chart","100_stacked_column":"sap-icon://full-stacked-column-chart","waterfall":"sap-icon://vertical-waterfall-chart","horizontal_waterfall":"sap-icon://horizontal-waterfall-chart"};C.prototype.displayChartTypes=function(o,d){if(!d||!o){return;}if(this.oPopover){return this.oPopover.openBy(o);}if(!this.oReadyPromise){this.oReadyPromise=new Promise(function(e){if(R){e(true);}else{sap.ui.require(["sap/m/ResponsivePopover","sap/m/List","sap/m/Bar","sap/m/SearchField","sap/m/StandardListItem","sap/ui/core/InvisibleText","sap/ui/Device"],function(f,g,h,i,j,k,l){R=f;L=g;b=h;S=i;c=j;I=k;D=l;if(!r){sap.ui.getCore().getLibraryResourceBundle("sap.ui.mdc",true).then(function(m){r=m;e(true);});}else{e(true);}});}});}this.oReadyPromise.then(function(){this.oPopover=this._createPopover(o,d);return this.oPopover.openBy(o);}.bind(this));};C.prototype._createPopover=function(o,d){var i=new c({title:"{$chart>text}",icon:"{$chart>icon}",selected:"{$chart>selected}"});var l=new L({mode:"SingleSelectMaster",items:{path:"$chart>/getAvailableChartTypes",template:i},selectionChange:function(E){if(E&&E.mParameters&&E.mParameters.listItem){var g=E.mParameters.listItem.getBinding("title");if(g){var h=g.getContext();if(h){var O=h.getObject();if(O&&O.key){sap.ui.require(["sap/ui/mdc/p13n/FlexUtil","sap/ui/mdc/flexibility/Chart.flexibility"],function(F,j){var k=[];k.push(j["setChartType"].changeHandler.createChange({control:d,chartType:O.key}));F.handleChanges(k,d);});}}}}p.close();}});var s=new b();var e=new S({placeholder:r.getText("chart.CHART_TYPE_SEARCH")});e.attachLiveChange(function(E){this._triggerSearchInPopover(E,l);});s.addContentRight(e);var p=new R({placement:"Bottom",subHeader:s,contentWidth:"25rem"});p.setModel(this.oChartModel,"$chart");if(D.system.desktop){var f=new I({text:r.getText("chart.CHART_TYPELIST_TEXT")});p.setShowHeader(false);p.addContent(f);p.addAriaLabelledBy(f);}else{p.setTitle(r.getText("chart.CHART_TYPELIST_TEXT"));}p.addContent(l);if(l.getItems().length<7){s.setVisible(false);}return p;};C.prototype.exit=function(){B.prototype.exit.apply(this,arguments);if(this.oPopover){this.oPopover.destroy();this.oPopover=null;}};return C;},true);
