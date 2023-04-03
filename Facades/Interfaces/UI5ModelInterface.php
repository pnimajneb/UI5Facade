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
     * Returns TRUE if the model contains other bindings with the same name (but for other widgets).
     * 
     * @param WidgetInterface $widget
     * @param string $bindingName
     * @return bool
     */
    public function hasBindingConflict(WidgetInterface $widget, string $bindingName) : bool;   
}