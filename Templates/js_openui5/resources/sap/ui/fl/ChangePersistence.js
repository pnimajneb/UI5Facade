/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2018 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["sap/ui/fl/Change","sap/ui/fl/Variant","sap/ui/fl/Utils","sap/ui/fl/LrepConnector","sap/ui/fl/Cache","sap/ui/fl/context/ContextManager","sap/ui/fl/registry/Settings","sap/ui/fl/transport/TransportSelection","sap/ui/fl/variants/VariantController","sap/ui/core/BusyIndicator","sap/m/MessageBox"],function(C,V,U,L,a,b,S,T,c,B,M){"use strict";var d=function(m){this._mComponent=m;this._mChanges={mChanges:{},mDependencies:{},mDependentChangesOnMe:{}};this._mChangesInitial={};this._mVariantsChanges={};if(!this._mComponent||!this._mComponent.name){U.log.error("The Control does not belong to an SAPUI5 component. Personalization and changes for this control might not work as expected.");throw new Error("Missing component name.");}this._oVariantController=new c(this._mComponent.name,this._mComponent.appVersion,{});this._oTransportSelection=new T();this._oConnector=this._createLrepConnector();this._aDirtyChanges=[];this._oMessagebundle=undefined;this._mChangesEntries={};};d.prototype.getComponentName=function(){return this._mComponent.name;};d.prototype._createLrepConnector=function(){return L.createConnector();};d.prototype.getCacheKey=function(){return a.getCacheKey(this._mComponent);};d.prototype._preconditionsFulfilled=function(A,i,o){if(!o.fileName){U.log.warning("A change without fileName is detected and excluded from component: "+this._mComponent.name);return false;}function _(){if(i){return(o.fileType==="change")||(o.fileType==="variant");}return(o.fileType==="change")&&(o.changeType!=="defaultVariant");}function e(){if(i){if((o.fileType==="variant")||(o.changeType==="defaultVariant")){return o.selector&&o.selector.persistencyKey;}}return true;}function f(){return b.doesContextMatch(o,A);}function g(){if((o.fileType==="ctrl_variant")||(o.fileType==="ctrl_variant_change")||(o.fileType==="ctrl_variant_management_change")){return o.variantManagementReference||o.variantReference||(o.selector&&o.selector.id);}}if((_()&&e()&&f())||g()){return true;}return false;};d.prototype.getChangesForComponent=function(p){return a.getChangesFillingCache(this._oConnector,this._mComponent,p).then(function(w){var o=p&&p.oComponent;if(w.changes&&w.changes.settings){S._storeInstance(w.changes.settings);}if(!w.changes||((!w.changes.changes||w.changes.changes.length==0)&&(!w.changes.variantSection||jQuery.isEmptyObject(w.changes.variantSection)))){return[];}var e=w.changes.changes;if(!this._oMessagebundle&&w.messagebundle&&o){if(!o.getModel("i18nFlexVendor")){if(e.some(function(j){return j.layer==="VENDOR";})){this._oMessagebundle=w.messagebundle;var m=new sap.ui.model.json.JSONModel(this._oMessagebundle);o.setModel(m,"i18nFlexVendor");}}}if(w.changes.variantSection&&Object.keys(w.changes.variantSection).length!==0&&Object.keys(this._oVariantController._getChangeFileContent()).length===0){this._oVariantController._setChangeFileContent(w,o);}var i=p&&p.includeCtrlVariants;if(Object.keys(this._oVariantController._getChangeFileContent()).length>0){var v=this._oVariantController.loadInitialChanges();e=i?e:e.concat(v);}if(i&&w.changes.variantSection){e=e.concat(this._getAllCtrlVariantChanges(w.changes.variantSection));}var s=p&&p.currentLayer;if(s){var f=[];e.forEach(function(j){if(j.layer===s){f.push(j);}});e=f;}else if(U.isLayerFilteringRequired()&&!(p&&p.ignoreMaxLayerParameter)){var F=[];e.forEach(function(j){if(!U.isOverMaxLayer(j.layer)){F.push(j);}});e=F;}var I=p&&p.includeVariants;var h=w.changes.contexts||[];return new Promise(function(r){b.getActiveContexts(h).then(function(A){r(e.filter(this._preconditionsFulfilled.bind(this,A,I)).map(g.bind(this)));}.bind(this));}.bind(this));}.bind(this));function g(o){var e;if(!this._mChangesEntries[o.fileName]){this._mChangesEntries[o.fileName]=new C(o);}e=this._mChangesEntries[o.fileName];e.setState(C.states.PERSISTED);return e;}};d.prototype._getAllCtrlVariantChanges=function(v){var e=[];Object.keys(v).forEach(function(s){var o=v[s];o.variants.forEach(function(f){if(Array.isArray(f.variantChanges.setVisible)&&f.variantChanges.setVisible.length>0){var A=f.variantChanges.setVisible.slice(-1)[0];if(!A.content.visible&&A.content.createdByReset){return;}}Object.keys(f.variantChanges).forEach(function(g){e=e.concat(f.variantChanges[g].slice(-1)[0]);});e=e.concat(f.controlChanges);e=(f.content.fileName!==s)?e.concat([f.content]):e;});Object.keys(o.variantManagementChanges).forEach(function(f){e=e.concat(o.variantManagementChanges[f].slice(-1)[0]);});});return e;};d.prototype.getChangesForVariant=function(s,e,p){if(this._mVariantsChanges[e]){return Promise.resolve(this._mVariantsChanges[e]);}var i=function(o){var f=false;var g=o._oDefinition.selector;jQuery.each(g,function(h,v){if(h===s&&v===e){f=true;}});return f;};var l=function(k,t){U.log.error("key : "+k+" and text : "+t.value);};return this.getChangesForComponent(p).then(function(f){return f.filter(i);}).then(function(f){this._mVariantsChanges[e]={};if(f&&f.length===0){return L.isFlexServiceAvailable().then(function(g){if(g===false){return Promise.reject();}return Promise.resolve(this._mVariantsChanges[e]);}.bind(this));}var I;f.forEach(function(o){I=o.getId();if(o.isValid()){if(this._mVariantsChanges[e][I]&&o.isVariant()){U.log.error("Id collision - two or more variant files having the same id detected: "+I);jQuery.each(o.getDefinition().texts,l);U.log.error("already exists in variant : ");jQuery.each(this._mVariantsChanges[e][I].getDefinition().texts,l);}this._mVariantsChanges[e][I]=o;}}.bind(this));return this._mVariantsChanges[e];}.bind(this));};d.prototype.addChangeForVariant=function(s,e,p){var f,i,I,o,g;if(!p){return undefined;}if(!p.type){U.log.error("sap.ui.fl.Persistence.addChange : type is not defined");}var h=jQuery.type(p.content);if(h!=='object'&&h!=='array'){U.log.error("mParameters.content is not of expected type object or array, but is: "+h,"sap.ui.fl.Persistence#addChange");}I={};if(typeof(p.texts)==="object"){jQuery.each(p.texts,function(j,t){I[j]={value:t,type:"XFLD"};});}var v={creation:this._mComponent.appVersion,from:this._mComponent.appVersion};if(this._mComponent.appVersion&&p.developerMode){v.to=this._mComponent.appVersion;}i={changeType:p.type,service:p.ODataService,texts:I,content:p.content,reference:this._mComponent.name,isVariant:p.isVariant,packageName:p.packageName,isUserDependent:p.isUserDependent,validAppVersions:v};i.selector={};i.selector[s]=e;f=C.createInitialFileContent(i);if(p.id){f.fileName=p.id;}o=new C(f);g=o.getId();if(!this._mVariantsChanges[e]){this._mVariantsChanges[e]={};}this._mVariantsChanges[e][g]=o;return o.getId();};d.prototype.saveAllChangesForVariant=function(s){var p=[];var t=this;jQuery.each(this._mVariantsChanges[s],function(i,o){var e=o.getId();switch(o.getPendingAction()){case"NEW":p.push(t._oConnector.create(o.getDefinition(),o.getRequest(),o.isVariant()).then(function(r){o.setResponse(r.response);if(a.isActive()){a.addChange({name:t._mComponent.name,appVersion:t._mComponent.appVersion},r.response);}return r;}));break;case"UPDATE":p.push(t._oConnector.update(o.getDefinition(),o.getId(),o.getRequest(),o.isVariant()).then(function(r){o.setResponse(r.response);if(a.isActive()){a.updateChange({name:t._mComponent.name,appVersion:t._mComponent.appVersion},r.response);}return r;}));break;case"DELETE":p.push(t._oConnector.deleteChange({sChangeName:o.getId(),sLayer:o.getLayer(),sNamespace:o.getNamespace(),sChangelist:o.getRequest()},o.isVariant()).then(function(r){var o=t._mVariantsChanges[s][e];if(o.getPendingAction()==="DELETE"){delete t._mVariantsChanges[s][e];}if(a.isActive()){a.deleteChange({name:t._mComponent.name,appVersion:t._mComponent.appVersion},o.getDefinition());}return r;}));break;default:break;}});return Promise.all(p);};d.prototype._addChangeIntoMap=function(o,e){var s=e.getSelector();if(s&&s.id){var f=s.id;if(s.idIsLocal){f=o.createId(f);}this._addMapEntry(f,e);if(s.idIsLocal===undefined&&f.indexOf("---")!=-1){var g=f.split("---")[0];if(g!==o.getId()){f=f.split("---")[1];f=o.createId(f);this._addMapEntry(f,e);}}}return this._mChanges;};d.prototype._addMapEntry=function(s,o){if(!this._mChanges.mChanges[s]){this._mChanges.mChanges[s]=[];}this._mChanges.mChanges[s].push(o);};d.prototype._addDependency=function(D,o){if(!this._mChanges.mDependencies[D.getId()]){this._mChanges.mDependencies[D.getId()]={changeObject:D,dependencies:[]};}this._mChanges.mDependencies[D.getId()].dependencies.push(o.getId());if(!this._mChanges.mDependentChangesOnMe[o.getId()]){this._mChanges.mDependentChangesOnMe[o.getId()]=[];}this._mChanges.mDependentChangesOnMe[o.getId()].push(D.getId());};d.prototype._addControlsDependencies=function(D,e){if(e.length>0){if(!this._mChanges.mDependencies[D.getId()]){this._mChanges.mDependencies[D.getId()]={changeObject:D,dependencies:[],controlsDependencies:[]};}this._mChanges.mDependencies[D.getId()].controlsDependencies=e;}};d.prototype.loadChangesMapForComponent=function(o,p){p.oComponent=o;return this.getChangesForComponent(p).then(e.bind(this));function e(f){this._mChanges={mChanges:{},mDependencies:{},mDependentChangesOnMe:{}};f.forEach(this._addChangeAndUpdateDependencies.bind(this,o));this._mChangesInitial=jQuery.extend(true,{},this._mChanges);return this.getChangesMapForComponent.bind(this);}};d.prototype.copyDependenciesFromInitialChangesMap=function(o,D){var i=jQuery.extend(true,{},this._mChangesInitial.mDependencies);var I=i[o.getId()];if(I){var n=[];I.dependencies.forEach(function(s){if(D(s)){if(!this._mChanges.mDependentChangesOnMe[s]){this._mChanges.mDependentChangesOnMe[s]=[];}this._mChanges.mDependentChangesOnMe[s].push(o.getId());n.push(s);}}.bind(this));I.dependencies=n;this._mChanges.mDependencies[o.getId()]=I;}return this._mChanges;};d.prototype._addChangeAndUpdateDependencies=function(o,e,I,f){this._addChangeIntoMap(o,e);var A=U.getAppComponentForControl(o);var D=e.getDependentIdList(A);var g=e.getDependentControlIdList(A);this._addControlsDependencies(e,g);var p;var P;var h;var F;for(var i=I-1;i>=0;i--){p=f[i];P=f[i].getDependentIdList(A);F=false;for(var j=0;j<D.length&&!F;j++){h=P.indexOf(D[j]);if(h>-1){this._addDependency(e,p);F=true;}}}};d.prototype.getChangesMapForComponent=function(){return this._mChanges;};d.prototype.getChangesForView=function(v,p){var t=this;return this.getChangesForComponent(p).then(function(f){return f.filter(e.bind(t));});function e(o){var s=o.getSelector();if(!s){return false;}var f=s.id;if(!f||!p){return false;}var g=f.slice(0,f.lastIndexOf("--"));var v;if(o.getSelector().idIsLocal){var A=p.appComponent;if(A){v=A.getLocalId(p.viewId);}}else{v=p.viewId;}return g===v;}};d.prototype.addChange=function(v,o){var e=this.addDirtyChange(v);this._addChangeIntoMap(o,e);this._addPropagationListener(o);return e;};d.prototype.addDirtyChange=function(v){var n;if(v instanceof C||v instanceof V){n=v;}else{n=new C(v);}this._aDirtyChanges.push(n);return n;};d.prototype._addPropagationListener=function(o){if(o){var f=function(p){return!p._bIsSapUiFlFlexControllerApplyChangesOnControl;};var n=o.getPropagationListeners().every(f);if(n){var m=o.getManifest();var v=U.getAppVersionFromManifest(m);var F=sap.ui.fl.FlexControllerFactory.create(this.getComponentName(),v);var p=F.getBoundApplyChangesOnControl(this.getChangesMapForComponent.bind(this),o);o.addPropagationListener(p);}}};d.prototype.saveDirtyChanges=function(s){var D=this._aDirtyChanges.slice(0);var e=this._aDirtyChanges;var r=this._getRequests(D);var p=this._getPendingActions(D);if(p.length===1&&r.length===1&&p[0]==="NEW"){var R=r[0];var P=this._prepareDirtyChanges(e);return this._oConnector.create(P,R).then(this._massUpdateCacheAndDirtyState(e,D,s));}else{return D.reduce(function(f,o){var g=f.then(this._performSingleSaveAction(o).bind(this));g.then(this._updateCacheAndDirtyState(e,o,s));return g;}.bind(this),Promise.resolve());}};d.prototype._performSingleSaveAction=function(D){return function(){if(D.getPendingAction()==="NEW"){return this._oConnector.create(D.getDefinition(),D.getRequest());}if(D.getPendingAction()==="DELETE"){return this._oConnector.deleteChange({sChangeName:D.getId(),sLayer:D.getLayer(),sNamespace:D.getNamespace(),sChangelist:D.getRequest()});}};};d.prototype._updateCacheAndDirtyState=function(D,o,s){var t=this;return function(){if(!s){if(o.getPendingAction()==="NEW"&&o.getFileType()!=="ctrl_variant_change"&&o.getFileType()!=="ctrl_variant_management_change"&&o.getFileType()!=="ctrl_variant"&&!o.getVariantReference()){a.addChange(t._mComponent,o.getDefinition());}else if(o.getPendingAction()==="DELETE"){a.deleteChange(t._mComponent,o.getDefinition());}}var i=D.indexOf(o);if(i>-1){D.splice(i,1);}};};d.prototype._massUpdateCacheAndDirtyState=function(D,e,s){e.forEach(function(o){this._updateCacheAndDirtyState(D,o,s)();},this);};d.prototype._getRequests=function(D){var r=[];D.forEach(function(o){var R=o.getRequest();if(r.indexOf(R)===-1){r.push(R);}});return r;};d.prototype._getPendingActions=function(D){var p=[];D.forEach(function(o){var P=o.getPendingAction();if(p.indexOf(P)===-1){p.push(P);}});return p;};d.prototype._prepareDirtyChanges=function(D){var e=[];D.forEach(function(o){e.push(o.getDefinition());});return e;};d.prototype.getDirtyChanges=function(){return this._aDirtyChanges;};d.prototype.deleteChange=function(o){var n=this._aDirtyChanges.indexOf(o);if(n>-1){if(o.getPendingAction()==="DELETE"){return;}this._aDirtyChanges.splice(n,1);this._deleteChangeInMap(o);return;}o.markForDeletion();this.addDirtyChange(o);this._deleteChangeInMap(o);};d.prototype._deleteChangeInMap=function(o){var s=o.getId();var m=this._mChanges.mChanges;var D=this._mChanges.mDependencies;var e=this._mChanges.mDependentChangesOnMe;Object.keys(m).some(function(k){var f=m[k];var n=f.map(function(E){return E.getId();}).indexOf(o.getId());if(n!==-1){f.splice(n,1);return true;}});Object.keys(D).forEach(function(k){if(k===s){delete D[k];}else if(D[k].dependencies&&jQuery.isArray(D[k].dependencies)&&D[k].dependencies.indexOf(s)!==-1){D[k].dependencies.splice(D[k].dependencies.indexOf(s),1);if(D[k].dependencies.length===0){delete D[k];}}});Object.keys(e).forEach(function(k){if(k===s){delete e[k];}else if(jQuery.isArray(e[k])&&e[k].indexOf(s)!==-1){e[k].splice(e[k].indexOf(s),1);if(e[k].length===0){delete e[k];}}});};d.prototype.loadSwitchChangesMapForComponent=function(v,s,n){return this._oVariantController.getChangesForVariantSwitch(v,s,n,this._mChanges.mChanges);};d.prototype.transportAllUIChanges=function(r,s,l){var h=function(e){B.hide();var R=sap.ui.getCore().getLibraryResourceBundle("sap.ui.fl");var m=R.getText("MSG_TRANSPORT_ERROR",e?[e.message||e]:undefined);var t=R.getText("HEADER_TRANSPORT_ERROR");U.log.error("transport error"+e);M.show(m,{icon:M.Icon.ERROR,title:t,styleClass:s});return"Error";};return this._oTransportSelection.openTransportSelection(null,r,s).then(function(t){if(this._oTransportSelection.checkTransportInfo(t)){B.show(0);return this.getChangesForComponent({currentLayer:l,includeCtrlVariants:true}).then(function(A){return this._oTransportSelection._prepareChangesForTransport(t,A).then(function(){B.hide();});}.bind(this));}else{return"Cancel";}}.bind(this))['catch'](h);};d.prototype.resetChanges=function(l,g){return this.getChangesForComponent({currentLayer:l,includeCtrlVariants:true}).then(function(e){return S.getInstance(this.getComponentName()).then(function(s){if(!s.isProductiveSystem()&&!s.hasMergeErrorOccured()){return this._oTransportSelection.setTransports(e,sap.ui.getCore().getComponent(this.getComponentName()));}}.bind(this)).then(function(){var u="?reference="+this.getComponentName()+"&appVersion="+this._mComponent.appVersion+"&layer="+l+"&generator="+g;if(e.length>0){u=u+"&changelist="+e[0].getRequest();}return this._oConnector.send("/sap/bc/lrep/changes/"+u,"DELETE");}.bind(this));}.bind(this));};return d;},true);