/*!
 * OpenUI5
 * (c) Copyright 2009-2019 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/fl/Utils","sap/m/MessageBox","sap/base/util/ObjectPath","sap/ui/rta/util/hasStableId","sap/ui/rta/util/showMessageBox","sap/base/Log","sap/ui/rta/Utils"],function(F,M,O,h,s,L,U){"use strict";return function(r){if("QUnit"in window||(window.frameElement&&window.frameElement.getAttribute("id")==="OpaFrame")){return;}var c=F.getAppComponentForControl(r.getRootControlInstance());if(c){var m=c.getManifest();if(m&&O.get(["sap.app","id"],m)!=="sap.ui.documentation.sdk"&&!O.get(["sap.ui.generic.app"],m)&&!O.get(["sap.ovp"],m)){var f=O.get(["sap.ui5","flexEnabled"],m);if(typeof f!=="boolean"){s(r._getTextResources().getText("MSG_NO_FLEX_ENABLED_FLAG"),{icon:M.Icon.WARNING,title:r._getTextResources().getText("HEADER_WARNING"),styleClass:U.getRtaStyleClassName()});}else{var v=true;r._oDesignTime.getElementOverlays().filter(function(e){return!e.getDesignTimeMetadata().markedAsNotAdaptable();}).forEach(function(e){var C=h(e);if(!C){L.error("Control ID was generated dynamically by SAPUI5. To support SAPUI5 flexibility, a stable control ID is needed to assign the changes to.",e.getElement().getId());}v=C&&v;});if(!v){s(r._getTextResources().getText("MSG_UNSTABLE_ID_FOUND"),{icon:M.Icon.ERROR,title:r._getTextResources().getText("HEADER_ERROR"),styleClass:U.getRtaStyleClassName()});}}}}};});