<?php
namespace exface\UI5Facade\Facades\Elements;

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
        (function(){
            var oSegmetedBtn = new sap.m.SegmentedButton("{$this->getId()}", {
    			{$this->buildJsProperties()}
            });
            oSegmetedBtn.setValueState = function(state) {
                if (state == 'Error') {
                    $('#{$this->getId()}').find(".sapMSegBBtnInner").each(function(index, el) {el.classList.add('segmentedButtonsError')})
                } else {
                    $('#{$this->getId()}').find(".sapMSegBBtnInner").each(function(index, el) {el.classList.remove('segmentedButtonsError')})
                }
            };
            oSegmetedBtn.setValueStateText = function(text) {
                return;
            }
            return oSegmetedBtn;
        })()
        {$this->buildJsPseudoEventHandlers()}
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
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsPropertyRequired()
     */
    protected function buildJsPropertyRequired()
    {
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsPropertyEditable()
     */
    protected function buildJsPropertyEditable()
    {
        return '';
    }
    
    protected function buildJsRequiredSetter(bool $required) : string
    {
        $val = $required ? 'true' : 'false';
        return "sap.ui.getCore().byId('{$this->getId()}')._exfRequired = {$val};";
    }
    
    protected function buildJsRequiredGetter() : string
    {
        $val = $this->getWidget()->isRequired() ? 'true' : 'false';
        return "sap.ui.getCore().byId('{$this->getId()}')._exfRequired || {$val}";
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5InputSelect::buildJsValueSetterMethod()
     */
    public function buildJsValueSetter($value)
    {
        return <<<JS

            (function(mVal) {
                var oCtrl = sap.ui.getCore().byId('{$this->getId()}');
                oCtrl.setSelectedKey({$value});
                oCtrl.fireSelectionChange({item: oCtrl.getSelectedItem()});
            })($value);
JS;
    }
}