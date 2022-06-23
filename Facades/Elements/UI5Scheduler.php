<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\UI5Facade\Facades\Elements\Traits\UI5DataElementTrait;
use exface\Core\Interfaces\WidgetInterface;
use exface\UI5Facade\Facades\Interfaces\UI5ValueBindingInterface;
use exface\Core\Widgets\Parts\DataTimeline;
use exface\Core\DataTypes\DateTimeDataType;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JsValueScaleTrait;
use exface\Core\Widgets\Parts\DataCalendarItem;

/**
 * 
 * @method \exface\Core\Widgets\Scheduler getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5Scheduler extends UI5AbstractElement
{
    use UI5DataElementTrait {
        buildJsDataLoaderOnLoaded as buildJsDataLoaderOnLoadedViaTrait;
        buildJsValueGetter as buildJsValueGetterViaTrait;
        buildJsDataResetter as buildJsDataResetterViaTrait;
    }
    
    use JsValueScaleTrait;

    const EVENT_NAME_TIMELINE_SHIFT = 'timeline_shift';
    
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
        switch ($this->getWidget()->getTimelineConfig()->getGranularity(DataTimeline::GRANULARITY_HOUR)) {
            case DataTimeline::GRANULARITY_DAY: $viewKey = 'sap.ui.unified.CalendarIntervalType.Day'; break;
            case DataTimeline::GRANULARITY_HOUR: $viewKey = 'sap.ui.unified.CalendarIntervalType.Hour'; break;
            case DataTimeline::GRANULARITY_WEEK: $viewKey = 'sap.ui.unified.CalendarIntervalType.Week'; break;
            case DataTimeline::GRANULARITY_MONTH: $viewKey = 'sap.ui.unified.CalendarIntervalType.OneMonth'; break;
            default: $viewKey = 'sap.ui.unified.CalendarIntervalType.Hour'; break;
        }
        
        if ($this->getWidget()->isPaged()) {
            $refreshOnNavigation = <<<JS

    startDateChange: {$controller->buildJsMethodCallFromView('onLoadData', $this)},
    viewChange: {$controller->buildJsMethodCallFromView('onLoadData', $this)},
JS;
        }
        
        $startDateProp = $this->getWidget()->getStartDate() ? "startDate: exfTools.date.parse('{$this->getWidget()->getStartDate()}')," : '';
        
        return <<<JS

new sap.m.PlanningCalendar("{$this->getId()}", {
    {$startDateProp}
	appointmentsVisualization: "Filled",
    viewKey: $viewKey,
	showRowHeaders: {$showRowHeaders},
    showEmptyIntervalHeaders: false,
	showWeekNumbers: true,
    {$refreshOnNavigation}
    appointmentSelect: {$controller->buildJsEventHandler($this, self::EVENT_NAME_CHANGE, true)},
	toolbarContent: [
		{$this->buildJsToolbarContent($oControllerJs)}
	],
	rows: {
		path: '/_scheduler/rows',
        template: {$this->buildJsRowsConstructors()}
	}
})
.data('_exfStartDate', {$this->escapeString($this->getWidget()->getStartDate())})
{$this->buildJsClickHandlers($oControllerJs)}

JS;
    }
		
    protected function buildJsRowsConstructors() : string
    {
        $widget = $this->getWidget();
        
        $calItem = $widget->getItemsConfig();
        $subtitleBinding = $calItem->hasSubtitle() ? $this->buildJsValueBindingForWidget($calItem->getSubtitleColumn()->getCellWidget()) : '""';
        
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
					text: {$subtitleBinding},
					key: "{{$this->getMetaObject()->getUidAttributeAlias()}}",
					type: "{type}",
                    {$this->buildJsRowPropertyColor($calItem)}
				})
            },
			intervalHeaders: {
                path: 'headers', 
                templateShareable: true,
                template: new sap.ui.unified.CalendarAppointment({
					startDate: "{start}",
					endDate: "{end}",
					icon: "{pic}",
					title: {$this->buildJsValueBindingForWidget($calItem->getTitleColumn()->getCellWidget())},
					text: {$subtitleBinding},
					type: "{type}",
				})
            },
		})

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
        
        $endTime = $calItem->hasEndTime() ? "oDataRow['{$calItem->getEndTimeColumn()->getDataColumnName()}']" : "''";
        $subtitle = $calItem->hasSubtitle() ? "{$calItem->getSubtitleColumn()->getDataColumnName()}: oDataRow['{$calItem->getSubtitleColumn()->getDataColumnName()}']," : '';
        
        if ($widget->hasUidColumn()) {
            $uid = "{$widget->getUidColumn()->getDataColumnName()}: oDataRow['{$widget->getUidColumn()->getDataColumnName()}'],";
        }
        
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
                oRows[sRowKey].items.push({
                    _start: dStart,
                    _end: dEnd,
                    {$calItem->getTitleColumn()->getDataColumnName()}: oDataRow["{$calItem->getTitleColumn()->getDataColumnName()}"],
                    {$uid}
                    {$subtitle}
                });
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
     * @see UI5DataElementTrait::buildJsGetSelectedRows()
     */
    protected function buildJsGetSelectedRows(string $oCalJs) : string
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
        return parent::addOnChangeScript($js);
    }
}