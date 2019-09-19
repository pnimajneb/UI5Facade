/*!
 * OpenUI5
 * (c) Copyright 2009-2019 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/core/UIComponent","sap/ui/core/ComponentContainer","sap/ui/core/mvc/XMLView","sap/ui/rta/command/CommandFactory","sap/ui/dt/DesignTime","sap/ui/dt/DesignTimeStatus","sap/ui/dt/OverlayRegistry","sap/ui/fl/ChangePersistence","sap/ui/model/Model","sap/ui/fl/registry/Settings","sap/ui/rta/ControlTreeModifier","sap/ui/fl/write/api/ChangesWriteAPI","sap/ui/fl/write/api/PersistenceWriteAPI","sap/ui/fl/Cache","sap/ui/thirdparty/sinon-4","sap/ui/fl/library"],function(U,C,X,a,D,b,O,c,M,S,d,e,P,f,s){"use strict";var g=function(m,o){if(g._only&&(m.indexOf(g._only)<0)){return;}if(typeof o.xmlView==="string"){o.xmlView={viewContent:o.xmlView};}var h=s.sandbox.create();o.before=o.before||function(){};o.after=o.after||function(){};QUnit.module(m,function(){QUnit.test("When using the 'controlEnablingCheck' function to test if your control is ready for UI adaptation at runtime",function(q){q.ok(o.afterAction,"then you implement a function to check if your action has been successful: See the afterAction parameter.");q.ok(o.afterUndo,"then you implement a function to check if the undo has been successful: See the afterUndo parameter.");q.ok(o.afterRedo,"then you implement a function to check if the redo has been successful: See the afterRedo parameter.");q.ok(o.xmlView,"then you provide an XML view to test on: See the.xmlView parameter.");var x=new DOMParser().parseFromString(o.xmlView.viewContent,"application/xml").documentElement;q.ok(x.tagName.match("View$"),"then you use the sap.ui.core.mvc View tag as the first tag in your view");q.ok(o.action,"then you provide an action: See the action parameter.");q.ok(o.action.name,"then you provide an action name: See the action.name parameter.");q.ok(o.action.controlId,"then you provide the id of the control to operate the action on: See the action.controlId.");});});var i="sap.ui.rta.control.enabling.comp";var j=false;var A=true;var k=U.extend(i,{metadata:{manifest:{"sap.app":{id:i,type:"application"},getEntry:function(){return{type:"application"};}}},createContent:function(){var v=Object.assign({},o.xmlView);v.id=this.createId("view");if(v.async===undefined){v.async=this.getComponentData().async;}var V=new X(v);return V;}});function l(q){this.oUiComponent=new k({id:"comp",componentData:{async:q}});this.oUiComponentContainer=new C({component:this.oUiComponent});this.oUiComponentContainer.placeAt(o.placeAt||"qunit-fixture");this.oView=this.oUiComponent.getRootControl();if(o.model instanceof M){this.oView.setModel(o.model);}sap.ui.getCore().applyChanges();return Promise.all([this.oView.loaded(),o.model&&o.model.getMetaModel()&&o.model.getMetaModel().loaded()]);}function n(q){this.oControl=this.oView.byId(o.action.controlId);return this.oControl.getMetadata().loadDesignTime(this.oControl).then(function(){var r;if(o.action.parameter){if(typeof o.action.parameter==="function"){r=o.action.parameter(this.oView);}else{r=o.action.parameter;}}else{r={};}sap.ui.getCore().applyChanges();return new Promise(function(t){this.oDesignTime=new D({rootElements:[this.oView]});this.oDesignTime.attachEventOnce("synced",function(){this.oControlOverlay=O.getOverlay(this.oControl);var u=new a({flexSettings:{layer:o.layer||"CUSTOMER"}});var E=this.oControlOverlay.getDesignTimeMetadata();if(o.action.name==="move"){var v=O.getOverlay(r.movedElements[0].element);var R=v.getRelevantContainer();this.oControl=R;E=v.getParentAggregationOverlay().getDesignTimeMetadata();}else if(o.action.name==="addODataProperty"){var w=E.getActionDataFromAggregations("addODataProperty",this.oControl);q.equal(w.length,1,"there should be only one aggregation with the possibility to do addODataProperty action");var x=this.oControlOverlay.getAggregationOverlay(w[0].aggregation);E=x.getDesignTimeMetadata();}u.getCommandFor(this.oControl,o.action.name,r,E).then(function(y){this.oCommand=y;q.ok(y,"then the registration for action to change type, the registration for change and control type to change handler is available and "+o.action.name+" is a valid action");t();}.bind(this)).catch(function(y){throw new Error(y);});}.bind(this));}.bind(this));}.bind(this));}function p(q){var r=q.getPreparedChange();if(q.getAppComponent){return P.remove({change:r,selector:q.getAppComponent()});}}if(!o.jsOnly){QUnit.module(m+" on async views",{before:function(q){this.hookContext={};return o.before.call(this.hookContext,q);},after:function(q){return o.after.call(this.hookContext,q);},beforeEach:function(){h.stub(S,"getInstance").resolves({_oSettings:{recordUndo:false}});},afterEach:function(){this.oUiComponentContainer.destroy();this.oDesignTime.destroy();this.oCommand.destroy();h.restore();}},function(){QUnit.test("When applying the change directly on the XMLView",function(q){var r=[];h.stub(c.prototype,"getChangesForComponent").resolves(r);h.stub(c.prototype,"getCacheKey").resolves("etag-123");return l.call(this,j).then(function(){return n.call(this,q);}.bind(this)).then(function(){var t=this.oCommand.getPreparedChange();r.push(t);this.oUiComponentContainer.destroy();return l.call(this,A);}.bind(this)).then(function(t){var v=t[0];return o.afterAction(this.oUiComponent,v,q);}.bind(this));});QUnit.test("When executing on XML and reverting the change in JS (e.g. variant switch)",function(q){var r=[];h.stub(c.prototype,"getChangesForComponent").resolves(r);h.stub(c.prototype,"getCacheKey").resolves("etag-123");return l.call(this,j).then(function(){return n.call(this,q);}.bind(this)).then(function(){var t=this.oCommand.getPreparedChange();r.push(t);this.oUiComponentContainer.destroy();return l.call(this,A);}.bind(this)).then(function(){return this.oCommand.undo();}.bind(this)).then(function(){return p(this.oCommand);}.bind(this)).then(function(){sap.ui.getCore().applyChanges();o.afterUndo(this.oUiComponent,this.oView,q);}.bind(this));});QUnit.test("When executing on XML, reverting the change in JS (e.g. variant switch) and applying again",function(q){var r=[];h.stub(c.prototype,"getChangesForComponent").resolves(r);h.stub(c.prototype,"getCacheKey").resolves("etag-123");return l.call(this,j).then(function(){return n.call(this,q);}.bind(this)).then(function(){var t=this.oCommand.getPreparedChange();r.push(t);this.oUiComponentContainer.destroy();return l.call(this,A);}.bind(this)).then(function(){return this.oCommand.undo();}.bind(this)).then(function(){return p(this.oCommand);}.bind(this)).then(function(){return this.oCommand.execute();}.bind(this)).then(function(){sap.ui.getCore().applyChanges();o.afterRedo(this.oUiComponent,this.oView,q);}.bind(this));});});}QUnit.module(m,{before:function(q){this.hookContext={};return o.before.call(this.hookContext,q);},after:function(q){return o.after.call(this.hookContext,q);},beforeEach:function(q){h.stub(c.prototype,"getChangesForComponent").returns(Promise.resolve([]));h.stub(c.prototype,"getCacheKey").returns(f.NOTAG);h.stub(S,"getInstance").returns(Promise.resolve({_oSettings:{recordUndo:false}}));return l.call(this,j).then(function(){return n.call(this,q);}.bind(this));},afterEach:function(){this.oDesignTime.destroy();this.oUiComponentContainer.destroy();this.oCommand.destroy();h.restore();}},function(){QUnit.test("When executing the underlying command on the control at runtime",function(q){return this.oCommand.execute().then(function(){return this.oDesignTime.getStatus()!==b.SYNCED?(new Promise(function(r){this.oDesignTime.attachEventOnce("synced",r);}.bind(this))):Promise.resolve();}.bind(this)).then(function(){sap.ui.getCore().applyChanges();return o.afterAction(this.oUiComponent,this.oView,q);}.bind(this));});QUnit.test("When executing and undoing the command",function(q){return this.oCommand.execute().then(function(){return this.oDesignTime.getStatus()!==b.SYNCED?(new Promise(function(r){this.oDesignTime.attachEventOnce("synced",r);}.bind(this))):Promise.resolve();}.bind(this)).then(this.oCommand.undo.bind(this.oCommand)).then(function(){return p(this.oCommand);}.bind(this)).then(function(){sap.ui.getCore().applyChanges();return o.afterUndo(this.oUiComponent,this.oView,q);}.bind(this));});QUnit.test("When executing, undoing and redoing the command",function(q){return this.oCommand.execute().then(function(){return this.oDesignTime.getStatus()!==b.SYNCED?(new Promise(function(r){this.oDesignTime.attachEventOnce("synced",r);}.bind(this))):Promise.resolve();}.bind(this)).then(this.oCommand.undo.bind(this.oCommand)).then(function(){return p(this.oCommand);}.bind(this)).then(this.oCommand.execute.bind(this.oCommand)).then(function(){sap.ui.getCore().applyChanges();return o.afterRedo(this.oUiComponent,this.oView,q);}.bind(this));});});};g.skip=function(){};g.only=function(m){g._only=m;};return g;});