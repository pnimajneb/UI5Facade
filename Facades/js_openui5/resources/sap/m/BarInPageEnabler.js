/*!
 * OpenUI5
 * (c) Copyright 2009-2019 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['sap/ui/base/Object','sap/m/library',"sap/base/Log"],function(O,l,L){"use strict";var I=l.IBarHTMLTag;var c={footer:{contextClass:"sapMFooter-CTX sapContrast sapContrastPlus",tag:"Footer",internalAriaLabel:"BAR_ARIA_DESCRIPTION_FOOTER"},header:{contextClass:"sapMHeader-CTX",tag:"Header",internalAriaLabel:"BAR_ARIA_DESCRIPTION_HEADER"},subheader:{contextClass:"sapMSubHeader-CTX",tag:"Header",internalAriaLabel:"BAR_ARIA_DESCRIPTION_SUBHEADER"}};var a="sapMIBar";var B=O.extend("sap.m.BarInPageEnabler",{isContextSensitive:function(){return this.getDesign&&this.getDesign()==="Auto";},setHTMLTag:function(n){if(n===this.sTag){return this;}this.sTag=n;return this;},getHTMLTag:function(){if(!this.hasOwnProperty("sTag")){this.sTag=I.Div;}return this.sTag;},getContext:function(){return c;},_getRootAccessibilityRole:function(){var r=this._sRootAccessibilityRole||"toolbar";return r;},_setRootAccessibilityRole:function(r){this._sRootAccessibilityRole=r;return this;},applyTagAndContextClassFor:function(C){this._applyTag(C);return this._applyContextClassFor(C);},_applyContextClassFor:function(C){var o=this._getContextOptions(C);if(!o){return this;}if(!this.isContextSensitive){L.error("The bar control you are using does not implement all the members of the IBar interface",this);return this;}if(!this.getRenderer().shouldAddIBarContext()){this.addStyleClass(a+"-CTX");}if(this.isContextSensitive()){this.addStyleClass(o.contextClass);}return this;},_applyTag:function(C){var o=this._getContextOptions(C);if(!o){return this;}if(!this.setHTMLTag){L.error("The bar control you are using does not implement all the members of the IBar interface",this);return this;}this.setHTMLTag(o.tag);return this;},_getContextOptions:function(C){var o;if(this.getContext){o=this.getContext();}else{o=c;}var b=o[C];if(!b){L.error("The context "+C+" is not known",this);return null;}return b;},render:function(r,C){var t=C.getHTMLTag().toLowerCase();r.openStart(t,C);r.class(a);if(this.shouldAddIBarContext(C)){r.class(a+"-CTX");}B.renderTooltip(r,C);this.decorateRootElement(r,C);r.openEnd();this.renderBarContent(r,C);r.close(t);}});B.renderTooltip=function(r,C){var t=C.getTooltip_AsString();if(t){r.attr("title",t);}};B.addChildClassTo=function(C){C.addStyleClass("sapMBarChild");};return B;});
