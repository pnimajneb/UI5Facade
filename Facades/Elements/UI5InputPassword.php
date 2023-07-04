<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\InputPassword;
use exface\Core\Factories\WidgetFactory;
use exface\Core\Interfaces\WidgetInterface;

/**
 * Renders a UI5 textbox and changes the input type to password.
 *
 * @method InputPassword getWidget()
 *
 * @author Andrej Kabachnik
 *
 */
class UI5InputPassword extends UI5Input
{
    
    private $conformationInputWidget = null;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsConstructorForMainControl()
     */
    public function buildJsConstructorForMainControl($oControllerJs = 'oController')
    {
        $widget = $this->getWidget();
        if ($widget->getShowSecondInputForConfirmation() === false) {
            return parent::buildJsConstructorForMainControl($oControllerJs);
        }
        
        $confirmInputElement = $this->getFacade()->getElement($this->getConfirmationInput());
        $confirmInputElement->setController($this->getController());
        $confirmInputElement->setValueBindingDisabled(true);
        $onChangeEnableDisableScript = <<<JS
        
                    if ({$this->buildJsValueGetter()} === '') {
                        {$confirmInputElement->buildJsSetDisabled(true)}
                        {$confirmInputElement->buildJsValueSetter('')}
                    } else {
                        {$confirmInputElement->buildJsSetDisabled(false)}
                    }
JS;
        $this->addOnChangeScript($onChangeEnableDisableScript);
        
        $onAfterRendering = <<<JS

            setTimeout(function(){
                if ({$this->buildJsValueGetter()} === '') {             
                    {$confirmInputElement->buildJsSetDisabled(true)}
                    {$confirmInputElement->buildJsValueSetter('')}
                }
            }, 0);

JS;
        //add script do disable confirm input if password input is empty initially
        //deactivated due to a bug in UI5 where enabling/disabling doesnt work even so the proeprty gets correctly changed
        //$confirmInputElement->addPseudoEventHandler('onAfterRendering', $onAfterRendering);
        
        $onConfirmInputChangeScript = <<<JS
        
            sap.ui.getCore().byId('{$this->getId()}').setValueStateText('{$this->getValidationErrorText()}');
            if(! {$this->buildJsValidator()} ) {
                {$this->buildJsValidationError()};
            } else {
                sap.ui.getCore().byId('{$this->getId()}').setValueState('None');
            }
    
JS;
        $confirmInputElement->addOnChangeScript($onConfirmInputChangeScript);
        $outputParent = parent::buildJsConstructorForMainControl($oControllerJs) . ',';
        $outputChild = $confirmInputElement->buildJsConstructorForMainControl($oControllerJs);
        $outputChildLabel = <<<JS
            new sap.m.Text('',{
                text: '{$this->getConfirmationInput()->getCaption()}',
                textAlign: "End",
                maxLines: 1,
                layoutData: new sap.m.FlexItemData({
                    growFactor: 0,                    
                    styleClass: 'sapUiTinyMarginBeginEnd'
                })
            }),

JS;
        $js = <<<JS
        new sap.m.HBox({
            width: "100%",
            direction: "RowReverse",
            items: [
                {$outputParent}
                {$outputChildLabel}
                {$outputChild}
            ]
        })

JS;
        return $js;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryInputValidationTrait::buildJsValidator()
     */
    public function buildJsValidator(?string $valJs = null)
    {
        if ($this->getWidget()->getShowSecondInputForConfirmation() === true) {
            $confirmInputElement = $this->getFacade()->getElement($this->getConfirmationInput());
            return "({$this->buildJsValueGetter()} === {$confirmInputElement->buildJsValueGetter()})";
        }
        return parent::buildJsValidator();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsPropertyType()
     */
    protected function buildJsPropertyType()
    {
        return 'type: sap.m.InputType.Password,';
    }
    
    /**
     * returns the password confirmation input widget
     * 
     * @return WidgetInterface
     */
    protected function getConfirmationInput() : WidgetInterface
    {
        if ($this->conformationInputWidget === null) {
            $widget = $this->getWidget();
            $confirmWidget = WidgetFactory::create($widget->getPage(), $widget->getWidgetType(), $widget->getParent());
            $confirmWidget->setMetaObject($this->getMetaObject());
            $confirmWidget->setCaption($this->translate("WIDGET.INPUTPASSWORD.CONFIRM"));
            //$confirmWidget->setWidth('100%');
            $this->conformationInputWidget = $confirmWidget;
        }
        return $this->conformationInputWidget;
    }
    
    /**
     * 
     */
    public function getValidationErrorText() : string
    {
        return $this->translate("WIDGET.INPUTPASSWORD.DONT_MATCH");
    }   
}