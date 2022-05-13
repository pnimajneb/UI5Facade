<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Interfaces\DataTypes\DataTypeInterface;
use exface\Core\Factories\DataTypeFactory;
use exface\Core\DataTypes\NumberDataType;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryInputValidationTrait;

/**
 * Renders a sap.m.Input with input type Number.
 * 
 * @method InputNumber getWidget()
 * 
 * @author Andrej Kabachnik
 *        
 */
class UI5InputNumber extends UI5Input
{    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsPropertyType()
     */
    protected function buildJsPropertyType()
    {        
        return 'type: sap.m.InputType.Number,';
    }
        
    /**
     * Returns the initial value defined in UXON as number or an quoted empty string
     * if not initial value was set.
     * 
     * @return string|NULL
     */
    protected function buildJsInitialValue() : string
    {
        $val = $this->getWidget()->getValueWithDefaults();
        return (is_null($val) || $val === '') ? '""' : $val;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsProperties()
     */
    public function buildJsProperties()
    {
        return parent::buildJsProperties() . <<<JS
            textAlign: sap.ui.core.TextAlign.Right,
JS;
    }
    
    /**
     *
     * {@inheritdoc}
     * @see JqueryInputValidationTrait::buildJsValidatorConstraints()
     */
    protected function buildJsValidatorConstraints(string $valueJs, string $onFailJs, DataTypeInterface $type) : string
    {
        $widget = $this->getWidget();
        $constraintsJs = parent::buildJsValidatorConstraints($valueJs, $onFailJs, $type);
        // If the widget has other min/max values than the data type, validate them separately
        // Do it by creating a data type with these constraints and letting it render the validator
        // Place this validator AFTER the regular validation of the data type because if the
        // data type has more severe constraints, the whole thing should still fail!
        if ((null !== $min = $widget->getMinValue()) || (null !== $max = $widget->getMaxValue())) {
            $numberType = DataTypeFactory::createFromString($this->getWorkbench(), NumberDataType::class);
            if ($min !== null) {
                $numberType->setMin($min);
            }
            if ($max !== null) {
                $numberType->setMax($max);
            }
            $numberValidator = $this->getFacade()->getDataTypeFormatter($numberType)->buildJsValidator($valueJs);
            $constraintsJs .= <<<JS

                    if($numberValidator !== true) {$onFailJs};
JS;
        }
        return $constraintsJs;
    }
}