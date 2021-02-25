<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;

/**
 * Generates custom VideoPlayer control to show a HTML5 video
 * 
 * @method \exface\Core\Widgets\Video getWidget()
 *
 * @author Andrej Kabachnik
 *        
 */
class UI5Video extends UI5Value
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructorForMainControl($oControllerJs = 'oController')
    {        
        $this->registerExternalModules($this->getController());
        
        return <<<JS

        new exface.ui5Custom.VideoPlayer("{$this->getid()}", {
            {$this->buildJsPropertyValue()}
            {$this->buildJsPropertyPoster()}
            {$this->buildJsPropertyWidth()}
            {$this->buildJsProperties()}
    	}).addStyleClass('sapUiResponsiveMargin')

JS;
    }
    
    protected function buildJsPropertyPoster() : string
    {
        $widget = $this->getWidget();
        switch (true) {
            case $widget->getThumbnailFromSecond() !== null:
                return 'posterFromSecond: ' . $widget->getThumbnailFromSecond() . ',';
        }
        
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Value::buildJsPropertyValue()
     */
    protected function buildJsPropertyValue()
    {
        $widget = $this->getWidget();
        $mimeType = $widget->getMimeType() ?? 'video/mp4';
        
        return <<<JS

            src: {$this->buildJsValue()},
            mimeType: "{$mimeType}",
JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::registerExternalModules()
     */
    public function registerExternalModules(UI5ControllerInterface $controller) : UI5AbstractElement
    {
        $controller->addExternalModule('libs.exface.ui5Custom.VideoPlayer', 'vendor/exface/UI5Facade/Facades/js/ui5Custom/VideoPlayer');
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Display::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        return $this->buildJsConstructorForMainControl($oControllerJs);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Value::buildCssHeightDefaultValue()
     */
    protected function buildCssHeightDefaultValue()
    {
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Value::buildCssWidthDefaultValue()
     */
    protected function buildCssWidthDefaultValue() : string
    {
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Value::buildJsValueBindingPropertyName()
     */
    public function buildJsValueBindingPropertyName() : string
    {
        return 'src';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::getCaption()
     */
    public function getCaption() : string
    {
        return '';
    }
}