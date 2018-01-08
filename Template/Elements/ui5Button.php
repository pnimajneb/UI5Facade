<?php
namespace exface\OpenUI5Template\Template\Elements;

use exface\Core\Widgets\DialogButton;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryButtonTrait;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement;
use exface\Core\Widgets\Button;
use exface\Core\Interfaces\Actions\iShowDialog;

/**
 * Generates jQuery Mobile buttons for ExFace
 *
 * @author Andrej Kabachnik
 *        
 */
class ui5Button extends ui5AbstractElement
{
    
    use JqueryButtonTrait;

    function generateJs()
    {
        $output = '';
        $hotkey_handlers = array();
        $action = $this->getAction();
        
        // Get the java script required for the action itself
        if ($action) {
            // Actions with template scripts may contain some helper functions or global variables.
            // Print the here first.
            if ($action && $action->implementsInterface('iRunTemplateScript')) {
                $output .= $this->getAction()->buildScriptHelperFunctions();
            }
        }
        
        if ($click = $this->buildJsClickFunction()) {
            
            // Generate the function to be called, when the button is clicked
            $output .= "
				function " . $this->buildJsClickFunctionName() . "(input){
                    " . $click . "
				}
				";
            
            // Handle hotkeys
            if ($this->getWidget()->getHotkey()) {
                $hotkey_handlers[$this->getWidget()->getHotkey()][] = $this->buildJsClickFunctionName();
            }
        }
        
        return $output;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\OpenUI5Template\Template\Elements\ui5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructor()
    {
        return <<<JS
new sap.m.Button("{$this->getId()}", { 
    {$this->buildJsProperties()}
})
JS;
    }
    
    public function buildJsProperties()
    {
        $widget = $this->getWidget();
        switch ($widget->getVisibility()) {
            case EXF_WIDGET_VISIBILITY_PROMOTED: 
                $visibility = ', type: "Emphasized", layoutData: new sap.m.OverflowToolbarLayoutData({priority: "High"})'; break;
            case EXF_WIDGET_VISIBILITY_OPTIONAL: 
                $visibility = ', type: "Default", layoutData: new sap.m.OverflowToolbarLayoutData({priority: "AlwaysOverflow"})'; break;
            case EXF_WIDGET_VISIBILITY_NORMAL: 
            default: $visibility = ', type: "Default"';
            
        }
        
        $press = $this->buildJsClickFunction() ? ', press: function(){' . $this->buildJsClickFunctionName() . '()}' : '';
        
        $icon = $widget->getIcon() ? ', icon: "' . $this->getIconSrc($widget->getIcon()) . '"' : '';
        
        $options = '
                    text: "' . $this->getCaption() . '"
                    ' . $icon . '
                    ' . $visibility . '
                    ' . $press;
        return $options;
    }

    protected function buildJsClickShowDialog(ActionInterface $action, AbstractJqueryElement $input_element)
    {
        $widget = $this->getWidget();
        
        /* @var $prefill_link \exface\Core\CommonLogic\WidgetLink */
        $prefill = '';
        if ($prefill_link = $this->getAction()->getPrefillWithDataFromWidgetLink()) {
            if ($prefill_link->getPage()->is($widget->getPage())) {
                $prefill = ", prefill: " . $this->getTemplate()->getElement($prefill_link->getWidget())->buildJsDataGetter($this->getAction());
            }
        }
        
        $output = $this->buildJsRequestDataCollector($action, $input_element);
        $output .= <<<JS
						{$this->buildJsBusyIconShow()}
						$.ajax({
							type: 'POST',
							url: '{$this->getAjaxUrl()}',
							dataType: 'html',
							data: {
								action: '{$widget->getActionAlias()}',
								resource: '{$widget->getPage()->getAliasWithNamespace()}',
								element: '{$widget->getId()}',
								data: requestData
								{$prefill}
							},
							success: function(data, textStatus, jqXHR) {
								{$this->buildJsCloseDialog($widget, $input_element)}
								{$this->buildJsBusyIconHide()}
		                       	
                                $('body').append(data);
                                oDialogStack.push({
                                    content: oShell.getContent(),
                                    dialog: sap.ui.view({
                                        type:sap.ui.core.mvc.ViewType.JS, 
                                        height: "100%", 
                                        viewName:"{$this->getTemplate()->getElement($this->getWidget()->getAction()->getWidget())->getViewName()}"
                                    }),
                                    onClose: function(){
								        {$this->buildJsInputRefresh($widget, $input_element)}
                                    }
                                });
                                {$this->buildJsDialogLoader()}
							},
							error: function(jqXHR, textStatus, errorThrown){
								{$this->buildJsShowError('jqXHR.responseText', 'jqXHR.status + " " + jqXHR.statusText')}
								{$this->buildJsBusyIconHide()}
							}
						});
JS;
        
        return $output;
    }

    /**
     * Returns javascript code with global variables and functions needed for certain button types
     */
    protected function buildJsGlobals()
    {
        $output = '';
        /*
         * Commented out because moved to generate_js()
         * // If the button reacts to any hotkey, we need to declare a global variable to collect keys pressed
         * if ($this->getWidget()->getHotkey() == 'any'){
         * $output .= 'var exfHotkeys = [];';
         * }
         */
        return $output;
    }

    /**
     * In OpenUI5 the button does not need any extra headers, as all headers needed for whatever the button loads will
     * come with the AJAX-request.
     *
     * {@inheritdoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::generateHeaders()
     */
    public function generateHeaders()
    {
        return array();
    }
    
    protected function buildJsCloseDialog($widget, $input_element)
    {
        return ($widget->getWidgetType() == 'DialogButton' && $widget->getCloseDialogAfterActionSucceeds() ? "closeTopDialog();" : "");
    }
    
    protected function buildJsDialogLoader()
    {
        $action = $this->getAction();
        
        // TODO only maximize a dialog (making it a page) if it has more than N widgets or
        // determine the best size some other way.
        if (($action instanceof iShowDialog) && $action->getMaximize() === false) {
            $maximize = false;
        } else {
            $maximize = true;
        }
        
        if ($maximize) {
            return $this->buildJsOpenPage();
        } else {
            return $this->buildJsOpenDialog();
        }
    }
    
    protected function buildJsOpenDialog()
    {
        $dialog = $this->getAction()->getWidget();
        return <<<JS

                                oShell.addContent(
                                    oDialogStack[oDialogStack.length-1].dialog
                                );
                                sap.ui.getCore().byId("{$dialog->getId()}").open();

JS;
    }
    
    protected function buildJsOpenPage()
    {
        return <<<JS

                                oShell.removeAllContent()
                                oShell.addContent(
                                    oDialogStack[oDialogStack.length-1].dialog
                                );

JS;
    }
}
?>