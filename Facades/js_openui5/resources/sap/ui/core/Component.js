/*
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['./Manifest','./ComponentMetadata','./Element','sap/base/util/extend','sap/base/util/deepExtend','sap/base/util/merge','sap/ui/base/ManagedObject','sap/ui/base/ManagedObjectRegistry','sap/ui/thirdparty/URI','sap/ui/performance/trace/Interaction','sap/base/assert','sap/base/Log','sap/base/util/ObjectPath','sap/base/util/UriParameters','sap/base/util/isPlainObject','sap/base/util/LoaderExtensions','sap/ui/VersionInfo'],function(M,C,E,a,d,b,c,f,U,I,g,L,O,h,j,k,V){"use strict";var l={JSON:"JSON",XML:"XML",HTML:"HTML",JS:"JS",Template:"Template"};var S={lazy:"lazy",eager:"eager",waitFor:"waitFor"};function n(e){['sap-client','sap-server'].forEach(function(N){if(!e.hasSearch(N)){var v=sap.ui.getCore().getConfiguration().getSAPParam(N);if(v){e.addSearch(N,v);}}});}function o(D,m,e,i){if(e){for(var N in D){if(!m[N]&&e[N]&&e[N].uri){m[N]=i;}}}}function p(m,e,K,i){var D=e.getEntry(K);if(D!==undefined&&!j(D)){return D;}var P,v;if(i&&(P=m.getParent())instanceof C){v=P.getManifestEntry(K,i);}if(v||D){D=d({},v,D);}return D;}function q(e,i){var v=Object.create(Object.getPrototypeOf(e));v._oMetadata=e;v._oManifest=i;for(var m in e){if(!/^(getManifest|getManifestObject|getManifestEntry|getMetadataVersion)$/.test(m)&&typeof e[m]==="function"){v[m]=e[m].bind(e);}}v.getManifest=function(){return i&&i.getJson();};v.getManifestObject=function(){return i;};v.getManifestEntry=function(K,B){return p(e,i,K,B);};v.getMetadataVersion=function(){return 2;};return v;}function r(e,i,T){g(typeof e==="function","fn must be a function");var m=c._sOwnerId;try{c._sOwnerId=i;return e.call(T);}finally{c._sOwnerId=m;}}var s=c.extend("sap.ui.core.Component",{constructor:function(i,m){var e=Array.prototype.slice.call(arguments);if(typeof i!=="string"){m=i;i=undefined;}if(m&&typeof m._metadataProxy==="object"){this._oMetadataProxy=m._metadataProxy;this._oManifest=m._metadataProxy._oManifest;delete m._metadataProxy;this.getMetadata=function(){return this._oMetadataProxy;};}if(m&&typeof m._cacheTokens==="object"){this._mCacheTokens=m._cacheTokens;delete m._cacheTokens;}if(m&&Array.isArray(m._activeTerminologies)){this._aActiveTerminologies=m._activeTerminologies;delete m._activeTerminologies;}if(m&&typeof m._manifestModels==="object"){this._mManifestModels=m._manifestModels;delete m._manifestModels;}else{this._mManifestModels={};}this._mServices={};c.apply(this,e);},metadata:{stereotype:"component","abstract":true,specialSettings:{componentData:'any'},version:"0.0",includes:[],dependencies:{libs:[],components:[],ui5version:""},config:{},customizing:{},library:"sap.ui.core"}},C);f.apply(s,{onDeregister:function(e){E.registry.forEach(function(i){if(i._sapui_candidateForDestroy&&i._sOwnerId===e&&!i.getParent()){L.debug("destroying dangling template "+i+" when destroying the owner component");i.destroy();}});}});s.prototype.getManifest=function(){if(!this._oManifest){return this.getMetadata().getManifest();}else{return this._oManifest.getJson();}};s.prototype.getManifestEntry=function(K){return this._getManifestEntry(K);};s.prototype._getManifestEntry=function(K,m){if(!this._oManifest){return this.getMetadata().getManifestEntry(K,m);}else{return p(this.getMetadata(),this._oManifest,K,m);}};s.prototype.getManifestObject=function(){if(!this._oManifest){return this.getMetadata().getManifestObject();}else{return this._oManifest;}};s.prototype._isVariant=function(){if(this._oManifest){var e=this.getManifestEntry("/sap.ui5/componentName");return e&&e!==this.getManifestEntry("/sap.app/id");}else{return false;}};s.activateCustomizing=function(e){};s.deactivateCustomizing=function(e){};s.getOwnerIdFor=function(e){g(e instanceof c,"oObject must be given and must be a ManagedObject");var i=(e instanceof c)&&e._sOwnerId;return i||undefined;};s.getOwnerComponentFor=function(e){return s.get(s.getOwnerIdFor(e));};s.prototype.runAsOwner=function(e){return r(e,this.getId());};s.prototype.getInterface=function(){return this;};s.prototype._initCompositeSupport=function(m){this.oComponentData=m&&m.componentData;if(!this._isVariant()){this.getMetadata().init();}else{this._oManifest.init(this);var e=this._oManifest.getEntry("/sap.app/id");if(e){x(e,this._oManifest.resolveUri("./","manifest"));}}this.initComponentModels();if(this.onWindowError){this._fnWindowErrorHandler=function(i){var v=i.originalEvent;this.onWindowError(v.message,v.filename,v.lineno);}.bind(this);window.addEventListener("error",this._fnWindowErrorHandler);}if(this.onWindowBeforeUnload){this._fnWindowBeforeUnloadHandler=this.onWindowBeforeUnload.bind(this);window.addEventListener("beforeunload",this._fnWindowBeforeUnloadHandler);}if(this.onWindowUnload){this._fnWindowUnloadHandler=this.onWindowUnload.bind(this);window.addEventListener("unload",this._fnWindowUnloadHandler);}};s.prototype.destroy=function(){for(var e in this._mServices){if(this._mServices[e].instance){this._mServices[e].instance.destroy();}}delete this._mServices;for(var m in this._mManifestModels){this._mManifestModels[m].destroy();}delete this._mManifestModels;if(this._fnWindowErrorHandler){window.removeEventListener("error",this._fnWindowErrorHandler);delete this._fnWindowErrorHandler;}if(this._fnWindowBeforeUnloadHandler){window.removeEventListener("beforeunload",this._fnWindowBeforeUnloadHandler);delete this._fnWindowBeforeUnloadHandler;}if(this._fnWindowUnloadHandler){window.removeEventListener("unload",this._fnWindowUnloadHandler);delete this._fnWindowUnloadHandler;}if(this._oEventBus){this._oEventBus.destroy();delete this._oEventBus;}c.prototype.destroy.apply(this,arguments);sap.ui.getCore().getMessageManager().unregisterObject(this);if(!this._isVariant()){this.getMetadata().exit();}else{this._oManifest.exit(this);delete this._oManifest;}};s.prototype.getComponentData=function(){return this.oComponentData;};s.prototype.getEventBus=function(){if(!this._oEventBus){var e=this.getMetadata().getName();L.warning("Synchronous loading of EventBus, due to #getEventBus() call on Component '"+e+"'.","SyncXHR",null,function(){return{type:"SyncXHR",name:e};});var i=sap.ui.requireSync("sap/ui/core/EventBus");this._oEventBus=new i();}return this._oEventBus;};s.prototype.initComponentModels=function(){var m=this.getMetadata();if(m.isBaseClass()){return;}var e=this._getManifestEntry("/sap.app/dataSources",true)||{};var i=this._getManifestEntry("/sap.ui5/models",true)||{};this._initComponentModels(i,e,this._mCacheTokens);};s.prototype._initComponentModels=function(m,D,e){var i=s._createManifestModelConfigurations({models:m,dataSources:D,component:this,mergeParent:true,cacheTokens:e,activeTerminologies:this.getActiveTerminologies()});if(!i){return;}var v={};for(var B in i){if(!this._mManifestModels[B]){v[B]=i[B];}}var F=s._createManifestModels(v,this.toString());for(var B in F){this._mManifestModels[B]=F[B];}for(var B in this._mManifestModels){var G=this._mManifestModels[B];this.setModel(G,B||undefined);}};s.prototype.getService=function(e){if(!this._mServices[e]){this._mServices[e]={};this._mServices[e].promise=new Promise(function(R,i){sap.ui.require(["sap/ui/core/service/ServiceFactoryRegistry"],function(m){var v=this._getManifestEntry("/sap.ui5/services/"+e,true);var B=v&&v.factoryName;if(!B){i(new Error("Service "+e+" not declared!"));return;}var D=m.get(B);if(D){D.createInstance({scopeObject:this,scopeType:"component",settings:v.settings||{}}).then(function(H){if(!this.bIsDestroyed){this._mServices[e].instance=H;this._mServices[e].interface=H.getInterface();R(this._mServices[e].interface);}else{i(new Error("Service "+e+" could not be loaded as its Component was destroyed."));}}.bind(this)).catch(i);}else{var F="The ServiceFactory "+B+" for Service "+e+" not found in ServiceFactoryRegistry!";var G=this._getManifestEntry("/sap.ui5/services/"+e+"/optional",true);if(!G){L.error(F);}i(new Error(F));}}.bind(this),i);}.bind(this));}return this._mServices[e].promise;};function t(e,i){var m=e._getManifestEntry("/sap.ui5/services",true);var v=i?[]:null;if(!m){return v;}var B=Object.keys(m);if(!i&&B.some(function(D){return m[D].startup===S.waitFor;})){throw new Error("The specified component \""+e.getMetadata().getName()+"\" cannot be loaded in sync mode since it has some services declared with \"startup\" set to \"waitFor\"");}return B.reduce(function(P,D){if(m[D].lazy===false||m[D].startup===S.waitFor||m[D].startup===S.eager){var F=e.getService(D);if(m[D].startup===S.waitFor){P.push(F);}}return P;},v);}s.prototype.createComponent=function(v){g((typeof v==='string'&&v)||(typeof v==='object'&&typeof v.usage==='string'&&v.usage),"vUsage either must be a non-empty string or an object with a non-empty usage id");var m={async:true};if(v){var e;if(typeof v==="object"){e=v.usage;["id","async","settings","componentData"].forEach(function(N){if(v[N]!==undefined){m[N]=v[N];}});}else if(typeof v==="string"){e=v;}m=this._enhanceWithUsageConfig(e,m);}return s._createComponent(m,this);};s.prototype._enhanceWithUsageConfig=function(e,m){var i=this.getManifestEntry("/sap.ui5/componentUsages/"+e);if(!i){throw new Error("Component usage \""+e+"\" not declared in Component \""+this.getManifestObject().getComponentName()+"\"!");}if(i.activeTerminologies){throw new Error("Terminologies vector can't be used in component usages");}return d(i,m);};s.prototype.getActiveTerminologies=function(){return this._aActiveTerminologies?this._aActiveTerminologies.slice():undefined;};s._createComponent=function(m,e){function i(){if(m.async===true){return s.create(m);}else{return sap.ui.component(m);}}if(e){return e.runAsOwner(i);}else{return i();}};s._applyCacheToken=function(e,i,m){var v=sap.ui.getCore().getConfiguration();var B=m?"Model":"DataSource";var D=m?"[\"sap.ui5\"][\"models\"]":"[\"sap.app\"][\"dataSources\"]";var F=m&&m["sap-language"]||e.search(true)["sap-language"];var G=m&&m["sap-client"]||e.search(true)["sap-client"];if(!F){L.warning("Component Manifest: Ignoring provided \"sap-context-token="+i.cacheToken+"\" for "+B+" \""+i.dataSource+"\" ("+e.toString()+"). "+"Missing \"sap-language\" URI parameter",D+"[\""+i.dataSource+"\"]",i.componentName);return;}if(!G){L.warning("Component Manifest: Ignoring provided \"sap-context-token="+i.cacheToken+"\" for "+B+" \""+i.dataSource+"\" ("+e.toString()+"). "+"Missing \"sap-client\" URI parameter",D+"[\""+i.dataSource+"\"]",i.componentName);return;}if(G!==v.getSAPParam("sap-client")){L.warning("Component Manifest: Ignoring provided \"sap-context-token="+i.cacheToken+"\" for "+B+" \""+i.dataSource+"\" ("+e.toString()+"). "+"URI parameter \"sap-client="+G+"\" must be identical with configuration \"sap-client="+v.getSAPParam("sap-client")+"\"",D+"[\""+i.dataSource+"\"]",i.componentName);return;}if(e.hasQuery("sap-context-token")&&!e.hasQuery("sap-context-token",i.cacheToken)||m&&m["sap-context-token"]&&m["sap-context-token"]!==i.cacheToken){L.warning("Component Manifest: Overriding existing \"sap-context-token="+(e.query(true)["sap-context-token"]||m["sap-context-token"])+"\" with provided value \""+i.cacheToken+"\" for "+B+" \""+i.dataSource+"\" ("+e.toString()+").",D+"[\""+i.dataSource+"\"]",i.componentName);}if(m){if(e.hasQuery("sap-context-token")){L.warning("Component Manifest: Move existing \"sap-context-token="+e.query(true)["sap-context-token"]+"\" to metadataUrlParams for "+B+" \""+i.dataSource+"\" ("+e.toString()+").",D+"[\""+i.dataSource+"\"]",i.componentName);}e.removeQuery("sap-context-token");m["sap-context-token"]=i.cacheToken;}else{e.setQuery("sap-context-token",i.cacheToken);}};s._createManifestModelConfigurations=function(m){var e=m.component;var v=m.manifest||e.getManifestObject();var B=m.mergeParent;var D=m.cacheTokens||{};var F=e?e.toString():v.getComponentName();var G=sap.ui.getCore().getConfiguration();var H=m.activeTerminologies;if(!m.models){return null;}var J={models:m.models,dataSources:m.dataSources||{},origin:{dataSources:{},models:{}}};if(e&&B){var K=e.getMetadata();while(K instanceof C){var N=K.getManifestObject();var P=K.getManifestEntry("/sap.app/dataSources");o(J.dataSources,J.origin.dataSources,P,N);var Q=K.getManifestEntry("/sap.ui5/models");o(J.models,J.origin.models,Q,N);K=K.getParent();}}var R={};for(var T in J.models){var W=J.models[T];var X=false;var Y=null;if(typeof W==='string'){W={dataSource:W};}if(W.dataSource){var Z=J.dataSources&&J.dataSources[W.dataSource];if(typeof Z==='object'){if(Z.type===undefined){Z.type='OData';}var $;if(!W.type){switch(Z.type){case'OData':$=Z.settings&&Z.settings.odataVersion;if($==="4.0"){W.type='sap.ui.model.odata.v4.ODataModel';}else if(!$||$==="2.0"){W.type='sap.ui.model.odata.v2.ODataModel';}else{L.error('Component Manifest: Provided OData version "'+$+'" in '+'dataSource "'+W.dataSource+'" for model "'+T+'" is unknown. '+'Falling back to default model type "sap.ui.model.odata.v2.ODataModel".','["sap.app"]["dataSources"]["'+W.dataSource+'"]',F);W.type='sap.ui.model.odata.v2.ODataModel';}break;case'JSON':W.type='sap.ui.model.json.JSONModel';break;case'XML':W.type='sap.ui.model.xml.XMLModel';break;default:}}if(W.type==='sap.ui.model.odata.v4.ODataModel'&&Z.settings&&Z.settings.odataVersion){W.settings=W.settings||{};W.settings.odataVersion=Z.settings.odataVersion;}if(!W.uri){W.uri=Z.uri;X=true;}if(Z.type==='OData'&&Z.settings&&typeof Z.settings.maxAge==="number"){W.settings=W.settings||{};W.settings.headers=W.settings.headers||{};W.settings.headers["Cache-Control"]="max-age="+Z.settings.maxAge;}if(Z.type==='OData'&&Z.settings&&Z.settings.annotations){var _=Z.settings.annotations;for(var i=0;i<_.length;i++){var a1=_[i];var b1=J.dataSources[a1];if(!b1){L.error("Component Manifest: ODataAnnotation \""+a1+"\" for dataSource \""+W.dataSource+"\" could not be found in manifest","[\"sap.app\"][\"dataSources\"][\""+a1+"\"]",F);continue;}if(b1.type!=='ODataAnnotation'){L.error("Component Manifest: dataSource \""+a1+"\" was expected to have type \"ODataAnnotation\" but was \""+b1.type+"\"","[\"sap.app\"][\"dataSources\"][\""+a1+"\"]",F);continue;}if(!b1.uri){L.error("Component Manifest: Missing \"uri\" for ODataAnnotation \""+a1+"\"","[\"sap.app\"][\"dataSources\"][\""+a1+"\"]",F);continue;}var c1=new U(b1.uri);if(W.type==='sap.ui.model.odata.v2.ODataModel'||W.type==='sap.ui.model.odata.v4.ODataModel'){var d1=D.dataSources&&D.dataSources[b1.uri];if(d1||W.type==='sap.ui.model.odata.v2.ODataModel'){["sap-language","sap-client"].forEach(function(p1){if(!c1.hasQuery(p1)&&G.getSAPParam(p1)){c1.setQuery(p1,G.getSAPParam(p1));}});}if(d1){s._applyCacheToken(c1,{cacheToken:d1,componentName:F,dataSource:a1});}}var e1=J.origin.dataSources[_[i]]||v;var f1=e1._resolveUri(c1).toString();W.settings=W.settings||{};W.settings.annotationURI=W.settings.annotationURI||[];W.settings.annotationURI.push(f1);}}}else{L.error("Component Manifest: dataSource \""+W.dataSource+"\" for model \""+T+"\" not found or invalid","[\"sap.app\"][\"dataSources\"][\""+W.dataSource+"\"]",F);continue;}}if(!W.type){L.error("Component Manifest: Missing \"type\" for model \""+T+"\"","[\"sap.ui5\"][\"models\"][\""+T+"\"]",F);continue;}if(W.type==='sap.ui.model.odata.ODataModel'&&(!W.settings||W.settings.json===undefined)){W.settings=W.settings||{};W.settings.json=true;}if(W.type==="sap.ui.model.resource.ResourceModel"){if(W.uri&&W.settings&&W.settings.bundleUrl){L.warning("Defining both model uri and bundleUrl is not supported. Only model uri will be resolved.");}if(!W.uri&&W.settings&&W.settings.terminologies){if(W.bundleUrl||W.settings.bundleUrl){W.uri=W.bundleUrl||W.settings.bundleUrl;delete W.settings.bundleUrl;}}}if(W.uri){var g1=new U(W.uri);var h1=(X?J.origin.dataSources[W.dataSource]:J.origin.models[T])||v;g1=h1._resolveUri(g1);if(W.dataSource){n(g1);if(W.type==='sap.ui.model.odata.v2.ODataModel'||W.type==='sap.ui.model.odata.v4.ODataModel'){var d1=D.dataSources&&D.dataSources[Z.uri];Y=W.settings&&W.settings.metadataUrlParams;var i1=(!Y||typeof Y['sap-language']==='undefined')&&!g1.hasQuery('sap-language')&&G.getSAPParam('sap-language');if((i1&&W.type==='sap.ui.model.odata.v2.ODataModel')||d1){W.settings=W.settings||{};Y=W.settings.metadataUrlParams=W.settings.metadataUrlParams||{};if(i1){Y['sap-language']=G.getSAPParam('sap-language');}}if(d1){s._applyCacheToken(g1,{cacheToken:d1,componentName:F,dataSource:T},Y);}}}W.uri=g1.toString();}if(W.uriSettingName===undefined){switch(W.type){case'sap.ui.model.odata.ODataModel':case'sap.ui.model.odata.v2.ODataModel':case'sap.ui.model.odata.v4.ODataModel':W.uriSettingName='serviceUrl';break;case'sap.ui.model.resource.ResourceModel':W.uriSettingName='bundleUrl';break;default:}}var j1;var k1;if(e){k1=e.getComponentData();}else{k1=m.componentData;}j1=k1&&k1.startupParameters&&k1.startupParameters["sap-system"];if(!j1){j1=G.getSAPParam("sap-system");}var l1=false;var m1;if(j1&&["sap.ui.model.odata.ODataModel","sap.ui.model.odata.v2.ODataModel"].indexOf(W.type)!=-1){l1=true;m1=sap.ui.requireSync("sap/ui/model/odata/ODataUtils");}if(W.uri){if(l1){W.preOriginBaseUri=W.uri.split("?")[0];W.uri=m1.setOrigin(W.uri,{alias:j1});W.postOriginBaseUri=W.uri.split("?")[0];}if(W.uriSettingName!==undefined){W.settings=W.settings||{};if(!W.settings[W.uriSettingName]){W.settings[W.uriSettingName]=W.uri;}}else if(W.settings){W.settings=[W.uri,W.settings];}else{W.settings=[W.uri];}}else{if(l1&&W.uriSettingName!==undefined&&W.settings&&W.settings[W.uriSettingName]){W.preOriginBaseUri=W.settings[W.uriSettingName].split("?")[0];W.settings[W.uriSettingName]=m1.setOrigin(W.settings[W.uriSettingName],{alias:j1});W.postOriginUri=W.settings[W.uriSettingName].split("?")[0];}}if(l1&&W.settings&&W.settings.annotationURI){var n1=[].concat(W.settings.annotationURI);var o1=[];for(var i=0;i<n1.length;i++){o1.push(m1.setAnnotationOrigin(n1[i],{alias:j1,preOriginBaseUri:W.preOriginBaseUri,postOriginBaseUri:W.postOriginBaseUri}));}W.settings.annotationURI=o1;}if(W.type==='sap.ui.model.resource.ResourceModel'&&W.settings){if(H){W.settings.activeTerminologies=H;}v._processResourceConfiguration(W.settings,undefined,true);}if(W.settings&&!Array.isArray(W.settings)){W.settings=[W.settings];}R[T]=W;}if(v.getEntry("/sap.ui5/commands")||(e&&e._getManifestEntry("/sap.ui5/commands",true))){R["$cmd"]={type:'sap.ui.model.json.JSONModel'};}return R;};s._createManifestModels=function(m,e){var i={};for(var v in m){var B=m[v];try{sap.ui.requireSync(B.type.replace(/\./g,"/"));}catch(D){L.error("Component Manifest: Class \""+B.type+"\" for model \""+v+"\" could not be loaded. "+D,"[\"sap.ui5\"][\"models\"][\""+v+"\"]",e);continue;}var F=O.get(B.type);if(!F){L.error("Component Manifest: Class \""+B.type+"\" for model \""+v+"\" could not be found","[\"sap.ui5\"][\"models\"][\""+v+"\"]",e);continue;}var G=[null].concat(B.settings||[]);var H=F.bind.apply(F,G);var J=new H();i[v]=J;}return i;};function u(m,e,i,v){var B={afterManifest:{},afterPreload:{}};var D=b({},m.getEntry("/sap.app/dataSources"));var F=b({},m.getEntry("/sap.ui5/models"));var G=s._createManifestModelConfigurations({models:F,dataSources:D,manifest:m,componentData:e,cacheTokens:i,activeTerminologies:v});var P=h.fromQuery(window.location.search).get("sap-ui-xx-preload-component-models-"+m.getComponentName());var H=P&&P.split(",");for(var J in G){var K=G[J];if(!K.preload&&H&&H.indexOf(J)>-1){K.preload=true;L.warning("FOR TESTING ONLY!!! Activating preload for model \""+J+"\" ("+K.type+")",m.getComponentName(),"sap.ui.core.Component");}if(K.type==="sap.ui.model.resource.ResourceModel"&&Array.isArray(K.settings)&&K.settings.length>0&&K.settings[0].async!==true){B.afterPreload[J]=K;}else if(K.preload){if(sap.ui.loader._.getModuleState(K.type.replace(/\./g,"/")+".js")){B.afterManifest[J]=K;}else{L.warning("Can not preload model \""+J+"\" as required class has not been loaded: \""+K.type+"\"",m.getComponentName(),"sap.ui.core.Component");}}}return B;}function w(e){return sap.ui.require.toUrl(e.replace(/\./g,"/")+"/manifest.json");}function x(m,v){k.registerResourcePath(m.replace(/\./g,"/"),v);}function y(R){var m=[];var e=[];function v(i){if(!i._oManifest){var N=i.getComponentName();var D=w(N);var B=k.loadResource({url:D,dataType:"json",async:true}).catch(function(F){L.error("Failed to load component manifest from \""+D+"\" (component "+N+")! Reason: "+F);return{};});m.push(B);e.push(i);}var P=i.getParent();if(P&&(P instanceof C)&&!P.isBaseClass()){v(P);}}v(R);return Promise.all(m).then(function(B){for(var i=0;i<B.length;i++){if(B[i]){e[i]._applyManifest(B[i]);}}});}s._fnLoadComponentCallback=null;s._fnOnInstanceCreated=null;s._fnPreprocessManifest=null;s.create=function(m){if(m==null||typeof m!=="object"){throw new TypeError("Component.create() must be called with a configuration object.");}var P=b({},m);P.async=true;if(P.manifest===undefined){P.manifest=true;}return z(P);};sap.ui.component=function(v){if(!v){throw new Error("sap.ui.component cannot be called without parameter!");}var e=function(i){return{type:"sap.ui.component",name:i};};if(typeof v==='string'){L.warning("Do not use deprecated function 'sap.ui.component' ("+v+") + for Component instance lookup. "+"Use 'Component.get' instead","sap.ui.component",null,e.bind(null,v));return sap.ui.getCore().getComponent(v);}if(v.async){L.info("Do not use deprecated factory function 'sap.ui.component' ("+v["name"]+"). "+"Use 'Component.create' instead","sap.ui.component",null,e.bind(null,v["name"]));}else{L.warning("Do not use synchronous component creation ("+v["name"]+")! "+"Use the new asynchronous factory 'Component.create' instead","sap.ui.component",null,e.bind(null,v["name"]));}return z(v);};function z(v){var e=s.get(c._sOwnerId);var i=v.activeTerminologies||(e&&e.getActiveTerminologies())||sap.ui.getCore().getConfiguration().getActiveTerminologies();if(!v.asyncHints||!v.asyncHints.cacheTokens){var m=e&&e._mCacheTokens;if(typeof m==="object"){v.asyncHints=v.asyncHints||{};v.asyncHints.cacheTokens=m;}}function B(H,v){if(typeof s._fnOnInstanceCreated==="function"){var P=s._fnOnInstanceCreated(H,v);if(v.async&&P instanceof Promise){return P;}}if(v.async){return Promise.resolve(H);}return H;}function D(H){var N=v.name,J=v.id,K=v.componentData,P=N+'.Component',Q=v.settings;var R=new H(a({},Q,{id:J,componentData:K,_cacheTokens:v.asyncHints&&v.asyncHints.cacheTokens,_activeTerminologies:i}));g(R instanceof s,"The specified component \""+P+"\" must be an instance of sap.ui.core.Component!");L.info("Component instance Id = "+R.getId());var T=R.getMetadata().handleValidation()!==undefined||v.handleValidation;if(T){if(R.getMetadata().handleValidation()!==undefined){T=R.getMetadata().handleValidation();}else{T=v.handleValidation;}sap.ui.getCore().getMessageManager().registerObject(R,T);}var W=t(R,v.async);if(v.async){return B(R,v).then(function(){return Promise.all(W);}).then(function(){return R;});}else{B(R,v);return R;}}var F=A(v,{failOnError:true,createModels:true,waitFor:v.asyncHints&&v.asyncHints.waitFor,activeTerminologies:i});if(v.async){var G=c._sOwnerId;return F.then(function(H){return r(function(){return D(H);},G);});}else{return D(F);}}s.load=function(m){var P=b({},m);P.async=true;if(P.manifest===undefined){P.manifest=true;}return A(P,{preloadOnly:P.asyncHints&&P.asyncHints.preloadOnly});};s.get=function(i){return sap.ui.getCore().getComponent(i);};sap.ui.component.load=function(e,F){L.warning("Do not use deprecated function 'sap.ui.component.load'! Use 'Component.load' instead");return A(e,{failOnError:F,preloadOnly:e.asyncHints&&e.asyncHints.preloadOnly});};function A(i,m){var B=m.activeTerminologies,N=i.name,D=i.url,F=sap.ui.getCore().getConfiguration(),G=/^(sync|async)$/.test(F.getComponentPreload()),H=i.manifest,J,K,P,Q,R,T;function W(e,m){var v=JSON.parse(JSON.stringify(e));if(i.async){return X(v).then(function($){return new M($,m);});}else{return new M(v,m);}}function X(e){if(typeof s._fnPreprocessManifest==="function"&&e!=null){try{var v=d({},i);return s._fnPreprocessManifest(e,v);}catch($){L.error("Failed to execute flexibility hook for manifest preprocessing.",$);return Promise.reject($);}}else{return Promise.resolve(e);}}g(!D||typeof D==='string',"sUrl must be a string or undefined");if(N&&typeof D==='string'){x(N,D);}I.setStepComponent(N);if(H===undefined){J=i.manifestFirst===undefined?F.getManifestFirst():!!i.manifestFirst;K=i.manifestUrl;}else{if(i.async===undefined){i.async=true;}J=!!H;K=H&&typeof H==='string'?H:undefined;P=H&&typeof H==='object'?W(H,{url:i&&i.altManifestUrl,activeTerminologies:B}):undefined;}if(!P&&K){P=M.load({activeTerminologies:B,manifestUrl:K,componentName:N,processJson:X,async:i.async,failOnError:true});}if(P&&!i.async){N=P.getComponentName();if(N&&typeof D==='string'){x(N,D);}}if(!(P&&i.async)){if(!N){throw new Error("The name of the component is undefined.");}g(typeof N==='string',"sName must be a string");}if(J&&!P){P=M.load({activeTerminologies:B,manifestUrl:w(N),componentName:N,async:i.async,processJson:X,failOnError:false});}function Y(){return(N+".Component").replace(/\./g,"/");}function Z(e){var v=N+'.Component';if(!e){var $="The specified component controller '"+v+"' could not be found!";if(m.failOnError){throw new Error($);}else{L.warning($);}}if(P){var k1=q(e.getMetadata(),P);var l1=function(){var m1=Array.prototype.slice.call(arguments);var n1;if(m1.length===0||typeof m1[0]==="object"){n1=m1[0]=m1[0]||{};}else if(typeof m1[0]==="string"){n1=m1[1]=m1[1]||{};}n1._metadataProxy=k1;if(Q){n1._manifestModels=Q;}var o1=Object.create(e.prototype);e.apply(o1,m1);return o1;};l1.getMetadata=function(){return k1;};l1.extend=function(){throw new Error("Extending Components created by Manifest is not supported!");};return l1;}else{return e;}}function _(v,e){g((typeof v==='string'&&v)||(typeof v==='object'&&typeof v.name==='string'&&v.name),"reference either must be a non-empty string or an object with a non-empty 'name' and an optional 'url' property");if(typeof v==='object'){if(v.url){x(v.name,v.url);}return(v.lazy&&e!==true)?undefined:v.name;}return v;}function a1(v,$){var k1=v+'.Component',l1=sap.ui.getCore().getConfiguration().getDepCache(),m1,n1,o1;if(G&&v!=null&&!sap.ui.loader._.getModuleState(k1.replace(/\./g,"/")+".js")){if($){n1=V._getTransitiveDependencyForComponent(v);if(n1){o1=[n1.library];Array.prototype.push.apply(o1,n1.dependencies);return sap.ui.getCore().loadLibraries(o1,{preloadOnly:true});}else{m1=k1.replace(/\./g,"/")+(l1?'-h2-preload.js':'-preload.js');return sap.ui.loader._.loadJSResourceAsync(m1,true);}}try{m1=k1+'-preload';sap.ui.requireSync(m1.replace(/\./g,"/"));}catch(e){L.warning("couldn't preload component from "+m1+": "+((e&&e.message)||e));}}else if($){return Promise.resolve();}}function b1(e,P,v){var $=[];var k1=v?function(t1){$.push(t1);}:function(){};var l1=P.getEntry("/sap.ui5/dependencies/libs");if(l1){var m1=[];for(var n1 in l1){if(!l1[n1].lazy){m1.push(n1);}}if(m1.length>0){L.info("Component \""+e+"\" is loading libraries: \""+m1.join(", ")+"\"");k1(sap.ui.getCore().loadLibraries(m1,{async:v}));}}var o1=P.getEntry("/sap.ui5/extends/component");if(o1){k1(a1(o1,v));}var p1=[];var q1=P.getEntry("/sap.ui5/dependencies/components");if(q1){for(var e in q1){if(!q1[e].lazy){p1.push(e);}}}var r1=P.getEntry("/sap.ui5/componentUsages");if(r1){for(var s1 in r1){if(r1[s1].lazy===false&&p1.indexOf(r1[s1].name)===-1){p1.push(r1[s1].name);}}}if(p1.length>0){p1.forEach(function(e){k1(a1(e,v));});}return v?Promise.all($):undefined;}if(i.async){var c1=i.asyncHints||{},d1=[],e1=function(e){e=e.then(function(v){return{result:v,rejected:false};},function(v){return{result:v,rejected:true};});return e;},f1=function(e){if(e){d1.push(e1(e));}},g1=function($){return $;},h1,i1;h1=[];if(Array.isArray(c1.preloadBundles)){c1.preloadBundles.forEach(function(v){h1.push(sap.ui.loader._.loadJSResourceAsync(_(v,true),true));});}if(Array.isArray(c1.libs)){i1=c1.libs.map(_).filter(g1);h1.push(sap.ui.getCore().loadLibraries(i1,{preloadOnly:true}));}h1=Promise.all(h1);if(i1&&!m.preloadOnly){h1=h1.then(function(){return sap.ui.getCore().loadLibraries(i1);});}f1(h1);if(c1.components){Object.keys(c1.components).forEach(function(e){f1(a1(_(c1.components[e]),true));});}if(!P){f1(a1(N,true));}else{var j1=[];P=P.then(function(P){var e=P.getComponentName();if(typeof D==='string'){x(e,D);}P.defineResourceRoots();P._preprocess({resolveUI5Urls:true,i18nProperties:j1});return P;});if(m.createModels){f1(P.then(function(P){R=u(P,i.componentData,c1.cacheTokens,B);return P;}).then(function(P){if(Object.keys(R.afterManifest).length>0){Q=s._createManifestModels(R.afterManifest,P.getComponentName());}return P;}));}f1(P.then(function(P){var e=Promise.resolve();if(!P.getEntry("/sap.app/embeddedBy")){e=a1(P.getComponentName(),true);}return e.then(function(){return P._processI18n(true,j1);}).then(function(){if(!m.createModels){return null;}var v=Object.keys(R.afterPreload);if(v.length===0){return null;}return new Promise(function($,k1){sap.ui.require(["sap/ui/model/resource/ResourceModel"],function(l1){$(l1);},k1);}).then(function($){function k1(l1){var m1=R.afterPreload[l1];if(Array.isArray(m1.settings)&&m1.settings.length>0){var n1=m1.settings[0];n1.activeTerminologies=m.activeTerminologies;return $.loadResourceBundle(n1,true).then(function(o1){n1.bundle=o1;delete n1.terminologies;delete n1.activeTerminologies;delete n1.enhanceWith;},function(o1){L.error("Component Manifest: Could not preload ResourceBundle for ResourceModel. "+"The model will be skipped here and tried to be created on Component initialization.","[\"sap.ui5\"][\"models\"][\""+l1+"\"]",P.getComponentName());L.error(o1);delete R.afterPreload[l1];});}else{return Promise.resolve();}}return Promise.all(v.map(k1)).then(function(){if(Object.keys(R.afterPreload).length>0){var l1=s._createManifestModels(R.afterPreload,P.getComponentName());if(!Q){Q={};}for(var m1 in l1){Q[m1]=l1[m1];}}});});});}));T=function(e){if(typeof s._fnLoadComponentCallback==="function"){var v=d({},i);var $=b({},e);try{s._fnLoadComponentCallback(v,$);}catch(k1){L.error("Callback for loading the component \""+e.getComponentName()+"\" run into an error. The callback was skipped and the component loading resumed.",k1,"sap.ui.core.Component");}}};}return Promise.all(d1).then(function(v){var e=[],$=false,k1;$=v.some(function(l1){if(l1&&l1.rejected){k1=l1.result;return true;}e.push(l1.result);});if($){return Promise.reject(k1);}return e;}).then(function(v){if(P&&T){P.then(T);}return v;}).then(function(v){L.debug("Component.load: all promises fulfilled, then "+v);if(P){return P.then(function(e){P=e;N=P.getComponentName();return b1(N,P,true);});}else{return v;}}).then(function(){if(m.preloadOnly){return true;}return new Promise(function(e,v){sap.ui.require([Y()],function($){e($);},v);}).then(function(e){var v=e.getMetadata();var N=v.getComponentName();var $=w(N);var k1;if(P&&typeof H!=="object"&&(typeof K==="undefined"||K===$)){v._applyManifest(JSON.parse(JSON.stringify(P.getRawJson())));}k1=y(v);return k1.then(function(){var l1=Promise.resolve();if(!P&&m.activeTerminologies){P=new M(v.getManifestObject().getRawJson(),{process:false,activeTerminologies:B});l1=P._processI18n(true);}return l1.then(Z.bind(undefined,e));});});}).then(function(e){if(!P){return e;}var v=[];var $;var k1=P.getEntry("/sap.ui5/rootView");if(typeof k1==="string"){$="XML";}else if(k1&&typeof k1==="object"&&k1.type){$=k1.type;}if($&&l[$]){var l1="sap/ui/core/mvc/"+l[$]+"View";v.push(l1);}var m1=P.getEntry("/sap.ui5/routing");if(m1&&m1.routes){var n1=P.getEntry("/sap.ui5/routing/config/routerClass")||"sap.ui.core.routing.Router";var o1=n1.replace(/\./g,"/");v.push(o1);}var p1=b({},P.getEntry("/sap.ui5/models"));var q1=b({},P.getEntry("/sap.app/dataSources"));var r1=s._createManifestModelConfigurations({models:p1,dataSources:q1,manifest:P,cacheTokens:c1.cacheTokens,activeTerminologies:B});for(var s1 in r1){if(!r1.hasOwnProperty(s1)){continue;}var t1=r1[s1];if(!t1.type){continue;}var u1=t1.type.replace(/\./g,"/");if(v.indexOf(u1)===-1){v.push(u1);}}if(v.length>0){return Promise.all(v.map(function(u1){return new Promise(function(v1,w1){var x1=false;function y1(z1){if(x1){return;}L.warning("Can not preload module \""+u1+"\". "+"This will most probably cause an error once the module is used later on.",P.getComponentName(),"sap.ui.core.Component");L.warning(z1);x1=true;v1();}sap.ui.require([u1],v1,y1);});})).then(function(){return e;});}else{return e;}}).then(function(e){var v=m.waitFor;if(v){var $=Array.isArray(v)?v:[v];return Promise.all($).then(function(){return e;});}return e;}).catch(function(e){if(Q){for(var N in Q){var v=Q[N];if(v&&typeof v.destroy==="function"){v.destroy();}}}throw e;});}if(P){P.defineResourceRoots();P._preprocess({resolveUI5Urls:true});b1(N,P);}a1(N);return Z(sap.ui.requireSync(Y()));}if(Math.sqrt(2)<1){sap.ui.require(["sap/ui/core/Core"],function(){});}s.prototype.getCommand=function(e){var i,m=this._getManifestEntry("/sap.ui5/commands",true);if(m&&e){i=m[e];}return e?i:m;};return s;});
