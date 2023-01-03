<?php
namespace exface\UI5Facade\Facades;

use exface\Core\Interfaces\PWA\PWAInterface;
use exface\Core\Interfaces\WidgetInterface;
use exface\Core\Interfaces\Widgets\iTriggerAction;
use exface\Core\Interfaces\Actions\iShowWidget;
use exface\Core\CommonLogic\PWA\AbstractPWA;
use exface\Core\CommonLogic\PWA\PWARoute;
use exface\Core\Interfaces\PWA\PWARouteInterface;
use exface\Core\Interfaces\Selectors\PWASelectorInterface;
use exface\UI5Facade\Webapp;
use exface\UI5Facade\Facades\Interfaces\UI5ViewInterface;
use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Interfaces\Actions\iReadData;
use exface\Core\DataTypes\OfflineStrategyDataType;
use exface\Core\Actions\ShowHelpDialog;
use exface\Core\Actions\ShowWidget;
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

/**
 * 
 * @author andrej.kabachnik
 * 
 * @method UI5Facade getFacade()
 *
 */
class UI5PWA extends AbstractPWA
{
    public function __construct(PWASelectorInterface $selector, FacadeInterface $facade)
    {
        parent::__construct($selector, $facade);
        $this->getFacade()->initWebapp($this->getMenuRoots()[0]->getAliasWithNamespace());
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\CommonLogic\PWA\AbstractPWA::generateModelForWidget()
     */
    protected function generateModelForWidget(WidgetInterface $widget, int $linkDepth = 100) : \Generator
    {
        if ($widget->getId() === 'lagerTable' || $widget->getId() === 'scanWizard') {
            $b =1;
        }
        if ($widget->hasParent() === false) {
            if ($widget->getPage()->isPublished() === false && $this->isAvailableOfflineUnpublished() === false) {
                return;
            }
            $this->addRoute(new PWARoute($this, $this->getViewForWidget($widget)->getRouteName(), $widget));
            yield 'Root widget for page ' . $widget->getPage()->getAliasWithNamespace() . ': ' . $widget->getId();
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
                            $this->addData($data, $action, $child);
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
                                $this->addData($data, $action, $child);
                            }
                            yield from $this->generateModelForWidget($child, ($linkDepth-1));
                            break;
                        case $action instanceof iShowWidget:
                            if (null !== $childActionWidget = $child->getAction()->getWidget()) {
                                $this->addRoute(new PWARoute($this, $this->getViewForWidget($childActionWidget)->getRouteName(), $childActionWidget));
                                yield 'Action widget for page ' . $widget->getPage()->getAliasWithNamespace() . ': ' . $widget->getId();
                                yield from $this->generateModelForWidget($childActionWidget, ($linkDepth-1));
                            } elseif (null !== $childActionPage = $child->getAction()->getPage()) {
                                yield from $this->generateModelForWidget($childActionPage->getWidgetRoot(), ($linkDepth-1));
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
    
    protected function getActionOfflineStrategy(ActionInterface $action) : string
    {
        $triggerWidget = $this->getActionWidget($action);
        $strategy = $action->getOfflineStrategy();
        switch (true) {
            case $strategy !== null: 
                return $strategy;
            case $action instanceof RefreshWidget:
            case $action instanceof CallWidgetFunction:
                return OfflineStrategyDataType::CLIENT_SIDE;
            case $action instanceof iExportData:
                return OfflineStrategyDataType::ENQUEUE;
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