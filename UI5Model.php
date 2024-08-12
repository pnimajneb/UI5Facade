<?php
namespace exface\UI5Facade;

use exface\Core\Interfaces\WidgetInterface;
use exface\Core\Interfaces\DataSheets\DataPointerInterface;
use exface\UI5Facade\Facades\Interfaces\UI5ModelInterface;
use exface\Core\Interfaces\Widgets\iShowSingleAttribute;
use exface\Core\Interfaces\Widgets\iShowDataColumn;
use exface\Core\Interfaces\Widgets\PrefillModelInterface;
use exface\Core\Widgets\Parts\PrefillModel;
use exface\Core\CommonLogic\DataSheets\DataColumn;

class UI5Model extends PrefillModel implements UI5ModelInterface
{    
    private $name = null;
    
    private $viewName = null;
    
    /**
     * 
     * @param WidgetInterface $widget
     * @param string $viewName
     * @param string $modelName
     */
    public function __construct(WidgetInterface $widget, string $viewName, string $modelName = '')
    {
        parent::__construct($widget);
        $this->viewName = $viewName;
        $this->name = $modelName;
    }  
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ModelInterface::getName()
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ModelInterface::getViewName()
     */
    public function getViewName() : string
    {
        return $this->viewName;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ModelInterface::getBindingPath()
     */
    public function getBindingPath(WidgetInterface $widget, string $bindingName) : string
    {
        $boundColumn = $this->getBindingDataColumn($widget, $bindingName);
        return $boundColumn === null ? '' : '/' . $boundColumn->getName();
    }
    
    protected function getBindingDataColumn(WidgetInterface $widget, string $bindingName) : ?DataColumn
    {
        $binding = $this->getBinding($widget, $bindingName);
        
        if ($binding === null) {
            return null;
        }
        
        if ($binding instanceof DataPointerInterface) {
            return $binding->getColumn();
        }
        
        return null;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ModelInterface::hasBindingConflict()
     */
    public function hasBindingConflict(WidgetInterface $widget, string $bindingName) : bool
    {
        // If there is a binding for the given widget and property, don't bother
        if ($this->hasBinding($widget, $bindingName)) {
            return false;
        }
        
        // If the given widget is not showing a single attribute,
        // it's really strange (what to bind???) - so treat that as a potential conflict :)
        if (! ($widget instanceof iShowSingleAttribute)) {
            return true;
        }
        // Same goes for the case, that the widget is not showing a data column
        if (! ($widget instanceof iShowDataColumn)) {
            return true;
        }
        
        // See if the widget has a data column at all. If not, it will not produce a conflict!
        $widgetColumnName = $widget->getDataColumnName();
        if ($widgetColumnName === null) {
            return false;
        }
        
        // Iterate through existing bindings looking for those with the same 
        // binding name and path, but a different meaning
        foreach ($this->getBoundWidgetIds() as $widgetId) {
            // If it's the same widget id, there cannot be any conflicts.
            if ($widgetId === $widget->getId()) {
                continue;
            }
            
            // If the binding is empty, there is no conflict either
            $bindingPointerWithSameName = $this->getBindingsForWidgetId($widgetId)[$bindingName] ?? null;
            if ($bindingPointerWithSameName === null) {
                continue;
            }
            
            $bindingWidget = $widget->getPage()->getWidget($widgetId);
            
            // If the other binding's widget is not showing a single attribute,
            // it's really strange - so treat that as a potential conflict :)
            if (! ($bindingWidget instanceof iShowSingleAttribute)) {
                return true;
            }
            // Same goes for the case, that the other widget is not showing a single data column
            if (! ($bindingWidget instanceof iShowDataColumn)) {
                return true;
            }
            
            // Now check if the resulting binding path is the same and if the
            // meaning (e.g. attribute) might be different
            $boundColumn = $bindingPointerWithSameName->getColumn();
            $bindingColumnName = $boundColumn === null ? null : $boundColumn->getName();
            
            // Conflicts may arise if the data column name is the same for different widgets
            // showing semantically different things
            if ($bindingColumnName === $widgetColumnName) {
                // If both can be bound to an attribute, but at least one is not, AND both have
                // the same data column name - it's OK, the same value is meant.
                if ((! $bindingWidget->isBoundToAttribute() || ! $widget->isBoundToAttribute())) {
                    continue;
                }
                
                // If both are bound to the same data column BUT the attributes are different, it's a conflict!
                if (! $bindingWidget->getAttribute()->is($widget->getAttribute())) {
                    return true;
                }
            }
        }
        
        return false;
    }
}