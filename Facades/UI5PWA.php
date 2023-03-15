<?php
namespace exface\UI5Facade\Facades;

use exface\Core\Interfaces\WidgetInterface;
use exface\Core\Interfaces\Widgets\iTriggerAction;
use exface\Core\Interfaces\Actions\iShowWidget;
use exface\Core\CommonLogic\PWA\AbstractPWA;
use exface\Core\CommonLogic\PWA\PWARoute;
use exface\UI5Facade\Webapp;
use exface\UI5Facade\Facades\Interfaces\UI5ViewInterface;
use exface\Core\Interfaces\Actions\iReadData;
use exface\Core\DataTypes\OfflineStrategyDataType;
use exface\Core\Actions\ShowHelpDialog;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Interfaces\Widgets\iSupportLazyLoading;
use exface\Core\Actions\ReadPrefill;
use exface\Core\Widgets\Dialog;
use exface\Core\Widgets\Data;
use exface\Core\Actions\RefreshWidget;
use exface\Core\Actions\CallWidgetFunction;
use exface\Core\Interfaces\Widgets\iUseInputWidget;
use exface\Core\Interfaces\Actions\iExportData;
use exface\Core\Interfaces\Actions\iNavigate;
use exface\Core\Factories\ActionFactory;
use exface\Core\Actions\ShowWidget;
use exface\Core\Interfaces\UserImpersonationInterface;
use exface\Core\Interfaces\DataSheets\DataSheetInterface;
use exface\Core\Factories\DataSheetFactory;
use exface\Core\DataTypes\ComparatorDataType;

/**
 * 
 * @author andrej.kabachnik
 * 
 * @method UI5Facade getFacade()
 *
 */
class UI5PWA extends AbstractPWA
{
    private $widgetsProcessed = [];
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\PWA\AbstractPWA::generateModelForWidget()
     */
    protected function generateModelForWidget(WidgetInterface $widget, int $linkDepth = 100, string $logIndent = '  ') : \Generator
    {
        if (in_array($widget, $this->widgetsProcessed, true) === true) {
            return;
        } else {
            $this->widgetsProcessed[] = $widget;
        }
        
        if ($widget->hasParent() === false) {
            if ($widget->getPage()->isPublished() === false && $this->isAvailableOfflineUnpublished() === false) {
                return;
            }
            $route = new PWARoute(
                $this, 
                $this->getViewForWidget($widget)->getRouteName(), 
                $widget, 
                ActionFactory::createFromString($this->getWorkbench(), ShowWidget::class, $widget)
            );
            $this->addRoute($route);
            yield "Page {$widget->getPage()->getName()} ({$widget->getPage()->getAliasWithNamespace()}):" . PHP_EOL;
        }
        
        if ($linkDepth <= 0) {
            return;
        }
        
        foreach ($widget->getChildren() as $child) {
            switch (true) {
                case $child instanceof iSupportLazyLoading:
                    if ($this->isWidgetLazyLoading($child) && $child->hasAction()) {
                        $action = $child->getAction();
                        $child->getLazyLoadingAction();
                        $this->addAction($action, $child);
                        if ($action->getOfflineStrategy() === null || $action->getOfflineStrategy() === OfflineStrategyDataType::PRESYNC) {
                            $data = $action instanceof ReadPrefill ? $child->prepareDataSheetToPrefill() : $child->prepareDataSheetToRead();
                            $dataSet = $this->addData($data, $action, $child);
                            yield $logIndent . 'Data for ' . $this->getDescriptionOf($dataSet) . PHP_EOL;
                        }
                    }
                    yield from $this->generateModelForWidget($child, ($linkDepth-1));
                    break;
                case $child instanceof iTriggerAction:
                    if (! $child->hasAction()) {
                        break;
                    }
                    $action = $child->getAction();
                    $this->addAction($action, $child);
                    switch (true) {
                        case $action instanceof iReadData:
                        case $child instanceof iSupportLazyLoading && $child->getLazyLoadingAction() === $action:
                            $inputWidget = $child instanceof iUseInputWidget ? $child->getInputWidget() : $child;
                            if ($this->getActionOfflineStrategy($action) === OfflineStrategyDataType::PRESYNC) {
                                $data = $action instanceof ReadPrefill ?  $inputWidget->prepareDataSheetToPrefill() : $inputWidget->prepareDataSheetToRead();
                                $dataSet = $this->addData($data, $action, $child);
                                yield $logIndent . 'Data for ' . $this->getDescriptionOf($dataSet) . PHP_EOL;
                            }
                            yield from $this->generateModelForWidget($child, ($linkDepth-1));
                            break;
                        case $action instanceof iShowWidget:
                            if (null !== $childActionWidget = $child->getAction()->getWidget()) {
                                $route = new PWARoute(
                                    $this, 
                                    $this->getViewForWidget($childActionWidget)->getRouteName(), 
                                    $childActionWidget, 
                                    $child->getAction()
                                );
                                $this->addRoute($route);
                                yield $logIndent . 'Route for ' . $this->getDescriptionOf($route) . PHP_EOL;
                                if ($this->getActionOfflineStrategy($action) !== OfflineStrategyDataType::ONLINE_ONLY) {
                                    yield from $this->generateModelForWidget($childActionWidget, ($linkDepth-1), $logIndent . '  ');
                                }
                            } elseif (null !== $childActionPage = $child->getAction()->getPage()) {
                                yield from $this->generateModelForWidget($childActionPage->getWidgetRoot(), ($linkDepth-1), $logIndent . '  ');
                            }
                            break;
                    }
                    break;
                default: 
                    yield from $this->generateModelForWidget($child, ($linkDepth-1));
            }
        }
    }
    
