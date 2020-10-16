/*!
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/thirdparty/jquery","sap/ui/base/ManagedObject","sap/ui/dt/ElementUtil","sap/ui/dt/DOMUtil","sap/base/util/merge","sap/base/util/ObjectPath","sap/base/util/includes"],function(q,M,E,D,m,O,i){"use strict";function e(A,o){if(typeof(A)==="function"){A=A(o);}if(typeof(A)==="string"){return{changeType:A};}return A;}var a=M.extend("sap.ui.dt.DesignTimeMetadata",{metadata:{library:"sap.ui.dt",properties:{data:{type:"any",defaultValue:{}}}}});a.prototype.setData=function(d){this.setProperty("data",m({},this.getDefaultData(),d));return this;};a.prototype.getDefaultData=function(){return{ignore:false,domRef:undefined};};a.prototype.isIgnored=function(o){var I=this.getData().ignore;if(!I||(I&&typeof I==="function"&&!I(o))){return false;}return true;};a.prototype.markedAsNotAdaptable=function(){var A=this.getData().actions;return A==="not-adaptable";};a.prototype.getDomRef=function(){return this.getData().domRef;};a.prototype.getAssociatedDomRef=function(o,d,A){if(o){var b=E.getDomRef(o);var c=[];c.push(o);if(A){c.push(A);}if(typeof(d)==="function"){var r=d.apply(null,c);return r?q(r):r;}else if(b&&typeof(d)==="string"){return D.getDomRefForCSSSelector(b,d);}}};a.prototype.getAction=function(A,o,s){var d=this.getData();var b=["actions",A];if(s){b.push(s);}return e(O.get(b,d),o);};a.prototype.getLibraryText=function(o,k,A){var b=o.getMetadata();return this._lookForLibraryTextInHierarchy(b,k,A);};a.prototype._lookForLibraryTextInHierarchy=function(o,k,A){var l;var p;var r;l=o.getLibraryName();r=this._getTextFromLibrary(l,k,A);if(!r){p=o.getParent();if(p&&p.getLibraryName){r=this._lookForLibraryTextInHierarchy(p,k,A);}else{r=k;}}return r;};a.prototype._getTextFromLibrary=function(l,k,A){var L=sap.ui.getCore().getLibraryResourceBundle(l+".designtime");if(L&&L.hasText(k)){return L.getText(k,A);}L=sap.ui.getCore().getLibraryResourceBundle(l);if(L&&L.hasText(k)){return L.getText(k,A);}};a.prototype.getLabel=function(){var l=this.getData().getLabel;return typeof l==="function"?l.apply(this,arguments):undefined;};a.prototype.getControllerExtensionTemplate=function(){return this.getData().controllerExtensionTemplate;};a.prototype.getResponsibleElement=function(o){var d=this.getData();var r=O.get(["actions","getResponsibleElement"],d);if(r){return r(o);}};a.prototype.isResponsibleActionAvailable=function(A){var d=this.getData();var b=O.get(["actions","actionsFromResponsibleElement"],d);if(b){return i(b,A);}return false;};return a;});
