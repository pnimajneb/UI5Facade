/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['jquery.sap.global','sap/ui/base/ManagedObject','sap/ui/core/Control','sap/ui/core/Element'],function(q,M,C,E){"use strict";var c=["getParent","setParent","_getPropertiesToPropagate","destroy"];function l(o,m){var b=sap.ui.require(m);return typeof b==="function"&&(o instanceof b);}function u(f){if(f._mProxyMethods){c.map(function(m){f[m]=f._mProxyMethods[m];});delete f._mProxyMethods;}}function r(R,o,f){if(!f._mProxyMethods){f._mProxyMethods={};c.map(function(m){f._mProxyMethods[m]=f[m];});}f.getParent=function(){return o;};f.setParent=function(){u(this);return this.setParent.apply(this,arguments);};f.destroy=function(){u(this);this.destroy.apply(this,arguments);};f._getPropertiesToPropagate=function(){if(l(R,"sap.ui.core.FragmentControl")){return R._getPropertiesToPropagate();}return this.getParent()._getPropertiesToPropagate();};}function a(){var p=this.getParent();if(p){var n=this.sParentAggregationName,b=p.getBinding(n),A=p.getMetadata().getAggregation(n);if(!A||!A.multiple){throw new Error("Cannot use FragmentControl proxy with single aggregations (="+n+" in parent "+p+") on lists");}if(b){if(!p[A._sGetter].fnOriginalGetter){var o=p[A._sGetter];p[A._sGetter]=function(){var R=[];if(b){var d=b.getContexts();for(var i=0;i<d.length;i++){var O=d[i].getProperty();r(b.getModel().getRootObject(),this,O);R.push(O);}this.mAggregations[A.name]=R;}return o.apply(this,[]);};p[A._sGetter].fnOriginalGetter=o;}}}}var F=M.extend("sap.ui.core.FragmentProxy",{constructor:function(i,s){if(!s){s=i;i=M.getMetadata().uid();}if(s.ref){return new S(i,{ref:s.ref});}else{if(!s.type){s.type="sap.ui.core.Control";}var b=sap.ui.require(q.sap.getResourceName(s.type,""));if(!b){q.sap.log.debug("The given proxy type "+s.type+" is unknown. Using control instead.");s.type="sap.ui.core.Control";b=C;}var I=new(b)();if(!(I instanceof E)){q.sap.log.error("The given type "+s.type+" needs to derive from sap.ui.core.Element");return null;}I.attachModelContextChange(a);return I;}},metadata:{properties:{type:{type:"string"}},aggregations:{ref:{type:"sap.ui.core.Control",multiple:true,_doesNotRequireFactory:true}}}});var S=C.extend("sap.ui.core.SingleFragmentProxy",{metadata:{aggregations:{ref:{type:"sap.ui.core.Control",multiple:true,_doesNotRequireFactory:true}}},renderer:function(R,o){var b=o._oContent;if(b&&b.getParent()===o.getParent()){R.renderControl(b);}}});S.prototype.updateRef=function(){var b=this.getBinding("ref");if(b){var o=b.getModel().getProperty(b.getPath(),b.getContext());if(Array.isArray(o)){q.sap.log.warning("Cannot add FragmentControl proxy with multiple aggregations");return;}if(o&&!o._bIsBeingDestroyed){r(b.getModel().getRootObject(),this.getParent(),o);if(o.getParent()){o.getParent().invalidate();}}this._oContent=o;}};return F;});