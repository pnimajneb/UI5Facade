sap.ui.define([
	"sap/ui/core/Control"
], function (Control) {
	"use strict";
	
	/**
	 * Constructor for a new <code>exface.ui5Custom.VideoPlayer</code>.
	 *
	 * @param {string} [sId] ID for the new control, generated automatically if no ID is given.
	 * @param {object} [mSettings] Initial settings for the new control.
	 *
	 * @class
	 * The <code>exface.ui5Custom.VideoPlayer</code> is a video player based on a HTML5 <code>video</code> tag.
	 *
	 * @extends sap.ui.core.Control
	 *
	 * @author Andrej Kabachnik
	 *
	 * @constructor
	 * @public
	 * @alias exface.ui5Custom.PdfViewer
	 */
	return Control.extend("exface.ui5Custom.VideoPlayer", {
		metadata: {
			properties: {
				src: {type : "string", group : "Data", defaultValue : null},
				mimeType: {type : "string", group : "Data", defaultValue : 'video/mp4'},
				showControls: {type : "boolean", group : "Appearance", defaultValue : true},
				posterFromSecond: {type : "float", group : "Appearance", defaultValue : null},
				width: {type: "sap.ui.core.CSSSize", group: "Dimension", defaultValue: '100%'},
			}
		},
		init: function () {
			
		},
		renderer: function (oRm, oControl) {
			var sProps = '';
			var sTime = '';
			if (oControl.getShowControls()) {
				sProps += ' controls';
			}
			oRm.write('<div style="text-align: center;" class="' + oControl.aCustomStyleClasses.join(' ') + '">');
			oRm.write('<video id="' + oControl.getId() + '_video" ' + sProps + ' preload="metadata" style="width: ' + oControl.getWidth() + '; max-width: 100%; ">');
			if (oControl.getSrc()) {
				if (oControl.getPosterFromSecond() !== null) {
					sTime = '#t=' + oControl.getPosterFromSecond();
				}
				oRm.write('<source src="' + oControl.getSrc() + sTime + '">');
			}
			oRm.write('This video is not supported by your browser!');
			oRm.write("</video>");
			oRm.write("</div>");
		},
		onAfterRendering: function (oEvent) {
			if (this.getPosterFromSecond() !== null) {
				$('#' + this.getId() + '_video').on('play', function() {
					this.currentTime=0;
				});
			}
		},
		setSrc: function(sValue) {
			this.$().find('source').remove();
			this.$().find('video').append('<source src="' + sValue + '">');
			return this.setProperty('src', sValue);
		},
	});
});