<?php
namespace exface\UI5Facade\Events;

use exface\Core\Events\AbstractEvent;
use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;
use exface\UI5Facade\Facades\Elements\UI5AbstractElement;
use exface\Core\Interfaces\WidgetInterface;

/**
 * Event fired after a UI5 controller was assigned to a facade element.
 *
 * This is usefull if some code needs to access the controller, but is called at a time, when the
 * controller might not yet be assigned - e.g. in `Element::init()`.
 *
 * @event exface.UI5Facade.OnControllerSet
 *
 * @author Andrej Kabachnik
 *
 */
class OnControllerSetEvent extends AbstractEvent
{
    private $controller = null;
    
    private $element = null;
    
    /**
     * 
     * @param UI5ControllerInterface $controller
     * @param UI5AbstractElement $element
     */
    public function __construct(UI5ControllerInterface $controller, UI5AbstractElement $element)
    {
        $this->controller = $controller;
        $this->element = $element;
    }
    
    /**
     * {@inheritdoc}
     * @see \exface\Core\Events\Action\OnBeforeActionInputValidatedEvent::getEventName()
     */
    public static function getEventName() : string
    {
        return 'exface.UI5Facade.OnControllerSet';
    }
    
    /**
     * 
     * @return UI5ControllerInterface
     */
    public function getController() : UI5ControllerInterface
    {
        return $this->controller;
    }
    
    /**
     * 
     * @return UI5AbstractElement
     */
    public function getElement() : UI5AbstractElement
    {
        return $this->element;
    }
    
    /**
     * 
     * @return WidgetInterface
     */
    public function getWidget() : WidgetInterface
    {
        return $this->element->getWidget();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\WorkbenchDependantInterface::getWorkbench()
     */
    public function getWorkbench()
    {
        return $this->element->getWorkbench();
    }
}