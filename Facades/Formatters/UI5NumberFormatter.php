<?php
namespace exface\UI5Facade\Facades\Formatters;

use exface\Core\Facades\AbstractAjaxFacade\Formatters\JsNumberFormatter;
use exface\Core\DataTypes\NumberDataType;

/**
 * 
 * @method JsNumberFormatter getJsFormatter()
 * @method NumberDataType getDataType()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5NumberFormatter extends AbstractUI5BindingFormatter
{    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5BindingFormatterInterface::buildJsBindingProperties()
     */
    public function buildJsBindingProperties()
    {
        $type = $this->getDataType();
        $options = '';
        $otherProps = '';
        
        if (! is_null($type->getPrecisionMin())){
            $options .= <<<JS

                    minFractionDigits: {$type->getPrecisionMin()},
JS;
        }
            
        if (! is_null($type->getPrecisionMax())){
            $options .= <<<JS

                    maxFractionDigits: {$type->getPrecisionMax()},
JS;
        }
         
        if ($type->getGroupDigits()) {
            $options .= <<<JS

                    groupingEnabled: true,
                    groupingSize: {$type->getGroupLength()},
                    groupingSeparator: "{$type->getGroupSeparator()}",
                    
JS;
        } else {
            $options .= <<<JS

                    groupingEnabled: false,
                    groupingSeparator: "",
JS;
        }
        
        if (($type instanceof NumberDataType) && ($type->getPrefix() || $type->getSuffix())) {
            $prefix = $type->getPrefix();
            $prefixJs = $prefix === '' || $prefix === null ? '""' : json_encode($prefix . ' ');
            $suffix = $type->getSuffix();
            $suffixJs = $suffix === '' || $suffix === null ? '""' : json_encode(' ' . $suffix);
            
            $otherProps = <<<JS

                formatter: function(mVal) {
                    var sPrefix = $prefixJs;
                    var sSuffix = $suffixJs;
                    if (mVal === '' || mVal === null || mVal === undefined) return mVal;
                    if (sPrefix !== '') {
                        mVal = sPrefix + mVal;
                    }
                    if (sSuffix !== '') {
                        mVal = mVal + sSuffix;
                    }
                    return mVal;
                },
JS;
        }
        
        return <<<JS

                type: '{$this->getSapDataType()}',
                formatOptions: {
                    {$options}
                }, $otherProps

JS;
    }
        
    protected function getSapDataType()
    {
        $type = $this->getDataType();
        if ($type->getPrecisionMax() === 0) {
            return 'sap.ui.model.type.Integer';
        } else {
            return 'sap.ui.model.type.Float';
        }
    }
}