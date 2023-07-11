<?php
namespace exface\UI5Facade\Facades\Elements;

class UI5WidgetGroup extends UI5Container
{
    
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        if ($this->getWidget()->isHidden()) {
            return parent::buildJsConstructor($oControllerJs);
        }
        
        $captionText = $this->getCaption() ? 'text: "' . $this->getCaption() . '",' : '';
        return  <<<JS
                new sap.ui.core.Title({
                    {$captionText}
                }),
                {$this->buildJsChildrenConstructors()}
JS;
    }
}
?>