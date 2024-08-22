<?php

namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Widgets\DiffHtml;
use exface\Core\Widgets\DiffText;
use exface\Core\Widgets\Value;

/**
 * UI5 implementation of the corresponding widget.
 *
 * @author Andrej Kabachnik, Georg Bieger
 */
class UI5DiffHtml extends UI5Value
{
    // Defining constants to avoid typos.
    const VAR_OLD = 'htmlOld';
    const VAR_NEW = 'htmlNew';
    const VAR_DIF = 'htmlDiff';

    /**
     * {@inheritDoc}
     * @return string
     */
    public function buildJsConstructorForMainControl($oControllerJs = 'oController') : string
    {
        $this->registerExternalModules($this->getController());

        // Assigning constants to local variables to enable string interpolation.
        $varOld = self::VAR_OLD;
        $varNew = self::VAR_NEW;
        $varDif = self::VAR_DIF;

        $widget = $this->getWidget();
        $widget->setHideCaption(true);
        $layout = $this->configureLayout($widget);

        $id = "{$this->getId()}";
        $bindingPathOld = $this->getValueBindingPath(Value::VALUE_ALIAS);
        $bindingPathNew = $this->getValueBindingPath(DiffText::VALUE_TO_COMPARE_ALIAS);

        $initPropsJs = <<<JS

            var oCtrl = sap.ui.getCore().byId('{$id}');
            var oModel = oCtrl.getModel();
            var oValueBinding = new sap.ui.model.Binding(
                oModel, 
                '{$bindingPathOld}', 
                oModel.getContext('{$bindingPathOld}')
            );
            oValueBinding.attachChange(function(oEvent){
                let {$varOld} = sap.ui.getCore().byId('{$id}').getModel().getProperty('{$bindingPathOld}');
                let {$varNew} = sap.ui.getCore().byId('{$id}').getModel().getProperty('{$bindingPathNew}');
                let {$varDif} = htmldiff({$varOld}, {$varNew});
                {$this->buildJsRefreshDiff($layout["left"], $layout["right"])}
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

    /**
     * Generates a title card depending on the corresponding layout.
     *
     * @param string $varName
     * @return string
     */
    public function buildHtmlVersionTitle(string $varName) : string
    {
        $varName = strtolower($varName);
        $color = str_contains($varName, 'diff') ? '#00a65a' : 'darkgrey';
        $title = match (true) {
            str_contains($varName, 'diff') => "Review Changes",
            str_contains($varName, 'new') => "Revision",
            str_contains($varName, 'old') => "Original",
            default => "",
        };

        return  '<div style="text-align:center; background-color:'.$color.'; margin-bottom: 30px; padding:.2rem; color:white;">'.
                '    <h1>'.$title.'</h1>'.
                '</div>';
    }

    /**
     * Generates a JS snippet that updates the displayed documents whenever a change event triggers.
     *
     * @param string $leftValName
     * @param string $rightValName
     * @return string
     */
    public function buildJsRefreshDiff(string $leftValName, string $rightValName) : string
    {
        return <<<JS
            var fnConstructor = function(mVal, sId, sTitle){
                if(mVal === undefined || mVal === null) {
                    return '';
                }
                // Apply styling.
                var sHtml = "".concat(
                    // Injecting CSS into the iFrame. TODO: Find a prettier way.
                    '<style> .difftext-container {border: 1px solid #c3d9e0;} .difftext-diff del {text-decoration: line-through; color: white; background-color: red;} .difftext-diff ins {text-decoration: none; color: white; background-color: green;} </style>',
                    sTitle,
                    '<div id="' + sId + '_shell" class="difftext-diff">', 
                    mVal,
                    '</div>'
                );
                // Escape HTML.  
                sHtml = exfTools.string.htmlEscape(sHtml, true);
                // Enclose in iFrame.
                return  '   <iframe ' +
                        '       id="IFRAME"  ' +
                        '       sandbox="allow-same-origin" ' + // Required for iFrame resizing. TODO: Is this a security risk?
                        '       title="HTML RENDERER" ' +
                        '       onload="this.style.height=(this.contentWindow.document.body.scrollHeight+20)+\'px\';"' + // Resizing iFrame to fit entire document.
                        '       {$this->buildCssIFrameStyle()} ' +
                        '       srcdoc="' + sHtml + '"' +
                        '   ></iframe>'
            };
            var sDiv = '<table style="width: calc(100% - 14px)"><tr>';
            sDiv += '<td width="50%" style="vertical-align: top;">' + fnConstructor({$leftValName}, '{$this->getId()}_left', '{$this->buildHtmlVersionTitle($leftValName)}') + '</td>';
            sDiv += '<td width="50%" style="vertical-align: top;">' + fnConstructor({$rightValName}, '{$this->getId()}_right', '{$this->buildHtmlVersionTitle($rightValName)}') + '</td>';
            sDiv += '</table>';

            sap.ui.getCore().byId('{$this->getId()}').setContent(sDiv);
JS;
    }

    /**
     * Generates CSS styling for the iFrame.
     *
     * @return string
     */
    public function buildCssIFrameStyle() : string
    {
        // TODO do not use padding in percent - use rem instead
        // TODO improved width calculation. Why 25px? Where do they come from!
        return <<< HTML
style="width: 97%; padding:.5%; border:5px solid lightgrey; background: white;"
HTML;
    }

    /**
     * Generates the binding path for the given property name.
     *
     * @param string $propertyName
     * @return string
     */
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

    /**
     * Gets the desired layout and injects it with local variable names.
     *
     * @param DiffHtml $widget
     * @return array
     */
    private function configureLayout(Value $widget) : array
    {
        $layout = $widget->getLayoutArray();
        foreach ($layout as $key => $value) {
            switch ($value) {
                case "old":
                    $layout[$key] = self::VAR_OLD;
                    break;
                case "new":
                    $layout[$key] = self::VAR_NEW;
                    break;
                case "diff":
                    $layout[$key] = self::VAR_DIF;
                    break;
            }
        }

        return $layout;
    }
}