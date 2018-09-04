/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2018 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */

// Provides element sap.m.FlexItemData
sap.ui.define(['./FlexBoxStylingHelper', './library', 'sap/ui/core/LayoutData'],
 function(FlexBoxStylingHelper, library, LayoutData) {
	"use strict";

	// shortcut for sap.m.BackgroundDesign
	var BackgroundDesign = library.BackgroundDesign;

	// shortcut for sap.m.FlexAlignSelf
	var FlexAlignSelf = library.FlexAlignSelf;

	/**
	 * Constructor for a new <code>sap.m.FlexItemData</code>.
	 *
	 * @param {string} [sId] ID for the new element, generated automatically if no ID is given
	 * @param {object} [mSettings] Initial settings for the new element.
	 *
	 * @class
	 * Holds layout data for a FlexBox / HBox / VBox.
	 * @extends sap.ui.core.LayoutData
	 * @version 1.56.6
	 *
	 * @constructor
	 * @public
	 * @alias sap.m.FlexItemData
	 * @ui5-metamodel This control/element also will be described in the UI5 (legacy) designtime metamodel
	 */
	var FlexItemData = LayoutData.extend("sap.m.FlexItemData", /** @lends sap.m.FlexItemData.prototype */ { metadata : {

		library : "sap.m",
		properties : {

			/**
			 * Determines cross-axis alignment of individual element (not currently supported in Internet Explorer).
			 *
			 * @see http://www.w3.org/TR/css-flexbox-1/#align-items-property
			 */
			alignSelf : {type : "sap.m.FlexAlignSelf", group : "Misc", defaultValue : FlexAlignSelf.Auto},

			/**
			 * Determines the display order of flex items independent of their source code order.
			 *
			 * @see http://www.w3.org/TR/css-flexbox-1/#order-property
			 */
			order : {type : "int", group : "Misc", defaultValue : 0},

			/**
			 * Determines the flexibility of the flex item when allocatable space is remaining.
			 *
			 * @see http://www.w3.org/TR/css-flexbox-1/#flex-grow-property
			 */
			growFactor : {type : "float", group : "Misc", defaultValue : 0},

			/**
			 * The shrink factor determines how much the flex item will shrink relative to the rest of the flex items in the flex container when negative free space is distributed.
			 *
			 * @see http://www.w3.org/TR/css-flexbox-1/#flex-shrink-property
			 *
			 * <b>Note:</b> This property is not supported in Internet Explorer 9, Android Native Browser/Webview <4.4, and Safari <7.
			 * @since 1.24.0
			 */
			shrinkFactor : {type : "float", group : "Misc", defaultValue : 1},

			/**
			 * The base size is the initial main size of the item for the flex algorithm. If set to "auto", this will be the computed size of the item.
			 *
			 * @see http://www.w3.org/TR/css-flexbox-1/#flex-basis-property
			 *
			 * @since 1.32.0
			 */
			baseSize : {type : "sap.ui.core.CSSSize", group : "Misc", defaultValue : "auto"},

			/**
			 * The minimum height of the flex item.
			 * @since 1.36.0
			 */
			minHeight : {type : "sap.ui.core.CSSSize", group : "Dimension", defaultValue : 'auto'},

			/**
			 * The maximum height of the flex item.
			 * @since 1.36.0
			 */
			maxHeight : {type : "sap.ui.core.CSSSize", group : "Dimension", defaultValue : ''},

			/**
			 * The minimum height of the flex item.
			 * @since 1.36.0
			 */
			minWidth : {type : "sap.ui.core.CSSSize", group : "Dimension", defaultValue : 'auto'},

			/**
			 * The maximum height of the flex item.
			 * @since 1.36.0
			 */
			maxWidth : {type : "sap.ui.core.CSSSize", group : "Dimension", defaultValue : ''},

			/**
			 * The style class will be applied to the flex item and can be used for CSS selectors.
			 */
			styleClass : {type : "string", group : "Misc", defaultValue : ''},

			/**
			 * Defines the background style of the flex item.
			 *
			 * @since 1.38.5
			 */
			backgroundDesign: {type: "sap.m.BackgroundDesign", group: "Appearance", defaultValue: BackgroundDesign.Transparent}
		}
	}});

	/**
	 * Sets the <code>alignSelf</code> property.
	 *
	 * @public
	 * @param {string} sValue Align option.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setAlignSelf = function(sValue) {
		var sOldValue = this.getAlignSelf();
		this.setProperty("alignSelf", sValue, true);
		this.$().removeClass("sapMFlexItemAlign" + sOldValue).addClass("sapMFlexItemAlign" + this.getAlignSelf());

		return this;
	};

	/**
	 * Sets the order.
	 *
	 * @public
	 * @param {string} sValue Order in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setOrder = function(sValue) {
		this.setProperty("order", sValue, true);
		FlexBoxStylingHelper.setStyle(null, this, "order", this.getOrder());

		return this;
	};

	/**
	 * Sets the <code>growFactor</code> property.
	 *
	 * @public
	 * @param {string} sValue Grow factor in string format.
	 * @returns {sap.m.FlexItemData} this FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setGrowFactor = function(sValue) {
		this.setProperty("growFactor", sValue, true);
		FlexBoxStylingHelper.setStyle(null, this, "flex-grow", this.getGrowFactor());

		return this;
	};

	/**
	 * Sets the <code>shrinkFactor</code> property.
	 *
	 * @public
	 * @param {string} sValue Shrink factor in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 * @see https://www.w3.org/TR/css-flexbox-1/#propdef-flex-shrink
	 */
	FlexItemData.prototype.setShrinkFactor = function(sValue) {
		this.setProperty("shrinkFactor", sValue, true);
		FlexBoxStylingHelper.setStyle(null, this, "flex-shrink", this.getShrinkFactor());

		return this;
	};

	/**
	 * Sets the base size for flex items.
	 *
	 * @public
	 * @param {string} sValue Base size in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setBaseSize = function(sValue) {
		this.setProperty("baseSize", sValue, true);
		FlexBoxStylingHelper.setStyle(null, this, "flex-basis", this.getBaseSize());

		return this;
	};

	/**
	 * Sets minimum height.
	 *
	 * @public
	 * @param {string} sValue Minimum height in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setMinHeight = function(sValue) {
		this.setProperty("minHeight", sValue, true);
		this.$().css("min-height", this.getMinHeight());

		return this;
	};

	/**
	 * Sets maximum height.
	 *
	 * @public
	 * @param {string} sValue Maximum height in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setMaxHeight = function(sValue) {
		this.setProperty("maxHeight", sValue, true);
		this.$().css("max-height", this.getMaxHeight());

		return this;
	};

	/**
	 * Sets minimum width.
	 *
	 * @public
	 * @param {string} sValue Minimum width in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setMinWidth = function(sValue) {
		this.setProperty("minWidth", sValue, true);
		this.$().css("min-width", this.getMinWidth());

		return this;
	};

	/**
	 * Sets maximum width.
	 *
	 * @public
	 * @param {string} sValue Maximum width in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setMaxWidth = function(sValue) {
		this.setProperty("maxWidth", sValue, true);
		this.$().css("max-width", this.getMaxWidth());

		return this;
	};

	/**
	 * Sets background design for flex items.
	 *
	 * @public
	 * @param {string} sValue Background design in string format.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setBackgroundDesign = function(sValue) {
		var sOldValue = this.getBackgroundDesign();
		this.setProperty("backgroundDesign", sValue, true);
		this.$().removeClass("sapMFlexBoxBG" + sOldValue).addClass("sapMFlexBoxBG" + this.getBackgroundDesign());

		return this;
	};

	/**
	 * Sets style class.
	 *
	 * @public
	 * @param {string} sValue Style class.
	 * @returns {sap.m.FlexItemData} <code>this</code> FlexItemData reference for chaining.
	 */
	FlexItemData.prototype.setStyleClass = function(sValue) {
		var sOldValue = this.getStyleClass();
		this.setProperty("styleClass", sValue, true);
		this.$().removeClass(sOldValue).addClass(this.getStyleClass());

		return this;
	};

	return FlexItemData;

});
