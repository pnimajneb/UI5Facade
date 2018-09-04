/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2018 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.predefine('sap/ui/fl/library',["sap/ui/fl/RegistrationDelegator"],function(R){"use strict";sap.ui.getCore().initLibrary({name:"sap.ui.fl",version:"1.56.6",controls:["sap.ui.fl.variants.VariantManagement"],dependencies:["sap.ui.core","sap.m"],designtime:"sap/ui/fl/designtime/library.designtime",extensions:{"sap.ui.support":{diagnosticPlugins:["sap/ui/fl/support/Flexibility"],publicRules:true}}});sap.ui.fl.Scenario={AppVariant:"APP_VARIANT",AdaptationProject:"ADAPTATION_PROJECT",FioriElementsFromScratch:"FE_FROM_SCRATCH",UiAdaptation:"UI_ADAPTATION"};R.registerAll();return sap.ui.fl;});
sap.ui.require.preload({
	"sap/ui/fl/manifest.json":'{"_version":"1.9.0","sap.app":{"id":"sap.ui.fl","type":"library","embeds":["support/apps/contentbrowser"],"applicationVersion":{"version":"1.56.6"},"title":"SAPUI5 library with sap.ui.fl controls.","description":"SAPUI5 library with sap.ui.fl controls.","ach":"CA-UI5-FL","resources":"resources.json","offline":true},"sap.ui":{"technology":"UI5","supportedThemes":["base"]},"sap.ui5":{"dependencies":{"minUI5Version":"1.56","libs":{"sap.ui.core":{"minVersion":"1.56.6"},"sap.m":{"minVersion":"1.56.6"}}},"library":{"i18n":"messagebundle.properties","content":{"controls":["sap.ui.fl.variants.VariantManagement"]}}}}'
},"sap/ui/fl/library-h2-preload"
);
sap.ui.loader.config({depCacheUI5:{
"sap/ui/fl/Cache.js":["sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/Change.js":["jquery.sap.global.js","sap/ui/base/ManagedObject.js","sap/ui/fl/Utils.js","sap/ui/fl/registry/Settings.js"],
"sap/ui/fl/ChangePersistence.js":["sap/m/MessageBox.js","sap/ui/core/BusyIndicator.js","sap/ui/fl/Cache.js","sap/ui/fl/Change.js","sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js","sap/ui/fl/Variant.js","sap/ui/fl/context/ContextManager.js","sap/ui/fl/registry/Settings.js","sap/ui/fl/transport/TransportSelection.js","sap/ui/fl/variants/VariantController.js","sap/ui/model/json/JSONModel.js"],
"sap/ui/fl/ChangePersistenceFactory.js":["jquery.sap.global.js","sap/ui/core/Component.js","sap/ui/fl/ChangePersistence.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/ControlPersonalizationAPI.js":["sap/ui/core/Component.js","sap/ui/core/Element.js","sap/ui/core/util/reflection/JsControlTreeModifier.js","sap/ui/fl/Utils.js","sap/ui/fl/registry/ChangeRegistry.js","sap/ui/fl/variants/VariantManagement.js"],
"sap/ui/fl/DefaultVariant.js":["jquery.sap.global.js","sap/ui/fl/Change.js"],
"sap/ui/fl/FakeLrepConnector.js":["jquery.sap.global.js","sap/ui/fl/Cache.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js","sap/ui/thirdparty/URI.js"],
"sap/ui/fl/FakeLrepConnectorLocalStorage.js":["sap/ui/fl/Cache.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/FakeLrepConnector.js","sap/ui/fl/FakeLrepLocalStorage.js","sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/FlexController.js":["jquery.sap.global.js","sap/ui/core/Element.js","sap/ui/core/mvc/View.js","sap/ui/core/util/reflection/JsControlTreeModifier.js","sap/ui/core/util/reflection/XmlTreeModifier.js","sap/ui/fl/Cache.js","sap/ui/fl/Change.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/LrepConnector.js","sap/ui/fl/Persistence.js","sap/ui/fl/Utils.js","sap/ui/fl/Variant.js","sap/ui/fl/context/ContextManager.js","sap/ui/fl/registry/ChangeRegistry.js","sap/ui/fl/registry/Settings.js"],
"sap/ui/fl/FlexControllerFactory.js":["jquery.sap.global.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/FlexController.js","sap/ui/fl/Utils.js","sap/ui/fl/variants/VariantModel.js"],
"sap/ui/fl/LrepConnector.js":["jquery.sap.global.js","sap/ui/fl/Utils.js","sap/ui/thirdparty/URI.js"],
"sap/ui/fl/Persistence.js":["jquery.sap.global.js","sap/ui/fl/Change.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/DefaultVariant.js","sap/ui/fl/StandardVariant.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/Preprocessor.js":["jquery.sap.global.js","sap/ui/base/Object.js"],
"sap/ui/fl/PreprocessorImpl.js":["jquery.sap.global.js","sap/ui/core/Component.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/RegistrationDelegator.js":["sap/ui/core/Component.js","sap/ui/core/mvc/Controller.js","sap/ui/core/mvc/XMLView.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/EventHistory.js","sap/ui/fl/FlexControllerFactory.js","sap/ui/fl/registry/ChangeHandlerRegistration.js"],
"sap/ui/fl/StandardVariant.js":["jquery.sap.global.js","sap/ui/fl/Change.js"],
"sap/ui/fl/Utils.js":["jquery.sap.global.js","sap/ui/core/Component.js","sap/ui/core/util/reflection/BaseTreeModifier.js","sap/ui/thirdparty/hasher.js"],
"sap/ui/fl/Variant.js":["jquery.sap.global.js","sap/ui/base/ManagedObject.js","sap/ui/fl/Utils.js","sap/ui/fl/registry/Settings.js"],
"sap/ui/fl/XmlPreprocessorImpl.js":["jquery.sap.global.js","sap/ui/core/Component.js","sap/ui/fl/ChangePersistence.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/FlexControllerFactory.js","sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/changeHandler/AddXML.js":["jquery.sap.global.js","sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/Base.js"],
"sap/ui/fl/changeHandler/BaseRename.js":["sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/Base.js"],
"sap/ui/fl/changeHandler/BaseTreeModifier.js":["sap/ui/core/util/reflection/BaseTreeModifier.js"],
"sap/ui/fl/changeHandler/ChangeHandlerMediator.js":["jquery.sap.global.js"],
"sap/ui/fl/changeHandler/HideControl.js":["jquery.sap.global.js"],
"sap/ui/fl/changeHandler/JsControlTreeModifier.js":["sap/ui/core/util/reflection/JsControlTreeModifier.js"],
"sap/ui/fl/changeHandler/MoveControls.js":["jquery.sap.global.js","sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/Base.js"],
"sap/ui/fl/changeHandler/MoveElements.js":["jquery.sap.global.js","sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/Base.js"],
"sap/ui/fl/changeHandler/PropertyBindingChange.js":["jquery.sap.global.js"],
"sap/ui/fl/changeHandler/PropertyChange.js":["jquery.sap.global.js","sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/Base.js"],
"sap/ui/fl/changeHandler/StashControl.js":["jquery.sap.global.js"],
"sap/ui/fl/changeHandler/UnhideControl.js":["jquery.sap.global.js"],
"sap/ui/fl/changeHandler/UnstashControl.js":["jquery.sap.global.js"],
"sap/ui/fl/changeHandler/XmlTreeModifier.js":["sap/ui/core/util/reflection/XmlTreeModifier.js"],
"sap/ui/fl/codeExt/CodeExtManager.js":["sap/ui/fl/Change.js","sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/context/BaseContextProvider.js":["sap/ui/base/ManagedObject.js"],
"sap/ui/fl/context/Context.js":["sap/ui/base/ManagedObject.js"],
"sap/ui/fl/context/ContextManager.js":["sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js","sap/ui/fl/context/Context.js"],
"sap/ui/fl/context/DeviceContextProvider.js":["sap/ui/Device.js","sap/ui/fl/context/BaseContextProvider.js"],
"sap/ui/fl/context/SwitchContextProvider.js":["sap/ui/fl/Cache.js","sap/ui/fl/context/BaseContextProvider.js"],
"sap/ui/fl/core/EventDelegate.js":["jquery.sap.global.js","sap/ui/base/EventProvider.js","sap/ui/fl/Utils.js","sap/ui/fl/core/FlexVisualizer.js","sap/ui/fl/registry/ChangeRegistry.js"],
"sap/ui/fl/core/FlexVisualizer.js":["jquery.sap.global.js"],
"sap/ui/fl/descriptorRelated/api/DescriptorChangeFactory.js":["sap/ui/fl/Change.js","sap/ui/fl/ChangePersistence.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/Utils.js","sap/ui/fl/descriptorRelated/internal/Utils.js","sap/ui/fl/registry/Settings.js"],
"sap/ui/fl/descriptorRelated/api/DescriptorInlineChangeFactory.js":["sap/ui/fl/descriptorRelated/internal/Utils.js"],
"sap/ui/fl/descriptorRelated/api/DescriptorVariantFactory.js":["sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js","sap/ui/fl/descriptorRelated/api/DescriptorInlineChangeFactory.js","sap/ui/fl/descriptorRelated/internal/Utils.js","sap/ui/fl/registry/Settings.js"],
"sap/ui/fl/fieldExt/Access.js":["jquery.sap.storage.js"],
"sap/ui/fl/library.js":["sap/ui/fl/RegistrationDelegator.js"],
"sap/ui/fl/library.support.js":["sap/ui/core/Component.js","sap/ui/dt/DesignTime.js","sap/ui/fl/Utils.js","sap/ui/support/library.js"],
"sap/ui/fl/registry/ChangeHandlerRegistration.js":["sap/ui/fl/registry/ChangeRegistry.js"],
"sap/ui/fl/registry/ChangeRegistry.js":["jquery.sap.global.js","sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/AddXML.js","sap/ui/fl/changeHandler/HideControl.js","sap/ui/fl/changeHandler/MoveControls.js","sap/ui/fl/changeHandler/MoveElements.js","sap/ui/fl/changeHandler/PropertyBindingChange.js","sap/ui/fl/changeHandler/PropertyChange.js","sap/ui/fl/changeHandler/StashControl.js","sap/ui/fl/changeHandler/UnhideControl.js","sap/ui/fl/changeHandler/UnstashControl.js","sap/ui/fl/registry/ChangeRegistryItem.js","sap/ui/fl/registry/ChangeTypeMetadata.js","sap/ui/fl/registry/Settings.js"],
"sap/ui/fl/registry/ChangeRegistryItem.js":["jquery.sap.global.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/registry/ChangeTypeMetadata.js":["jquery.sap.global.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/registry/Settings.js":["jquery.sap.global.js","sap/ui/base/EventProvider.js","sap/ui/fl/Cache.js","sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/registry/SimpleChanges.js":["jquery.sap.global.js","sap/ui/fl/changeHandler/HideControl.js","sap/ui/fl/changeHandler/MoveControls.js","sap/ui/fl/changeHandler/MoveElements.js","sap/ui/fl/changeHandler/PropertyBindingChange.js","sap/ui/fl/changeHandler/PropertyChange.js","sap/ui/fl/changeHandler/StashControl.js","sap/ui/fl/changeHandler/UnhideControl.js","sap/ui/fl/changeHandler/UnstashControl.js"],
"sap/ui/fl/support/Flexibility.js":["jquery.sap.global.js","sap/ui/core/support/Plugin.js","sap/ui/core/support/Support.js","sap/ui/fl/ChangePersistenceFactory.js","sap/ui/fl/FlexController.js","sap/ui/fl/Utils.js","sap/ui/model/json/JSONModel.js"],
"sap/ui/fl/support/apps/contentbrowser/Component.js":["sap/ui/core/UIComponent.js"],
"sap/ui/fl/support/apps/contentbrowser/controller/ContentDetails.controller.js":["sap/m/Button.js","sap/m/Dialog.js","sap/m/Text.js","sap/ui/core/mvc/Controller.js","sap/ui/fl/support/apps/contentbrowser/lrepConnector/LRepConnector.js","sap/ui/fl/support/apps/contentbrowser/utils/DataUtils.js"],
"sap/ui/fl/support/apps/contentbrowser/controller/ContentDetailsEdit.controller.js":["sap/ui/core/mvc/Controller.js","sap/ui/fl/support/apps/contentbrowser/lrepConnector/LRepConnector.js","sap/ui/fl/support/apps/contentbrowser/utils/DataUtils.js"],
"sap/ui/fl/support/apps/contentbrowser/controller/LayerContentMaster.controller.js":["sap/ui/core/UIComponent.js","sap/ui/core/mvc/Controller.js","sap/ui/fl/support/apps/contentbrowser/lrepConnector/LRepConnector.js","sap/ui/fl/support/apps/contentbrowser/utils/DataUtils.js","sap/ui/model/Filter.js","sap/ui/model/FilterOperator.js"],
"sap/ui/fl/support/apps/contentbrowser/controller/Layers.controller.js":["sap/ui/core/mvc/Controller.js","sap/ui/fl/support/apps/contentbrowser/utils/ErrorUtils.js"],
"sap/ui/fl/support/apps/contentbrowser/lrepConnector/LRepConnector.js":["sap/ui/fl/Utils.js"],
"sap/ui/fl/support/apps/contentbrowser/utils/DataUtils.js":["sap/m/GroupHeaderListItem.js"],
"sap/ui/fl/support/apps/contentbrowser/utils/ErrorUtils.js":["sap/m/MessagePopover.js","sap/m/MessagePopoverItem.js"],
"sap/ui/fl/support/apps/contentbrowser/view/ContentDetails.view.xml":["sap/m/Button.js","sap/m/DisplayListItem.js","sap/m/IconTabBar.js","sap/m/IconTabFilter.js","sap/m/List.js","sap/m/Page.js","sap/m/Text.js","sap/m/Toolbar.js","sap/m/ToolbarSpacer.js","sap/ui/core/mvc/XMLView.js","sap/ui/fl/support/apps/contentbrowser/controller/ContentDetails.controller.js","sap/ui/layout/form/SimpleForm.js"],
"sap/ui/fl/support/apps/contentbrowser/view/ContentDetailsEdit.view.xml":["sap/m/Button.js","sap/m/Page.js","sap/m/TextArea.js","sap/m/Toolbar.js","sap/m/ToolbarSpacer.js","sap/ui/core/mvc/XMLView.js","sap/ui/fl/support/apps/contentbrowser/controller/ContentDetailsEdit.controller.js","sap/ui/layout/form/SimpleForm.js"],
"sap/ui/fl/support/apps/contentbrowser/view/EmptyDetails.view.xml":["sap/m/Page.js","sap/ui/core/mvc/XMLView.js"],
"sap/ui/fl/support/apps/contentbrowser/view/LayerContentMaster.view.xml":["sap/m/Button.js","sap/m/List.js","sap/m/Page.js","sap/m/SearchField.js","sap/m/StandardListItem.js","sap/m/Toolbar.js","sap/ui/core/mvc/XMLView.js","sap/ui/fl/support/apps/contentbrowser/controller/LayerContentMaster.controller.js"],
"sap/ui/fl/support/apps/contentbrowser/view/Layers.view.xml":["sap/m/Button.js","sap/m/List.js","sap/m/Page.js","sap/m/StandardListItem.js","sap/m/Toolbar.js","sap/ui/core/mvc/XMLView.js","sap/ui/fl/support/apps/contentbrowser/controller/Layers.controller.js"],
"sap/ui/fl/support/apps/contentbrowser/view/MainView.view.xml":["sap/m/SplitApp.js","sap/ui/core/mvc/XMLView.js"],
"sap/ui/fl/support/diagnostics/Flexibility.controller.js":["sap/ui/core/mvc/Controller.js","sap/ui/fl/support/Flexibility.js","sap/ui/model/Filter.js","sap/ui/model/FilterOperator.js"],
"sap/ui/fl/support/diagnostics/Flexibility.view.xml":["sap/m/Button.js","sap/m/CustomListItem.js","sap/m/GroupHeaderListItem.js","sap/m/Label.js","sap/m/List.js","sap/m/Select.js","sap/m/StandardTreeItem.js","sap/m/Text.js","sap/m/Toolbar.js","sap/m/ToolbarSpacer.js","sap/m/Tree.js","sap/ui/core/CustomData.js","sap/ui/core/HTML.js","sap/ui/core/ListItem.js","sap/ui/core/mvc/XMLView.js","sap/ui/fl/support/diagnostics/Flexibility.controller.js","sap/ui/layout/Splitter.js","sap/ui/layout/SplitterLayoutData.js","sap/ui/layout/VerticalLayout.js"],
"sap/ui/fl/transport/TransportDialog.js":["jquery.sap.global.js","sap/m/Button.js","sap/m/ComboBox.js","sap/m/Dialog.js","sap/m/DialogRenderer.js","sap/m/Input.js","sap/m/InputListItem.js","sap/m/Label.js","sap/m/List.js","sap/m/MessageToast.js","sap/ui/core/ListItem.js","sap/ui/fl/transport/Transports.js"],
"sap/ui/fl/transport/TransportSelection.js":["jquery.sap.global.js","sap/ui/fl/Utils.js","sap/ui/fl/registry/Settings.js","sap/ui/fl/transport/TransportDialog.js","sap/ui/fl/transport/Transports.js"],
"sap/ui/fl/transport/Transports.js":["sap/ui/fl/LrepConnector.js","sap/ui/fl/Utils.js"],
"sap/ui/fl/variants/VariantController.js":["jquery.sap.global.js","sap/ui/fl/Cache.js","sap/ui/fl/Change.js","sap/ui/fl/Utils.js","sap/ui/fl/Variant.js"],
"sap/ui/fl/variants/VariantManagement.js":["jquery.sap.global.js","sap/m/Bar.js","sap/m/Button.js","sap/m/ButtonType.js","sap/m/CheckBox.js","sap/m/Column.js","sap/m/ColumnListItem.js","sap/m/Dialog.js","sap/m/Input.js","sap/m/Label.js","sap/m/ObjectIdentifier.js","sap/m/OverflowToolbar.js","sap/m/OverflowToolbarLayoutData.js","sap/m/OverflowToolbarPriority.js","sap/m/Page.js","sap/m/PlacementType.js","sap/m/PopinDisplay.js","sap/m/RadioButton.js","sap/m/ResponsivePopover.js","sap/m/ScreenSize.js","sap/m/SearchField.js","sap/m/SelectList.js","sap/m/Table.js","sap/m/Text.js","sap/m/Title.js","sap/m/Toolbar.js","sap/m/ToolbarSpacer.js","sap/m/VBox.js","sap/ui/Device.js","sap/ui/core/Control.js","sap/ui/core/Icon.js","sap/ui/core/InvisibleText.js","sap/ui/core/TextAlign.js","sap/ui/core/ValueState.js","sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/BaseTreeModifier.js","sap/ui/layout/Grid.js","sap/ui/layout/HorizontalLayout.js","sap/ui/model/Context.js","sap/ui/model/Filter.js","sap/ui/model/PropertyBinding.js","sap/ui/model/json/JSONModel.js"],
"sap/ui/fl/variants/VariantModel.js":["jquery.sap.global.js","sap/ui/core/BusyIndicator.js","sap/ui/core/util/reflection/BaseTreeModifier.js","sap/ui/fl/Change.js","sap/ui/fl/Utils.js","sap/ui/fl/changeHandler/Base.js","sap/ui/fl/variants/util/VariantUtil.js","sap/ui/model/json/JSONModel.js"],
"sap/ui/fl/variants/util/VariantUtil.js":["jquery.sap.global.js","sap/base/Log.js","sap/base/util/equal.js","sap/ui/core/Component.js","sap/ui/core/routing/HashChanger.js","sap/ui/core/routing/History.js","sap/ui/fl/Utils.js"]
}});
//# sourceMappingURL=library-h2-preload.js.map