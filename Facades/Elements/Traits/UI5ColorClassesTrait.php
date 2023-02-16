<?php
namespace exface\UI5Facade\Facades\Elements\Traits;

use exface\Core\DataTypes\StringDataType;

/**
 * This trait helps add CSS classes for color scales. 
 * 
 * @author Andrej Kabachnik
 * 
 * @method iHaveContextualHelp getWidget()
 *
 */
trait UI5ColorClassesTrait {
    
    /**
     * 
     * @return void
     */
    protected function registerColorClasses(array $colorScale, string $cssColorClass = '.exf-custom-color.exf-color-[#color#]', string $cssColorProperties = 'background-color: [#color#]', bool $skipSemanticColors = true)
    {
        $css = '';
        foreach ($colorScale as $color) {
            if (substr($color, 0, 1) === '~') {
                if ($skipSemanticColors === true) {
                    continue;
                } else {
                    // TODO
                }
            }
            $class = StringDataType::replacePlaceholder($cssColorClass, 'color', trim(trim($color), "#"));
            $properties = StringDataType::replacePlaceholder($cssColorProperties, 'color', $color);
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
}