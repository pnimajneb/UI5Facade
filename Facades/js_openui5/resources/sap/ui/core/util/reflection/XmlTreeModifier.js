/*!
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["./BaseTreeModifier","sap/ui/base/ManagedObject","sap/ui/base/DataType","sap/base/util/merge","sap/ui/util/XMLHelper","sap/ui/core/mvc/EventHandlerResolver","sap/base/util/includes","sap/base/util/ObjectPath","sap/base/util/isPlainObject","sap/ui/core/Fragment"],function(B,M,D,m,X,E,a,O,b){"use strict";var c=m({},B,{targets:"xmlTree",setVisible:function(C,v){if(v){C.removeAttribute("visible");}else{C.setAttribute("visible",v);}},getVisible:function(C){return c.getProperty(C,"visible");},setStashed:function(C,s){if(!s){C.removeAttribute("stashed");}else{C.setAttribute("stashed",s);}c.setVisible(C,!s);},getStashed:function(C){return c.getProperty(C,"stashed")||!c.getProperty(C,"visible");},bindProperty:function(C,p,v){C.setAttribute(p,"{"+v+"}");},unbindProperty:function(C,p){C.removeAttribute(p);},_setProperty:function(C,p,P,e){var v=c._getSerializedValue(P);if(e){v=c._escapeCurlyBracketsInString(v);}C.setAttribute(p,v);},setProperty:function(C,p,P){c._setProperty(C,p,P,true);},getProperty:function(C,p){var P=C.getAttribute(p);var o=c.getControlMetadata(C).getProperty(p);if(o){var t=o.getType();if(p==="value"&&c.getControlType(C)==="sap.ui.core.CustomData"&&c.getProperty(C,"key")==="sap-ui-custom-settings"){t=D.getType("object");}if(P===null){P=o.getDefaultValue()||t.getDefaultValue();}else{var u=M.bindingParser(P,undefined,true);if(b(u)){if(u.path||u.parts){P=undefined;}else{P=u;}}else{P=t.parseValue(u||P);}}}return P;},isPropertyInitial:function(C,p){var P=C.getAttribute(p);return(P==null);},setPropertyBinding:function(C,p,P){if(typeof P!=="string"){throw new Error("For XML, only strings are supported to be set as property binding.");}C.setAttribute(p,P);},getPropertyBinding:function(C,p){var P=C.getAttribute(p);if(P){var u=M.bindingParser(P,undefined,true);if(u&&(u.path||u.parts)){return u;}}},createControl:function(C,A,v,s,S,d){var i,l,e;if(!c.bySelector(s,A,v)){var f=C.split('.');var n="";if(f.length>1){l=f.pop();n=f.join('.');}var N=v.ownerDocument.createElementNS(n,l);i=c.getControlIdBySelector(s,A);if(i){N.setAttribute("id",i);}if(S){c.applySettings(N,S);}return d?Promise.resolve(N):N;}else{e=new Error("Can't create a control with duplicated ID "+i);if(d){return Promise.reject(e);}throw e;}},applySettings:function(C,s){var o=c.getControlMetadata(C);var d=o.getJSONKeys();Object.keys(s).forEach(function(k){var K=d[k];var v=s[k];switch(K._iKind){case 0:c._setProperty(C,k,v,false);break;case 3:c.setAssociation(C,k,v);break;default:throw new Error("Unsupported in applySettings on XMLTreeModifier: "+k);}});},_byId:function(i,v){if(v){if(v.ownerDocument&&v.ownerDocument.getElementById&&v.ownerDocument.getElementById(i)){return v.ownerDocument.getElementById(i);}else{return v.querySelector("[id='"+i+"']");}}},getId:function(C){return C.getAttribute("id");},getParent:function(C){var p=C.parentNode;if(!c.getId(p)&&!c._isExtensionPoint(p)){p=p.parentNode;}return p;},_getLocalName:function(x){return x.localName||x.baseName||x.nodeName;},getControlType:function(C){return c._getControlTypeInXml(C);},setAssociation:function(p,n,i){if(typeof i!=="string"){i=c.getId(i);}p.setAttribute(n,i);},getAssociation:function(p,n){return p.getAttribute(n);},getAllAggregations:function(C){var o=c.getControlMetadata(C);return o.getAllAggregations();},getAggregation:function(p,n){var A=c._findAggregationNode(p,n);var s=c._isSingleValueAggregation(p,n);var C=[];if(A){C=c._getControlsInAggregation(p,A);}else if(c._isAltTypeAggregation(p,n)&&s){C.push(c.getProperty(p,n));}if(n==="customData"){var d="http://schemas.sap.com/sapui5/extension/sap.ui.core.CustomData/1";var e;var N=Array.prototype.slice.call(p.attributes).reduce(function(f,g){var l=c._getLocalName(g);if(g.namespaceURI===d){var o=p.ownerDocument.createElementNS("sap.ui.core","CustomData");o.setAttribute("key",l);o.setAttribute("value",g.value);f.push(o);}else if(g.namespaceURI&&g.name.indexOf("xmlns:")!==0){if(!e){e={};}if(!e.hasOwnProperty(g.namespaceURI)){e[g.namespaceURI]={};}e[g.namespaceURI][l]=g.nodeValue;}return f;},[]);C=C.concat(N);if(e){var o=p.ownerDocument.createElementNS("sap.ui.core","CustomData");o.setAttribute("key","sap-ui-custom-settings");c.setProperty(o,"value",e);C.push(o);}}return s?C[0]:C;},insertAggregation:function(p,n,o,i,v){var A=c._findAggregationNode(p,n);if(!A){var N=p.namespaceURI;A=c.createControl(N+"."+n,undefined,v);p.appendChild(A);}if(i>=A.childElementCount){A.appendChild(o);}else{var r=c._getControlsInAggregation(p,A)[i];A.insertBefore(o,r);}},removeAggregation:function(p,n,o){var A=c._findAggregationNode(p,n);A.removeChild(o);},removeAllAggregation:function(C,n){var A=c._findAggregationNode(C,n);if(C===A){var d=c._getControlsInAggregation(C,C);d.forEach(function(o){C.removeChild(o);});}else{C.removeChild(A);}},_findAggregationNode:function(p,n){var A;var C=c._children(p);for(var i=0;i<C.length;i++){var N=C[i];if(N.localName===n){A=N;break;}}if(!A&&c._isDefaultAggregation(p,n)){A=p;}return A;},_isDefaultAggregation:function(p,A){var C=c.getControlMetadata(p);var d=C.getDefaultAggregation();return d&&A===d.name;},_isNotNamedAggregationNode:function(p,C){var A=c.getAllAggregations(p);var o=A[C.localName];return p.namespaceURI!==C.namespaceURI||!o;},_isSingleValueAggregation:function(p,A){var d=c.getAllAggregations(p);var o=d[A];return!o.multiple;},_isAltTypeAggregation:function(p,A){var C=c.getControlMetadata(p);var o=C.getAllAggregations()[A];return!!o.altTypes;},_isExtensionPoint:function(C){return c._getControlTypeInXml(C)==="sap.ui.core.ExtensionPoint";},getControlMetadata:function(C){return c._getControlMetadataInXml(C);},_getControlsInAggregation:function(p,A){var C=Array.prototype.slice.call(c._children(A));return C.filter(c._isNotNamedAggregationNode.bind(this,p));},_children:function(p){if(p.children){return p.children;}else{var C=[];for(var i=0;i<p.childNodes.length;i++){var n=p.childNodes[i];if(n.nodeType===n.ELEMENT_NODE){C.push(n);}}return C;}},getBindingTemplate:function(C,A){var o=c._findAggregationNode(C,A);if(o){var d=c._children(o);if(d.length===1){return d[0];}}},updateAggregation:function(C,A){},findIndexInParentAggregation:function(C){var p,A,d;p=c.getParent(C);if(!p){return-1;}A=c.getParentAggregationName(C,p);d=c.getAggregation(p,A);if(Array.isArray(d)){d=d.filter(function(C){if(c._isExtensionPoint(C)){return true;}return!c.getProperty(C,"stashed");});return d.indexOf(C);}else{return 0;}},getParentAggregationName:function(C,p){var n,A;if(!p.isSameNode(C.parentNode)){n=false;}else{n=c._isNotNamedAggregationNode(p,C);}if(n){A=c.getControlMetadata(p).getDefaultAggregationName();}else{A=c._getLocalName(C.parentNode);}return A;},findAggregation:function(C,A){var o=c.getControlMetadata(C);var d=o.getAllAggregations();if(d){return d[A];}},validateType:function(C,A,p,f,i){var t=A.type;if(A.multiple===false&&c.getAggregation(p,A.name)&&c.getAggregation(p,A.name).length>0){return false;}var d=sap.ui.xmlfragment({fragmentContent:f});if(!Array.isArray(d)){d=[d];}var r=c._isInstanceOf(d[i],t)||c._hasInterface(d[i],t);d.forEach(function(F){F.destroy();});return r;},instantiateFragment:function(f,n,v){var C;var F=X.parse(f);F=c._checkAndPrefixIdsInFragment(F,n);if(F.localName==="FragmentDefinition"){C=c._getElementNodeChildren(F);}else{C=[F];}C.forEach(function(N){if(c._byId(N.getAttribute("id"),v)){throw Error("The following ID is already in the view: "+N.getAttribute("id"));}});return C;},templateControlFragment:function(f,p){return B._templateFragment(f,p).then(function(F){return c._children(F);});},destroy:function(C){var p=C.parentNode;if(p){p.removeChild(C);}},_getFlexCustomData:function(C,t){if(!C){return undefined;}return C.getAttributeNS("sap.ui.fl",t);},attachEvent:function(n,e,f,d){if(typeof O.get(f)!=="function"){throw new Error("Can't attach event because the event handler function is not found or not a function.");}var v=c.getProperty(n,e)||"";var g=E.parse(v);var s=f;var p=["$event"];if(d){p.push(JSON.stringify(d));}s+="("+p.join(",")+")";if(!a(g,s)){g.push(s);}n.setAttribute(e,g.join(";"));},detachEvent:function(n,e,f){if(typeof O.get(f)!=="function"){throw new Error("Can't attach event because the event handler function is not found or not a function.");}var v=c.getProperty(n,e)||"";var d=E.parse(v);var i=d.findIndex(function(s){return s.includes(f);});if(i>-1){d.splice(i,1);}if(d.length){n.setAttribute(e,d.join(";"));}else{n.removeAttribute(e);}},bindAggregation:function(n,A,v,V){c.bindProperty(n,A,v.path);c.insertAggregation(n,A,v.template,0,V);},unbindAggregation:function(n,A){if(n.hasAttribute(A)){n.removeAttribute(A);c.removeAllAggregation(n,A);}},getExtensionPointInfo:function(e,v){if(v&&e){var d=Array.prototype.slice.call(v.getElementsByTagNameNS("sap.ui.core","ExtensionPoint"));var f=d.filter(function(h){return h.getAttribute("name")===e;});var o=(f.length===1)?f[0]:undefined;if(o){var p=c.getParent(o);var g={parent:p,aggregationName:c.getParentAggregationName(o,p),index:c.findIndexInParentAggregation(o)+1,defaultContent:Array.prototype.slice.call(c._children(o))};return g;}}}});return c;},true);
