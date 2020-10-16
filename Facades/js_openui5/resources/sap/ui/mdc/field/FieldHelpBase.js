/*
 * ! OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['sap/ui/mdc/Element','sap/base/Log','sap/base/util/merge','sap/ui/base/SyncPromise','sap/ui/model/FormatException','sap/ui/model/ParseException'],function(E,L,m,S,F,P){"use strict";var a;var l;var b=E.extend("sap.ui.mdc.field.FieldHelpBase",{metadata:{interfaces:["sap.ui.core.PopupInterface"],library:"sap.ui.mdc",properties:{conditions:{type:"object[]",defaultValue:[],byValue:true},delegate:{type:"object",group:"Data",defaultValue:{name:"sap/ui/mdc/field/FieldHelpBaseDelegate"}},filterValue:{type:"string",defaultValue:""},validateInput:{type:"boolean",defaultValue:true}},aggregations:{_popover:{type:"sap.m.Popover",multiple:false,visibility:"hidden"}},events:{select:{parameters:{conditions:{type:"object[]"},add:{type:"boolean"},close:{type:"boolean"}}},navigate:{parameters:{value:{type:"any"},key:{type:"any"},condition:{type:"object"},itemId:{type:"string"}}},dataUpdate:{},disconnect:{},open:{suggestion:{type:"boolean"}},afterClose:{}},defaultProperty:"filterValue"}});b._init=function(){a=undefined;l=undefined;};b.prototype.init=function(){E.prototype.init.apply(this,arguments);this._oTextOrKeyPromises={};};b.prototype.invalidate=function(o){if(o){var p=this.getAggregation("_popover");if(p&&o===p){if(o.bOutput&&!this._bIsBeingDestroyed){var j=this.getParent();if(j){j.invalidate(this);}}return;}}};b.prototype.setFilterValue=function(s){this.setProperty("filterValue",s,true);return this;};b.prototype.connect=function(o){if(this._oField&&this._oField!==o){var p=this.getAggregation("_popover");if(p){p._oPreviousFocus=null;}this.close();this.setFilterValue("");this.setConditions([]);this.fireDisconnect();}this._oField=o;return this;};b.prototype._getField=function(){if(this._oField){return this._oField;}else{return this.getParent();}};b.prototype._getControlForSuggestion=function(){var o=this._getField();if(o.getControlForSuggestion){return o.getControlForSuggestion();}else{return o;}};b.prototype.getFieldPath=function(){var s="";if(this._oField&&this._oField.getFieldPath){s=this._oField.getFieldPath();}return s||"Help";};b.prototype.getDomRef=function(){var p=this.getAggregation("_popover");if(p){return p.getDomRef();}else{return E.prototype.getDomRef.apply(this,arguments);}};b.prototype.getContentId=function(){var p=this.getAggregation("_popover");if(p){var C=p._getAllContent();if(C.length===1){return C[0].getId();}}};b.prototype.getRoleDescription=function(){return null;};b.prototype.open=function(s){var o=this._getField();if(o){var p=this._getPopover();if(p){delete this._bOpen;delete this._bSuggestion;if(!p.isOpen()){if(!this.isFocusInHelp()){p.setInitialFocus(this._getControlForSuggestion());}var O=function(){if(this._bOpenAfterPromise){delete this._bOpenAfterPromise;this.open(s);}}.bind(this);var j=this._fireOpen(!!s,O);if(j){if(p._getAllContent().length>0){p.openBy(this._getControlForSuggestion());}else{this._bOpenIfContent=true;}}else{this._bOpenAfterPromise=true;}}}else{this._bOpen=true;this._bSuggestion=s;}}else{L.warning("FieldHelp not assigned to field -> can not be opened.",this);}};b.prototype.close=function(){var p=this.getAggregation("_popover");if(p&&p.isOpen()){var j=p.oPopup.getOpenState();if(j!=="CLOSED"&&j!=="CLOSING"){this._bClosing=true;p.close();}}else{delete this._bOpen;delete this._bSuggestion;delete this._bOpenIfContent;delete this._bOpenAfterPromise;}this._bReopen=false;};b.prototype.toggleOpen=function(s){var p=this.getAggregation("_popover");if(p){if(p.isOpen()){var j=p.oPopup.getOpenState();if(j!=="CLOSED"&&j!=="CLOSING"){this.close();}else{this._bReopen=true;}}else{this.open(s);}}else if(this._bOpen||this._bOpenIfContent||this._bOpenAfterPromise){delete this._bOpen;delete this._bSuggestion;delete this._bOpenIfContent;delete this._bOpenAfterPromise;}else{this.open(s);}};b.prototype.isOpen=function(C){if(C&&this._bClosing){return false;}var I=false;var p=this.getAggregation("_popover");if(p){I=p.isOpen();}return I;};b.prototype.skipOpening=function(){if(this._bOpenIfContent){delete this._bOpenIfContent;}if(this._bOpenAfterPromise){delete this._bOpenAfterPromise;}};b.prototype._createPopover=function(){var p;if((!a||!l)&&!this._bPopoverRequested){a=sap.ui.require("sap/m/Popover");l=sap.ui.require("sap/m/library");if(!a||!l){sap.ui.require(["sap/m/Popover","sap/m/library"],_.bind(this));this._bPopoverRequested=true;}}if(a&&l&&!this._bPopoverRequested){p=new a(this.getId()+"-pop",{placement:l.PlacementType.VerticalPreferredBottom,showHeader:false,showArrow:false,afterOpen:this._handleAfterOpen.bind(this),afterClose:this._handleAfterClose.bind(this)});p.isPopupAdaptationAllowed=function(){return false;};this.setAggregation("_popover",p,true);if(this._oContent){this._setContent(this._oContent);}}return p;};function _(p,j){a=p;l=j;this._bPopoverRequested=false;if(!this._bIsBeingDestroyed){this._createPopover();if(this._bOpen){this.open(this._bSuggestion);}}}b.prototype._getPopover=function(){var p=this.getAggregation("_popover");if(!p){p=this._createPopover();}return p;};b.prototype._handleAfterOpen=function(o){};b.prototype._handleAfterClose=function(o){this._bClosing=false;if(this._bReopen){this._bReopen=false;this.open();}this.fireAfterClose();};b.prototype.openByTyping=function(){return false;};b.prototype.openByClick=function(){return false;};b.prototype.isFocusInHelp=function(){return!this.openByTyping();};b.prototype.navigate=function(s){};b.prototype.getTextForKey=function(k,I,o,B){return c.call(this,k,B,I,o,false);};function c(k,B,I,o,n){return g.call(this,true,k,B,I,o,n);}b.prototype.getKeyForText=function(t,B){return d.call(this,t,B,false);};function d(t,B,n){return g.call(this,false,t,B,undefined,undefined,n);}b.prototype._getTextOrKey=function(v,k,B,I,o,n){if(k){return"";}else{return undefined;}};b.prototype._isTextOrKeyRequestSupported=function(){return false;};b.prototype.isValidationSupported=function(){return true;};b.prototype.getItemForValue=function(v,p,I,o,B,C,j,k){return S.resolve().then(function(){return e.call(this,v,p,I,o,B,C&&j,j,k,true,true);}.bind(this)).then(function(r){if(!r&&this._isTextOrKeyRequestSupported()){return e.call(this,v,p,I,o,B,C&&j,j,k,true,false);}else{return r;}}.bind(this)).catch(function(n){f.call(this,n,!this._isTextOrKeyRequestSupported());if(this._isTextOrKeyRequestSupported()){var r=e.call(this,v,p,I,o,B,C&&j,j,k,true,false);if(!r){throw n;}return r;}}.bind(this)).unwrap();};function e(v,p,I,o,B,C,j,k,n,N){return S.resolve().then(function(){if(C){if(j){return c.call(this,p,B,I,o,N);}}else if(k){return d.call(this,v,B,N);}}.bind(this)).then(function(r){if(r){if(typeof r==="object"){return r;}else if(C){return{key:p,description:r};}else{return{key:r,description:v};}}else if(n&&((C&&k)||(!C&&j))){return e.call(this,v,p,I,o,B,!C,j,k,false,N);}else{return undefined;}}.bind(this)).catch(function(q){f.call(this,q,q&&q._bSecondCheck);if(n&&((C&&k)||(!C&&j))){var r=e.call(this,v,p,I,o,B,!C,j,k,false,N);if(!r){throw q;}return r;}else{q._bSecondCheck=true;throw q;}}.bind(this)).unwrap();}function f(o,t){if(o){if(t){throw o;}if(!(o instanceof P)&&!(o instanceof F)){throw o;}if(o._bNotUnique){throw o;}}}b.prototype.isUsableForValidation=function(){return true;};b.prototype.onFieldChange=function(){};b.prototype._setContent=function(C){var p=this.getAggregation("_popover");if(p){p.removeAllContent();p.addContent(C);this._oContent=undefined;if(this._bOpenIfContent){var o=this._getField();if(o){p.openBy(this._getControlForSuggestion());}this._bOpenIfContent=false;}}else{this._oContent=C;}return this;};b.prototype.getIcon=function(){return"sap-icon://slim-arrow-down";};b.prototype.getUIArea=function(){var u=E.prototype.getUIArea.apply(this,arguments);if(!u){if(this._oField){u=this._oField.getUIArea();}}return u;};b.prototype.getScrollDelegate=function(){var p=this.getAggregation("_popover");if(p){return p.getScrollDelegate();}else{return undefined;}};b.prototype._fireOpen=function(s,C){var j=this._callContentRequest(s,C);if(j){this.fireOpen({suggestion:s});}return j;};b.prototype._callContentRequest=function(s,C){if(!this._bNoContentRequest){if(this._oContentRequestPromise){return false;}this.initControlDelegate();if(this.bDelegateInitialized){var p=this.getControlDelegate().contentRequest(this.getPayload(),this,s);if(p instanceof Promise){this._oContentRequestPromise=p;p.then(function(){this._oContentRequestPromise=undefined;this._bNoContentRequest=true;C();this._bNoContentRequest=false;}.bind(this));return false;}}else{this.awaitControlDelegate().then(function(){if(this._callContentRequest(s,C)){C();}}.bind(this));return false;}}return true;};function g(k,v,B,I,o,n){var s=JSON.stringify(I);var C=B&&B.getPath();if(this._oTextOrKeyPromises[k]&&this._oTextOrKeyPromises[k][v]&&this._oTextOrKeyPromises[k][v][s]&&this._oTextOrKeyPromises[k][v][s][C]){return this._oTextOrKeyPromises[k][v][s][C].promise;}var j=function(){h.call(this);}.bind(this);var p=this._callContentRequest(true,j);if(!p){if(!this._oTextOrKeyPromises[k]){this._oTextOrKeyPromises[k]={};}if(!this._oTextOrKeyPromises[k][v]){this._oTextOrKeyPromises[k][v]={};}if(!this._oTextOrKeyPromises[k][v][s]){this._oTextOrKeyPromises[k][v][s]={};}if(!this._oTextOrKeyPromises[k][v][s][C]){this._oTextOrKeyPromises[k][v][s][C]={};}this._oTextOrKeyPromises[k][v][s][C].promise=new Promise(function(r,R){this._oTextOrKeyPromises[k][v][s][C].resolve=r;this._oTextOrKeyPromises[k][v][s][C].reject=R;this._oTextOrKeyPromises[k][v][s][C].key=k;this._oTextOrKeyPromises[k][v][s][C].value=v;this._oTextOrKeyPromises[k][v][s][C].inParameters=I?m({},I):undefined;this._oTextOrKeyPromises[k][v][s][C].outParameters=o?m({},o):undefined;this._oTextOrKeyPromises[k][v][s][C].bindingContext=B;this._oTextOrKeyPromises[k][v][s][C].noRequest=n;}.bind(this));return this._oTextOrKeyPromises[k][v][s][C].promise;}return this._getTextOrKey(v,k,B,I,o,n);}function h(){for(var k in this._oTextOrKeyPromises){for(var v in this._oTextOrKeyPromises[k]){for(var I in this._oTextOrKeyPromises[k][v]){for(var C in this._oTextOrKeyPromises[k][v][I]){i.call(this,this._oTextOrKeyPromises[k][v][I][C]);delete this._oTextOrKeyPromises[k][v][I][C];}}}}}function i(t){var M=t.value;var j=t.key;var I=t.inParameters;var o=t.outParameters;var B=t.bindingContext;var n=t.noRequest;var r=t.resolve;var R=t.reject;S.resolve().then(function(){return this._getTextOrKey(M,j,B,I,o,n);}.bind(this)).then(function(v){r(v);}).catch(function(k){R(k);}).unwrap();}return b;});
