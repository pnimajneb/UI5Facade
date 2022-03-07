<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\DataTypes\TextDataType;

/**
 * Generates sap.m.FormattedText controls for Text widgets
 * 
 * TODO wrap value in <b>, <i>, etc. depending on text formatting
 * 
 * @method \exface\Core\Widgets\Text getWidget()
 *
 * @author Andrej Kabachnik
 *        
 */
class UI5Text extends UI5Display
{
    protected $alignmentProperty = null;
    
    public function buildJsConstructorForMainControl($oControllerJs = 'oController')
    {
        if ($this->getWidget()->getVisibility() === EXF_WIDGET_VISIBILITY_PROMOTED) {
            $this->addElementCssClass('exf-promoted');
        }
        
        return <<<JS
        
        new sap.m.FormattedText("{$this->getId()}", {
            {$this->buildJsProperties()}
            {$this->buildJsPropertyValue()}
        }).addStyleClass("{$this->buildCssElementClass()}")
        
JS;
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
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::getWrapping()
     */
    protected function getWrapping() : bool
    {
        return true;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::setAlignment()
     */
    public function setAlignment($propertyValue)
    {
        $this->alignmentProperty = $propertyValue;
        return $this;
    }
    
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsPropertyAlignment()
     */
    protected function buildJsPropertyAlignment()
    {
        return $this->alignmentProperty ? 'textAlign: ' . $this->alignmentProperty . ',' : '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Value::buildJsValue()
     */
    public function buildJsValue()
    {
        if (! $this->isValueBoundToModel()) {
            $value = nl2br($this->getWidget()->getValue());
            $value = '"' . $this->escapeJsTextValue($value) . '"';
        } else {
            $value = $this->buildJsValueBinding();
        }
        return $value;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Value::buildJsValueBindingPropertyName()
     */
    public function buildJsValueBindingPropertyName() : string
    {
        return 'htmlText';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsValueBindingOptions()
     */
    public function buildJsValueBindingOptions()
    {
        if ($this->getWidget()->getValueDataType() instanceof TextDataType) {
            return <<<JS
            
                formatter: function(value) {
                    if (value == undefined || value == null) {
                        return '';
                    }
                    return ({$this->getValueBindingFormatter()->getJsFormatter()->buildJsFormatter('value')} + '').replace(/([^>\\r\\n]?)(\\r\\n|\\n\\r|\\r|\\n)/g, '$1<br>$2');
                },
                
JS;
        }
        return parent::buildJsValueBindingOptions();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsValueGetterMethod()
     */
    public function buildJsValueGetterMethod()
    {
        return "(getHtmlText()+'').replace(/<\\s*\\/?br\\s*[\\/]?>/gi, \"\\n\")";
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsValueSetterMethod()
     */
    public function buildJsValueSetterMethod($valueJs)
    {
        return "setHtmlText(({$valueJs} || '').replace(/([^>\\r\\n]?)(\\r\\n|\\n\\r|\\r|\\n)/g, '$1<br>$2'))";
    }
}