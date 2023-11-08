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
use exface\UI5Facade\Facades\Interfaces\UI5DataElementInterface;

/**
 * 
 * @method \exface\Core\Widgets\Scheduler getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5Scheduler extends UI5AbstractElement implements UI5DataElementInterface
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
        
        $cssClasses = '';
        $customViewsJs = <<<JS
        
        new sap.m.PlanningCalendarView({
            description: 'Years',
            key: 'Years',
            intervalsL: 24,
            intervalsM: 16,
            intervalsS: 12,
            intervalType: 'Month',
            showSubIntervals: false
        }),
JS;
        
        $showRowHeaders = $this->getWidget()->hasResources() ? 'true' : 'false';
        switch ($this->getWidget()->getTimelineConfig()->getGranularity(DataTimeline::GRANULARITY_HOURS)) {
            case DataTimeline::GRANULARITY_HOURS: $viewKey = 'sap.ui.unified.CalendarIntervalType.Hour'; break;
            case DataTimeline::GRANULARITY_DAYS: $viewKey = 'sap.ui.unified.CalendarIntervalType.Day'; break;
            case DataTimeline::GRANULARITY_DAYS_PER_WEEK: $viewKey = 'sap.ui.unified.CalendarIntervalType.Week'; break;
            case DataTimeline::GRANULARITY_DAYS_PER_MONTH: $viewKey = 'sap.ui.unified.CalendarIntervalType.OneMonth'; break;
            case DataTimeline::GRANULARITY_MONTHS: $viewKey = 'sap.ui.unified.CalendarIntervalType.Month'; break;
            case DataTimeline::GRANULARITY_WEEKS: throw new FacadeUnsupportedWidgetPropertyWarning('Timeline granularity `weeks` currently not supported in UI5!'); break;
            case DataTimeline::GRANULARITY_YEARS: $viewKey = "'Years'"; break;
            default: $viewKey = 'sap.ui.unified.CalendarIntervalType.Hour'; break;
        }
        
        if ($this->getWidget()->isPaged()) {
            $refreshOnNavigation = <<<JS

    startDateChange: {$controller->buildJsMethodCallFromView('onLoadData', $this)},
    viewChange: {$controller->buildJsMethodCallFromView('onLoadData', $this)},
JS;
        } else {
            $refreshOnNavigation = <<<JS
            
    startDateChange: {$controller->buildJsEventHandler($this, self::EVENT_NAME_TIMELINE_SHIFT, true)},
    viewChange: {$controller->buildJsEventHandler($this, self::EVENT_NAME_TIMELINE_SHIFT, true)},
JS;
        }
        
        if (! $this->getWidget()->getItemsConfig()->hasSubtitle()) {
            $aptHeight = 'appointmentHeight: sap.ui.unified.CalendarAppointmentHeight.HalfSize,';
            // Reduce the height of the planning calendar rows too with a CSS hack
            // @see https://answers.sap.com/questions/13327688/row-height-of-the-planning-calendar.html
            $cssClasses .= 'halfHeight';
        } else {
            $aptHeight = '';
        }
        
        $startDateProp = $this->getWidget()->getStartDate() ? "startDate: exfTools.date.parse('{$this->getWidget()->getStartDate()}')," : '';
        
        return <<<JS

new sap.m.PlanningCalendar("{$this->getId()}", {
    {$startDateProp}
    {$aptHeight}
	appointmentsVisualization: sap.ui.unified.CalendarAppointmentVisualization.Filled,
    groupAppointmentsMode: sap.ui.unified.GroupAppointmentsMode.Expanded,
    builtInViews: [
        sap.ui.unified.CalendarIntervalType.Hour,
        sap.ui.unified.CalendarIntervalType.Day,
        sap.ui.unified.CalendarIntervalType.Week,
        sap.ui.unified.CalendarIntervalType.OneMonth,
        sap.ui.unified.CalendarIntervalType.Month
    ],
    views: [
        $customViewsJs
    ],
    viewKey: $viewKey,
	showRowHeaders: {$showRowHeaders},
    showEmptyIntervalHeaders: false,
    showIntervalHeaders: true,
	showWeekNumbers: true,
    {$refreshOnNavigation}
    appointmentSelect: {$controller->buildJsEventHandler($this, self::EVENT_NAME_CHANGE, true)},
    rowSelectionChange: {$controller->buildJsEventHandler($this, self::EVENT_NAME_ROW_SELECTION_CHANGE, true)},
	toolbarContent: [
		{$this->buildJsToolbarContent($oControllerJs)}
	],
	rows: {
		path: '/_scheduler/rows',
        template: {$this->buildJsRowsConstructors()}
	}
})
.data('_exfStartDate', {$this->escapeString($this->getWidget()->getStartDate())})
.addStyleClass('$cssClasses')
{$this->buildJsClickHandlers($oControllerJs)}

JS;
    }
		
    protected function buildJsRowsConstructors() : string
    {
        $widget = $this->getWidget();
        
        $calItem = $widget->getItemsConfig();
        if ($calItem->hasSubtitle()) {
            $subtitleOptions = "text: {$this->buildJsValueBindingForWidget($calItem->getSubtitleColumn()->getCellWidget())},";
        } else {
            $subtitleOptions = '';
        }
        
        $rowProps = '';
        if ($widget->hasResources() === true) {
            $resource = $widget->getResourcesConfig();
            $rowProps .= 'title: ' . $this->buildJsValueBindingForWidget($resource->getTitleColumn()->getCellWidget()) . ',';
            if ($resource->hasSubtitle()) {
                $rowProps .= 'text: ' . $this->buildJsValueBindingForWidget($resource->getSubtitleColumn()->getCellWidget()) . ',';
            }
        }
        
        return <<<JS

        new sap.m.PlanningCalendarRow({
			{$rowProps}
			appointments: {
                path: 'items', 
                templateShareable: true,
                template: new sap.ui.unified.CalendarAppointment({
					startDate: "{_start}",
					endDate: "{_end}",
					icon: "{pic}",
					title: {$this->buildJsValueBindingForWidget($calItem->getTitleColumn()->getCellWidget())},
					tooltip: {$this->buildJsValueBindingForWidget($calItem->getTitleColumn()->getCellWidget())},
					{$subtitleOptions}
					key: "{{$this->getMetaObject()->getUidAttributeAlias()}}",
                    {$this->buildJsAppointmentPropertyColor($calItem)}
				})
            },/*
			intervalHeaders: {
                path: 'headers', 
                templateShareable: true,
                template: new sap.ui.unified.CalendarAppointment({
					startDate: "{start}",
					endDate: "{end}",
					icon: "{pic}",
					title: "{title}"
				})
            },*/
		})

