/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2018 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.predefine('sap/ui/table/library',['sap/ui/core/Core','sap/ui/model/TreeAutoExpandMode','sap/ui/core/library','sap/ui/unified/library'],function(C,T){"use strict";sap.ui.getCore().initLibrary({name:"sap.ui.table",version:"1.56.6",dependencies:["sap.ui.core","sap.ui.unified"],designtime:"sap/ui/table/designtime/library.designtime",types:["sap.ui.table.NavigationMode","sap.ui.table.RowActionType","sap.ui.table.SelectionBehavior","sap.ui.table.SelectionMode","sap.ui.table.SortOrder","sap.ui.table.VisibleRowCountMode","sap.ui.table.SharedDomRef","sap.ui.table.TreeAutoExpandMode"],interfaces:[],controls:["sap.ui.table.AnalyticalColumnMenu","sap.ui.table.AnalyticalTable","sap.ui.table.ColumnMenu","sap.ui.table.Table","sap.ui.table.TreeTable","sap.ui.table.RowAction"],elements:["sap.ui.table.AnalyticalColumn","sap.ui.table.Column","sap.ui.table.Row","sap.ui.table.RowActionItem","sap.ui.table.RowSettings"],extensions:{flChangeHandlers:{"sap.ui.table.Column":{"propertyChange":"default"},"sap.ui.table.Table":{"moveElements":"default"},"sap.ui.table.AnalyticalTable":{"moveElements":"default"}},"sap.ui.support":{publicRules:true}}});var t=sap.ui.table;t.NavigationMode={Scrollbar:"Scrollbar",Paginator:"Paginator"};t.RowActionType={Custom:"Custom",Navigation:"Navigation",Delete:"Delete"};t.SelectionBehavior={Row:"Row",RowSelector:"RowSelector",RowOnly:"RowOnly"};t.SelectionMode={MultiToggle:"MultiToggle",Multi:"Multi",Single:"Single",None:"None"};t.SortOrder={Ascending:"Ascending",Descending:"Descending"};t.VisibleRowCountMode={Fixed:"Fixed",Interactive:"Interactive",Auto:"Auto"};t.SharedDomRef={HorizontalScrollBar:"hsb",VerticalScrollBar:"vsb"};t.GroupEventType={group:"group",ungroup:"ungroup",ungroupAll:"ungroupAll",moveUp:"moveUp",moveDown:"moveDown",showGroupedColumn:"showGroupedColumn",hideGroupedColumn:"hideGroupedColumn"};t.ColumnHeader=t.Column;t.TreeAutoExpandMode=T;if(!t.TableHelper){t.TableHelper={addTableClass:function(){return"";},createLabel:function(c){throw new Error("no Label control available!");},createTextView:function(c){throw new Error("no TextView control available!");},bFinal:false};}return t;});
sap.ui.require.preload({
	"sap/ui/table/manifest.json":'{"_version":"1.9.0","sap.app":{"id":"sap.ui.table","type":"library","embeds":[],"applicationVersion":{"version":"1.56.6"},"title":"Table-like controls, mainly for desktop scenarios.","description":"Table-like controls, mainly for desktop scenarios.","ach":"CA-UI5-TBL","resources":"resources.json","offline":true},"sap.ui":{"technology":"UI5","supportedThemes":["base","sap_hcb"]},"sap.ui5":{"dependencies":{"minUI5Version":"1.56","libs":{"sap.ui.core":{"minVersion":"1.56.6"},"sap.ui.unified":{"minVersion":"1.56.6"}}},"library":{"i18n":"messagebundle.properties","content":{"controls":["sap.ui.table.AnalyticalColumnMenu","sap.ui.table.AnalyticalTable","sap.ui.table.ColumnMenu","sap.ui.table.Table","sap.ui.table.TreeTable","sap.ui.table.RowAction"],"elements":["sap.ui.table.AnalyticalColumn","sap.ui.table.Column","sap.ui.table.Row","sap.ui.table.RowActionItem","sap.ui.table.RowSettings"],"types":["sap.ui.table.NavigationMode","sap.ui.table.RowActionType","sap.ui.table.SelectionBehavior","sap.ui.table.SelectionMode","sap.ui.table.SortOrder","sap.ui.table.VisibleRowCountMode","sap.ui.table.SharedDomRef","sap.ui.table.TreeAutoExpandMode"],"interfaces":[]}}}}'
},"sap/ui/table/library-h2-preload"
);
sap.ui.loader.config({depCacheUI5:{
"sap/ui/table/AnalyticalColumn.js":["jquery.sap.global.js","sap/ui/core/Element.js","sap/ui/model/type/Boolean.js","sap/ui/model/type/DateTime.js","sap/ui/model/type/Float.js","sap/ui/model/type/Integer.js","sap/ui/model/type/Time.js","sap/ui/table/AnalyticalColumnMenu.js","sap/ui/table/Column.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/AnalyticalColumnMenu.js":["jquery.sap.global.js","sap/ui/table/ColumnMenu.js","sap/ui/table/library.js"],
"sap/ui/table/AnalyticalColumnMenuRenderer.js":["sap/ui/table/AnalyticalColumnMenu.js"],
"sap/ui/table/AnalyticalTable.js":["jquery.sap.global.js","sap/ui/core/Popup.js","sap/ui/model/SelectionModel.js","sap/ui/model/Sorter.js","sap/ui/model/analytics/ODataModelAdapter.js","sap/ui/table/AnalyticalColumn.js","sap/ui/table/Table.js","sap/ui/table/TableUtils.js","sap/ui/table/TreeTable.js","sap/ui/table/library.js","sap/ui/unified/Menu.js","sap/ui/unified/MenuItem.js"],
"sap/ui/table/AnalyticalTableRenderer.js":["sap/ui/table/AnalyticalTable.js"],
"sap/ui/table/Column.js":["jquery.sap.global.js","sap/ui/core/Element.js","sap/ui/core/Popup.js","sap/ui/core/library.js","sap/ui/model/Filter.js","sap/ui/model/FilterOperator.js","sap/ui/model/FilterType.js","sap/ui/model/Sorter.js","sap/ui/model/Type.js","sap/ui/model/type/String.js","sap/ui/table/ColumnMenu.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/ColumnMenu.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js","sap/ui/unified/Menu.js","sap/ui/unified/MenuItem.js","sap/ui/unified/MenuTextFieldItem.js"],
"sap/ui/table/ColumnMenuRenderer.js":["sap/ui/table/ColumnMenu.js"],
"sap/ui/table/Row.js":["jquery.sap.global.js","sap/ui/core/Element.js","sap/ui/model/Context.js","sap/ui/table/TableUtils.js"],
"sap/ui/table/RowAction.js":["jquery.sap.global.js","jquery.sap.keycodes.js","sap/ui/core/Control.js","sap/ui/core/Icon.js","sap/ui/core/Popup.js","sap/ui/table/RowActionRenderer.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js","sap/ui/unified/Menu.js"],
"sap/ui/table/RowActionItem.js":["sap/ui/core/Element.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js","sap/ui/unified/MenuItem.js"],
"sap/ui/table/RowActionRenderer.js":["sap/ui/table/Row.js"],
"sap/ui/table/RowSettings.js":["sap/ui/core/Element.js","sap/ui/core/library.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/Table.js":["jquery.sap.dom.js","jquery.sap.events.js","jquery.sap.global.js","jquery.sap.trace.js","sap/ui/Device.js","sap/ui/core/Control.js","sap/ui/core/Element.js","sap/ui/core/IconPool.js","sap/ui/model/BindingMode.js","sap/ui/model/ChangeReason.js","sap/ui/model/Filter.js","sap/ui/model/SelectionModel.js","sap/ui/model/Sorter.js","sap/ui/table/Column.js","sap/ui/table/Row.js","sap/ui/table/TableAccExtension.js","sap/ui/table/TableDragAndDropExtension.js","sap/ui/table/TableExtension.js","sap/ui/table/TableKeyboardExtension.js","sap/ui/table/TablePointerExtension.js","sap/ui/table/TableRenderer.js","sap/ui/table/TableScrollExtension.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TableAccExtension.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/core/Control.js","sap/ui/table/TableAccRenderExtension.js","sap/ui/table/TableExtension.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TableAccRenderExtension.js":["jquery.sap.global.js","sap/ui/table/TableExtension.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TableColumnUtils.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/table/library.js"],
"sap/ui/table/TableDragAndDropExtension.js":["sap/ui/core/dnd/DropPosition.js","sap/ui/table/TableExtension.js","sap/ui/table/TableUtils.js"],
"sap/ui/table/TableExtension.js":["sap/ui/base/Object.js","sap/ui/table/TableUtils.js"],
"sap/ui/table/TableGrouping.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/core/Element.js","sap/ui/model/Sorter.js","sap/ui/table/library.js"],
"sap/ui/table/TableKeyboardDelegate2.js":["jquery.sap.global.js","jquery.sap.keycodes.js","sap/ui/Device.js","sap/ui/base/Object.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TableKeyboardExtension.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/core/delegate/ItemNavigation.js","sap/ui/table/TableExtension.js","sap/ui/table/TableKeyboardDelegate2.js","sap/ui/table/TableUtils.js"],
"sap/ui/table/TableMenuUtils.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/core/Popup.js","sap/ui/unified/Menu.js","sap/ui/unified/MenuItem.js"],
"sap/ui/table/TablePersoController.js":["jquery.sap.global.js","sap/ui/base/ManagedObject.js"],
"sap/ui/table/TablePointerExtension.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/core/Popup.js","sap/ui/table/TableExtension.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TableRenderer.js":["sap/ui/Device.js","sap/ui/core/Control.js","sap/ui/core/IconPool.js","sap/ui/core/Renderer.js","sap/ui/core/theming/Parameters.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TableRendererUtils.js":["jquery.sap.global.js","sap/ui/core/Control.js"],
"sap/ui/table/TableScrollExtension.js":["jquery.sap.events.js","jquery.sap.global.js","jquery.sap.trace.js","sap/ui/Device.js","sap/ui/table/TableExtension.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TableUtils.js":["jquery.sap.global.js","sap/ui/core/Control.js","sap/ui/core/ResizeHandler.js","sap/ui/core/library.js","sap/ui/model/ChangeReason.js","sap/ui/table/TableBindingUtils.js","sap/ui/table/TableColumnUtils.js","sap/ui/table/TableGrouping.js","sap/ui/table/TableMenuUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TreeTable.js":["jquery.sap.global.js","sap/ui/core/Element.js","sap/ui/model/ClientTreeBindingAdapter.js","sap/ui/model/TreeBindingCompatibilityAdapter.js","sap/ui/table/Table.js","sap/ui/table/TableUtils.js","sap/ui/table/library.js"],
"sap/ui/table/TreeTableRenderer.js":["sap/ui/table/TreeTable.js"],
"sap/ui/table/library.js":["sap/ui/core/Core.js","sap/ui/core/library.js","sap/ui/model/TreeAutoExpandMode.js","sap/ui/unified/library.js"],
"sap/ui/table/library.support.js":["jquery.sap.global.js","sap/ui/Device.js","sap/ui/support/library.js","sap/ui/support/supportRules/RuleSet.js","sap/ui/table/rules/TableHelper.support.js"],
"sap/ui/table/rules/TableHelper.support.js":["jquery.sap.global.js","sap/ui/support/library.js"]
}});
//# sourceMappingURL=library-h2-preload.js.map