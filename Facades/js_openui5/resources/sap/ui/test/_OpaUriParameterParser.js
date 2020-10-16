/*!
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/base/Object","sap/ui/test/_OpaLogger","sap/ui/thirdparty/jquery","sap/ui/thirdparty/URI"],function(U,_,$,a){"use strict";var b=U.extend("sap.ui.test._OpaUriParameterParser",{});var l=_.getLogger("sap.ui.test._OpaUriParameterParser");b.PREFIX="opa";b.BLACKLIST_PATTERNS=[/^opa((?!(Frame)).*)$/,/hidepassed/,/noglobals/,/notrycatch/,/coverage/,/module/,/filter/];b._getOpaParams=function(){var o={};var u=new a().search(true);for(var s in u){if(s.startsWith(b.PREFIX)){var O=s.substr(b.PREFIX.length);O=O.charAt(0).toLowerCase()+O.substr(1);o[O]=b._parseParam(u[s]);}}return o;};b._getAppParams=function(){var A={};var u=new a().search(true);for(var s in u){if(b._isBlacklistedParam(s)){l.debug("URI parameter '"+s+"' is recognized as OPA parameter and will not be set in application frame!");}else{A[s]=u[s];}}return A;};b._isBlacklistedParam=function(p){var i=false;b.BLACKLIST_PATTERNS.forEach(function(P){i=i||(p&&p.match(P));});return i;};b._parseParam=function(p){var P=p;["bool","integer","floating"].forEach(function(t){var m=b._parsers[t](p);P=m.parsed?m.value:P;});return P;};b._parsers={bool:function(p){var r={};if(p&&p.match(/^true$/i)){r={parsed:true,value:true};}if(p&&p.match(/^false$/i)){r={parsed:true,value:false};}return r;},integer:function(p){var v=parseInt(p);return{parsed:b._isNumber(v),value:v};},floating:function(p){var v=parseFloat(p);return{parsed:b._isNumber(v),value:v};}};b._isNumber=function(v){return typeof v==="number"&&!isNaN(v);};return b;});
