<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\Dialog;

class UI5Tab extends UI5Panel
{
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Panel::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        // Since the tab is allways a child of Tabs, we don't need to check for hasPageWrapper() here
        return $this->buildJsIconTabFilter();
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsIconTabFilter()
    {
        $caption = json_encode($this->getCaption());
        return <<<JS
    new sap.m.IconTabFilter("{$this->getId()}", {
        text: {$caption},
        content: [
            {$this->buildJsLayoutConstructor()}
        ]
    })
JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::registerConditionalProperties()
     */
    public function registerConditionalProperties() : UI5AbstractElement
    {
        parent::registerConditionalProperties();
        $contoller = $this->getController();
        
        // required_if
        if ($activeIf = $this->getWidget()->getActiveIf()) {
            $this->registerConditionalPropertyUpdaterOnLinkedElements($activeIf, $this->buildJsSetActive(true), $this->buildJsSetActive(false));
            $js = $this->buildJsConditionalProperty(
                $activeIf,
                $this->buildJsSetActive(true),
                $this->buildJsSetActive(false),
                true
            );
            $contoller
            ->addOnInitScript($js)
            ->addOnPrefillDataChangedScript($js);
        }
        
        return $this;
    }
    
    /**
     *
     * @param bool $trueOrFalse
     * @return string
     */
    public function buildJsSetActive(bool $trueOrFalse) : string
    {
        if ($trueOrFalse === false) {
            return '';
        }
        $widget = $this->getWidget();
        if ($widget->getTabs()->hasParent() && ($parent = $widget->getTabs()->getParent()) instanceof Dialog) {
            $dialogEl = $this->getFacade()->getElement($parent);
            if ($dialogEl->isObjectPageLayout()) {
                return "sap.ui.getCore().byId('{$dialogEl->getIdOfObjectPageLayout()}').setSelectedSection('{$this->getId()}')";
            }
        }
        return "sap.ui.getCore().byId('{$this->getFacade()->getElement($widget->getTabs())->getId()}').setSelectedKey('{$widget->getTabIndex()}')";
    }
}