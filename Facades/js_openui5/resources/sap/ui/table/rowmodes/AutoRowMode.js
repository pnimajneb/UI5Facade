/*
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["../library","../utils/TableUtils","./RowMode","sap/ui/Device","sap/base/Log"],function(l,T,R,D,L){"use strict";var A=R.extend("sap.ui.table.rowmodes.AutoRowMode",{metadata:{library:"sap.ui.table",properties:{rowContentHeight:{type:"int",defaultValue:0,group:"Appearance"},minRowCount:{type:"int",defaultValue:5,group:"Appearance"},maxRowCount:{type:"int",defaultValue:-1,group:"Appearance"},hideEmptyRows:{type:"boolean",defaultValue:false,group:"Appearance"}}},constructor:function(i){Object.defineProperty(this,"bLegacy",{value:typeof i==="boolean"?i:false});R.apply(this,arguments);}});var a={};function g(r){var t=r.getTable();var o=t?t.getDomRef("tableCCnt"):null;if(o&&D.browser.chrome&&window.devicePixelRatio!==1){var b=document.createElement("table");var c=b.insertRow();var i=r.getRowContentHeight();var n;b.classList.add("sapUiTableCtrl");c.classList.add("sapUiTableTr");if(i>0){c.style.height=r.getBaseRowHeightOfTable()+"px";}o.appendChild(b);n=c.getBoundingClientRect().height;o.removeChild(b);return n;}else{return r.getBaseRowHeightOfTable();}}A.prototype.init=function(){R.prototype.init.apply(this,arguments);this.bRowCountAutoAdjustmentActive=false;this.iLastAvailableSpace=0;this.bTableIsFlexItem=false;this.adjustRowCountToAvailableSpaceAsync=T.debounce(this.adjustRowCountToAvailableSpace,{requestAnimationFrame:true});};A.prototype.attachEvents=function(){R.prototype.attachEvents.apply(this,arguments);T.addDelegate(this.getTable(),a,this);};A.prototype.detachEvents=function(){R.prototype.detachEvents.apply(this,arguments);T.removeDelegate(this.getTable(),a);};A.prototype.cancelAsyncOperations=function(){R.prototype.cancelAsyncOperations.apply(this,arguments);this.stopAutoRowMode();};A.prototype.registerHooks=function(){R.prototype.registerHooks.apply(this,arguments);T.Hook.register(this.getTable(),T.Hook.Keys.Table.RefreshRows,this._onTableRefreshRows,this);T.Hook.register(this.getTable(),T.Hook.Keys.Table.UpdateSizes,this._onUpdateTableSizes,this);};A.prototype.deregisterHooks=function(){R.prototype.deregisterHooks.apply(this,arguments);T.Hook.deregister(this.getTable(),T.Hook.Keys.Table.RefreshRows,this._onTableRefreshRows,this);T.Hook.deregister(this.getTable(),T.Hook.Keys.Table.UpdateSizes,this._onUpdateTableSizes,this);};A.prototype.setRowCount=function(){L.error("The row count is set automatically and cannot be set manually.",this);return this;};A.prototype.getRowCount=function(){if(this.bLegacy){var t=this.getTable();return t?t.getVisibleRowCount():0;}return this.getProperty("rowCount");};A.prototype.getFixedTopRowCount=function(){if(this.bLegacy){var t=this.getTable();return t?t.getFixedRowCount():0;}return this.getProperty("fixedTopRowCount");};A.prototype.getFixedBottomRowCount=function(){if(this.bLegacy){var t=this.getTable();return t?t.getFixedBottomRowCount():0;}return this.getProperty("fixedBottomRowCount");};A.prototype.getMinRowCount=function(){if(this.bLegacy){var t=this.getTable();return t?t.getMinAutoRowCount():0;}return this.getProperty("minRowCount");};A.prototype.getRowContentHeight=function(){if(this.bLegacy){var t=this.getTable();return t?t.getRowHeight():0;}return this.getProperty("rowContentHeight");};A.prototype._getMinRowCount=function(){var m=this.getMinRowCount();var M=this.getMaxRowCount();if(M>=0){return Math.min(m,M);}else{return m;}};A.prototype.getMinRequestLength=function(){var t=this.getTable();var r=this.getConfiguredRowCount();if(this.isPropertyInitial("rowCount")||(t&&!t._bContextsAvailable)){var e=Math.ceil(D.resize.height/T.DefaultRowHeight.sapUiSizeCondensed);r=Math.max(r,e);}return r;};A.prototype.getComputedRowCounts=function(){if(this.isPropertyInitial("rowCount")){return{count:0,scrollable:0,fixedTop:0,fixedBottom:0};}var r=this.getConfiguredRowCount();var f=this.getFixedTopRowCount();var F=this.getFixedBottomRowCount();if(this.getHideEmptyRows()){r=Math.min(r,this.getTotalRowCountOfTable());}return this.sanitizeRowCounts(r,f,F);};A.prototype.getTableStyles=function(){var h="0px";if(this.isPropertyInitial("rowCount")){h="auto";}else{var r=this.getConfiguredRowCount();if(r===0||r===this._getMinRowCount()){h="auto";}}return{height:h};};A.prototype.getTableBottomPlaceholderStyles=function(){if(!this.getHideEmptyRows()){return undefined;}var r;if(this.isPropertyInitial("rowCount")){r=this._getMinRowCount();}else{r=this.getConfiguredRowCount()-this.getComputedRowCounts().count;}return{height:r*this.getBaseRowHeightOfTable()+"px"};};A.prototype.getRowContainerStyles=function(){return{height:this.getComputedRowCounts().count*Math.max(this.getBaseRowHeightOfTable(),g(this))+"px"};};A.prototype.renderRowStyles=function(r){var i=this.getRowContentHeight();if(i>0){r.style("height",this.getBaseRowHeightOfTable()+"px");}};A.prototype.renderCellContentStyles=function(r){var i=this.getRowContentHeight();if(!this.bLegacy&&i<=0){i=this.getDefaultRowContentHeightOfTable();}if(i>0){r.style("max-height",i+"px");}};A.prototype.getBaseRowContentHeight=function(){return Math.max(0,this.getRowContentHeight());};A.prototype._onTableRefreshRows=function(){var c=this.getConfiguredRowCount();if(c>0){if(!this.isPropertyInitial("rowCount")){this.initTableRowsAfterDataRequested(c);}this.getRowContexts(c,true);}};A.prototype.getConfiguredRowCount=function(){var r=Math.max(0,this.getMinRowCount(),this.getRowCount());var m=this.getMaxRowCount();if(m>=0){r=Math.min(r,m);}return r;};A.prototype.startAutoRowMode=function(){this.adjustRowCountToAvailableSpaceAsync(T.RowsUpdateReason.Render,true);};A.prototype.stopAutoRowMode=function(){this.deregisterResizeHandler();this.adjustRowCountToAvailableSpaceAsync.cancel();this.bRowCountAutoAdjustmentActive=false;};A.prototype.registerResizeHandler=function(o){var t=this.getTable();if(t){T.registerResizeHandler(t,"AutoRowMode",this.onResize.bind(this),null,o===true);}};A.prototype.deregisterResizeHandler=function(){var t=this.getTable();if(t){T.deregisterResizeHandler(t,"AutoRowMode");}};A.prototype.onResize=function(e){var o=e.oldSize.height;var n=e.size.height;if(o!==n){this.adjustRowCountToAvailableSpaceAsync(T.RowsUpdateReason.Resize);}};A.prototype._onUpdateTableSizes=function(r){if(r===T.RowsUpdateReason.Resize||r===T.RowsUpdateReason.Render){return;}if(this.bRowCountAutoAdjustmentActive){this.adjustRowCountToAvailableSpaceAsync(r);}};A.prototype.adjustRowCountToAvailableSpace=function(r,s){s=s===true;var t=this.getTable();var o=t?t.getDomRef():null;if(!t||t._bInvalid||!o||!sap.ui.getCore().isThemeApplied()){return;}this.bTableIsFlexItem=window.getComputedStyle(o.parentNode).display==="flex";if(o.scrollHeight===0){if(s){this.registerResizeHandler(!this.bTableIsFlexItem);this.bRowCountAutoAdjustmentActive=true;}return;}var n=this.determineAvailableSpace();var O=this.getConfiguredRowCount();var N=Math.floor(n/g(this));var i=this.getComputedRowCounts().count;var b;if(this.bLegacy){t.setProperty("visibleRowCount",N,true);}this.setProperty("rowCount",N,true);b=this.getComputedRowCounts().count;if(this.bLegacy){t.setProperty("visibleRowCount",b,true);}if(i!==b){this.updateTable(r);}else{if(O!==N||r===T.RowsUpdateReason.Zoom){this.applyTableStyles();this.applyRowContainerStyles();this.applyTableBottomPlaceholderStyles();}if(!this._bFiredRowsUpdatedAfterRendering&&t.getRows().length>0){this.fireRowsUpdated(r);}}if(s){this.registerResizeHandler(!this.bTableIsFlexItem);this.bRowCountAutoAdjustmentActive=true;}};A.prototype.determineAvailableSpace=function(){var t=this.getTable();var o=t?t.getDomRef():null;var r=t?t.getDomRef("tableCCnt"):null;var p=t?t.getDomRef("placeholder-bottom"):null;if(!o||!r||!o.parentNode){return 0;}var u=0;var b=r.clientHeight;var P=p?p.clientHeight:0;if(this.bTableIsFlexItem){var c=o.childNodes;for(var i=0;i<c.length;i++){u+=c[i].offsetHeight;}u-=b-P;}else{u=o.scrollHeight-b-P;}var s=t._getScrollExtension();if(!s.isHorizontalScrollbarVisible()){var d={};d[D.browser.BROWSER.CHROME]=16;d[D.browser.BROWSER.FIREFOX]=16;d[D.browser.BROWSER.INTERNET_EXPLORER]=18;d[D.browser.BROWSER.EDGE]=16;d[D.browser.BROWSER.SAFARI]=16;d[D.browser.BROWSER.ANDROID]=8;u+=d[D.browser.name];}var e=this.bTableIsFlexItem?o:o.parentNode;var n=Math.max(0,Math.floor(jQuery(e).height()-u));var f=Math.abs(n-this.iLastAvailableSpace);if(f>=5){this.iLastAvailableSpace=n;}return this.iLastAvailableSpace;};a.onBeforeRendering=function(e){var r=e&&e.isMarked("renderRows");if(!r){this.stopAutoRowMode();this.updateTable(T.RowsUpdateReason.Render);}};a.onAfterRendering=function(e){var r=e&&e.isMarked("renderRows");if(!r){this.startAutoRowMode();}};return A;});
