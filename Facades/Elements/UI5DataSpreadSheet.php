<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\JExcelTrait;
use exface\UI5Facade\Facades\Elements\Traits\UI5DataElementTrait;
use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;
use exface\UI5Facade\Facades\Interfaces\UI5DataElementInterface;
use exface\Core\Interfaces\Actions\ActionInterface;

class UI5DataSpreadSheet extends UI5AbstractElement implements UI5DataElementInterface
{    
    use JExcelTrait;
    use UI5DataElementTrait {
        UI5DataElementTrait::buildJsDataResetter as buildJsDataResetterViaTrait;
        UI5DataElementTrait::buildJsDataLoaderOnLoaded as buildJsDataLoaderOnLoadedViaTrait;
        JExcelTrait::buildJsDataResetter as buildJsJExcelResetter;
        JExcelTrait::buildJsDataGetter as buildJsJExcelDataGetter;
        JExcelTrait::buildJsValueGetter insteadof UI5DataElementTrait;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructorForControl($oControllerJs = 'oController') : string
    {
        $this->getFacade()->getElement($this->getWidget()->getConfiguratorWidget())->registerFiltersWithApplyOnChange($this);
        
        $this->registerReferencesAtLinkedElements();
        
        $controller = $this->getController();
        $controller->addOnDefineScript($this->buildJsFixJqueryImportUseStrict());
        
        $controller->addMethod('onFixedFooterSpread', $this, '', $this->buildJsFixedFootersSpreadFunctionBody());
        
        $this->registerExternalModules($controller);
        
        $table = <<<JS
        
                new sap.ui.core.HTML("{$this->getId()}", {
                    content: "<div id=\"{$this->getId()}\"><div id=\"{$this->getId()}_jexcel\" class=\"{$this->buildCssElementClass()} sapUiTable\"></div></div>",
                    afterRendering: function(oEvent) {
                        {$this->buildJsDestroy()};
                        {$this->buildJsJExcelInit()};
                        {$this->buildJsRefresh()};
                        {$this->buildJsFixOverflowVisibility()}
                    }
                })
                
JS;
                            
        return $this->buildJsPanelWrapper($table, $oControllerJs) . ".addStyleClass('sapUiNoContentPadding exf-panel-no-border')";
    }
    
    /**
     * Dropdowns from jExcel are cut off by the border of the containing UI5 control sometimes
     * because that UI5 control has overflow:hidden at some point. This code fixes this.
     * 
     * Every time a dropdown is opened, the corresponding menu gets the css property `position:fixed`.
     * This nails down the current position relative to the viewport. Thus, the menu is not bound by
     * the encoling DOM elements anymore and is displayed above them. 
     * 
     * However, if the spreadsheet is scrollable, the menu does not scroll with it. This is done
     * explicitly by recalculating the menus offset on scroll events.
     * 
     * The idea was taken from https://medium.com/@thomas.ryu/css-overriding-the-parents-overflow-hidden-90c75a0e7296
     * 
     * @return string
     */
    protected function buildJsFixOverflowVisibility() : string
    {
        return <<<JS
                        (function() {
                            var jExcel = {$this->buildJsJqueryElement()}[0].exfWidget.getJExcel();
                            var fnOnEditStart = jExcel.options.oneditionstart;
                            var fnOnEditEnd = jExcel.options.oneditionend;

                            jExcel.options.oneditionstart = function(el, domCell, x, y){
                                var jqCell = $(domCell);
                                // The dropdown is not instantiated yet! There is just the cell
                                if (jqCell.hasClass('jexcel_dropdown')) {                                    
                                    setTimeout(function(){
                                        // Now the dropdown is here 
                                        var jqExcel = {$this->buildJsJqueryElement()};
                                        var jqScroller = jqExcel.parents('.sapMPanelContent').first();
                                        var jqDD = jqCell.find('.jdropdown-container');
                                        var oPosCellInit = jqCell.offset();
                                        var oPosDDInit = jqDD.offset();
                                        jqDD.css('position', 'fixed');
                                        oPos = jqCell.offset();
                                        jqScroller.on('scroll', function(oEvent) {
                                            var oPosCellCur = jqCell.offset();
                                            var iScrollTop = oPosCellCur.top - oPosCellInit.top;
                                            var iScrollLeft = oPosCellCur.left - oPosCellInit.left;
                                            jqDD.offset({
                                                top: oPosDDInit.top + iScrollTop,
                                                left: oPosDDInit.left + iScrollLeft
                                            });
                                        });
                                    }, 0);
                                }

                                if (fnOnEditStart) {
                                    fnOnEditStart(el, domCell, x, y);
                                }
                            };
                        })();

JS;
    }
    
    /**
     * @see JExcelTrait::buildJsJqueryElement()
     */
    protected function buildJsJqueryElement() : string
    {
        return "$('#{$this->getId()}_jexcel')";
    }
    
    /**
     *
     * {@inheritdoc}
     * @see UI5DataElementTrait::buildJsDataLoaderOnLoaded()
     */
    protected function buildJsDataLoaderOnLoaded(string $oModelJs = 'oModel') : string
    {
        return $this->buildJsDataLoaderOnLoadedViaTrait($oModelJs) . <<<JS

        {$this->buildJsDataSetter('oModel.getData()')}
        {$this->buildJsFooterRefresh('oModel.getData()', $this->buildJsJqueryElement())}

JS;
    }
    
    /**
     * 
     * @return string
     */
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
     * {@inheritdoc}
     * @see UI5DataElementTrait::isEditable()
     */
    protected function isEditable()
    {
        return true;
    }

    /**
     *
     * {@inheritdoc}
     * @see UI5DataElementTrait::buildJsGetRowsSelected()
     */
    protected function buildJsGetRowsSelected(string $oTableJs): string
    {
        return '[]';
    }

    /**
     * 
     * @return string
     */
    protected function buildJsDataResetter() : string
    {
        return $this->buildJsDataResetterViaTrait() . ';' . $this->buildJsJExcelResetter();
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
     * @see UI5DataElementTrait::buildJsEditableChangesChecker()
     * @param string $oTableJs
     * @return string
     */
    public function buildJsEditableChangesChecker(string $oTableJs = null) : string
    {
        return "({$this->buildJsJqueryElement()}.length === 0 ? false : {$this->buildJsJqueryElement()}[0].exfWidget.hasChanges())";
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
        $this->getController()->addOnPrefillDataChangedScript("(function(jqEl){if (jqEl.length) jqEl[0].exfWidget.refreshConditionalProperties()})({$this->buildJsJqueryElement()})");
        return $this;
    }
    
    /**
     *
     * @see UI5DataElementTrait::isWrappedInPanel()
     */
    protected function isWrappedInPanel() : bool
    {
        return true;
    }
    
    /**
     * 
     * @see JExcelTrait::buildJsCheckHidden()
     */
    protected function buildJsCheckHidden(string $jqElement) : string
    {
        return "($jqElement.parents().filter('.sapUiHidden').length > 0)";
    }
    
    /**
     *
     * {@inheritDoc}
     * @see JExcelTrait::buildJsDataGetter()
     */
    public function buildJsDataGetter(ActionInterface $action = null)
    {
        return <<<JS
        
        (function(){
            var bDataPending = {$this->buildJsIsDataPending()};
            if (bDataPending) {
                return {};
            }
            
            return ({$this->buildJsJExcelDataGetter($action)});
        }())
JS;
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