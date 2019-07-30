/*!
 * OpenUI5
 * (c) Copyright 2009-2019 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['sap/ui/Device',"sap/base/Log","sap/ui/thirdparty/jquery"],function(D,L,q){"use strict";return{_routeMatched:function(a,s,n){var r=this._oRouter,t,c,e,v=null,T=null,i,o,C,b=this;r._matchedRoute=this;if(!s||s===true){i=true;s=Promise.resolve();}if(this._oParent){s=this._oParent._routeMatched(a,s);}else if(this._oNestingParent){this._oNestingParent._routeMatched(a,s,this);}c=q.extend({},r._oConfig,this._oConfig);o=q.extend({},a);o.routeConfig=c;e={name:c.name,arguments:a,config:c};if(n){e.nestedRoute=n;}this.fireBeforeMatched(e);r.fireBeforeRouteMatched(e);if(this._oTarget){t=this._oTarget;t._updateOptions(this._convertToTargetOptions(c));s=t._place(s);if(this._oRouter._oTargetHandler&&this._oRouter._oTargetHandler._chainNavigation){C=s;s=this._oRouter._oTargetHandler._chainNavigation(function(){return C;});}}else{if(!this._oConfig.afterCreateHook){this._oConfig.afterCreateHook=function(d){if(d.isA("sap.ui.core.UIComponent")){var r=d.getRouter();if(r){b.attachEvent("switched",function(){r.stop();});}}};}if(D.browser.msie||D.browser.edge){C=s;s=new Promise(function(d,f){setTimeout(function(){if(r._oTargets){var g=r._oTargets._display(b._oConfig.target,o,b._oConfig.titleTarget,C,b._oConfig.afterCreateHook);g.then(d,f);}else{d();}},0);});}else{s=r._oTargets._display(this._oConfig.target,o,this._oConfig.titleTarget,s,this._oConfig.afterCreateHook);}}return s.then(function(R){var d,V,f;if(Array.isArray(R)){d=R;R=d[0];}R=R||{};v=R.view;T=R.control;e.view=v;e.targetControl=T;if(d){V=[];f=[];d.forEach(function(R){V.push(R.view);f.push(R.control);});e.views=V;e.targetControls=f;}if(c.callback){c.callback(this,a,c,T,v);}this.fireEvent("matched",e);r.fireRouteMatched(e);if(i){L.info("The route named '"+c.name+"' did match with its pattern",this);this.fireEvent("patternMatched",e);r.fireRoutePatternMatched(e);}return R;}.bind(this));}};});
