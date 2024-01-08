<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\JExcelTrait;
use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;
use exface\UI5Facade\Facades\Elements\Traits\UI5HelpButtonTrait;

class UI5DataImporter extends UI5AbstractElement
{    
    use JExcelTrait;
    use UI5HelpButtonTrait;
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        if ($this->getWidget()->hasPreview() === true) {
            $this->getFacade()->getElement($this->getWidget()->getPreviewButton())->addOnSuccessScript($this->buildJsDataSetter('response'));
        }
        
        $controller = $this->getController();
        $controller->addOnDefineScript($this->buildJsFixJqueryImportUseStrict());
        $controller->addOnPrefillDataChangedScript($this->buildJsResetter());
        
        $controller->addMethod('onFixedFooterSpread', $this, '', $this->buildJsFixedFootersSpreadFunctionBody());
        
        $this->registerExternalModules($controller);
        
        $table = <<<JS
        
                new sap.ui.core.HTML("{$this->getId()}", {
                    content: "<div id=\"{$this->getId()}_jexcel\" class=\"{$this->buildCssElementClass()} sapUiTable\"></div>",
                    afterRendering: function(oEvent) {
                        {$this->buildJsDestroy()}
                        {$this->buildJsJExcelInit()}
                        {$this->buildJsFixOverflowVisibility()}
                    }
                })
                
JS;
                            
        return $this->buildJsPanelWrapper($table, $oControllerJs) . ".addStyleClass('sapUiNoContentPadding exf-panel-no-border')";
    }
    
    /**
     * 
     * @param string $contentConstructorsJs
     * @param string $oControllerJs
     * @param string $toolbar
     * @return string
     */
    protected function buildJsPanelWrapper(string $contentConstructorsJs, string $oControllerJs = 'oController', string $toolbar = null)  : string
    {
        $toolbar = $toolbar ?? $this->buildJsToolbar($oControllerJs);
        $hDim = $this->getWidget()->getHeight();
        if (! $hDim->isUndefined()) {
            $height = $this->getHeight();
        } else {
            $height = $this->buildCssHeightDefaultValue();
        }
        return <<<JS
        new sap.m.Panel({
            height: "$height",
            headerToolbar: [
                {$toolbar}.addStyleClass("sapMTBHeader-CTX")
            ],
            content: [
                {$contentConstructorsJs}
            ]
        })
        
JS;
    }
    
    /**
     * 
     * @param string $oControllerJsVar
     * @param string $leftExtras
     * @param string $rightExtras
     * @return string
     */
    protected function buildJsToolbar($oControllerJsVar = 'oController', string $leftExtras = null, string $rightExtras = null)
    {
        $widget = $this->getWidget();
        
        $visible = $widget->getHideCaption() === true || ($this->getCaption() === '' && $widget->hasButtons() === false) ? 'false' : 'true';
        $heading = $widget->getHideCaption() === true ? '' : 'new sap.m.Label({text: ' . json_encode($this->getCaption()) . '}),';
        
        $buttons = '';
        foreach ($widget->getToolbarMain()->getButtonGroups() as $btn_group) {
            $buttons .= ($buttons && $btn_group->getVisibility() > EXF_WIDGET_VISIBILITY_OPTIONAL ? ",\n new sap.m.ToolbarSeparator()" : '');
            foreach ($btn_group->getButtons() as $btn) {
                $buttons .= $this->getFacade()->getElement($btn)->buildJsConstructor() . ",\n";
            }
        }
        
        return <<<JS
        
			new sap.m.OverflowToolbar({
                design: "Transparent",
                visible: {$visible},
				content: [
					{$heading}
                    {$leftExtras}
			        new sap.m.ToolbarSpacer(),
                    {$buttons}
                    {$this->buildJsHelpButtonConstructor()}
				]
			})
			
JS;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsFixOverflowVisibility() : string
    {
        return <<<JS

                        var aParents = {$this->buildJsJqueryElement()}.parents();
                        for (var i = 0; i < aParents.length; i++) {
                            var jqParent = $(aParents[i]);
                            if (jqParent.hasClass('sapUiRespGrid ') === true) {
                                break;
                            }
                            $(jqParent).css('overflow', 'visible');
                        }

JS;
    }
    
    /**
     * @see JExcelTrait::buildJsJqueryElement()
     */
    protected function buildJsJqueryElement() : string
    {
        return "$('#{$this->getId()}_jexcel')";
    }
    
    protected function buildJsFixedFootersSpread() : string
    {
        return $this->getController()->buildJsMethodCallFromController('onFixedFooterSpread', $this, '');
    }
    
    /**
     *
     * @return array
     */
    protected function getJsIncludes() : array
    {
        $htmlTagsArray = $this->buildHtmlHeadTagsForJExcel();
        $tags = implode('', $htmlTagsArray);
        $jsTags = [];
        preg_match_all('#<script[^>]*src="([^"]*)"[^>]*></script>#is', $tags, $jsTags);
        return $jsTags[1];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::registerExternalModules()
     */
    public function registerExternalModules(UI5ControllerInterface $controller) : UI5AbstractElement
    {
        $controller->addExternalModule('exface.openui5.jexcel', $this->getFacade()->buildUrlToSource("LIBS.JEXCEL.JS"), null, 'jexcel');
        $controller->addExternalCss($this->getFacade()->buildUrlToSource('LIBS.JEXCEL.CSS'));
        $controller->addExternalModule('exface.openui5.jsuites', $this->getFacade()->buildUrlToSource("LIBS.JEXCEL.JS_JSUITES"), null, 'jsuites');
        $controller->addExternalCss($this->getFacade()->buildUrlToSource('LIBS.JEXCEL.CSS_JSUITES'));
        
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsBusyIconShow()
     */
    public function buildJsBusyIconShow($global = false)
    {
        if ($global) {
            return parent::buildJsBusyIconShow($global);
        } else {
            return 'sap.ui.getCore().byId("' . $this->getId() . '").getParent().setBusyIndicatorDelay(0).setBusy(true);';
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsBusyIconHide()
     */
    public function buildJsBusyIconHide($global = false)
    {
        if ($global) {
            return parent::buildJsBusyIconHide($global);
        } else {
            return 'sap.ui.getCore().byId("' . $this->getId() . '").getParent().setBusy(false);';
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::registerConditionalProperties()
     */
    public function registerConditionalProperties() : UI5AbstractElement
    {
        parent::registerConditionalProperties();
        $this->registerConditionalPropertiesOfColumns();
        $this->getController()->addOnPrefillDataChangedScript("{$this->buildJsJqueryElement()}[0].exfWidget.refreshConditionalProperties()");
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsResetter()
     */
    public function buildJsResetter() : string
    {
        return $this->buildJsDataResetter();
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsChangesGetter()
     */
    public function buildJsChangesGetter() : string
    {
        return "({$this->buildJsJqueryElement()}[0].exfWidget.hasChanges() ? [{elementId: '{$this->getId()}', caption: {$this->escapeString($this->getCaption())}}] : [])";
    }
}