    protected function isWidgetLazyLoading(WidgetInterface $widget) : bool
    {
        switch (true) {
            case $widget instanceof Dialog:
            case $widget instanceof Data:
                $default = true;
                break;
            default:
                $default = false;
        }
        return $widget->getLazyLoading($default);
    }
    
    protected function findOfflineStrategy(ActionInterface $action, WidgetInterface $triggerWidget) : string
    {
        $strategy = $action->getOfflineStrategy();
        switch (true) {
            case $strategy !== null: 
                return $strategy;
            case $action instanceof RefreshWidget:
            case $action instanceof CallWidgetFunction:
                return OfflineStrategyDataType::CLIENT_SIDE;
            case $action instanceof iExportData:
                return OfflineStrategyDataType::ONLINE_ONLY;
            case $this->isAvailableOffline() === true && $action instanceof iReadData: 
                return OfflineStrategyDataType::PRESYNC;
            case $this->isAvailableOfflineHelp() === false && $action instanceof ShowHelpDialog:
                return OfflineStrategyDataType::ONLINE_ONLY;
            case $this->isAvailableOfflineUnpublished() === false && $triggerWidget && $triggerWidget->getPage()->isPublished() === false: 
                return OfflineStrategyDataType::ONLINE_ONLY;
            // TODO what offline strategy do non-lazy dialogs get?
            case $action instanceof iShowWidget: 
                return OfflineStrategyDataType::PRESYNC;
            case $action instanceof iNavigate:
                return OfflineStrategyDataType::CLIENT_SIDE;
        }
        return OfflineStrategyDataType::ENQUEUE;
    }
    
    protected function getWebapp() : Webapp
    {
        return $this->getFacade()->getWebapp();
    }
    
    protected function getViewForWidget(WidgetInterface $widget) : UI5ViewInterface
    {
        return $this->getWebapp()->getViewForWidget($widget);
    }
    
    public function buildJsBaseControllerOnInit() : string
    {
        $url = ltrim($this->getWebapp()->getComponentUrl(), '/');
        return <<<JS
            
(function(oController){
    var oBtnOffline;
    if (! exfPreloader.ui5Preloaded) {
        oBtnOffline = sap.ui.getCore().byId('exf-network-indicator');
        oBtnOffline.setBusyIndicatorDelay(0).setBusy(true);
        exfPreloader.ui5Preloaded = true;
        $.ajax({
    		url: '{$url}Offline-preload.js',
    		dataType: "script",
    		cache: true,
    		success: function(script, textStatus) {
                oBtnOffline.setBusy(false);
    			console.log('offline stuff loaded');
    		},
    		error: function(jqXHR, textStatus, errorThrown) {
                oBtnOffline.setBusy(false);
    			console.warn("Failed loading offline data from $url");    			
    			oController.getOwnerComponent().showAjaxErrorDialog(jqXHR);
    		}
    	})
    }
})(this);

JS;
    }
}