<?php
namespace exface\UI5Facade\Facades\Elements;

class UI5Tab extends UI5Panel
{
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Panel::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        if ($hiddenIf = $this->getWidget()->getHiddenIf()) {
            $this->registerConditionalPropertyUpdaterOnLinkedElements(
                $hiddenIf,
                $this->buildJsVisibilitySetter(false),
                $this->buildJsVisibilitySetter(true)
                );
        }
        if ($condProp = $this->getWidget()->getHiddenIf()) {
            $this->getController()->addOnPrefillDataChangedScript(
                $this->buildJsConditionalPropertyInitializer(
                    $condProp, 
                    $this->buildJsVisibilitySetter(false), 
                    $this->buildJsVisibilitySetter(true)
                )
            );
        } 
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
}