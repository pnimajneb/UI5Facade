<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\SplitHorizontal;
use exface\Core\Widgets\Split;

/**
 * Generates a sap.ui.layout.SplitPane or a sap.ui.layout.PaneContainer for a SplitPanel widget
 * 
 * Which control to use depends on whether the panel contains regular
 * widgets (-> SplitPane) or a nested split (-> PaneContainer).
 * 
 * @see UI5Split for more details about the structure of split widgets in UI5
 * 
 * @method \exface\Core\Widgets\SplitPanel getWidget()
 * @author Andrej Kabachnik
 *
 */
class UI5SplitPanel extends UI5Panel
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Panel::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $widget = $this->getWidget();
        if ($widget->isFilledBySingleWidget() && $widget->getFillerWidget() instanceof Split) {
            // If the panel contains a nested split, make its UI5Split render the content
            // but give it the layout data containing the correct width/heigth first.
            $innerSplitElement = $this->getFacade()->getElement($widget->getFillerWidget());
            $innerSplitElement->setLayoutData($this->buildJsSizeLayoutConstructor());
            return $innerSplitElement->buildJsConstructor($oControllerJs);
        } else {
            // If the Panel contains anything else, render a SplitPane with a regular Panel
            // as content.
            $panel = parent::buildJsConstructor($oControllerJs);
            return <<<JS

            new sap.ui.layout.SplitPane({
                content: [
                    {$panel}
                ]
            })
JS;
        }
    }
    
    /**
     * In addition to panel properties, the SplitPanel always includes layoutData.
     * 
     * @see \exface\UI5Facade\Facades\Elements\UI5Panel::buildJsProperties()
     */
    public function buildJsProperties()
    {
        return parent::buildJsProperties() . " layoutData: {$this->buildJsSizeLayoutConstructor()},";
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsSizeLayoutConstructor() : string
    {
        $widget = $this->getWidget();
        $sizeDimension = $widget->getParent() instanceof SplitHorizontal ? $widget->getWidth() : $widget->getHeight();
        switch (true) {
            case $sizeDimension->isFacadeSpecific() === true:
            case $sizeDimension->isPercentual() === true:
                $size = $sizeDimension->getValue();
                break;
            default:
                $size = 'auto';
        }
        return "new sap.ui.layout.SplitterLayoutData({size: '$size'})";
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Panel::buildJsPropertyHeight()
     */
    protected function buildJsPropertyHeight() : string
    {
        return 'height: "100%",';
    }
    
    /**
     * Do not set the widht of the panel inside SplitPane - otherwise a SplitPane with 30% width will
     * have a panel, that is 30% of the SplitPane! The width of the SplitPane itself is set in
     * `buildJsSizeLayoutConstructor()`.
     * 
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsPropertyWidth()
     */
    protected function buildJsPropertyWidth()
    {
        return '';
    }
}
