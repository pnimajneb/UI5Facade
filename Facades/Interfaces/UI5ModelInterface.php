<?php
namespace exface\UI5Facade\Facades\Interfaces;

use exface\Core\Interfaces\Widgets\PrefillModelInterface;
use exface\Core\Interfaces\WidgetInterface;

/**
 * 
 * @author Andrej Kabachnik
 *
 */
interface UI5ModelInterface extends PrefillModelInterface
{

    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ModelInterface::getName()
     */
    public function getName() : string;
    
    /**
     * 
     * @return string
     */
    public function getViewName() : string;
    
    /**
     *
     * @param WidgetInterface $widget
     * @param string $bindingName
     * @return string
     */
    public function getBindingPath(WidgetInterface $widget, string $bindingName) : string;
    
    /**
     * Returns TRUE if the model already contains other bindings with the same name and path.
     * 
     * This is important to be able to check, if a control MAY get a model binding if it
     * does not have one explicitly. This method checks, if the given widget and binding
     * name would produce a binding path, that already exists, but has a differen meaning.
     * 
     * @param WidgetInterface $widget
     * @param string $bindingName
     * @return bool
     */
    public function hasBindingConflict(WidgetInterface $widget, string $bindingName) : bool;   
}