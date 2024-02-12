<?php
namespace exface\UI5Facade\Facades\Elements\Traits;

use exface\Core\DataTypes\StringDataType;

/**
 * This trait helps add CSS classes for color scales. 
 * 
 * The only way to give most UI5 controls a custom color seems to be giving it a
 * CSS class. 
 * 
 * To use this trait add custom CSS classes to the page via `registerColorClasses()` 
 * and use them by calling `buildJsColorClassSetter()`. See UI5ObjectStatus and
 * UI5ProgressBar for examples.
 * 
 * @author Andrej Kabachnik
 * 
 * @method iHaveContextualHelp getWidget()
 *
 */
trait UI5ColorClassesTrait {
    
    /**
     * Characters to be removed from values and colors if they are to be used in CSS selectors
     * 
     * @var string[]
     */
    private $cssClassNameRemoveChars = ['#', '.', '+'];
    
    /**
     * Makes the controller run a script to add custom CSS styles every time the view is shown.
     * 
     * @return void
     */
    protected function registerColorClasses(array $colorScale, string $cssSelectorToColor = '.exf-custom-color.exf-color-[#color#]', string $cssColorProperties = 'background-color: [#color#]', bool $skipSemanticColors = true)
    {
        $css = '';
        foreach ($colorScale as $value => $color) {
            if (substr($color, 0, 1) === '~') {
                if ($skipSemanticColors === true) {
                    continue;
                } else {
                    $color = $this->getFacade()->getSemanticColors()[$color];
                }
            }
            $class = StringDataType::replacePlaceholders($cssSelectorToColor, [
                'color' => str_replace($this->cssClassNameRemoveChars, '', trim($color)), 
                'value' => str_replace($this->cssClassNameRemoveChars, '', trim($value))
            ]);
            $properties = StringDataType::replacePlaceholders($cssColorProperties, [
                'color' => $color, 
                'value' => $value
            ]);
            $css .= "$class { $properties } ";
        }
        
        $cssId = $this->getId();
        if (! $this->getUseWidgetId()) {
            $this->setUseWidgetId(true);
            $cssId = $this->getId();
            $this->setUseWidgetId(false);
        }
        $cssId .= '_color_css';
        
        $this->getController()->addOnShowViewScript(<<<JS
            
(function(){
    var jqTag = $('#{$cssId}');
    if (jqTag.length === 0) {
        $('head').append($('<style type="text/css" id="{$cssId}"></style>').text('$css'));
    }
})();

JS, false);
        
        $this->getController()->addOnHideViewScript("$('#{$cssId}').remove();");
        
        return;
    }
    
    /**
     * Applies the CSS class corresponding to given color via Control.addStyleClass()
     * 
     * Note, that if the control is used inside a sap.ui.table.Table, the DOM
     * element might not be there yet, when the color setter is called. In this case,
     * an onAfterRendering-handler is registered to add the CSS class and removed right
     * after this. The trouble with sap.ui.table.Table is that it instantiates its
     * cells at some obscure moment and reuses them when scrolling, so we need to
     * be prepared for different situations here.
     * 
     * @param string $oControlJs
     * @param string $sColorJs
     * @param string $cssCustomColorClass
     * @param string $cssColorClassPrefix
     * @return string
     */
    protected function buildJsColorClassSetter(string $oControlJs, string $sColorJs, string $cssCustomColorClass = 'exf-custom-color', $cssColorClassPrefix = 'exf-color-') : string
    {
        $cssReplaceJSON = json_encode($this->cssClassNameRemoveChars);
        return <<<JS
        
        (function(oCtrl, sColor){
            var aCssClassReplace = $cssReplaceJSON;
            var sColorClassSuffix = (sColor === null || sColor === undefined ? '' : sColor).toString();
            var fnStyler = function(){
                (oCtrl.$().attr('class') || '').split(/\s+/).forEach(function(sClass) {
                    if (sClass.startsWith('{$cssColorClassPrefix}')) {
                        oCtrl.removeStyleClass(sClass);
                    }
                });
                if (sColor === null) {
                    oCtrl.removeStyleClass('{$cssCustomColorClass}');
                } else {
                    oCtrl.addStyleClass('{$cssCustomColorClass} {$cssColorClassPrefix}' + sColorClassSuffix);
                }
            };
            var oDelegate = {
                onAfterRendering: function() {
                    fnStyler();
                    oCtrl.removeEventDelegate(oDelegate);
                }
            };
            aCssClassReplace.forEach(function(sChar) {
                sColorClassSuffix = sColorClassSuffix.replace(sChar, '');
            });
            fnStyler();
            if (oCtrl.$().length === 0) {
                oCtrl.addEventDelegate(oDelegate);
            }
        })($oControlJs, $sColorJs);
JS;
    }
}