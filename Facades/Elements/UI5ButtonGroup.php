<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryButtonGroupTrait;
use exface\Core\CommonLogic\UxonObject;

/**
 * UI5 does not have anything comparable to a button group, so the actual rendering takes place in UI5Toolbar
 *
 * @author Andrej Kabachnik
 *        
 * @method \exface\Core\Widgets\ButtonGroup getWidget()
 */
class UI5ButtonGroup extends UI5AbstractElement
{
    use JqueryButtonGroupTrait;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::init()
     */
    protected function init()
    {
        parent::init();
        // Since there is no UI5 control for the button group, we need to pass conditional properties
        // to each individual button
        // FIXME this will overwrite the conditional property of the button if it has one, so
        // it would be better to add the some how. But how?
        if ($condProp = $this->getWidget()->getHiddenIf()) {
            foreach ($this->getWidget()->getButtons() as $btn) {
                // Do not override setting of the button itself!
                if ($btn->isHidden() === false) {
                    $newPropUxon = $condProp->exportUxonObject();
                    if (null !== $ownProp = $btn->getHiddenIf()) {
                        $newPropUxon = new UxonObject([
                            'operator' => EXF_LOGICAL_OR,
                            'condition_groups' => [
                                $newPropUxon->toArray(),
                                $ownProp->exportUxonObject()->toArray()
                            ]
                        ]);
                    }
                    $btn->setHiddenIf($newPropUxon);
                }
            }
        }
        if ($condProp = $this->getWidget()->getDisabledIf()) {
            foreach ($this->getWidget()->getButtons() as $btn) {
                // Do not override setting of the button itself!
                if ($btn->isDisabled() === false) {
                    $newPropUxon = $condProp->exportUxonObject();
                    if (null !== $ownProp = $btn->getDisabledIf()) {
                        $newPropUxon = new UxonObject([
                            'operator' => EXF_LOGICAL_OR,
                            'condition_groups' => [
                                $newPropUxon->toArray(),
                                $ownProp->exportUxonObject()->toArray()
                            ]
                        ]);
                    }
                    $btn->setDisabledIf($newPropUxon);
                }
            }
        }
    }
}