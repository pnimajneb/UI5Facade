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

/**
 * 
 * @method \exface\Core\Widgets\Scheduler getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5Gantt extends UI5AbstractElement
{
    use UI5DataElementTrait {
        buildJsDataLoaderOnLoaded as buildJsDataLoaderOnLoadedViaTrait;
        buildJsValueGetter as buildJsValueGetterViaTrait;
        buildJsDataResetter as buildJsDataResetterViaTrait;
    }
    
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
        
        $showRowHeaders = $this->getWidget()->hasResources() ? 'true' : 'false';
        switch ($this->getWidget()->getTimelineConfig()->getGranularity(DataTimeline::GRANULARITY_HOURS)) {
            case DataTimeline::GRANULARITY_HOURS: $viewKey = 'sap.ui.unified.CalendarIntervalType.Hour'; break;
            case DataTimeline::GRANULARITY_DAYS: $viewKey = 'sap.ui.unified.CalendarIntervalType.Day'; break;
            case DataTimeline::GRANULARITY_DAYS_PER_WEEK: $viewKey = 'sap.ui.unified.CalendarIntervalType.Week'; break;
            case DataTimeline::GRANULARITY_DAYS_PER_MONTH: $viewKey = 'sap.ui.unified.CalendarIntervalType.OneMonth'; break;
            case DataTimeline::GRANULARITY_MONTHS: $viewKey = 'sap.ui.unified.CalendarIntervalType.Month'; break;
            case DataTimeline::GRANULARITY_WEEKS: throw new FacadeUnsupportedWidgetPropertyWarning('Timeline granularity `weeks` currently not supported in UI5!'); break;
            default: $viewKey = 'sap.ui.unified.CalendarIntervalType.Hour'; break;
        }
        
        $startDateProp = $this->getWidget()->getStartDate() ? "startDate: exfTools.date.parse('{$this->getWidget()->getStartDate()}')," : '';
        $controller->addDependentObject('gantt', $this, 'null');
        $gantt = <<<JS
        new sap.ui.layout.Splitter({
            contentAreas: [
                new sap.ui.table.TreeTable({
                    rows: {
                        path:'/rows', 
                        parameters: {
                            arrayNames: [
                                '_children'
                            ]
                        }
                    },
                    selectionMode: "MultiToggle",
                    enableSelectAll: false,
                    columns: [
                        new sap.ui.table.Column({
                            width: "13rem",
                            label: new sap.ui.commons.Label({
                                text: "Categories"
                            }),
                            template: new sap.m.Text({ 
                                text:"{name}" ,
                                wrapping: false 
                            })
                        })
                    ]
                }),
                new sap.ui.core.HTML("{$this->getId()}", {
                    content: "<div id=\"{$this->getId()}\" style=\"height: 100%;\"><div id=\"{$this->getId()}_gantt\" class=\"exf-gantt\" style=\"height:100%; min-height: 100px; overflow: hidden;\"></div></div>",
                    afterRendering: function(oEvent) {
                        var oCtrl = sap.ui.getCore().byId('{$this->getId()}');
                        if (oCtrl.gantt === undefined) {
                            oCtrl.gantt = {$this->buildJsGanttInit()}
                        }
console.log('init');
                        
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
        return $this->buildJsPanelWrapper($gantt, $oControllerJs);
    }
    
    protected function buildJsGanttGetInstance() : string
    {
        return $this->getController()->buildJsDependentObjectGetter('gantt', $this);
    }
		
    protected function buildJsGanttInit() : string
    {
        return <<<JS
(function() {   
    return new Gantt("#{$this->getId()}_gantt", [
      {
        id: 'Task 1',
        name: 'Loading',
        start: '2022-12-28',
        end: '2022-12-31',
        progress: 20,
        dependencies: 'Task 2, Task 3',
        custom_class: 'bar-milestone' // optional
      }
    ], {
        header_height: 26,
        column_width: 30,
        step: 24,
        view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
        bar_height: 20,
        bar_corner_radius: 3,
        arrow_curve: 5,
        padding: 18,
        view_mode: 'Year',
        date_format: 'DD-MM-YYYY',
        language: 'en', // or 'es', 'it', 'ru', 'ptBr', 'fr', 'tr', 'zh', 'de', 'hu'
        custom_popup_html: null
    });
})();

JS;
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
        return $this->buildJsDataResetterViaTrait() . "; sap.ui.getCore().byId('{$this->getId()}').data('_exfStartDate', {$this->escapeString($this->getWidget()->getStartDate())})";
    }
    
    /**
     * 
     * @param string $oModelJs
     * @return string
     */
    protected function buildJsDataLoaderOnLoaded(string $oModelJs = 'oModel') : string
    {
        $widget = $this->getWidget();
        $calItem = $widget->getItemsConfig();
        
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
        
        if ($widget->hasResources()) {
            $rConf = $widget->getResourcesConfig();
            $rowKeyGetter = "oRow['{$rConf->getTitleColumn()->getDataColumnName()}']";
            if ($rConf->hasSubtitle()) {
                $rSubtitle = "{$rConf->getSubtitleColumn()->getDataColumnName()}: oRow['{$rConf->getSubtitleColumn()->getDataColumnName()}'],";
            }
            $rowProps = <<<JS

                        {$rConf->getTitleColumn()->getDataColumnName()}: oRow['{$rConf->getTitleColumn()->getDataColumnName()}'],
                        {$rSubtitle}

JS;
        } else {
            $rowKeyGetter = "''";
        }
        
        return $this->buildJsDataLoaderOnLoadedViaTrait($oModelJs) . <<<JS
        
            var aData = {$oModelJs}.getProperty('/rows');
            var oRows = [];
            var aTasks = [];
            aData.forEach(function(oRow) {
                var dMin, dStart, dEnd, sEnd;
                var oTask = {
                    id: oRow['{$widget->getUidColumn()->getDataColumnName()}'],
                    name: oRow['{$calItem->getTitleColumn()->getDataColumnName()}'],
                    start: oRow["{$calItem->getStartTimeColumn()->getDataColumnName()}"],
                    end: oRow["{$calItem->getEndTimeColumn()->getDataColumnName()}"],
                    progress: 0,
                    dependencies: ''
                    // custom_class: 'bar-milestone'
                };
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
            oGantt.setup_tasks(aTasks);
            oGantt.clear();
            oGantt.render();
            console.log(oGantt);
			
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
                    expression: '{$this->getWidget()->getItemsConfig()->getStartTimeColumn()->getDataColumnName()}',
                    comparator: '>=',
                    value: exfTools.date.format(oStartDate, '$dateFormat')
                });
                $oParamsJs.data.filters.conditions.push({
                    expression: '{$this->getWidget()->getItemsConfig()->getStartTimeColumn()->getDataColumnName()}',
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
    
    protected function buildJsValueBindingForWidget(WidgetInterface $tplWidget, string $modelName = null) : string
    {
        $tpl = $this->getFacade()->getElement($tplWidget);
        // Disable using widget id as control id because this is a template for multiple controls
        $tpl->setUseWidgetId(false);
        
        $modelPrefix = $modelName ? $modelName . '>' : '';
        if ($tpl instanceof UI5Display) {
            $tpl->setValueBindingPrefix($modelPrefix);
        } elseif ($tpl instanceof UI5ValueBindingInterface) {
            $tpl->setValueBindingPrefix($modelPrefix);
        }
        
        return $tpl->buildJsValueBinding();
    }
    
    /**
     * 
     * @see UI5DataElementTrait::buildJsGetRowsSelected()
     */
    protected function buildJsGetRowsSelected(string $oCalJs) : string
    {
        return <<<JS
        function(){
            var aApts = $oCalJs.getSelectedAppointments(),
                sUid,
                rows = [],
                data = sap.ui.getCore().byId('{$this->getId()}').getModel().getData().rows;
    
            for (var i in aApts) {
                var sUid = sap.ui.getCore().byId(aApts[i]).getKey();
                for (var j in data) {
                    if (data[j]['{$this->getWidget()->getMetaObject()->getUidAttributeAlias()}'] == sUid) {
                        rows.push(data[j]);
                    }
                }
            }
            return rows;
        }()

JS;
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
    protected function isEditable()
    {
        return false;
    }
    
    /**
     * {@inheritdoc}
     * @see UI5DataElementTrait::hasPaginator()
     */
    protected function hasPaginator() : bool
    {
        return false;
    }
    
    public function buildJsValueGetter($dataColumnName = null, $rowNr = null)
    {
        if ($dataColumnName !== null) {
            $dateFormat = DateTimeDataType::DATETIME_ICU_FORMAT_INTERNAL;
            if (mb_strtolower($dataColumnName) === '~start_date') {
                return "exfTools.date.format(sap.ui.getCore().byId('{$this->getId()}').getStartDate(), '$dateFormat')";
            }
            if (mb_strtolower($dataColumnName) === '~end_date') {
                return "exfTools.date.format(function(oPCal){return oPCal.getEndDate !== undefined ? oPCal.getEndDate() : oPCal._getFirstAndLastRangeDate().oEndDate.oDate}(sap.ui.getCore().byId('{$this->getId()}')), '$dateFormat')";
            }            
            if (mb_strtolower($dataColumnName) === '~resources_title') {
                $col = $this->getWidget()->getResourcesConfig()->getTitleColumn();
                $delim = $col && $col->isBoundToAttribute() ? $col->getAttribute()->getValueListDelimiter() : EXF_LIST_SEPARATOR;
                return <<<JS
                
(function(){
    var oPCal = sap.ui.getCore().byId('{$this->getId()}');
    var aSelectedRows = oPCal.getSelectedRows();
    var aTitles = [];
    for (var i = 0; i < aSelectedRows.length; i++) {
        if (aSelectedRows[i].getTitle() !== '' && aSelectedRows[i].getTitle() !== undefined) {
            aTitles.push(aSelectedRows[i].getTitle());
        }    
    }
    return aTitles.join('{$delim}');
}() || '')

JS;
            }
            
        }
        return $this->buildJsValueGetterViaTrait($dataColumnName, $rowNr);
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
        if (strpos($js, $this->buildJsValueGetter('~resources_title')) !== false) {
            $this->getController()->addOnEventScript($this, UI5Gantt::EVENT_NAME_ROW_SELECTION_CHANGE, $js);
            return $this;
        }
        return parent::addOnChangeScript($js);
    }
    
    public function registerExternalModules(UI5ControllerInterface $controller) : UI5AbstractElement
    {
        // $f = $this->getFacade();
        $controller->addExternalModule('libs.exface.gantt.Gantt', 'vendor/exface/UI5Facade/Facades/js/frappe-gantt/dist/frappe-gantt.min.js', null, 'Gantt');
        $controller->addExternalCss('vendor/exface/UI5Facade/Facades/js/frappe-gantt/dist/frappe-gantt.min.css');
        return $this;
    }
}