/*
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['./TablePersoDialog','sap/ui/base/ManagedObject','sap/ui/base/ManagedObjectRegistry',"sap/ui/core/syncStyleClass","sap/base/Log","sap/ui/thirdparty/jquery"],function(T,M,d,s,L,q){"use strict";var e=M.extend("sap.m.TablePersoController",{constructor:function(i,S){M.apply(this,arguments);},metadata:{properties:{"contentWidth":{type:"sap.ui.core.CSSSize"},"contentHeight":{type:"sap.ui.core.CSSSize",since:"1.22"},"componentName":{type:"string",since:"1.20.2"},"hasGrouping":{type:"boolean",defaultValue:false,since:"1.22"},"showSelectAll":{type:"boolean",defaultValue:true,since:"1.22"},"showResetAll":{type:"boolean",defaultValue:true,since:"1.22"}},aggregations:{"_tablePersoDialog":{type:"sap.m.TablePersoDialog",multiple:false,visibility:"hidden"},"persoService":{type:"Object",multiple:false}},associations:{"table":{type:"sap.m.Table",multiple:false},"tables":{type:"sap.m.Table",multiple:true}},events:{personalizationsDone:{}},library:"sap.m"}});d.apply(e,{onDuplicate:function(i,o,n){if(o._sapui_candidateForDestroy){L.debug("destroying dangling template "+o+" when creating new object with same ID");o.destroy();}else{var m="adding TablePersoController with duplicate id '"+i+"'";if(sap.ui.getCore().getConfiguration().getNoDuplicateIds()){L.error(m);throw new Error("Error: "+m);}else{L.warning(m);}}}});e.prototype.init=function(){this._schemaProperty="_persoSchemaVersion";this._schemaVersion="1.0";this._oPersonalizations=null;this._mDelegateMap={};this._mTablePersMap={};this._mInitialTableStateMap={};this._triggersPersDoneEvent=true;};e.prototype.exit=function(){this._callFunctionForAllTables(q.proxy(function(t){t.removeDelegate(this._mDelegateMap[t]);t._hasTablePersoController=function(){return false;};},this));delete this._mDelegateMap;delete this._mTablePersMap;delete this._mInitialTableStateMap;};e.prototype.activate=function(){this._callFunctionForAllTables(this._rememberInitialTableStates);this._callFunctionForAllTables(this._createAndAddDelegateForTable);return this;};e.prototype.getTablePersoDialog=function(){return this.getAggregation("_tablePersoDialog");};e.prototype.applyPersonalizations=function(t){var r=this.getPersoService().getPersData();var a=this;r.done(function(p){if(p){a._adjustTable(p,t);}});r.fail(function(){L.error("Problem reading persisted personalization data.");});};e.prototype._createAndAddDelegateForTable=function(t){if(!this._mDelegateMap[t]){var o={onBeforeRendering:function(){this.applyPersonalizations(t);if(!this.getAggregation("_tablePersoDialog")){this._createTablePersoDialog(t);}}.bind(this)};t.addDelegate(o);o.onBeforeRendering();this._mDelegateMap[t]=o;var a=this;t._hasTablePersoController=function(){return!!a._mDelegateMap[this];};}};e.prototype._createTablePersoDialog=function(t){var o=new T(t.getId()+"-PersoDialog",{persoDialogFor:t,persoMap:this._getPersoColumnMap(t),columnInfoCallback:this._tableColumnInfo.bind(this),initialColumnState:this._mInitialTableStateMap[t],contentWidth:this.getContentWidth(),contentHeight:this.getContentHeight(),hasGrouping:this.getHasGrouping(),showSelectAll:this.getShowSelectAll(),showResetAll:this.getShowResetAll()});this.setAggregation("_tablePersoDialog",o);o.attachConfirm(q.proxy(function(){this._oPersonalizations=o.retrievePersonalizations();this._callFunctionForAllTables(this._personalizeTable);this.savePersonalizations();this.firePersonalizationsDone();},this));};e.prototype._adjustTable=function(D,t){if(D&&D.hasOwnProperty(this._schemaProperty)&&D[this._schemaProperty]===this._schemaVersion){this._oPersonalizations=D;if(t){this._personalizeTable(t);}else{this._callFunctionForAllTables(this._personalizeTable);}}};e.prototype._personalizeTable=function(t){var p=this._getPersoColumnMap(t);if(!!p&&!!this._oPersonalizations){var D=false;for(var c=0,a=this._oPersonalizations.aColumns.length;c<a;c++){var n=this._oPersonalizations.aColumns[c];var o=p[n.id];if(!o){o=sap.ui.getCore().byId(n.id);if(o){L.info("Migrating personalization persistence id of column "+n.id);n.id=p[o];D=true;}}if(o){o.setVisible(n.visible);o.setOrder(n.order);}else{L.warning("Personalization could not be applied to column "+n.id+" - not found!");}}if(D){this.savePersonalizations();}t.invalidate();}};e.prototype.savePersonalizations=function(){var b=this._oPersonalizations;b[this._schemaProperty]=this._schemaVersion;var w=this.getPersoService().setPersData(b);w.done(function(){});w.fail(function(){L.error("Problem persisting personalization data.");});};e.prototype.refresh=function(){var r=function(o){this._mTablePersMap={};o.invalidate();};this._callFunctionForAllTables(r);var t=this.getAggregation("_tablePersoDialog");if(t){t.setPersoMap(this._getPersoColumnMap(sap.ui.getCore().byId(t.getPersoDialogFor())));}};e.prototype.openDialog=function(){var t=this.getAggregation("_tablePersoDialog");if(t){s("sapUiSizeCompact",t.getPersoDialogFor(),t._oDialog);t.open();}else{L.warning("sap.m.TablePersoController: trying to open TablePersoDialog before TablePersoService has been activated.");}};e.prototype.setContentWidth=function(w){this.setProperty("contentWidth",w,true);var t=this.getAggregation("_tablePersoDialog");if(t){t.setContentWidth(w);}return this;};e.prototype.setContentHeight=function(h){this.setProperty("contentHeight",h,true);var t=this.getAggregation("_tablePersoDialog");if(t){t.setContentHeight(h);}return this;};e.prototype.setHasGrouping=function(h){this.setProperty("hasGrouping",h,true);var t=this.getAggregation("_tablePersoDialog");if(t){t.setHasGrouping(h);}return this;};e.prototype.setShowSelectAll=function(S){this.setProperty("showSelectAll",S,true);var t=this.getAggregation("_tablePersoDialog");if(t){t.setShowSelectAll(S);}return this;};e.prototype.setShowResetAll=function(S){this.setProperty("showResetAll",S,true);var t=this.getAggregation("_tablePersoDialog");if(t){t.setShowResetAll(S);}return this;};e.prototype.setComponentName=function(c){this.setProperty("componentName",c,true);return this;};e.prototype._getMyComponentName=function(c){if(this.getComponentName()){return this.getComponentName();}if(c===null){return"empty_component";}var m=c.getMetadata();if(c.getMetadata().getStereotype()==="component"){return m._sComponentName;}return this._getMyComponentName(c.getParent());};e.prototype._callFunctionForAllTables=function(t){var o=sap.ui.getCore().byId(this.getAssociation("table"));if(o){t.call(this,o);}var a=this.getAssociation("tables");if(a){for(var i=0,l=this.getAssociation("tables").length;i<l;i++){o=sap.ui.getCore().byId(this.getAssociation("tables")[i]);t.call(this,o);}}};e.prototype._isStatic=function(i){var u=sap.ui.getCore().getConfiguration().getUIDPrefix();var r=new RegExp("^"+u);return!r.test(i);};e.prototype._getPersoColumnMap=function(t){var r=this._mTablePersMap[t];if(!r){r={};var E=function(i){var l=i.lastIndexOf("-");return i.substring(l+1);};var a=E.call(this,t.getId());if(!this._isStatic(a)){L.error("Table "+t.getId()+" must have a static id suffix. Otherwise personalization can not be persisted.");r=null;return null;}var n;var c=this._getMyComponentName(t);var b=this;t.getColumns().forEach(function(N){if(r){var f=N.getId();var g=E.call(b,f);if(!b._isStatic(g)){L.error("Suffix "+g+" of table column "+f+" must be static. Otherwise personalization can not be persisted for its table.");r=null;return null;}n=c+"-"+a+"-"+g;r[N]=n;r[n]=N;}});this._mTablePersMap[t]=r;}return r;};e.prototype._rememberInitialTableStates=function(t){this._mInitialTableStateMap[t]=this._tableColumnInfo(t,this._getPersoColumnMap(t));};e.prototype._tableColumnInfo=function(t,p){if(p){var c=t.getColumns(),C=[],P=this.getPersoService();c.forEach(function(o){var a=null;if(P.getCaption){a=P.getCaption(o);}var g=null;if(P.getGroup){g=P.getGroup(o);}if(!a){var b=o.getHeader();if(b.getText&&b.getText()){a=b.getText();}else if(b.getTitle&&b.getTitle()){a=b.getTitle();}if(!a){a=o.getId();L.warning("Please 'getCaption' callback implentation in your TablePersoProvider for column "+o+". Table personalization uses column id as fallback value.");}}C.push({text:a,order:o.getOrder(),visible:o.getVisible(),id:p[o],group:g});});C.sort(function(a,b){return a.order-b.order;});return C;}return null;};return e;});
