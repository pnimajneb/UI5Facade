<?php
namespace exface\UI5Facade\Facades\Elements\ServerAdapters;

use exface\UI5Facade\Facades\Elements\UI5AbstractElement;
use exface\UI5Facade\Facades\Interfaces\UI5ServerAdapterInterface;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Actions\ReadData;
use exface\Core\Interfaces\Widgets\iHaveQuickSearch;
use exface\Core\Actions\ReadPrefill;
use exface\Core\Exceptions\Facades\FacadeUnsupportedWidgetPropertyWarning;
use exface\Core\Interfaces\Model\MetaAttributeInterface;

class OfflineServerAdapter implements UI5ServerAdapterInterface
{
    private $element = null;
    
    private $fallbackAdapter = null;
    
    public function __construct(UI5AbstractElement $element, UI5ServerAdapterInterface $fallBackAdapter)
    {
        $this->element = $element;
        $this->fallbackAdapter = $fallBackAdapter;
    }
    
    public function getElement() : UI5AbstractElement
    {
        return $this->element;
    }
    
    protected function getFallbackAdapter() : UI5ServerAdapterInterface
    {
        return $this->fallbackAdapter;
    }
    
    public function buildJsServerRequest(ActionInterface $action, string $oModelJs, string $oParamsJs, string $onModelLoadedJs, string $onErrorJs = '', string $onOfflineJs = '') : string
    {
        $fallBackRequest = $this->getFallbackAdapter()->buildJsServerRequest($action, $oModelJs, $oParamsJs, $onModelLoadedJs, $onOfflineJs);
        switch (true) {
            case $action instanceof ReadPrefill:
                return $this->buildJsPrefillLoader($oModelJs, $oParamsJs, $onModelLoadedJs, $onOfflineJs, $fallBackRequest);
            case $action instanceof ReadData:
                return $this->buildJsDataLoader($oModelJs, $oParamsJs, $onModelLoadedJs, $onOfflineJs, $fallBackRequest);
        }
        
        return $fallBackRequest;
    }
    
    protected function buildJsPrefillLoader(string $oModelJs, string $oParamsJs, string $onModelLoadedJs, string $onOfflineJs, string $fallBackRequest) : string
    {
        $uidComp = EXF_COMPARATOR_EQUALS;
        return <<<JS

                var uid;
                if ($oParamsJs.data && $oParamsJs.data.rows && $oParamsJs.data.rows[0]) {
                    uid = $oParamsJs.data.rows[0]['{$this->getElement()->getMetaObject()->getUidAttribute()->getAlias()}'];
                } else {
                    console.warn('Cannot fetch offline data: no request data rows selected!');
                }

                if (uid === undefined || uid === '') {
                    console.warn('Cannot prefill from preload data: no UID value found in input rows!');
                }

                if ($oParamsJs.data !== undefined) {

                    if ($oParamsJs.data.filters === undefined) {
                        $oParamsJs.data.filters = {};
                    }
    
                    if ($oParamsJs.data.filters.conditions === undefined) {
                        $oParamsJs.data.filters.conditions = [];
                    }     
    
                    $oParamsJs.data.filters.conditions.push({
                        expression: '{$this->getElement()->getMetaObject()->getUidAttribute()->getDataAddress()}',
                        comparator: '{$uidComp}',
                        value: uid,
                        object_alias: '{$this->getElement()->getMetaObject()->getAliasWithNamespace()}'
                    });

                }

                {$this->buildJsDataLoader($oModelJs, $oParamsJs, $onModelLoadedJs, $onOfflineJs, $fallBackRequest, true)}

JS;
    }
    