JS;
    }
    
    protected function buildJsAppointmentPropertyColor(DataCalendarItem $calItem) : string
    {
        switch (true) {
            case $colorCol = $calItem->getColorColumn();
                $colorResolverJs = ! $calItem->hasColorScale() ? '(value || "").toString()' : $this->buildJsScaleResolver('value', $calItem->getColorScale(), $calItem->isColorScaleRangeBased());
                $semanticColors = $this->getFacade()->getSemanticColors();
                $semanticColorsJs = json_encode(empty($semanticColors) ? new \stdClass() : $semanticColors);
                return <<<JS
                    color: {
                        path: "{$colorCol->getDataColumnName()}",
                        formatter: function(value){
                            var sColor = {$colorResolverJs};
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
        
        $endTime = $calItem->hasEndTime() ? "oDataRow['{$calItem->getEndTimeColumn()->getDataColumnName()}']" : "''";
        
        if ($workdayStart = $widget->getTimelineConfig()->getWorkdayStartTime()){
            $workdayStartSplit = explode(':', $workdayStart);
            $workdayStartSplit = array_map('intval', $workdayStartSplit);
            $workdayStartJs = 'dMin.setHours(' . implode(', ', $workdayStartSplit) . ');';
        }
        
        if ($widget->hasResources()) {
            $rConf = $widget->getResourcesConfig();
            $rowKeyGetter = "oDataRow['{$rConf->getTitleColumn()->getDataColumnName()}']";
            if ($rConf->hasSubtitle()) {
                $rSubtitle = "{$rConf->getSubtitleColumn()->getDataColumnName()}: oDataRow['{$rConf->getSubtitleColumn()->getDataColumnName()}'],";
            }
            $rowProps = <<<JS

                        {$rConf->getTitleColumn()->getDataColumnName()}: oDataRow['{$rConf->getTitleColumn()->getDataColumnName()}'],
                        {$rSubtitle}

JS;
        } else {
            $rowKeyGetter = "''";
        }
        
        return $this->buildJsDataLoaderOnLoadedViaTrait($oModelJs) . <<<JS
        
            var aData = {$oModelJs}.getProperty('/rows');
            var oRows = [];
            var dMin, dStart, dEnd, sEnd, oDataRow, sRowKey;
            for (var i in aData) {
                oDataRow = aData[i];

                sRowKey = {$rowKeyGetter};
                if (oRows[sRowKey] === undefined) {
                    oRows[sRowKey] = {
                        {$rowProps}
                        items: [],
                        headers: []
                    };
                }

                dStart = new Date(oDataRow["{$calItem->getStartTimeColumn()->getDataColumnName()}"]);
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
                oRows[sRowKey].items.push(
                    $.extend({
                            _start: dStart,
                            _end: dEnd,
                            _dataRowIdx: i
                        }, 
                        oDataRow
                    )
                );
            }

            if (dMin !== undefined && ! sap.ui.getCore().byId('{$this->getId()}').data('_exfStartDate') && {$oModelJs}.getProperty('/_scheduler') === undefined) {
                sap.ui.getCore().byId('{$this->getId()}').data('_exfStartDate', dMin).setStartDate(dMin);
            }
            {$oModelJs}.setProperty('/_scheduler', {
                rows: Object.values(oRows),
            });

            setTimeout(function(){
                {$this->getController()->buildJsEventHandler($this, self::EVENT_NAME_TIMELINE_SHIFT, false)}
            }, 0);
            // fire selection change as selected rows are reseted
            setTimeout(function(){                
                sap.ui.getCore().byId('{$this->getId()}').fireRowSelectionChange();
            }, 0);
			
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
            $this->getController()->addOnEventScript($this, self::EVENT_NAME_TIMELINE_SHIFT, $js);
            return $this;
        }
        if (strpos($js, $this->buildJsValueGetter('~resources_title')) !== false) {
            $this->getController()->addOnEventScript($this, SELF::EVENT_NAME_ROW_SELECTION_CHANGE, $js);
            return $this;
        }
        return parent::addOnChangeScript($js);
    }
    
    /**
     * @see UI5DataElementTrait::buildJsSelectRowByIndex()
     * @param string $oTableJs
     * @param string $iRowIdxJs
     * @param bool $deSelect
     * @param string $bScrollToJs
     * @return string
     */
    public function buildJsSelectRowByIndex(string $oTableJs = 'oTable', string $iRowIdxJs = 'iRowIdx', bool $deSelect = false, string $bScrollToJs = 'true') : string
    {
        if ($deSelect) {
            $js = "if (oItem.getBindingContext().getProperty('_dataRowIdx') == iRowIdx) { oItem.setSelected(false); }";
        } else {
            $js = "oItem.setSelected((oItem.getBindingContext().getProperty('_dataRowIdx') == iRowIdx ? true : false));";
        }
        
        return <<<JS
        (function(oTable, iRowIdx, bScrollTo){
            oTable.getRows().forEach(function(oCalRow){
                oCalRow.getAppointments().forEach(function(oItem){
                    $js
                });
            });
        })($oTableJs, $iRowIdxJs, $bScrollToJs)

JS;
    }
    
    /**
     * @see UI5DataElementTrait::buildJsClickGetRowIndex()
     * @param string $oDomElementClickedJs
     * @return string
     */
    protected function buildJsClickGetRowIndex(string $oDomElementClickedJs) : string
    {
        return <<<JS
        (function(domClicked){
            var sCalDomId = $(domClicked).parents('div.sapUiCalendarApp').data('sap-ui');
            var oCalItem = sap.ui.getCore().byId(sCalDomId);
            if (oCalItem === undefined) return -1;
            return oCalItem.getBindingContext().getProperty('_dataRowIdx');
        })($oDomElementClickedJs)
JS;
        return "sap.ui.getCore().byId($({$oDomElementClickedJs}).parents('div.sapUiCalendarApp').data('data-sap-ui')).getKey()";
    }
}