<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\Tabs;
use exface\Core\Widgets\Tab;
use exface\Core\Widgets\Image;
use exface\Core\Interfaces\Widgets\iHaveValue;
use exface\Core\Interfaces\Actions\iShowWidget;
use exface\Core\Interfaces\Model\MetaAttributeInterface;
use exface\Core\Factories\WidgetFactory;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Interfaces\WidgetInterface;
use exface\Core\Interfaces\Widgets\iFillEntireContainer;
use exface\Core\Factories\ActionFactory;
use exface\Core\Widgets\Split;

/**
 * Renders a Popup widget as a sap.m.Popover
 * 
 * @method \exface\Core\Widgets\Popup getWidget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class UI5Popup extends UI5Form
{
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Form::buildJsConstructor()
     */   
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $widget = $this->getWidget();
        $controller = $this->getController();
        
        // Submit on enter
        $this->registerSubmitOnEnter($oControllerJs);
        
        
        // Finally, instantiate the dialog
        $icon = $widget->getIcon() ? 'icon: "' . $this->getIconSrc($widget->getIcon()) . '",' : '';
        return <<<JS

        new sap.m.Popover("{$this->getId()}", {
			{$icon}
            {$this->buildJsPropertyContentHeight()}
            {$this->buildJsPropertyContentWidth()}
            title: {$this->escapeString($this->getCaption())},
			content : [ {$this->buildJsLayoutConstructor()} ],
            footer: {$this->buildJsFloatingToolbar()}
		}).addStyleClass('{$this->buildCssElementClass()}')
        {$this->buildJsPseudoEventHandlers()}
JS;
    }

    /**
     * 
     * @return string
     */
    protected function buildJsPropertyContentHeight() : string
    {
        $height = '';
        
        $dim = $this->getWidget()->getHeight();
        switch (true) {
            case $dim->isPercentual():
            case $dim->isFacadeSpecific() && strtolower($dim->getValue()) !== 'auto':
                $height = json_encode($dim->getValue());
                break;
            case $dim->isRelative():
                $height = json_encode(($dim->getValue() * $this->getHeightRelativeUnit()) . 'px');
                break;
            default:
                $height = '"auto"';
        }
        
        return $height ? 'contentHeight: ' . $height . ',' : '';
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsPropertyContentWidth() : string
    {
        $width = '';
        
        $dim = $this->getWidget()->getWidth();
        switch (true) {
            case $dim->isPercentual():
            case $dim->isFacadeSpecific():
                $width = json_encode($dim->getValue());
                break;
            case $dim->isRelative():
                $width = json_encode(($dim->getValue() * $this->getWidthRelativeUnit()) . 'px');
                break;
            default:
                $width = '"auto"'; // This is the size of a P13nDialog used for data configurator
        }
        
        return $width ? 'contentWidth: ' . $width . ',' : '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::getCaption()
     */
    protected function getCaption() : string
    {
        $caption = parent::getCaption();
        $widget = $this->getWidget();
        $objectName = $widget->getMetaObject()->getName();
        $buttonCaption = $widget->hasParent() ? $widget->getParent()->getCaption() : null;
        
        // Append the object name to the caption unless
        // - The dialog has a custom caption (= not qual to the button caption)
        // - The caption is the same as the object name (would look stupid then)
        return $caption === $objectName || $caption !== $buttonCaption ? $caption : $caption . ': ' . $objectName;
    }
    
    /**
     * Returns the JS constructor for the sap.m.Page used as the top-level control when rendering
     * the dialog as an object page layout. 
     * 
     * The page will have a floating toolbar with all dialog buttons and a header with a title and
     * the close/back button.
     * 
     * @param string $content_js
     * @return string
     */
    protected function buildJsPage($content_js, string $oControllerJs = 'oController')
    {
        $this->getController()->addOnRouteMatchedScript($this->buildJsRefresh(false), 'loadPrefill');
        if ($this->getWidget()->isCacheable() === false) {
            $this->getController()->addOnHideViewScript("sap.ui.getCore().byId('{$this->getId()}').destroy()");
        }
        
        return <<<JS
        
        new sap.m.Page("{$this->getId()}", {
            title: "{$this->getCaption()}",
            showNavButton: true,
            navButtonPress: {$this->getController()->buildJsMethodCallFromView(UI5Popup::CONTROLLER_METHOD_CLOSE_DIALOG, $this, $oControllerJs)},
            content: [
                {$content_js}
            ],
            headerContent: [
                {$this->buildJsPageHeaderContent($oControllerJs)}
            ],
            footer: {$this->buildJsFloatingToolbar()}
        }).addStyleClass('{$this->buildCssElementClass()}')
        {$this->buildJsPseudoEventHandlers()}

JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Form::buildCssElementClass()
     */
    public function buildCssElementClass()
    {
        return 'exf-popup' .  ($this->getWidget()->isFilledBySingleWidget() ? ' exf-dialog-filled' : '');
    }
    
    /**
     * Returns the button constructors for the dialog buttons.
     * 
     * @return string
     */
    protected function buildJsDialogButtons(bool $addSpacer = true)
    {
        $toolbarEl = $this->getFacade()->getElement($this->getWidget()->getToolbarMain());
        $js = $toolbarEl->buildJsConstructorsForLeftButtons();
        if ($addSpacer === true) {
            $js .= 'new sap.m.ToolbarSpacer(),';
        }
        $js .= $toolbarEl->buildJsConstructorsForRightButtons();
        return $js;
    }
    
    /**
     *
     * @return string
     */
    protected function buildJsLayoutFormFixes() : string
    {
        $fixContainerQueryJs = <<<JS
        
                    var oGrid = sap.ui.getCore().byId($("#{$this->getId()}-scrollCont > .sapUiForm > .sapUiFormResGrid > .sapUiRGLContainer > .sapUiRGLContainerCont > .sapUiRespGrid").attr("id"));
                    if (oGrid !== undefined) {
                        oGrid.setContainerQuery(false);
                    }
                    
JS;
        $this->addPseudoEventHandler('onAfterRendering', $fixContainerQueryJs);
        
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::hasButtonBack()
     */
    public function hasButtonBack() : bool
    {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Form::buildJsFloatingToolbar()
     */
    protected function buildJsFloatingToolbar()
    {
        // The Dialog does not need a caption in the toolbar like the Form does
        return $this->getFacade()->getElement($this->getWidget()->getToolbarMain())->buildJsConstructor();
    }
    
    /**
     * 
     * @return string
     */
    public function buildJsCloseDialog() : string
    {
        return "sap.ui.getCore().byId('{$this->getId()}').close();";
    }
}