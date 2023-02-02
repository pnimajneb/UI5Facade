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
        
        $endTime = $calItem->hasEndTime() ? "oRow['{$calItem->getEndTimeColumn()->getDataColumnName()}']" : "''";
        $subtitle = $calItem->hasSubtitle() ? "{$calItem->getSubtitleColumn()->getDataColumnName()}: oRow['{$calItem->getSubtitleColumn()->getDataColumnName()}']," : '';
        
        if ($widget->hasUidColumn()) {
            $uid = "{$widget->getUidColumn()->getDataColumnName()}: oRow['{$widget->getUidColumn()->getDataColumnName()}'],";
        }
        
        if ($workdayStart = $widget->getTimelineConfig()->getWorkdayStartTime()){
            $workdayStartSplit = explode(':', $workdayStart);
            $workdayStartSplit = array_map('intval', $workdayStartSplit);
            $workdayStartJs = 'dMin.setHours(' . implode(', ', $workdayStartSplit) . ');';
        }
        
        return <<<JS
            (function(oTable) {
                var aTasks = [];
                oTable.getRows().forEach(function(oTreeRow) {
                    var oCtxt = oTreeRow.getBindingContext();
                    if (! oCtxt) return;
                    var oRow = oTable.getModel().getProperty(oCtxt.sPath);
                    var dMin, dStart, dEnd, sEnd;
                    var oTask = {
                        id: oRow['{$widget->getUidColumn()->getDataColumnName()}'],
                        name: oRow['{$calItem->getTitleColumn()->getDataColumnName()}'],
                        start: oRow["{$calItem->getStartTimeColumn()->getDataColumnName()}"],
                        end: oRow["{$calItem->getEndTimeColumn()->getDataColumnName()}"],
                        progress: 0,
                        dependencies: '',
                        custom_class: 'bar-style-' + oRow['roadmap_category']
                    };
    
                    if(oRow._children.length > 0 && oTask.start && oTask.end) {
                        oTask.custom_class += ' bar-folder';
                    }
    
                    aTasks.push(oTask);
                    
                    dStart = new Date(oRow["{$calItem->getStartTimeColumn()->getDataColumnName()}"]);
                    if (dMin === undefined) {
                        dMin = new Date(dStart.getTime());
                        {$workdayStartJs}
                    }
                    sEnd = $endTime;
                    if (sEnd) {
                        dEnd = new Date(sEnd);
                    } else {
                        dEnd = new Date(dStart.getTime());
                        dEnd.setHours(dEnd.getHours() + {$calItem->getDefaultDurationHours(1)});
                    }
                });
    
                var oGantt = sap.ui.getCore().byId('{$this->getId()}').gantt;
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
     *
     * @param string $oControlEventJsVar
     * @param string $oParamsJs
     * @param string $keepPagePosJsVar
     * @return string
     */
    protected function buildJsDataLoaderParams(string $oControlEventJsVar = 'oControlEvent', string $oParamsJs = 'params', $keepPagePosJsVar = 'bKeepPagingPos') : string
    {
        // Don't call the parent here as we don't want "regular" pagination. 
        $js = '';
        
        // If we are paging, page via start/end dates of the currently visible timeline
        if ($this->getWidget()->isPaged()) {
            $dateFormat = DateTimeDataType::DATETIME_ICU_FORMAT_INTERNAL;
            return <<<JS
        
            var oPCal = sap.ui.getCore().byId('{$this->getId()}');
            var oSchedulerModel = oPCal.getModel().getProperty('/_scheduler');
            var oStartDate = oPCal.getStartDate();
            var oEndDate = oPCal.getEndDate !== undefined ? oPCal.getEndDate() : oPCal._getFirstAndLastRangeDate().oEndDate.oDate;
            if (oSchedulerModel !== undefined) {
                if ($oParamsJs.data.filters === undefined) {
                    $oParamsJs.data.filters = {operator: "AND", conditions: []};
                }
                $oParamsJs.data.filters.conditions.push({
                    expression: '{$this->getWidget()->getTasksConfig()->getStartTimeColumn()->getDataColumnName()}',
                    comparator: '>=',
                    value: exfTools.date.format(oStartDate, '$dateFormat')
                });
                $oParamsJs.data.filters.conditions.push({
                    expression: '{$this->getWidget()->getTasksConfig()->getStartTimeColumn()->getDataColumnName()}',
                    comparator: '<=',
                    value: exfTools.date.format(oEndDate, '$dateFormat')
                });
            }
            
JS;
        }
        return $js;
    }
    
    /**
     *
     * @return bool
     */
    protected function hasQuickSearch() : bool
    {
        return true;
    }
    
    /**
     *
     * {@inheritdoc}
     * @see UI5DataElementTrait::buildJsClickHandlerLeftClick()
     */
    protected function buildJsClickHandlerLeftClick($oControllerJsVar = 'oController') : string
    {
        // Single click. Currently only supports one click action - the first one in the list of buttons
        if ($leftclick_button = $this->getWidget()->getButtonsBoundToMouseAction(EXF_MOUSE_ACTION_LEFT_CLICK)[0]) {
            return <<<JS
            
            .attachAppointmentSelect(function(oEvent) {
                {$this->getFacade()->getElement($leftclick_button)->buildJsClickEventHandlerCall($oControllerJsVar)};
            })
JS;
        }
        
        return '';
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
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::addOnChangeScript()
     */
    public function addOnChangeScript($js)
    {
        if (strpos($js, $this->buildJsValueGetter('~start_date')) !== false || strpos($js, $this->buildJsValueGetter('~end_date')) !== false) {
            $this->getController()->addOnEventScript($this, UI5Gantt::EVENT_NAME_TIMELINE_SHIFT, $js);
            return $this;
        }
        return parent::addOnChangeScript($js);
    }
    
    public function registerExternalModules(UI5ControllerInterface $controller) : UI5AbstractElement
    {
        // $f = $this->getFacade();
        $controller->addExternalModule('libs.exface.gantt.Gantt', 'vendor/exface/UI5Facade/Facades/js/frappe-gantt/dist/frappe-gantt.js', null, 'Gantt');
        $controller->addExternalCss('vendor/exface/UI5Facade/Facades/js/frappe-gantt/dist/frappe-gantt.min.css');
        return $this;
    }
    
    protected function buildJsRowPropertyColor(DataCalendarItem $calItem) : string
    {
        switch (true) {
            case $colorCol = $calItem->getColorColumn();
            $semanticColors = $this->getFacade()->getSemanticColors();
            $semanticColorsJs = json_encode(empty($semanticColors) ? new \stdClass() : $semanticColors);
            return <<<JS
                    color: {
                        path: "{$colorCol->getDataColumnName()}",
                        formatter: function(value){
                            var sColor = {$this->buildJsScaleResolver('value', $calItem->getColorScale(), $calItem->isColorScaleRangeBased())};
                            var sValueColor;
                            var oCtrl = this;
                            var sCssColor = '';
                            var oSemanticColors = $semanticColorsJs;
                            if (sColor.startsWith('~')) {
                                sCssColor = oSemanticColors[sColor] || '';
                            } else if (sColor) {
                                sCssColor = sColor;
                            }
                            return sCssColor;
                        }
                    },
JS;
            case null !== $colorVal = $calItem->getColor():
                return 'color: ' . $this->escapeString($colorVal) . ',';
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
    
    protected function isUiTable() : bool
    {
        return true;
    }
    
    protected function isMTable() : bool
    {
        return false;
    }
    
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