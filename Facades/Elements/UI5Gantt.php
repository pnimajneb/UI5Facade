<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\UI5Facade\Facades\Elements\Traits\UI5DataElementTrait;
use exface\Core\Interfaces\WidgetInterface;
use exface\UI5Facade\Facades\Interfaces\UI5ValueBindingInterface;
use exface\Core\Widgets\Parts\DataTimeline;
use exface\Core\DataTypes\DateTimeDataType;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JsValueScaleTrait;
use exface\Core\Widgets\Parts\DataCalendarItem;
use exface\Core\Exceptions\Facades\FacadeUnsupportedWidgetPropertyWarning;
use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;
use exface\Core\Facades\AbstractAjaxFacade\Formatters\JsDateFormatter;
use exface\Core\DataTypes\DateDataType;

/**
 * 
 * @method \exface\Core\Widgets\Scheduler getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5Gantt extends UI5DataTable
{
    use JsValueScaleTrait;

    const EVENT_NAME_TIMELINE_SHIFT = 'timeline_shift';
    
    const EVENT_NAME_ROW_SELECTION_CHANGE = 'row_selection_change';
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructorForControl($oControllerJs = 'oController') : string
    {
        $controller = $this->getController();
        $this->initConfiguratorControl($controller);
        $widget = $this->getWidget();
        
        $selection_mode = $widget->getMultiSelect() ? 'sap.ui.table.SelectionMode.MultiToggle' : 'sap.ui.table.SelectionMode.Single';
        $selection_behavior = $widget->getMultiSelect() ? 'sap.ui.table.SelectionBehavior.Row' : 'sap.ui.table.SelectionBehavior.RowOnly';
        
        if ($widget->getTreeExpandedLevels() !== null) {
            $numberOfExpandedLevelsJs = "numberOfExpandedLevels: {$widget->getTreeExpandedLevels()},";
        } else {
            $numberOfExpandedLevelsJs = "";
        }
        
        $gantt = <<<JS
        new sap.ui.layout.Splitter({
            contentAreas: [
                new sap.ui.table.TreeTable('{$this->getId()}', {
                    columnHeaderHeight: 52,
                    rows: {
                        path:'/rows', 
                        parameters: {
                            arrayNames: [
                                '_children'
                            ],
                            {$numberOfExpandedLevelsJs}
                        }
                    },
                    selectionMode: {$selection_mode},
    		        selectionBehavior: {$selection_behavior},
            		columns: [
            			{$this->buildJsColumnsForUiTable()}
            		],
                    noData: [
                        new sap.m.FlexBox({
                            height: "100%",
                            width: "100%",
                            justifyContent: "Center",
                            alignItems: "Center",
                            items: [
                                new sap.m.Text("{$this->getId()}_noData", {text: "{$this->getWidget()->getEmptyText()}"})
                            ]
                        })
                    ],
                    toggleOpenState: function(oEvent) {
                        var oTable = oEvent.getSource();
                        setTimeout(function(){
                            {$this->buildJsSyncTreeToGantt('oTable')};
                        },10);
                    },
                    firstVisibleRowChanged: function(oEvent) {
                        var oTable = oEvent.getSource();
                        var domGanttContainer = $('#{$this->getId()}_gantt .gantt-container')[0];
                        var iScrollLeft = domGanttContainer.scrollLeft;
                        setTimeout(function(){
                            {$this->buildJsSyncTreeToGantt('oTable')};
                            domGanttContainer.scrollTo(iScrollLeft, 0);
                        },10);
                    }
                }),
                new sap.ui.core.HTML("{$this->getId()}_wrapper", {
                    content: "<div id=\"{$this->getId()}_gantt\" class=\"exf-gantt\" style=\"height:100%; min-height: 100px; overflow: hidden;\"></div>",
                    afterRendering: function(oEvent) {
                        var oCtrl = sap.ui.getCore().byId('{$this->getId()}');
                        if (oCtrl.gantt === undefined) {
                            oCtrl.gantt = {$this->buildJsGanttInit()}
                            var oRowsBinding = new sap.ui.model.Binding(sap.ui.getCore().byId('{$this->getId()}').getModel(), '/rows', sap.ui.getCore().byId('{$this->getId()}').getModel().getContext('/rows'));
                            oRowsBinding.attachChange(function(oEvent){
                                var oBinding = oEvent.getSource();
                                var oTable = sap.ui.getCore().getElementById('{$this->getId()}');
                                setTimeout(function(){
                                    {$this->buildJsSyncTreeToGantt('oTable')};
                                },100);
                            });
                        }
                        
                        setTimeout(function(){
                            // TODO
                        }, 0);
                        sap.ui.core.ResizeHandler.register(sap.ui.getCore().byId('{$this->getId()}').getParent(), function(){
                            // TODO
                        });
                    }
                })
            ]
        })

JS;
        return $this->buildJsPanelWrapper($gantt, $oControllerJs) . ".addStyleClass('sapUiNoContentPadding')";
    }
    
    protected function buildJsGanttGetInstance() : string
    {
        return $this->getController()->buildJsDependentObjectGetter('gantt', $this);
    }
		
    protected function buildJsGanttInit() : string
    {
        $widget = $this->getWidget();
        $dateFormat = $this->escapeString($this->getWorkbench()->getCoreApp()->getTranslator()->translate('LOCALIZATION.DATE.DATE_FORMAT'));
        
        $calItem = $widget->getTasksConfig();
        $startCol = $calItem->getStartTimeColumn();
        $startFormatter = $this->getFacade()->getDataTypeFormatter($startCol->getDataType());
        $endCol = $calItem->getEndTimeColumn();
        $endFormatter = $this->getFacade()->getDataTypeFormatter($endCol->getDataType());
        
        switch ($widget->getTimelineConfig()->getGranularity(DataTimeline::GRANULARITY_HOURS)) {
            case DataTimeline::GRANULARITY_HOURS: $viewMode = 'Quater Day'; break;
            case DataTimeline::GRANULARITY_DAYS: $viewMode = 'Day'; break;
            case DataTimeline::GRANULARITY_DAYS_PER_WEEK: $viewMode = 'Day'; break;
            case DataTimeline::GRANULARITY_DAYS_PER_MONTH: $viewMode = 'Day'; break;
            case DataTimeline::GRANULARITY_MONTHS: $viewMode = 'Month'; break;
            case DataTimeline::GRANULARITY_WEEKS: $viewMode = 'Week'; break;
            case DataTimeline::GRANULARITY_YEARS: $viewMode = 'Year'; break;
            default: $viewMode = 'sap.ui.unified.CalendarIntervalType.Hour'; break;
        }
        
        return <<<JS
(function() {   
    return new Gantt("#{$this->getId()}_gantt", [
      {
        id: 1,
        name: 'Loading...',
        start: null,
        end: null
      }
    ], {
        header_height: 46,
        column_width: 30,
        step: 24,
        view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
        bar_height: 19,
        bar_corner_radius: 3,
        arrow_curve: 5,
        padding: 14,
        view_mode: '$viewMode',
        date_format: $dateFormat,
        language: 'en', // or 'es', 'it', 'ru', 'ptBr', 'fr', 'tr', 'zh', 'de', 'hu'
        custom_popup_html: null,
    	on_date_change: function(oTask, dStart, dEnd) {
    		var oTable = sap.ui.getCore().byId('{$this->getId()}');
            var oModel = oTable.getModel();
            var oGantt = sap.ui.getCore().byId('{$this->getId()}').gantt;
            var iRow = oGantt.tasks.indexOf(oTask);
            var oCtxt = oTable.getRows()[iRow].getBindingContext();
            var oRow = oTable.getModel().getProperty(oCtxt.sPath);
            oModel.setProperty(oCtxt.sPath + '/{$startCol->getDataColumnName()}', {$startFormatter->buildJsFormatDateObjectToInternal('dStart')});
            oModel.setProperty(oCtxt.sPath + '/{$endCol->getDataColumnName()}', {$endFormatter->buildJsFormatDateObjectToInternal('dEnd')});
    	}
    });
})();

JS;
    }
    
    /**
     * 
     * @param string $oModelJs
     * @return string
     */
    protected function buildJsDataLoaderOnLoaded(string $oModelJs = 'oModel') : string
    {    
        return parent::buildJsDataLoaderOnLoaded($oModelJs) . <<<JS

                var oDataTree = {$this->buildJsTransformToTree($oModelJs . '.getData()')} 
                {$oModelJs}.setData(oDataTree);

JS;
    }
    
    /**
     * 
     * @param string $oControlJs
     * @return string
     */
    protected function buildJsGetRowsAll(string $oControlJs) : string
    {
        // NOTE: oTable.getModel().getData() returns only the top level rows, but .getJSON() yields
        // all. This is why the JSON parsing became neccessary
        return "({$this->buildJsTransformFromTree("JSON.parse({$oControlJs}.getModel().getJSON()")}).rows || [])";
    }
    
    /**
     * 
     * @param string $oDataJs
     * @return string
     */
    protected function buildJsTransformFromTree(string $oDataJs) : string
    {
        return <<<JS
        
                (function(oDataTree) {
                    var oDataFlat = $.extend({}, oDataTree);
                    var fnFlatten = function(aRows) {
                        var aFlat = [];
                        aRows.forEach(function(oRow) {
                            aFlat.push(oRow);
                            if (Array.isArray(oRow._children) && oRow._children.length > 0) {
                                aFlat.push(...fnFlatten(oRow._children));
                            }
                            delete oRow._children;
                        });
                        return aFlat;
                    };
                    oDataFlat.rows = fnFlatten(oDataTree.rows || []);                  
                    return oDataFlat;
                })($oDataJs)
                
JS;
    }
    
    /**
     * 
     * @param string $oDataJs
     * @return string
     */
    protected function buildJsTransformToTree(string $oDataJs) : string
    {
        return <<<JS

                (function(oDataFlat) {
                    var oDataTree = $.extend({}, oDataFlat);
                    var sParentCol = '{$this->getWidget()->getTreeParentRelationAlias()}';

                    function list_to_tree(list) {
                      var map = {}, node, roots = [], i;
                      
                      for (i = 0; i < list.length; i += 1) {
                        map[list[i].id] = i; // initialize the map
                        list[i]._children = []; // initialize the children
                      }
                      
                      for (i = 0; i < list.length; i += 1) {
                        node = list[i];
                        if (node[sParentCol] !== '' && node[sParentCol] !== null) {
                          // if you have dangling branches check that map[node.parentId] exists
                          list[map[node[sParentCol]]]._children.push(node);
                        } else {
                          roots.push(node);
                        }
                      }
                      return roots;
                    }

                    oDataTree.rows = list_to_tree(oDataFlat.rows);
                    return oDataTree;
                })($oDataJs)

JS;
    }
    
    protected function buildJsSyncTreeToGantt(string $oTableJs) : string
    {
        $widget = $this->getWidget();
        $calItem = $widget->getTasksConfig();
        
        return <<<JS
            (function(oTable) {
                var oGantt = sap.ui.getCore().byId('{$this->getId()}').gantt;
                var aTasks = [];
                oTable.getRows().forEach(function(oTreeRow) {
                    var oCtxt = oTreeRow.getBindingContext();
                    if (! oCtxt) return;
                    var oRow = oTable.getModel().getProperty(oCtxt.sPath);
                    var oTask = {
                        id: oRow['{$widget->getUidColumn()->getDataColumnName()}'],
                        name: oRow['{$calItem->getTitleColumn()->getDataColumnName()}'],
                        start: oRow["{$calItem->getStartTimeColumn()->getDataColumnName()}"],
                        end: oRow["{$calItem->getEndTimeColumn()->getDataColumnName()}"],
                        progress: 0,
                        dependencies: ''/*,
                        TODO
                        custom_class: 'bar-style-' + */
                    };
    
                    if(oRow._children.length > 0 && oTask.start && oTask.end) {
                        oTask.custom_class += ' bar-folder';
                    }
    
                    aTasks.push(oTask);
                });
    
                oGantt.tasks = aTasks;
                if (aTasks.length > 0) {
                    oGantt.refresh(aTasks);
                } else  {
                    oGantt.clear();
                }
            })($oTableJs)
            
JS;
    }
    
    /**
     * {@inheritdoc}
     * @see UI5DataElementTrait::isEditable()
     */
    public function isEditable()
    {
        return $this->getWidget()->isEditable();
    }
    
    /**
     * {@inheritdoc}
     * @see UI5DataElementTrait::hasPaginator()
     */
    protected function hasPaginator() : bool
    {
        return false;
    }
    
    public function registerExternalModules(UI5ControllerInterface $controller) : UI5AbstractElement
    {
        // $f = $this->getFacade();
        $controller->addExternalModule('libs.exface.gantt.Gantt', 'vendor/exface/UI5Facade/Facades/js/frappe-gantt/dist/frappe-gantt.js', null, 'Gantt');
        $controller->addExternalCss('vendor/exface/UI5Facade/Facades/js/frappe-gantt/dist/frappe-gantt.min.css');
        return $this;
    }
    
    /**
     * 
     * @param DataCalendarItem $calItem
     * @param string $oRowJs
     * @return string
     */
    protected function buildJsColorResolver(DataCalendarItem $calItem, string $oRowJs) : string
    {
        switch (true) {
            case $colorCol = $calItem->getColorColumn();
                $semanticColors = $this->getFacade()->getSemanticColors();
                $semanticColorsJs = json_encode(empty($semanticColors) ? new \stdClass() : $semanticColors);
                if ($calItem->hasColorScale()) {
                    return <<<JS
                        (function(oRow){
                            var value = oRowJs['{$colorCol->getDataColumnName()}']
                            var sColor = {$this->buildJsScaleResolver('value', $calItem->getColorScale(), $calItem->isColorScaleRangeBased())};
                            var sCssColor = '';
                            var oSemanticColors = $semanticColorsJs;
                            if (sColor.startsWith('~')) {
                                sCssColor = oSemanticColors[sColor] || '';
                            } else if (sColor) {
                                sCssColor = sColor;
                            }
                            return sCssColor;
                        })(oRow)
JS;
                } else {
                    return "oRow['{$colorCol->getDataColumnName()}']";
                }
            case null !== $colorVal = $calItem->getColor():
                return $this->escapeString($colorVal);
        }
        return '';
    }
    
    /**
     *
     * @return string
     */
    protected function buildJsDataResetter() : string
    {
        return parent::buildJsDataResetter() . "; sap.ui.getCore().byId('{$this->getId()}').data('_exfStartDate', {$this->escapeString($this->getWidget()->getStartDate())});";
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataTable::isUiTable()
     */
    protected function isUiTable() : bool
    {
        return true;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataTable::isMTable()
     */
    protected function isMTable() : bool
    {
        return false;
    }
    
    /**
     * TODO For some reason, transforming model data to tree did not work when there was a dirty-column
     * 
     * @return bool
     */
    protected function hasDirtyColumn() : bool
    {
        return false;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsFullscreenContainerGetter() : string
    {
        return "$('#{$this->getId()}').parents('.sapMPanel').first().parent().parent()";
    }
}