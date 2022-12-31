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
        if ($widget->hasParent() === false) {
            $this->addRoute(new PWARoute($this, $this->getViewForWidget($widget)->getRouteName(), $widget));
            yield 'Root widget for page ' . $widget->getPage()->getAliasWithNamespace() . ': ' . $widget->getId();
        }
        
        if ($linkDepth > 0) {
            foreach ($widget->getChildren() as $child) {
                if ($child instanceof iTriggerAction && $child->hasAction() && $child->getAction() instanceof iShowWidget) {
                    if (null !== $childActionWidget = $child->getAction()->getWidget()) {
                        $this->addRoute(new PWARoute($this, $this->getViewForWidget($childActionWidget)->getRouteName(), $childActionWidget));
                        yield 'Action widget for page ' . $widget->getPage()->getAliasWithNamespace() . ': ' . $widget->getId();
                        yield from $this->generateModelForWidget($childActionWidget, ($linkDepth-1));
                    } elseif (null !== $childActionPage = $child->getAction()->getPage()) {
                        yield from $this->generateModelForWidget($childActionPage->getWidgetRoot(), ($linkDepth-1));
                    }
                } else {
                    yield from $this->generateModelForWidget($child, ($linkDepth-1));
                }
            }
        }
    }
    
    protected function getRouteOfflineStrategy(PWARouteInterface $route) : string
    {
        $action = $route->getAction();
        $triggerWidget = $route->getTriggerWidget();
        $inputWidget = $route->getTriggerInputWidget();
        if ($action !== null) {
            $strategy = $action->getOfflineStrategy();
            switch (true) {
                case $strategy !== null: return $strategy;
                case $action instanceof ShowHelpDialog: return OfflineStrategyDataType::ONLINE_ONLY;
                case $inputWidget !== null && $inputWidget->isHidden(): return OfflineStrategyDataType::USE_CACHE;
                // TODO what offline strategy do non-lazy dialogs get?
                case $action instanceof iShowWidget: return OfflineStrategyDataType::PRESYNC;
            }
        }
        return OfflineStrategyDataType::PRESYNC;
    }
    
    protected function getWebapp() : Webapp
    {
        return $this->getFacade()->getWebapp();
    }
    
    protected function getViewForWidget(WidgetInterface $widget) : UI5ViewInterface
    {
        // IMPORTANT: generate the view first to allow it to add controller methods!
        $view = $this->getWebapp()->getViewForWidget($widget);
        $controller = $view->getController();
        return $view;
    }
}