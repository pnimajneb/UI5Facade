<?php

namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\DiffText;
use exface\Core\Widgets\Value;

class UI5DiffHtml extends UI5Value
{
    public function buildJsConstructorForMainControl($oControllerJs = 'oController')
    {
        $widget = $this->getWidget();
        $this->registerExternalModules($this->getController());

        $layout = $widget->getVersionToRender();
        $id = "{$this->getId()}";
        $bindingPathOld = $this->getValueBindingPath(Value::VALUE_ALIAS);
        $bindingPathNew = $this->getValueBindingPath(DiffText::VALUE_TO_COMPARE_ALIAS);
        $widget->setHideCaption(true);
        $initPropsJs = <<<JS

            var oCtrl = sap.ui.getCore().byId('{$id}');
            var oModel = oCtrl.getModel();
            var oValueBinding = new sap.ui.model.Binding(
                oModel, 
                '{$bindingPathOld}', 
                oModel.getContext('{$bindingPathOld}')
            );
            oValueBinding.attachChange(function(oEvent){
                let htmlOld = sap.ui.getCore().byId('{$id}').getModel().getProperty('{$bindingPathOld}');
                let htmlNew = sap.ui.getCore().byId('{$id}').getModel().getProperty('{$bindingPathNew}');
                let htmlDiff = htmldiff(htmlOld, htmlNew);
                {$this->buildJsRefreshDiff('htmlOld', "htmlDiff")}
                console.log("SET");
            });

JS;

        return <<<JS
        new sap.ui.core.HTML("{$id}", {
            preferDOM: false,
            afterRendering: function() {
                {$initPropsJs}
            }
        })
JS;
    }
    
    public function buildJsValueSetter($variableName) : string
    {
        
    }

    public function buildJsRefreshDiff(string $leftValJs, string $rightValJs) : string
    {
        $js = <<<JS
            console.log("HI");
            var fnConstructor = function(mVal, sId){
                if(mVal === undefined || mVal === null) {
                    return '';
                }
                // Apply styling.
                var sHtml = "".concat(
                    // Injecting CSS into the iFrame. TODO: Find a prettier way.
                    '<style> .difftext-container {border: 1px solid #c3d9e0;} .difftext-diff del {text-decoration: line-through; color: white; background-color: red;} .difftext-diff ins {text-decoration: none; color: white; background-color: green;} </style>',
                    '<div id="' + sId + '_shell" class="difftext-diff">', 
                    mVal,
                    '</div>'
                );
                // Escape HTML.            
                sHtml = sHtml
                  .replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
console.log(mVal, sHtml);
                // Enclose in iFrame.
                return  '   <iframe ' +
                        '       id="IFRAME"  ' +
                        '       sandbox="allow-same-origin" ' + // Required for iFrame resizing. TODO: Is this a security risk?
                        '       title="HTML RENDERER" ' +
                        '       onload="this.style.height=(this.contentWindow.document.body.scrollHeight+20)+\'px\';"' + // Resizing iFrame to fit entire document.
                        '       {$this->buildCssElementStyle()} ' +
                        '       srcdoc="' + sHtml + '"' +
                        '   ></iframe>'
            };
            var sDiv = '<table style="width: calc(100% - 14px)"><tr>';
            sDiv += '<td width="50%" style="vertical-align: top;">' + fnConstructor({$leftValJs}, '{$this->getId()}_oldval') + '</td>';
            sDiv += '<td width="50%" style="vertical-align: top;">' + fnConstructor({$rightValJs}, '{$this->getId()}_diff') + '</td>';
            sDiv += '</table>';

            sap.ui.getCore().byId('{$this->getId()}').setContent(sDiv);
JS;
        return $js;
    }
    
    protected function buildJsConstructorForIFrame() : string
    {
        return <<<JS
                
                    
JS;
    }

    public function buildCssElementStyle() : string
    {
        // TODO do not use padding in percent - use rem instead
        // TODO improved widht calculation. Why 25px? Where do they come from!
        return <<< HTML
style="width: 100%; padding:.5%; border:5px solid lightgrey; background: white;"
HTML;
    }

    public function getValueBindingPath(string $propertyName = "value") : string
    {
        $widget = $this->getWidget();
        $model = $this->getView()->getModel();
        if ($model->hasBinding($widget, $propertyName)) {
            return $model->getBindingPath($widget, $propertyName);
        }
        return $this->getValueBindingPrefix() . $this->getWidget()->getDataColumnName();
    }

    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::registerExternalModules()
     */
    public function registerExternalModules(\exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface $controller) : UI5AbstractElement
    {
        $controller->addExternalModule('libs.exface.custom.htmlDiff', $this->getFacade()->buildUrlToSource('LIBS.HTMLDIFF.JS'));
        return $this;
    }
}