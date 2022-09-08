<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\DataList;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryDataTransposerTrait;
use exface\Core\Widgets\DataColumnTransposed;

/**
 *
 * @method DataList getWidget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class UI5DataMatrix extends UI5DataTable
{
    use JqueryDataTransposerTrait;
    
    protected function init()
    {
        $this->initViaTrait();
        $this->getConfiguratorElement()->setIncludeColumnsTab(false);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataTable::isUiTable()
     */
    protected function isUiTable()
    {
        return true;    
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataTable::isMTable()
     */
    protected function isMTable()
    {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataTable::buildJsConstructorForUiTable()
     */
    protected function buildJsConstructorForUiTable(string $oControllerJs = 'oController')
    {
        return parent::buildJsConstructorForUiTable($oControllerJs);
    }
    
    protected function buildJsColumnStylers() : string
    {
        return <<<JS
        
        
        setTimeout(function(){
            $('#{$this->getId()}-table tbody tr').each(function(i, domRow) {
                var iMStoneFirst = null;
                var iMStoneLast = null;
                $(domRow).children().each(function(i, domCell) {
                    var jqCell = $(domCell);
                    var mVal = jqCell.text();
                    if (iMStoneFirst !== null) {
                        jqCell.css('background', '#5899da');
                        if (mVal) {
                            iMStoneLast = jqCell.index();
                        }
                        
                        if (mVal.includes('SAT') || mVal.includes('FAT')) {
                            jqCell.css('background', 'yellow');
                        }
                    } else {
                        if (jqCell.index() >= 4 && mVal) {
                            iMStoneFirst = jqCell.index();
                            jqCell.css('background', '#5899da');
                        } else {
                            jqCell.css('background', 'initial');
                        }
                    }
                });
                    if (iMStoneLast !== null) {
                        $(domRow).children().each(function(i, domCell) {
                            var jqCell = $(domCell);
                            if (jqCell.index() > iMStoneLast) {
                                jqCell.css('background', 'initial');
                            }
                        });
                    }
            });
        }, 0);
            
JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataTable::buildJsDataLoaderOnLoaded()
     */
    protected function buildJsDataLoaderOnLoaded(string $oModelJs = 'oModel') : string
    {
        return $this->buildJsTransposeColumns($oModelJs) . parent::buildJsDataLoaderOnLoaded($oModelJs);
    }
    
    /**
     * 
     * @param string $oModelJs
     * @return string
     */
    protected function buildJsTransposeColumns(string $oModelJs) : string
    {
        return <<<JS

(function(oModel) {        
    var oTable = sap.ui.getCore().byId('{$this->getId()}');
    var aColsNew = [];
    var oData = oModel.getData();
    
    if (! oTable._exfColModels || ! oTable._exfColControls) {
        oTable._exfColControls = oTable.getColumns();
        oTable._exfColModels = {$this->buildJsTransposerColumnModels()};
        
        // Add facade-specific column models parts
        oTable._exfColControls.forEach(function(oCol){
            oTable._exfColModels[oCol.data('_exfDataColumnName')].oUI5Col = oCol;
        });
    }
    
    var oTransposed = {$this->buildJsTranspose('oData', 'oTable._exfColModels')}
    
    oTable.removeAllColumns();
    oTable._exfColControls.forEach(function(oColCtrl){
        var sDataColumnName = oColCtrl.data('_exfDataColumnName');
        if (sDataColumnName === undefined) return;
        var oColModel = oTable._exfColModels[sDataColumnName];
        if (oColModel === undefined) return;
        switch (true) {
            case oColModel.aReplacedWithColumnKeys.length > 0:
                var oCol = oTable._exfColControls.find(function(oCol){
                    return oCol.data('_exfDataColumnName') === oColModel.sDataColumnName;
                });
                oColModel.aReplacedWithColumnKeys.forEach(function(sColKey){
                    var oColModelNew = oTransposed.oColModelsTransposed[sColKey];
                    var oColNew = oCol.clone();
                    var oTplNew = oColNew.getTemplate();
                    var sBindings = JSON.stringify(oTplNew.mBindingInfos);
                    var sBindingsNew = sBindings.replaceAll(oColModel.sDataColumnName, oColModelNew.sDataColumnName);
                    var oBindingsNew = JSON.parse(sBindingsNew);
                    var sBindingSetter;

                    for (var sProp in oBindingsNew) {
                        sBindingSetter = 'bind'+ sProp.charAt(0).toUpperCase() + sProp.slice(1);
                        if (oTplNew.getMetadata()._mAllProperties[sProp] !== undefined) {
                            oTplNew[sBindingSetter](oBindingsNew[sProp]);
                        }
                    }

                    oColNew.getLabel()
                        .setText(oColModelNew.sCaption)
                        .setTooltip(oColModelNew.sHint);
                    oColNew.setSorted(false);
                    oColNew.setShowSortMenuEntry(false);
                    oColNew.setShowFilterMenuEntry(false);
                    oColNew.setVisible(! oColModelNew.bHidden);
                    // TODO align?
                    
                    oTable.addColumn(oColNew);
                });
                break;
            case oColModel.bTransposeData === true:
                break;
            default:
                oTable.addColumn(oColCtrl);
                break;
        }
    });
    
    oModel.setData(oTransposed.oDataTransposed);

})($oModelJs);

JS;
    }
}