<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\WidgetGrid;
use exface\Core\Interfaces\Widgets\iHaveValue;
use exface\Core\Interfaces\Widgets\iContainOtherWidgets;
use exface\Core\Interfaces\Widgets\iDisplayValue;
use exface\Core\Widgets\Input;

/**
 * Generates the controls inside a sap.uxap.ObjectPageHeader.
 *
 * @author Andrej Kabachnik
 *        
 */
class UI5DialogHeader extends UI5Container
{
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $js = '';
        
        foreach ($this->getWidget()->getWidgets() as $widget) {
            switch (true) {
                case $widget instanceof Input:
                    $js .= $this->getFacade()->getElement($widget)->buildJsConstructor() . ',';
                    break;
                case $widget instanceof iHaveValue:
                    $js .= $this->buildJsObjectStatus($widget) . ',';
                    break;
                case $widget instanceof WidgetGrid:
                    $js .= $this->buildJsVerticalLayout($widget) . ',';
                    break;
            }
        }
        
        return $js;
    }   
                    
    protected function buildJsObjectStatus(iHaveValue $widget)
    {
        $element = new UI5ObjectStatus($widget, $this->getFacade());
        return $element->buildJsConstructor();
    }
        
    protected function buildJsVerticalLayout(iContainOtherWidgets $widget)
    {
        if ($widget->isHidden()){
            return '';
        }
        
        $title = $widget->getCaption() ? 'new sap.m.Title({text: "' . $this->escapeJsTextValue($widget->getCaption()) . '"}),' : '';
        foreach ($widget->getWidgets() as $w) {
            if ($w instanceof WidgetGrid) {
                $content .= $this->buildJsVerticalLayout($w) . ',';
            } elseif ($w->getWidgetType() !== 'Display' && $w instanceof iDisplayValue) {
                $content .= $this->getFacade()->getElement($w)->buildJsConstructor('oController') . ',';
            } elseif ($w instanceof iHaveValue) {
                $content .= $this->buildJsObjectStatus($w) . ',';
            }
        }
        return <<<JS
        
            new sap.ui.layout.VerticalLayout({
                content: [
                    {$title}
                    {$content}
                ]
            }),
            
JS;
    }
}
?>