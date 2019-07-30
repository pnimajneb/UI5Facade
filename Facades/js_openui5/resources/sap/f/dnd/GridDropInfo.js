/*!
 * OpenUI5
 * (c) Copyright 2009-2019 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/core/library","sap/ui/core/dnd/DropInfo","sap/f/dnd/GridDragOver","sap/base/Log","sap/ui/Device"],function(c,D,G,L,a){"use strict";var b=D.extend("sap.f.dnd.GridDropInfo",{metadata:{library:"sap.ui.core",interfaces:["sap.ui.core.dnd.IDropInfo"]}});b.prototype.isDroppable=function(C,e){if(!this._shouldEnhance()){return D.prototype.isDroppable.apply(this,arguments);}if(!this.getEnabled()){return false;}if(!C||!e){return false;}var d=this.getDropTarget();if(!d){return false;}var A=d.getDomRefForSetting(this.getTargetAggregation());if(A&&A.contains(e.target)){e.setMark("DragWithin",this.getTargetAggregation());return true;}if(!A&&d===C){return true;}return false;};b.prototype.fireDragEnter=function(e){if(!this._shouldEnhance()){return D.prototype.fireDragEnter.apply(this,arguments);}if(!e||!e.dragSession||!e.dragSession.getDragControl()){return null;}this._hideDefaultIndicator(e);var d=this._suggestDropPosition(e);return this.fireEvent("dragEnter",{dragSession:e.dragSession,browserEvent:e.originalEvent,target:d?d.targetControl:null},true);};b.prototype.fireDragOver=function(e){if(!this._shouldEnhance()){return D.prototype.fireDragOver.apply(this,arguments);}if(!e||!e.dragSession||!e.dragSession.getDragControl()){return null;}this._hideDefaultIndicator(e);var d=this._suggestDropPosition(e);if(d&&e.dragSession){e.dragSession.setDropControl(d.targetControl);}return this.fireEvent("dragOver",{dragSession:e.dragSession,browserEvent:e.originalEvent,target:d?d.targetControl:null,dropPosition:d?d.position:null});};b.prototype.fireDrop=function(e){if(!this._shouldEnhance()){return D.prototype.fireDrop.apply(this,arguments);}if(!e||!e.dragSession||!e.dragSession.getDragControl()){return null;}var d=e.dragSession,g=G.getInstance(),m;g.setCurrentContext(d.getDragControl(),this.getDropTarget(),this.getTargetAggregation());m=g.getSuggestedDropPosition();this.fireEvent("drop",{dragSession:e.dragSession,browserEvent:e.originalEvent,dropPosition:m?m.position:null,draggedControl:d.getDragControl(),droppedControl:m?m.targetControl:null});g.endDrag();};b.prototype._shouldEnhance=function(){if(this._bShouldEnhance===undefined){if(!this.getParent().isA("sap.f.dnd.IGridDroppable")){L.error("The control which uses 'sap.f.dnd.GridDropInfo' has to implement 'sap.f.dnd.IGridDroppable'.","sap.f.dnd.GridDropInfo");this._bShouldEnhance=false;return this._bShouldEnhance;}if(a.browser.msie){this._bShouldEnhance=false;return this._bShouldEnhance;}this._bShouldEnhance=this.getDropPosition()===c.dnd.DropPosition.Between&&this.getDropLayout()===c.dnd.DropLayout.Horizontal;}return this._bShouldEnhance;};b.prototype._suggestDropPosition=function(d){if(!d.dragSession||!d.dragSession.getDragControl()){return null;}var g=G.getInstance();g.setCurrentContext(d.dragSession.getDragControl(),this.getDropTarget(),this.getTargetAggregation());g.handleDragOver(d);return g.getSuggestedDropPosition();};b.prototype._hideDefaultIndicator=function(d){d.dragSession.setIndicatorConfig({visibility:"hidden",position:"relative"});};return b;},true);
