/*!
 * OpenUI5
 * (c) Copyright 2009-2020 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['sap/ui/base/Object','./FilterOperator',"sap/base/Log"],function(B,F,L){"use strict";var c=B.extend("sap.ui.model.Filter",{constructor:function(f,o,v,V){if(typeof f==="object"&&!Array.isArray(f)){this.sPath=f.path;this.sOperator=f.operator;this.oValue1=f.value1;this.oValue2=f.value2;this.sVariable=f.variable;this.oCondition=f.condition;this.aFilters=f.filters||f.aFilters;this.bAnd=f.and||f.bAnd;this.fnTest=f.test;this.fnCompare=f.comparator;this.bCaseSensitive=f.caseSensitive;}else{if(Array.isArray(f)){this.aFilters=f;}else{this.sPath=f;}if(typeof o==="boolean"){this.bAnd=o;}else if(typeof o==="function"){this.fnTest=o;}else{this.sOperator=o;}this.oValue1=v;this.oValue2=V;if(this.sOperator===F.Any||this.sOperator===F.All){throw new Error("The filter operators 'Any' and 'All' are only supported with the parameter object notation.");}}if(this.sOperator===F.Any){if(this.sVariable&&this.oCondition){this._checkLambdaArgumentTypes();}else if(!this.sVariable&&!this.oCondition){}else{throw new Error("When using the filter operator 'Any', a lambda variable and a condition have to be given or neither.");}}else if(this.sOperator===F.All){this._checkLambdaArgumentTypes();}else{if(Array.isArray(this.aFilters)&&!this.sPath&&!this.sOperator&&!this.oValue1&&!this.oValue2){this._bMultiFilter=true;if(!this.aFilters.every(d)){L.error("Filter in Aggregation of Multi filter has to be instance of sap.ui.model.Filter");}}else if(!this.aFilters&&this.sPath!==undefined&&((this.sOperator&&this.oValue1!==undefined)||this.fnTest)){this._bMultiFilter=false;}else{L.error("Wrong parameters defined for filter.");}}}});c.prototype._checkLambdaArgumentTypes=function(){if(!this.sVariable||typeof this.sVariable!=="string"){throw new Error("When using the filter operators 'Any' or 'All', a string has to be given as argument 'variable'.");}if(!d(this.oCondition)){throw new Error("When using the filter operator 'Any' or 'All', a valid instance of sap.ui.model.Filter has to be given as argument 'condition'.");}};function d(v){return v instanceof c;}var T={Logical:"Logical",Binary:"Binary",Unary:"Unary",Lambda:"Lambda",Reference:"Reference",Literal:"Literal",Variable:"Variable",Call:"Call",Custom:"Custom"};var O={Equal:"==",NotEqual:"!=",LessThan:"<",GreaterThan:">",LessThanOrEqual:"<=",GreaterThanOrEqual:">=",And:"&&",Or:"||",Not:"!"};var e={Contains:"contains",StartsWith:"startswith",EndsWith:"endswith"};c.prototype.getAST=function(I){var r,o,s,R,v,f,t,V,C;function l(o,m,n){return{type:T.Logical,op:o,left:m,right:n};}function b(o,m,n){return{type:T.Binary,op:o,left:m,right:n};}function u(o,A){return{type:T.Unary,op:o,arg:A};}function a(o,R,V,C){return{type:T.Lambda,op:o,ref:R,variable:V,condition:C};}function g(p){return{type:T.Reference,path:p};}function h(m){return{type:T.Literal,value:m};}function j(n){return{type:T.Variable,name:n};}function k(n,A){return{type:T.Call,name:n,args:A};}if(this.aFilters){o=this.bAnd?O.And:O.Or;s=this.bAnd?"AND":"OR";r=this.aFilters[this.aFilters.length-1].getAST(I);for(var i=this.aFilters.length-2;i>=0;i--){r=l(o,this.aFilters[i].getAST(I),r);}}else{o=this.sOperator;s=this.sOperator;R=g(this.sPath);v=h(this.oValue1);switch(o){case F.EQ:r=b(O.Equal,R,v);break;case F.NE:r=b(O.NotEqual,R,v);break;case F.LT:r=b(O.LessThan,R,v);break;case F.GT:r=b(O.GreaterThan,R,v);break;case F.LE:r=b(O.LessThanOrEqual,R,v);break;case F.GE:r=b(O.GreaterThanOrEqual,R,v);break;case F.Contains:r=k(e.Contains,[R,v]);break;case F.StartsWith:r=k(e.StartsWith,[R,v]);break;case F.EndsWith:r=k(e.EndsWith,[R,v]);break;case F.NotContains:r=u(O.Not,k(e.Contains,[R,v]));break;case F.NotStartsWith:r=u(O.Not,k(e.StartsWith,[R,v]));break;case F.NotEndsWith:r=u(O.Not,k(e.EndsWith,[R,v]));break;case F.BT:f=v;t=h(this.oValue2);r=l(O.And,b(O.GreaterThanOrEqual,R,f),b(O.LessThanOrEqual,R,t));break;case F.NB:f=v;t=h(this.oValue2);r=l(O.Or,b(O.LessThan,R,f),b(O.GreaterThan,R,t));break;case F.Any:case F.All:V=j(this.sVariable);C=this.oCondition.getAST(I);r=a(o,R,V,C);break;default:throw new Error("Unknown operator: "+o);}}if(I&&!r.origin){r.origin=s;}return r;};c.defaultComparator=function(a,b){if(a==b){return 0;}if(a==null||b==null){return NaN;}if(typeof a=="string"&&typeof b=="string"){return a.localeCompare(b);}if(a<b){return-1;}if(a>b){return 1;}return NaN;};return c;});
