<?php
namespace exface\UI5Facade\Facades\Elements;

/**
 * Renders a sap.m.IconTabBar for the Tabs widget
 * 
 * @method \exface\Core\Widgets\Tabs getWidget() 
 * 
 * @author andrej.kabachnik
 *
 */
class UI5Tabs extends UI5Container
{

    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $iconTabBar = $this->buildJsIconTabBar();
        
        if ($this->hasPageWrapper() === true) {
            return $this->buildJsPageWrapper($iconTabBar);
        }
        
        return $iconTabBar;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsIconTabBar()
    {
        $widget = $this->getWidget();
        $options = '';
        $selectedTab = $widget->getTab($widget->getActiveTabIndex());
        if ($selectedTab->isFilledBySingleWidget() === true) {
            $options .= 'applyContentPadding: false,';
        }
        if ($widget->getActiveTabIndex() > 0) {
            $options .= 'selectedKey: ' . $this->escapeString($this->getFacade()->getElement($selectedTab)->getId()) . ',';
        }
        
        return <<<JS

    new sap.m.IconTabBar("{$this->getId()}", {
        showOverflowSelectList: true,
        expandable: false,
        stretchContentHeight: true, // FIXME makes header of ObjectPage sometimes inivsible if set
        $options
        items: [
            {$this->buildJsChildrenConstructors()}
        ],
        select: function(oEvent) {
            {$this->buildJsOnChangeScript('oEvent')}
        }
    })
    {$this->buildJsPseudoEventHandlers()}
JS;
    }
    
    /**
     * 
     * @param string $oEventJs
     * @return string
     */
    protected function buildJsOnChangeScript(string $oEventJs) : string
    {
        $filledTabIds = [];
        /* @var $tab \exface\Core\Widgets\Tab */
        foreach ($this->getWidget()->getWidgets() as $tab) {
            if ($tab->isFilledBySingleWidget() === true) {
                $filledTabIds[] = $this->getFacade()->getElement($tab)->getId();
            }
        }
        $filledTabIdsJSON = json_encode($filledTabIds);
        
        return <<<JS
            
            var oParams = $oEventJs.getParameters();
            var oTabBar = $oEventJs.getSource();
            var sKey = oParams.selectedKey;
            var aTabIdsNoPadding = $filledTabIdsJSON;
            if (aTabIdsNoPadding.indexOf('sKey') !== -1) {
                oTabBar.setApplyContentPadding(true);
            } else {
                oTabBar.setApplyContentPadding(false);
            }
JS . $this->getOnChangeScript();
    }
}
?>
