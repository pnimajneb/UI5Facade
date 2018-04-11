/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['jquery.sap.global','./InputBase','./ComboBoxTextField','./ComboBoxBase','./Input','./ToggleButton','./List','./Popover','./library','sap/ui/core/EnabledPropagator','sap/ui/core/IconPool','sap/ui/core/library','sap/ui/Device','sap/ui/core/Item','jquery.sap.xml','jquery.sap.keycodes'],function(q,I,C,a,b,T,L,P,l,E,c,d,D,e){"use strict";var f=l.ListType;var g=l.ListMode;var V=d.ValueState;var O=d.OpenState;var M=a.extend("sap.m.MultiComboBox",{metadata:{library:"sap.m",properties:{selectedKeys:{type:"string[]",group:"Data",defaultValue:[]}},associations:{selectedItems:{type:"sap.ui.core.Item",multiple:true,singularName:"selectedItem"}},events:{selectionChange:{parameters:{changedItem:{type:"sap.ui.core.Item"},selected:{type:"boolean"}}},selectionFinish:{parameters:{selectedItems:{type:"sap.ui.core.Item[]"}}}}}});c.insertFontFaceStyle();E.apply(M.prototype,[true]);M.prototype.onsapend=function(o){sap.m.Tokenizer.prototype.onsapend.apply(this._oTokenizer,arguments);};M.prototype.onsaphome=function(o){sap.m.Tokenizer.prototype.onsaphome.apply(this._oTokenizer,arguments);};M.prototype.onsapdown=function(o){if(!this.getEnabled()||!this.getEditable()){return;}o.setMarked();o.preventDefault();var i=this.getSelectableItems();var h=i[0];if(h&&this.isOpen()){this.getListItem(h).focus();return;}if(this._oTokenizer.getSelectedTokens().length){return;}this._oTraversalItem=this._getNextTraversalItem();if(this._oTraversalItem){this.updateDomValue(this._oTraversalItem.getText());this.selectText(0,this.getValue().length);}};M.prototype.onsapup=function(o){if(!this.getEnabled()||!this.getEditable()){return;}o.setMarked();o.preventDefault();if(this._oTokenizer.getSelectedTokens().length){return;}this._oTraversalItem=this._getPreviousTraversalItem();if(this._oTraversalItem){this.updateDomValue(this._oTraversalItem.getText());this.selectText(0,this.getValue().length);}};M.prototype.onsapshow=function(o){var h=this.getList(),p=this.getPicker(),s=this.getSelectableItems(),S=this.getSelectedItems(),i,j=h.getItemNavigation(),k,m;m=q(document.activeElement).control()[0];if(m instanceof sap.m.Token){i=this._getItemByToken(m);}else{i=S.length?this._getItemByListItem(this.getList().getSelectedItems()[0]):s[0];}k=this.getItems().indexOf(i);if(j){j.setSelectedIndex(k);}else{this._bListItemNavigationInvalidated=true;this._iInitialItemFocus=k;}p.setInitialFocus(h);a.prototype.onsapshow.apply(this,arguments);};M.prototype.onsaphide=M.prototype.onsapshow;M.prototype._selectItemByKey=function(o){var v,p,h,i,j,k=this.isOpen();if(!this.getEnabled()||!this.getEditable()){return;}if(o){o.setMarked();}v=this._getUnselectedItems(k?"":this.getValue());for(i=0;i<v.length;i++){if(v[i].getText().toUpperCase()===this.getValue().toUpperCase()){h=v[i];j=true;break;}}if(j){p={item:h,id:h.getId(),key:h.getKey(),fireChangeEvent:true,fireFinishEvent:true,suppressInvalidate:true,listItemUpdated:false};this._bPreventValueRemove=false;if(this.getValue()===""||q.sap.startsWithIgnoreCase(h.getText(),this.getValue())){if(this.getListItem(h).isSelected()){this.setValue('');}else{this.setSelection(p);}}}else{this._bPreventValueRemove=true;this._showWrongValueVisualEffect();}if(o){this.close();}};M.prototype.onsapenter=function(o){I.prototype.onsapenter.apply(this,arguments);if(this.getValue()){this._selectItemByKey(o);}};M.prototype.onsaptabnext=function(o){var i=this.getValue();if(i){var s=this._getUnselectedItemsStartingText(i);if(s.length===1){this._selectItemByKey(o);}else{this._showWrongValueVisualEffect();this.setValue(null);}}};M.prototype.onsapfocusleave=function(o){var p=this.getAggregation("picker");var t=this.isPlatformTablet();var h=sap.ui.getCore().byId(o.relatedControlId);var F=h&&h.getFocusDomRef();if(!p||!p.getFocusDomRef()||!F||!q.contains(p.getFocusDomRef(),F)){this.setValue(null);if(!(h instanceof sap.m.Token)){this._oTokenizer.scrollToEnd();}}if(p&&F){if(q.sap.equal(p.getFocusDomRef(),F)&&!t&&!this.isPickerDialog()){this.focus();}}};M.prototype.onfocusin=function(o){var h=this.getPickerType()==="Dropdown";if(o.target===this.getFocusDomRef()){this.getEditable()&&this.addStyleClass("sapMMultiComboBoxFocus");}if(o.target===this.getOpenArea()&&h&&!this.isPlatformTablet()){this.focus();}if(!this.isOpen()&&this.shouldValueStateMessageBeOpened()){this.openValueStateMessage();}};M.prototype._handleItemTap=function(o){if(o.target.childElementCount===0||o.target.childElementCount===2){this._bCheckBoxClicked=false;if(this.isOpen()&&!this._isListInSuggestMode()){this.close();}}};M.prototype._handleItemPress=function(o){if(this.isOpen()&&this._isListInSuggestMode()&&this.getPicker().oPopup.getOpenState()!==O.CLOSING){this.clearFilter();var i=this._getLastSelectedItem();if(i){this.getListItem(i).focus();}}};M.prototype._handleSelectionLiveChange=function(o){var h=o.getParameter("listItem");var i=o.getParameter("selected");var n=this._getItemByListItem(h);if(h.getType()==="Inactive"){return;}if(!n){return;}var p={item:n,id:n.getId(),key:n.getKey(),fireChangeEvent:true,suppressInvalidate:true,listItemUpdated:true};if(i){this.fireChangeEvent(n.getText());this.setSelection(p);}else{this.fireChangeEvent(n.getText());this.removeSelection(p);}if(this._bCheckBoxClicked){this.setValue(this._sOldValue);if(this.isOpen()&&this.getPicker().oPopup.getOpenState()!==O.CLOSING){h.focus();}}else{this._bCheckBoxClicked=true;this.setValue("");this.close();}};M.prototype.onkeydown=function(o){a.prototype.onkeydown.apply(this,arguments);if(!this.getEnabled()||!this.getEditable()){return;}this._bIsPasteEvent=(o.ctrlKey||o.metaKey)&&(o.which===q.sap.KeyCodes.V);if(this.getValue().length===0&&(o.ctrlKey||o.metaKey)&&(o.which===q.sap.KeyCodes.A)&&this._hasTokens()){this._oTokenizer.focus();this._oTokenizer.selectAllTokens(true);o.preventDefault();}if(this.isPickerDialog()){this._sOldValue=this.getPickerTextField().getValue();this._iOldCursorPos=q(this.getFocusDomRef()).cursorPos();}};M.prototype.oninput=function(o){a.prototype.oninput.apply(this,arguments);var i=o.srcControl;if(!this.getEnabled()||!this.getEditable()){return;}if(this._bIsPasteEvent){i.updateDomValue(this._sOldValue||"");return;}if(!this._bCompositionStart&&!this._bCompositionEnd){this._handleInputValidation(o,false);}};M.prototype.filterItems=function(i,v){i.forEach(function(o){var m=q.sap.startsWithIgnoreCase(o.getText(),v);if(v===""){m=true;if(!this.bOpenedByKeyboardOrButton){return;}}var h=this.getListItem(o);if(h){h.setVisible(m);}},this);};M.prototype.onkeyup=function(o){if(!this.getEnabled()||!this.getEditable()){return;}this._sOldValue=this.getValue();this._iOldCursorPos=q(this.getFocusDomRef()).cursorPos();};M.prototype._showWrongValueVisualEffect=function(){var o=this.getValueState();if(o===V.Error){return;}if(this.isPickerDialog()){this.getPickerTextField().setValueState(V.Error);q.sap.delayedCall(1000,this.getPickerTextField(),"setValueState",[o]);}else{this.setValueState(V.Error);q.sap.delayedCall(1000,this,"setValueState",[o]);}};M.prototype.createPicker=function(p){var o=this.getAggregation("picker");if(o){return o;}o=this["create"+p]();this.setAggregation("picker",o,true);var r=this.getRenderer(),h=r.CSS_CLASS_MULTICOMBOBOX;o.setHorizontalScrolling(false).addStyleClass(r.CSS_CLASS_COMBOBOXBASE+"Picker").addStyleClass(h+"Picker").addStyleClass(h+"Picker-CTX").attachBeforeOpen(this.onBeforeOpen,this).attachAfterOpen(this.onAfterOpen,this).attachBeforeClose(this.onBeforeClose,this).attachAfterClose(this.onAfterClose,this).addEventDelegate({onBeforeRendering:this.onBeforeRenderingPicker,onAfterRendering:this.onAfterRenderingPicker},this).addContent(this.getList());return o;};M.prototype.createPickerTextField=function(){return new b();};M.prototype.onBeforeRendering=function(){a.prototype.onBeforeRendering.apply(this,arguments);var i=this.getItems();var o=this.getList();if(o){this._synchronizeSelectedItemAndKey(i);o.destroyItems();this._clearTokenizer();this._fillList(i);if(o.getItemNavigation()){this._iFocusedIndex=o.getItemNavigation().getFocusedIndex();}this.setEditable(this.getEditable());}};M.prototype.onBeforeRenderingPicker=function(){var o=this["_onBeforeRendering"+this.getPickerType()];if(o){o.call(this);}};M.prototype.onAfterRenderingPicker=function(){var o=this["_onAfterRendering"+this.getPickerType()];if(o){o.call(this);}};M.prototype.onBeforeOpen=function(){var p=this["_onBeforeOpen"+this.getPickerType()];this.addStyleClass(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"Pressed");this._resetCurrentItem();this.addContent();this._aInitiallySelectedItems=this.getSelectedItems();if(p){p.call(this);}};M.prototype.onAfterOpen=function(){if(!this.isPlatformTablet()){this.getPicker().setInitialFocus(this);}this.closeValueStateMessage();};M.prototype.onBeforeClose=function(){a.prototype.onBeforeClose.apply(this,arguments);};M.prototype.onAfterClose=function(){this.removeStyleClass(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"Pressed");this.clearFilter();!this._bPreventValueRemove&&this.setValue("");this._sOldValue="";if(this.isPickerDialog()){this.getPickerTextField().setValue("");this._getFilterSelectedButton().setPressed(false);}this.fireSelectionFinish({selectedItems:this.getSelectedItems()});};M.prototype._onBeforeOpenDialog=function(){};M.prototype._onBeforeOpenDropdown=function(){var p=this.getPicker(),o=this.getDomRef(),w;if(o&&p){w=(o.offsetWidth/parseFloat(l.BaseFontSize))+"rem";p.setContentMinWidth(w);}};M.prototype._decoratePopover=function(p){var t=this;p.open=function(){return this.openBy(t);};};M.prototype.createDropdown=function(){var o=new P(this.getDropdownSettings());o.setInitialFocus(this);this._decoratePopover(o);return o;};M.prototype.createDialog=function(){var o=a.prototype.createDialog.apply(this,arguments),s=this._createFilterSelectedButton();o.getSubHeader().addContent(s);return o;};M.prototype._createFilterSelectedButton=function(){var i=c.getIconURI("multiselect-all"),r=this.getRenderer(),t=this;return new T({icon:i,press:t._filterSelectedItems.bind(this)}).addStyleClass(r.CSS_CLASS_MULTICOMBOBOX+"ToggleButton");};M.prototype._getFilterSelectedButton=function(){return this.getPicker().getSubHeader().getContent()[1];};M.prototype._filterSelectedItems=function(o){var B=o.oSource,h,m,v=this.getPickerTextField().getValue(),p=B.getPressed(),i=this.getVisibleItems(),j=this.getItems(),s=this.getSelectedItems();if(p){i.forEach(function(k){m=s.indexOf(k)>-1?true:false;h=this.getListItem(k);if(h){h.setVisible(m);}},this);}else{this.filterItems(j,v);}};M.prototype.revertSelection=function(){this.setSelectedItems(this._aInitiallySelectedItems);};M.prototype.createList=function(){var r=this.getRenderer();this._oList=new L({width:"100%",mode:g.MultiSelect,includeItemInSelection:true,rememberSelections:false}).addStyleClass(r.CSS_CLASS_COMBOBOXBASE+"List").addStyleClass(r.CSS_CLASS_MULTICOMBOBOX+"List").attachBrowserEvent("tap",this._handleItemTap,this).attachSelectionChange(this._handleSelectionLiveChange,this).attachItemPress(this._handleItemPress,this);this._oList.addEventDelegate({onAfterRendering:this.onAfterRenderingList,onfocusin:this.onFocusinList},this);};M.prototype.setSelection=function(o){if(o.item&&this.isItemSelected(o.item)){return;}if(!o.item){return;}if(!o.listItemUpdated&&this.getListItem(o.item)){this.getList().setSelectedItem(this.getListItem(o.item),true);}var t=new sap.m.Token({key:o.key});t.setText(o.item.getText());t.setTooltip(o.item.getText());o.item.data(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"Token",t);this._oTokenizer.addToken(t);this.$().toggleClass("sapMMultiComboBoxHasToken",this._hasTokens());this.setValue('');this.addAssociation("selectedItems",o.item,o.suppressInvalidate);var s=this.getKeys(this.getSelectedItems());this.setProperty("selectedKeys",s,o.suppressInvalidate);if(o.fireChangeEvent){this.fireSelectionChange({changedItem:o.item,selected:true});}if(o.fireFinishEvent){if(!this.isOpen()){this.fireSelectionFinish({selectedItems:this.getSelectedItems()});}}};M.prototype.removeSelection=function(o){if(o.item&&!this.isItemSelected(o.item)){return;}if(!o.item){return;}this.removeAssociation("selectedItems",o.item,o.suppressInvalidate);var s=this.getKeys(this.getSelectedItems());this.setProperty("selectedKeys",s,o.suppressInvalidate);if(!o.listItemUpdated&&this.getListItem(o.item)){this.getList().setSelectedItem(this.getListItem(o.item),false);}if(!o.tokenUpdated){var t=this._getTokenByItem(o.item);o.item.data(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"Token",null);this._oTokenizer.removeToken(t);}this.$().toggleClass("sapMMultiComboBoxHasToken",this._hasTokens());if(o.fireChangeEvent){this.fireSelectionChange({changedItem:o.item,selected:false});}if(o.fireFinishEvent){if(!this.isOpen()){this.fireSelectionFinish({selectedItems:this.getSelectedItems()});}}};M.prototype._synchronizeSelectedItemAndKey=function(h){if(!h.length){q.sap.log.info("Info: _synchronizeSelectedItemAndKey() the MultiComboBox control does not contain any item on ",this);return;}var s=this.getSelectedKeys()||this._aCustomerKeys;var k=this.getKeys(this.getSelectedItems());if(s.length){for(var i=0,K=null,o=null,j=null,m=s.length;i<m;i++){K=s[i];if(k.indexOf(K)>-1){if(this._aCustomerKeys.length&&(j=this._aCustomerKeys.indexOf(K))>-1){this._aCustomerKeys.splice(j,1);}continue;}o=this.getItemByKey(""+K);if(o){if(this._aCustomerKeys.length&&(j=this._aCustomerKeys.indexOf(K))>-1){this._aCustomerKeys.splice(j,1);}this.setSelection({item:o,id:o.getId(),key:o.getKey(),fireChangeEvent:false,suppressInvalidate:true,listItemUpdated:false});}}return;}};M.prototype._getTokenByItem=function(i){return i?i.data(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"Token"):null;};M.prototype.updateItems=function(r){var k,i,K=this.getSelectedKeys();var u=a.prototype.updateItems.apply(this,arguments);i=this.getSelectedItems();k=(i.length===K.length)&&i.every(function(o){return o&&o.getKey&&K.indexOf(o.getKey())>-1;});if(!k){i=K.map(this.getItemByKey,this);this.setSelectedItems(i);}return u;};M.prototype._getSelectedItemsOf=function(h){for(var i=0,j=h.length,s=[];i<j;i++){if(this.getListItem(h[i]).isSelected()){s.push(h[i]);}}return s;};M.prototype._getLastSelectedItem=function(){var t=this._oTokenizer.getTokens();var o=t.length?t[t.length-1]:null;if(!o){return null;}return this._getItemByToken(o);};M.prototype._getOrderedSelectedItems=function(){var h=[];for(var i=0,t=this._oTokenizer.getTokens(),j=t.length;i<j;i++){h[i]=this._getItemByToken(t[i]);}return h;};M.prototype._getFocusedListItem=function(){if(!document.activeElement){return null;}var F=sap.ui.getCore().byId(document.activeElement.id);if(this.getList()&&q.sap.containsOrEquals(this.getList().getFocusDomRef(),F.getFocusDomRef())){return F;}return null;};M.prototype._getFocusedItem=function(){var o=this._getFocusedListItem();return this._getItemByListItem(o);};M.prototype._isRangeSelectionSet=function(o){var $=o.getDomRef();return $.indexOf(this.getRenderer().CSS_CLASS_MULTICOMBOBOX+"ItemRangeSelection")>-1?true:false;};M.prototype._hasTokens=function(){return this._oTokenizer.getTokens().length>0;};M.prototype._getCurrentItem=function(){if(!this._oCurrentItem){return this._getFocusedItem();}return this._oCurrentItem;};M.prototype._setCurrentItem=function(i){this._oCurrentItem=i;};M.prototype._resetCurrentItem=function(){this._oCurrentItem=null;};M.prototype._decorateListItem=function(o){o.addDelegate({onkeyup:function(h){var i=null;if(h.which==q.sap.KeyCodes.SPACE&&this.isOpen()&&this._isListInSuggestMode()){this.open();i=this._getLastSelectedItem();if(i){this.getListItem(i).focus();}return;}},onkeydown:function(h){var i=null,j=null;if(h.shiftKey&&h.which==q.sap.KeyCodes.ARROW_DOWN){j=this._getCurrentItem();i=this._getNextVisibleItemOf(j);}if(h.shiftKey&&h.which==q.sap.KeyCodes.ARROW_UP){j=this._getCurrentItem();i=this._getPreviousVisibleItemOf(j);}if(h.shiftKey&&h.which===q.sap.KeyCodes.SPACE){j=this._getCurrentItem();this._selectPreviousItemsOf(j);}if(i&&i!==j){if(this.getListItem(j).isSelected()){this.setSelection({item:i,id:i.getId(),key:i.getKey(),fireChangeEvent:true,suppressInvalidate:true});this._setCurrentItem(i);}else{this.removeSelection({item:i,id:i.getId(),key:i.getKey(),fireChangeEvent:true,suppressInvalidate:true});this._setCurrentItem(i);}return;}this._resetCurrentItem();if((h.ctrlKey||h.metaKey)&&h.which==q.sap.KeyCodes.A){h.setMarked();h.preventDefault();var v=this.getSelectableItems();var s=this._getSelectedItemsOf(v);if(s.length!==v.length){v.forEach(function(i){this.setSelection({item:i,id:i.getId(),key:i.getKey(),fireChangeEvent:true,suppressInvalidate:true,listItemUpdated:false});},this);}else{v.forEach(function(i){this.removeSelection({item:i,id:i.getId(),key:i.getKey(),fireChangeEvent:true,suppressInvalidate:true,listItemUpdated:false});},this);}}}},true,this);o.addEventDelegate({onsapbackspace:function(h){h.preventDefault();},onsapshow:function(h){h.setMarked();if(h.keyCode===q.sap.KeyCodes.F4){h.preventDefault();}if(this.isOpen()){this.close();return;}if(this.hasContent()){this.open();}},onsaphide:function(h){this.onsapshow(h);},onsapenter:function(h){h.setMarked();this.close();},onsaphome:function(h){h.setMarked();h.preventDefault();var v=this.getSelectableItems();var i=v[0];this.getListItem(i).focus();},onsapend:function(h){h.setMarked();h.preventDefault();var v=this.getSelectableItems();var i=v[v.length-1];this.getListItem(i).focus();},onsapup:function(h){h.setMarked();h.preventDefault();var v=this.getSelectableItems();var i=v[0];var j=q(document.activeElement).control()[0];if(j===this.getListItem(i)){this.focus();h.stopPropagation(true);}},onfocusin:function(h){this.addStyleClass(this.getRenderer().CSS_CLASS_MULTICOMBOBOX+"Focused");},onfocusout:function(h){this.removeStyleClass(this.getRenderer().CSS_CLASS_MULTICOMBOBOX+"Focused");},onsapfocusleave:function(h){var p=this.getAggregation("picker");var i=sap.ui.getCore().byId(h.relatedControlId);if(p&&i&&q.sap.equal(p.getFocusDomRef(),i.getFocusDomRef())){if(h.srcControl){h.srcControl.focus();}}}},this);if(D.support.touch){o.addEventDelegate({ontouchstart:function(h){h.setMark("cancelAutoClose");}});}};M.prototype._createTokenizer=function(){var t=new sap.m.Tokenizer({tokens:[]}).attachTokenChange(this._handleTokenChange,this);t.setParent(this);t.addEventDelegate({onAfterRendering:this._onAfterRenderingTokenizer},this);return t;};M.prototype._onAfterRenderingTokenizer=function(){this._oTokenizer.scrollToEnd();};M.prototype._handleTokenChange=function(o){var t=o.getParameter("type");var h=o.getParameter("token");var i=null;if(t!==sap.m.Tokenizer.TokenChangeType.Removed&&t!==sap.m.Tokenizer.TokenChangeType.Added){return;}if(t===sap.m.Tokenizer.TokenChangeType.Removed){i=(h&&this._getItemByToken(h));if(i&&this.isItemSelected(i)){this.removeSelection({item:i,id:i.getId(),key:i.getKey(),tokenUpdated:true,fireChangeEvent:true,fireFinishEvent:true,suppressInvalidate:true});!this.isPickerDialog()&&this.focus();this.fireChangeEvent("");}}};M.prototype.onAfterRenderingList=function(){var o=this.getList();if(this._iFocusedIndex!=null&&o.getItems().length>this._iFocusedIndex){o.getItems()[this._iFocusedIndex].focus();this._iFocusedIndex=null;}};M.prototype.onFocusinList=function(){if(this._bListItemNavigationInvalidated){this.getList().getItemNavigation().setSelectedIndex(this._iInitialItemFocus);this._bListItemNavigationInvalidated=false;}};M.prototype.onAfterRendering=function(){a.prototype.onAfterRendering.apply(this,arguments);var p=this.getPicker();var o=q(this.getDomRef());var B=o.find(this.getRenderer().DOT_CSS_CLASS_MULTICOMBOBOX+"Border");p._oOpenBy=B[0];};M.prototype.onfocusout=function(o){this.removeStyleClass("sapMMultiComboBoxFocus");a.prototype.onfocusout.apply(this,arguments);};M.prototype.onpaste=function(o){var s;if(window.clipboardData){s=window.clipboardData.getData("Text");}else{s=o.originalEvent.clipboardData.getData('text/plain');}var S=this._oTokenizer._parseString(s);if(S&&S.length>0){this.getSelectableItems().forEach(function(i){if(q.inArray(i.getText(),S)>-1){this.setSelection({item:i,id:i.getId(),key:i.getKey(),fireChangeEvent:true,fireFinishEvent:true,suppressInvalidate:true,listItemUpdated:false});}},this);}};M.prototype.onsapbackspace=function(o){if(!this.getEnabled()||!this.getEditable()){o.preventDefault();return;}if(this.getCursorPosition()>0||this.getValue().length>0){return;}sap.m.Tokenizer.prototype.onsapbackspace.apply(this._oTokenizer,arguments);o.preventDefault();};M.prototype.onsapdelete=function(o){if(!this.getEnabled()||!this.getEditable()){return;}if(this.getValue()&&!this._isCompleteTextSelected()){return;}sap.m.Tokenizer.prototype.onsapdelete.apply(this._oTokenizer,arguments);};M.prototype.onsapnext=function(o){if(o.isMarked()){return;}var F=q(document.activeElement).control()[0];if(!F){return;}if(F===this._oTokenizer||this._oTokenizer.$().find(F.$()).length>0&&this.getEditable()){this.focus();}};M.prototype.onsapprevious=function(o){if(this.getCursorPosition()===0&&!this._isCompleteTextSelected()){if(o.srcControl===this){sap.m.Tokenizer.prototype.onsapprevious.apply(this._oTokenizer,arguments);}}};M.prototype.getOpenArea=function(){if(this.isPickerDialog()){return this.getDomRef();}else{return a.prototype.getOpenArea.apply(this,arguments);}};M.prototype._getItemsStartingText=function(t,i){var h=[],s=i?this.getEnabledItems():this.getSelectableItems();s.forEach(function(o){if(q.sap.startsWithIgnoreCase(o.getText(),t)){h.push(o);}},this);return h;};M.prototype._getUnselectedItemsStartingText=function(t){var i=[];this._getUnselectedItems().forEach(function(o){if(q.sap.startsWithIgnoreCase(o.getText(),t)){i.push(o);}},this);return i;};M.prototype.getCursorPosition=function(){return this._$input.cursorPos();};M.prototype._isCompleteTextSelected=function(){if(!this.getValue().length){return false;}var i=this._$input[0];if(i.selectionStart!==0||i.selectionEnd!==this.getValue().length){return false;}return true;};M.prototype._selectPreviousItemsOf=function(i){var h;do{h=true;var p=this._getPreviousVisibleItemOf(i);if(p){var o=this.getListItem(p);if(o){h=this.getListItem(p).getSelected();}}this.setSelection({item:i,id:i.getId(),key:i.getKey(),fireChangeEvent:true,suppressInvalidate:true});i=p;}while(!h);};M.prototype._getNextVisibleItemOf=function(i){var h=this.getSelectableItems();var j=h.indexOf(i)+1;if(j<=0||j>h.length-1){return null;}return h[j];};M.prototype._getPreviousVisibleItemOf=function(i){var h=this.getSelectableItems();var j=h.indexOf(i)-1;if(j<0){return null;}return h[j];};M.prototype._getNextUnselectedItemOf=function(i){var h=this._getUnselectedItems();var j=h.indexOf(i)+1;if(j<=0||j>h.length-1){return null;}return h[j];};M.prototype._getPreviousUnselectedItemOf=function(i){var h=this._getUnselectedItems();var j=h.indexOf(i)-1;if(j<0){return null;}return h[j];};M.prototype._getNextTraversalItem=function(){var i=this._getItemsStartingText(this.getValue());var s=this._getUnselectedItems();if(i.indexOf(this._oTraversalItem)>-1&&this._oTraversalItem.getText()===this.getValue()){return this._getNextUnselectedItemOf(this._oTraversalItem);}if(i.length&&i[0].getText()===this.getValue()){return this._getNextUnselectedItemOf(i[0]);}return i.length?i[0]:s[0];};M.prototype._getPreviousTraversalItem=function(){var i=this._getItemsStartingText(this.getValue());if(i.indexOf(this._oTraversalItem)>-1&&this._oTraversalItem.getText()===this.getValue()){return this._getPreviousUnselectedItemOf(this._oTraversalItem);}if(i.length&&i[i.length-1].getText()===this.getValue()){return this._getPreviousUnselectedItemOf(i[i.length-1]);}if(i.length){return i[i.length-1];}else{var s=this._getUnselectedItems();if(s.length>0){return s[s.length-1];}else{return null;}}};M.prototype.findFirstEnabledItem=function(h){h=h||this.getItems();for(var i=0;i<h.length;i++){if(h[i].getEnabled()){return h[i];}}return null;};M.prototype.getVisibleItems=function(){for(var i=0,o,h=this.getItems(),v=[];i<h.length;i++){o=this.getListItem(h[i]);if(o&&o.getVisible()){v.push(h[i]);}}return v;};M.prototype.findLastEnabledItem=function(i){i=i||this.getItems();return this.findFirstEnabledItem(i.reverse());};M.prototype.setSelectedItems=function(i){this.removeAllSelectedItems();if(!i||!i.length){return this;}if(!q.isArray(i)){q.sap.log.warning("Warning: setSelectedItems() has to be an array of sap.ui.core.Item instances or of valid sap.ui.core.Item IDs",this);return this;}i.forEach(function(o){if(!(o instanceof e)&&(typeof o!=="string")){q.sap.log.warning("Warning: setSelectedItems() has to be an array of sap.ui.core.Item instances or of valid sap.ui.core.Item IDs",this);return;}if(typeof o==="string"){o=sap.ui.getCore().byId(o);}this.setSelection({item:o?o:null,id:o?o.getId():"",key:o?o.getKey():"",suppressInvalidate:true});},this);return this;};M.prototype.addSelectedItem=function(i){if(!i){return this;}if(typeof i==="string"){i=sap.ui.getCore().byId(i);}this.setSelection({item:i?i:null,id:i?i.getId():"",key:i?i.getKey():"",fireChangeEvent:false,suppressInvalidate:true});return this;};M.prototype.removeSelectedItem=function(i){if(!i){return null;}if(typeof i==="string"){i=sap.ui.getCore().byId(i);}if(!this.isItemSelected(i)){return null;}this.removeSelection({item:i,id:i.getId(),key:i.getKey(),fireChangeEvent:false,suppressInvalidate:true});return i;};M.prototype.removeAllSelectedItems=function(){var i=[];var h=this.getAssociation("selectedItems",[]);h.forEach(function(o){var j=this.removeSelectedItem(o);if(j){i.push(j.getId());}},this);return i;};M.prototype.removeSelectedKeys=function(k){var i=[],h;if(!k||!k.length||!q.isArray(k)){return i;}var o;k.forEach(function(K){o=this.getItemByKey(K);if(o){this.removeSelection({item:o?o:null,id:o?o.getId():"",key:o?o.getKey():"",fireChangeEvent:false,suppressInvalidate:true});i.push(o);}if(this._aCustomerKeys.length&&(h=this._aCustomerKeys.indexOf(K))>-1){this._aCustomerKeys.splice(h,1);}},this);return i;};M.prototype.setSelectedKeys=function(k){this.removeAllSelectedItems();this._aCustomerKeys=[];this.addSelectedKeys(k);return this;};M.prototype.addSelectedKeys=function(k){k=this.validateProperty("selectedKeys",k);k.forEach(function(K){var i=this.getItemByKey(K);if(i){this.addSelectedItem(i);}else if(K!=null){this._aCustomerKeys.push(K);}},this);return this;};M.prototype.getSelectedKeys=function(){var i=this.getSelectedItems()||[],k=[];i.forEach(function(o){k.push(o.getKey());},this);if(this._aCustomerKeys.length){k=k.concat(this._aCustomerKeys);}return k;};M.prototype._getUnselectedItems=function(){return q(this.getSelectableItems()).not(this.getSelectedItems()).get();};M.prototype.getSelectedItems=function(){var i=[],h=this.getAssociation("selectedItems")||[];h.forEach(function(s){var o=sap.ui.getCore().byId(s);if(o){i.push(o);}},this);return i;};M.prototype.getSelectableItems=function(){return this.getEnabledItems(this.getVisibleItems());};M.prototype.getWidth=function(){return this.getProperty("width")||"100%";};M.prototype.setEditable=function(h){a.prototype.setEditable.apply(this,arguments);this._oTokenizer.setEditable(h);return this;};M.prototype.clearFilter=function(){this.getItems().forEach(function(i){this.getListItem(i).setVisible(i.getEnabled()&&this.getSelectable(i));},this);};M.prototype._isListInSuggestMode=function(){return this.getList().getItems().some(function(o){return!o.getVisible()&&this._getItemByListItem(o).getEnabled();},this);};M.prototype._mapItemToListItem=function(i){if(!i){return null;}var s=this.getRenderer().CSS_CLASS_MULTICOMBOBOX+"Item";var h=(this.isItemSelected(i))?s+"Selected":"";var o=new sap.m.StandardListItem({type:f.Active,visible:i.getEnabled()}).addStyleClass(s+" "+h);o.setTooltip(i.getTooltip());i.data(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"ListItem",o);o.setTitle(i.getText());if(h){var t=new sap.m.Token({key:i.getKey()});t.setText(i.getText());t.setTooltip(i.getText());i.data(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"Token",t);this._oTokenizer.addToken(t);}this.setSelectable(i,i.getEnabled());this._decorateListItem(o);return o;};M.prototype._findMappedItem=function(o,h){for(var i=0,h=h||this.getItems(),j=h.length;i<j;i++){if(this.getListItem(h[i])===o){return h[i];}}return null;};M.prototype.setSelectable=function(i,s){if(this.indexOfItem(i)<0){return;}i._bSelectable=s;var o=this.getListItem(i);if(o){o.setVisible(s);}var t=this._getTokenByItem(i);if(t){t.setVisible(s);}};M.prototype.getSelectable=function(i){return i._bSelectable;};M.prototype._fillList=function(h){if(!h){return null;}if(!this._oListItemEnterEventDelegate){this._oListItemEnterEventDelegate={onsapenter:function(k){if(k.srcControl.isSelected()){k.setMarked();}}};}for(var i=0,o,j=h.length;i<j;i++){o=this._mapItemToListItem(h[i]);o.removeEventDelegate(this._oListItemEnterEventDelegate);o.addDelegate(this._oListItemEnterEventDelegate,true,this,true);this.getList().addAggregation("items",o,true);if(this.isItemSelected(h[i])){this.getList().setSelectedItem(o,true);}}};M.prototype._handleInputValidation=function(o,h){var v=o.target.value,i,j,k,r,u,s;var m=h?q(o.target).control(0):o.srcControl;i=this._getItemsStartingText(v,true);j=!!i.length;if(!j&&v!==""){u=h?this._sComposition:(this._sOldValue||"");m.updateDomValue(u);if(this._iOldCursorPos){q(m.getFocusDomRef()).cursorPos(this._iOldCursorPos);}this._showWrongValueVisualEffect();return;}k=this.getEnabledItems();r=this._sOldInput&&this._sOldInput.length>v.length;if(this.isPickerDialog()){s=this._getFilterSelectedButton();if(s!=null&&s.getPressed()){s.setPressed(false);}}if(r){k=this.getItems();}this.filterItems(k,v);if((!this.getValue()||!j)&&!this.bOpenedByKeyboardOrButton&&!this.isPickerDialog()){this.close();}else{this.open();}this._sOldInput=v;};M.prototype.init=function(){C.prototype.init.apply(this,arguments);this.createList();this.bItemsUpdated=false;this._bListItemNavigationInvalidated=false;this._iInitialItemFocus=-1;this._bCheckBoxClicked=true;this._bPreventValueRemove=false;this.setPickerType(D.system.phone?"Dialog":"Dropdown");this._oTokenizer=this._createTokenizer();this._aCustomerKeys=[];this._aInitiallySelectedItems=[];this._bCompositionStart=false;this._bCompositionEnd=false;this._sComposition="";this.attachBrowserEvent("compositionstart",function(){this._bCompositionStart=true;this._bCompositionEnd=false;},this);this.attachBrowserEvent("compositionend",function(o){this._bCompositionStart=false;this._bCompositionEnd=true;this._handleInputValidation(o,true);this._bCompositionEnd=false;this._sComposition=o.target.value;},this);};M.prototype.clearSelection=function(){this.removeAllSelectedItems();};M.prototype.addItem=function(i){this.addAggregation("items",i);if(i){i.attachEvent("_change",this.onItemChange,this);}if(this.getList()){this.getList().addItem(this._mapItemToListItem(i));}return this;};M.prototype.insertItem=function(i,h){this.insertAggregation("items",i,h,true);if(i){i.attachEvent("_change",this.onItemChange,this);}if(this.getList()){this.getList().insertItem(this._mapItemToListItem(i),h);}return this;};M.prototype.getEnabledItems=function(i){i=i||this.getItems();return i.filter(function(o){return o.getEnabled();});};M.prototype.getItemByKey=function(k){return this.findItem("key",k);};M.prototype.removeItem=function(i){i=this.removeAggregation("items",i);if(this.getList()){this.getList().removeItem(i&&this.getListItem(i));}this.removeSelection({item:i,id:i?i.getId():"",key:i?i.getKey():"",fireChangeEvent:false,suppressInvalidate:true,listItemUpdated:true});return i;};M.prototype.isItemSelected=function(i){return this.getSelectedItems().indexOf(i)>-1;};M.prototype.findItem=function(p,v){var m="get"+p.charAt(0).toUpperCase()+p.slice(1);for(var i=0,h=this.getItems();i<h.length;i++){if(h[i][m]()===v){return h[i];}}return null;};M.prototype._clearTokenizer=function(){this._oTokenizer.destroyAggregation("tokens",true);};M.prototype.getList=function(){return this._oList;};M.prototype.exit=function(){a.prototype.exit.apply(this,arguments);if(this.getList()){this.getList().destroy();this._oList=null;}if(this._oTokenizer){this._oTokenizer.destroy();this._oTokenizer=null;}};M.prototype.destroyItems=function(){this.destroyAggregation("items");if(this.getList()){this.getList().destroyItems();}this._oTokenizer.destroyTokens();return this;};M.prototype.removeAllItems=function(){var i=this.removeAllAggregation("items");this.removeAllSelectedItems();if(this.getList()){this.getList().removeAllItems();}return i;};M.prototype._getItemByListItem=function(o){return this._getItemBy(o,"ListItem");};M.prototype._getItemByToken=function(t){return this._getItemBy(t,"Token");};M.prototype._getItemBy=function(o,s){s=this.getRenderer().CSS_CLASS_COMBOBOXBASE+s;for(var i=0,h=this.getItems(),j=h.length;i<j;i++){if(h[i].data(s)===o){return h[i];}}return null;};M.prototype.getListItem=function(i){return i?i.data(this.getRenderer().CSS_CLASS_COMBOBOXBASE+"ListItem"):null;};M.prototype.getAccessibilityInfo=function(){var t=this.getSelectedItems().map(function(o){return o.getText();}).join(" ");var i=a.prototype.getAccessibilityInfo.apply(this,arguments);i.type=sap.ui.getCore().getLibraryResourceBundle("sap.m").getText("ACC_CTR_TYPE_MULTICOMBO");i.description=((i.description||"")+" "+t).trim();return i;};return M;});