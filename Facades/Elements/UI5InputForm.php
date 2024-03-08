<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;
use exface\Core\DataTypes\StringDataType;
use exface\Core\Facades\AbstractAjaxFacade\Elements\SurveyJsTrait;

/**
 * Creates a Survey-JS instance for an InputForm widget
 * 
 * @method \exface\Core\Widgets\InputForm getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5InputForm extends UI5Input
{
    use SurveyJsTrait {
        buildJsSurveyInit AS buildJsSurveyInitViaTrait;
    }
    
    const CONTROLLER_VAR_SURVEY = 'survey';
    
    protected function init()
    {
        parent::init();
        
        // Make sure to register the controller var as early as possible because it is needed in buildJsValidator(),
        // which is called by the outer Dialog or Form widget
        $this->getController()->addDependentObject(self::CONTROLLER_VAR_SURVEY, $this, 'null');
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsConstructorForMainControl()
     */
    public function buildJsConstructorForMainControl($oControllerJs = 'oController')
    {
        $controller = $this->getController();
        
        $this->registerExternalModules($controller);
        
        // Update the survey every time the value in the UI5 model changes.
        // Also update the UI5 model every time the answer to a survey question changes. Note,
        // that this doesnot seem to trigger a binding change, so there will be no recursion
        $this->addOnChangeScript(<<<JS
            
                    (function(oHtml) {
                        var oCurrentValue = {$this->buildJsValueGetter()};
                        oHtml.getModel().setProperty('{$this->getValueBindingPath()}', oCurrentValue);
                    })(sap.ui.getCore().byId("{$this->getId()}"))
JS);
        
        return <<<JS

        new sap.ui.core.HTML("{$this->getId()}", {
            content: "<div id=\"{$this->getIdOfSurveyDiv()}\"></div>",
            afterRendering: function() {
                {$this->buildJsSurveySetup()}

                var oValueBinding = new sap.ui.model.Binding(sap.ui.getCore().byId('{$this->getId()}').getModel(), '{$this->getValueBindingPath()}', sap.ui.getCore().byId('{$this->getId()}').getModel().getContext('{$this->getValueBindingPath()}'));
                oValueBinding.attachChange(function(oEvent){
                    {$this->buildJsValueSetter("sap.ui.getCore().byId('{$this->getId()}').getModel().getProperty('{$this->getValueBindingPath()}')")};
                });
            }
        })

JS;
    }
    
    /**
     * 
     * @see SurveyJsTrait::buildJsSurveyVar()
     */
    protected function buildJsSurveyVar() : string
    {
        return $this->getController()->buildJsDependentObjectGetter(self::CONTROLLER_VAR_SURVEY, $this);
    }
    
    /**
     * 
     * @see SurveyJsTrait::buildJsSurveyModelGetter()
     */
    protected function buildJsSurveyModelGetter() : string
    {
        $widget = $this->getWidget();
        $model = $this->getView()->getModel();
        if ($model->hasBinding($widget, 'form_config')) {
            $modelPath = $model->getBindingPath($widget, 'form_config');
        } else {
            $modelPath = $this->getValueBindingPrefix() . $this->getWidget()->getFormConfigDataColumnName();
        }
        return "sap.ui.getCore().byId('{$this->getId()}').getModel().getProperty('{$modelPath}')";
    }
    
    /**
     * 
     * @see SurveyJsTrait::buildJsSurveyInit()
     */
    protected function buildJsSurveyInit(string $oSurveyJs = 'oSurvey') : string
    {
        // Make sure the left-aligned titles are the same width as those of UI5 controls
        return $this->buildJsSurveyInitViaTrait($oSurveyJs) . <<<JS
    
    $oSurveyJs.onUpdateQuestionCssClasses.add(function(_, options) {
        const classes = options.cssClasses;
        if (classes.headerLeft === 'title-left') {
            classes.titleLeftRoot += ' sapUiRespGrid sapUiRespGridHSpace0 sapUiRespGridVSpace0 sapUiFormResGridCont sapUiRespGridOverflowHidden sapUiRespGridMedia-Std-LargeDesktop';
            /* TODO replace class sapUiRespGridMedia-Std-LargeDesktop to match current device in UI5 */
            classes.headerLeft += ' sapUiRespGridSpanXL5 sapUiRespGridSpanL4 sapUiRespGridSpanM4 sapUiRespGridSpanS12';
        }
    });

JS;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryInputValidationTrait::buildJsValidator()
     */
    public function buildJsValidator(?string $valJs = null) : string
    {
        // Always validate the form if it can be found in the dialog - even if the widget is not required explicitly. Otherwise required
        // fields inside the form will not produce validation errors if the InputForm is not explicitly
        // marked as required
        //
        return <<<JS
(function(){
    var surveyJsVar = {$this->buildJsSurveyVar()};
    if (surveyJsVar !== null && surveyJsVar !== undefined) {   
        return {$this->buildJsSurveyVar()}.validate();
    }
    return true;
}())
JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Input::buildJsValidationError()
     */
    public function buildJsValidationError()
    {
        // No need to do anything here - the .validate() method of Survey.js already shows the errors
        return '';
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::registerExternalModules()
     */
    public function registerExternalModules(UI5ControllerInterface $controller) : UI5AbstractElement
    {
        foreach ($this->getJsIncludes() as $src) {
            $name = StringDataType::substringAfter($src, '/', $src, false, true);
            $name = str_replace('-', '_', $name);
            
            $name = 'libs.exface.survey.' . $name;
            $controller->addExternalModule($name, $src);
        }
        
        foreach ($this->getCssIncludes() as $src) {
            $controller->addExternalCss($src);
        }
        
        return $this;
    }
    
    /**
     *
     * @return string[]
     */
    protected function getJsIncludes() : array
    {
        $htmlTagsArray = $this->buildHtmlHeadTagsForSurvey();
        $tags = implode('', $htmlTagsArray);
        $jsTags = [];
        preg_match_all('#<script[^>]*src="([^"]*)"[^>]*></script>#is', $tags, $jsTags);
        return $jsTags[1];
    }
    
    /**
     *
     * @return string[]
     */
    protected function getCssIncludes() : array
    {
        $htmlTagsArray = $this->buildHtmlHeadTagsForSurvey();
        $tags = implode('', $htmlTagsArray);
        $jsTags = [];
        preg_match_all('#<link[^>]*href="([^"]*)"[^>]*/?>#is', $tags, $jsTags);
        return $jsTags[1];
    }
}