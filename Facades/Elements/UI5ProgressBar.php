<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Interfaces\Widgets\iHaveColorScale;

/**
 * Renders a sap.m.ProgressIndicator for a ProgressBar widget
 * 
 * @method \exface\Core\Widgets\ProgressBar getWidget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class UI5ProgressBar extends UI5Display
{
    private $textBindingPath = null;
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructorForMainControl($oControllerJs = 'oController')
    {
        // Register stuff here, that is needed for in-table rendering where buildJsConstructor()
        // is not called
        $this->registerExternalModules($this->getController());
        $this->registerColorClasses();
        
        // NOTE: displayOnly:true makes the progressbar look nice inside responsive table 
        // cells! Otherwise it has top and bottom margins and is displayed uneven with the
        // caption.
        
        return <<<JS
        
        new sap.m.ProgressIndicator("{$this->getid()}", {
            showValue: true,
            state: "None",
            displayOnly: true,
    		percentValue: {$this->buildJsValuePercent()},
            displayValue: {$this->buildJsDisplayValue()},
            {$this->buildJsProperties()}
            {$this->buildJsPropertyState()}
            {$this->buildJsPropertyDisplayAnimation()}
    	})
        {$this->buildJsPseudoEventHandlers()}
    	
JS;
    }
            
    public function buildJsDisplayValue() : string
    {
        $widget = $this->getWidget();
        
        if ($widget->isTextBoundToAttribute() === false) {
            return $this->buildJsValue();
        }
        
        if (! $this->isValueBoundToModel()) {
            $value = $widget->getText($widget->getValue());
        } else {
            $textAttribute = $widget->getTextAttribute();
            $value = <<<JS
            {
                path: "{$this->getTextBindingPath()}",
                {$this->getFacade()->getDataTypeFormatterForUI5Bindings($textAttribute->getDataType())->buildJsBindingProperties()}
            }
JS;
        }
        return $value;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ValueBindingInterface::getValueBindingPath()
     */
    public function getTextBindingPath() : string
    {
        if ($this->textBindingPath === null) {
            $widget = $this->getWidget();
            $model = $this->getView()->getModel();
            if ($model->hasBinding($widget, 'text')) {
                return $model->getBindingPath($widget, 'text');
            }
            return $this->getValueBindingPrefix() . $this->getWidget()->getTextDataColumnName();
        }
        return $this->textBindingPath;
    }
            
    public function buildJsValuePercent() : string
    {
       if (! $this->isValueBoundToModel()) {
            $value = $this->getWidget()->getValueDataType()->parse($this->getWidget()->getValue());
        } else {
            $bindingOptions = <<<JS
                formatter: function(value){
                    if (value === null || value === undefined) {
                        this.setVisible(false);
                        return null;
                    }
                    this.setVisible(true);
                    return parseFloat(value);
                }

JS;
            $value = $this->buildJsValueBinding($bindingOptions);
        }
        return $value ?? 0;
    }
            
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Value::buildJsValueBindingPropertyName()
     */
    public function buildJsValueBindingPropertyName() : string
    {
        return 'percentValue';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsValueSetterMethod()
     */
    public function buildJsValueSetterMethod($valueJs)
    {
        return "setPercentValue({$valueJs})";
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsPropertyAlignment()
     */
    protected function buildJsPropertyAlignment()
    {
        return '';
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsPropertyWrapping()
     */
    protected function buildJsPropertyWrapping()
    {
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsColorCssSetter()
     */
    protected function buildJsColorCssSetter(string $oControlJs, string $sColorJs) : string
    {
        // The only way to git a sap.m.ProgressIndicator a custom color seems to be giving it a
        // CSS class. So we add custom CSS classes to the page via registerColorClasses() and use 
        // them here then.
        // However, if the ProgressIndicator is used inside a sap.ui.table.Table, the DOM
        // element might not be there yet, when the color setter is called. In this case,
        // an onAfterRendering-handler is registered to add the CSS class and removed right
        // after this. The trouble with sap.ui.table.Table is that it instantiates its
        // cells at some obscure moment and reuses them when scrolling, so we need to 
        // be prepared for different situations here.
        return <<<JS

        (function(oBar, sColor){
            var fnStyler = function(){
                (oBar.$().attr('class') || '').split(/\s+/).forEach(function(sClass) {
                    if (sClass.startsWith('exf-color-')) {
                        oBar.removeStyleClass(sClass);
                    }
                });
                if (sColor === null) {
                    oBar.removeStyleClass('exf-custom-color');
                } else {
                    oBar.addStyleClass('exf-custom-color exf-color-' + sColor.replace("#", ""));
                }
            };
            var oDelegate = {
                onAfterRendering: function() {
                    fnStyler();
                    oBar.removeEventDelegate(oDelegate);
                }
            };

            fnStyler();
            if (oBar.$().length === 0) {
                oBar.addEventDelegate(oDelegate);
            }
        })($oControlJs, $sColorJs);

JS;
    }
    
    /**
     * @return void
     */
    protected function registerColorClasses()
    {
        if (! $this->getWidget()->hasColorScale()) {
            return;
        }
        $css = '';
        foreach ($this->getWidget()->getColorScale() as $color) {
            if (substr($color, 0, 1) === '~') {
                continue;
            }
            $css .= '.exf-custom-color.exf-color-' . trim(trim($color), "#") . ' .sapMPIBar {background-color: ' . $color . '}';
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
     * 
     * @return string
     */
    protected function buildJsPropertyState() : string
    {
        if ($this->getWidget() instanceof iHaveColorScale) {
            $stateJs = $this->buildJsColorValue();
        }
        
        return $stateJs ? 'state: ' . $stateJs . ',' : '';
    }
    
    protected function buildJsPropertyDisplayAnimation() : string
    {
        return $this->getWidget()->isInTable() ? 'displayAnimation: false,' : '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsColorValueNoColor()
     */
    protected function buildJsColorValueNoColor() : string
    {
        return 'sap.ui.core.ValueState.None';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsPropertyTooltip()
     */
    protected function buildJsPropertyTooltip()
    {
        if ($this->getWidget()->isInTable() === true && $this->isValueBoundToModel()) {
            $value = $this->buildJsValueBinding();
            return 'tooltip: ' . $value .',';
        }
        
        return parent::buildJsPropertyTooltip();
    }
}