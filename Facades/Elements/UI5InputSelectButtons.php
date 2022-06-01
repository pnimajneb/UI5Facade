<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Interfaces\DataTypes\DataTypeInterface;
use exface\Core\Exceptions\Facades\FacadeUnsupportedWidgetPropertyWarning;

/**
 * Generates a sap.m.SegmetedButton for InputSelectButtons widget
 *
 * @method \exface\Core\Widgets\InputSelectButtons getWidget()
 * 
 * @author Andrej Kabachnik
 *        
 */
class UI5InputSelectButtons extends UI5InputSelect
{
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5InputSelect::buildJsConstructorForMainControl()
     */
    public function buildJsConstructorForMainControl($oControllerJs = 'oController')
    {
        if ($this->getWidget()->getMultiSelect()) {
            throw new FacadeUnsupportedWidgetPropertyWarning('Widget InputSelectButtons currently does not support multi_select in UI5!');
        }
        return <<<JS
        new sap.m.SegmentedButton("{$this->getId()}", {
			{$this->buildJsProperties()}
        }){$this->buildJsPseudoEventHandlers()}
JS;
    }
			
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5InputSelect::buildJsPropertyItems()
     */
    protected function buildJsPropertyItems() : string
    {
        $items = '';
        foreach ($this->getWidget()->getSelectableOptions() as $key => $value) {
            $items .= <<<JS
                new sap.m.SegmentedButtonItem({
                    key: "{$key}",
                    text: "{$value}"
                }),
JS;
        }
        
        return <<<JS
            items: [
                {$items}
            ],
JS;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5InputSelect::buildJsPropertyChange()
     */
    protected function buildJsPropertyChange()
    {
        return 'selectionChange: ' . $this->getController()->buildJsEventHandler($this, self::EVENT_NAME_CHANGE, true) . ',';
    }
    
    protected function buildJsPropertyRequired()
    {
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::registerOnChangeValidation()
     */
    protected function registerOnChangeValidation()
    {
        return;
    }
}