    protected function buildJsDataLoader(string $oModelJs, string $oParamsJs, string $onModelLoadedJs, string $onOfflineJs, string $fallBackRequest, bool $useFirstRowOnly = false) : string
    {
        $element = $this->getElement();
        $widget = $element->getWidget();
        
        $useFirstRowJs = $useFirstRowOnly ? 'true' : 'false';
        
        return <<<JS
        
            (function(){
                var fnFallback = function(){
                    {$fallBackRequest};
                };

                if (navigator.onLine) {
                    return fnFallback();
                };

                return exfPWA
                .getOfflineData('{$widget->getMetaObject()->getAliasWithNamespace()}')
                .then(oDataSet => {
                    var bGetFirstRowOnly = $useFirstRowJs;
                    var aData = [];
                    var iFiltered = null;

                    if (oDataSet === undefined || ! Array.isArray(oDataSet.rows)) {
                        console.log('No ofline data found for {$widget->getMetaObject()->getAliasWithNamespace()}: falling back to server request');
                        return fnFallback();
                    }
                    
                    aData = oDataSet.rows;
                    if ({$oParamsJs}.data && {$oParamsJs}.data.filters) {
                        aData = exfTools.data.filterRows(aData, {$oParamsJs}.data.filters);
                    }

                    if ({$oParamsJs}.q !== undefined && {$oParamsJs}.q !== '') {
                        var sQuery = {$oParamsJs}.q.toString().toLowerCase();
                        {$this->buildJsQuickSearchFilter('sQuery', 'aData')}
                    }
                    
                    iFiltered = aData.length;
                    
                    if ({$oParamsJs}.start >= 0 && {$oParamsJs}.length > 0) {
                        aData = aData.slice({$oParamsJs}.start, {$oParamsJs}.start+{$oParamsJs}.length);
                    }

                    if (bGetFirstRowOnly) {
                        {$oModelJs}.setData(aData = aData[0]);
                    } else {
                        {$oModelJs}.setData({
                            oId: '{$widget->getMetaObject()->getId()}', 
                            rows: aData, 
                            recordsFiltered: iFiltered
                        });
                    }
                    {$onModelLoadedJs}
                })
                .then(function(){
                    return $oModelJs;
                });

            })()
                
JS;
    }
    
    /**
     * Returns an inline JS-snippet to test if a given JS row object matches the quick search string.
     *  
     * @param string $sQueryJs
     * @param string $oRowJs
     * @return string
     */
    protected function buildJsQuickSearchFilter(string $sQueryJs = 'sQuery', string $aDataJs = 'aData') : string
    {
        $widget = $this->getElement()->getWidget();
        
        if (! $widget instanceof iHaveQuickSearch) {
            return '';
        }
        
        $filters = [];
        $quickSearchCondGroup = $widget->getQuickSearchConditionGroup();
        if ($quickSearchCondGroup->countNestedGroups(false) > 0) {
            throw new FacadeUnsupportedWidgetPropertyWarning('Quick search with custom condition_group not supported in preloaded offline data!');
        }
        foreach ($quickSearchCondGroup->getConditions() as $condition) {
            if ($condition->getExpression()->isMetaAttribute()) {
                $filters[] = "((oRow['{$condition->getExpression()->toString()}'] || '').toString().toLowerCase().indexOf({$sQueryJs}) !== -1)";
                if ($condition->getExpression()->getAttribute()->isLabelForObject()) {
                    $labelAlias = MetaAttributeInterface::OBJECT_LABEL_ALIAS;
                    $filters[] = "((oRow['{$labelAlias}'] || '').toString().toLowerCase().indexOf({$sQueryJs}) !== -1)";
                }
            } else {
                throw new FacadeUnsupportedWidgetPropertyWarning('Quick search filters not based on simple attribute_alias not supported in preloaded offline data!');
            }
        }
        
        if (! empty($filters)) {
            $filterJs = implode(' || ', $filters);
        } else {
            return ''; 
        }
        
        return <<<JS

                            
                                {$aDataJs} = {$aDataJs}.filter(oRow => {
                                    if (oRow === undefined) {
                                        return false;
                                    }
                                    return {$filterJs};
                                });

JS;
    }
}