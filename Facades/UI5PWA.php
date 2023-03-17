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
        
        switch (true) {
            case $widget instanceof iSupportLazyLoading:
                if ($this->isWidgetLazyLoading($widget) && $widget->hasAction()) {
                    $action = $widget->getAction();
                    $widget->getLazyLoadingAction();
                    $this->addAction($action, $widget);
                    if ($action->getOfflineStrategy() === null || $action->getOfflineStrategy() === OfflineStrategyDataType::PRESYNC) {
                        $data = $action instanceof ReadPrefill ? $widget->prepareDataSheetToPrefill() : $widget->prepareDataSheetToRead();
                        $dataSet = $this->addData($data, $action, $widget);
                        yield $logIndent . 'Data for ' . $this->getDescriptionOf($dataSet) . PHP_EOL;
                    }
                }
                yield from $this->generateModelForWidget($widget, ($linkDepth-1));
                break;
            case $widget instanceof iTriggerAction:
                if (! $widget->hasAction()) {
                    break;
                }
                $action = $widget->getAction();
                $this->addAction($action, $widget);
                switch (true) {
                    case $action instanceof iReadData:
                    case $widget instanceof iSupportLazyLoading && $widget->getLazyLoadingAction() === $action:
                        $inputWidget = $widget instanceof iUseInputWidget ? $widget->getInputWidget() : $widget;
                        if ($this->getActionOfflineStrategy($action) === OfflineStrategyDataType::PRESYNC) {
                            $data = $action instanceof ReadPrefill ?  $inputWidget->prepareDataSheetToPrefill() : $inputWidget->prepareDataSheetToRead();
                            $dataSet = $this->addData($data, $action, $widget);
                            yield $logIndent . 'Data for ' . $this->getDescriptionOf($dataSet) . PHP_EOL;
                        }
                        break;
                    case $action instanceof iShowWidget:
                        if (null !== $widgetActionWidget = $widget->getAction()->getWidget()) {
                            $route = new PWARoute(
                                $this,
                                $this->getViewForWidget($widgetActionWidget)->getRouteName(),
                                $widgetActionWidget,
                                $widget->getAction()
                                );
                            $this->addRoute($route);
                            yield $logIndent . 'Route for ' . $this->getDescriptionOf($route) . PHP_EOL;
                            if ($this->getActionOfflineStrategy($action) !== OfflineStrategyDataType::ONLINE_ONLY) {
                                yield from $this->generateModelForWidget($widgetActionWidget, ($linkDepth-1), $logIndent . '  ');
                            }
                        } elseif (null !== $widgetActionPage = $widget->getAction()->getPage()) {
                            yield from $this->generateModelForWidget($widgetActionPage->getWidgetRoot(), ($linkDepth-1), $logIndent . '  ');
                        }
                        break;
                }
                break;
        }
        
        foreach ($widget->getChildren() as $child) {
            yield from $this->generateModelForWidget($child, ($linkDepth-1));
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
}