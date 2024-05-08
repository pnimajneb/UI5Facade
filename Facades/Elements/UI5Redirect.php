<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\UI5Facade\Facades\Interfaces\UI5ControlWithToolbarInterface;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryLayoutTrait;
use exface\UI5Facade\Facades\Elements\Traits\UI5HelpButtonTrait;
use exface\Core\Widgets\Card;

/**
 * Redirects via JS and generates a sap.m.MessagePage as fallback.
 * 
 * @author Andrej Kabachnik
 * 
 * @method \exface\Core\Widgets\Redirect getWidget()
 *
 */
class UI5Redirect extends UI5AbstractElement
{
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $widget = $this->getWidget();
        $controller = $this->getController();
        $url = $widget->getTargetUrl($this->getFacade());
        if ($widget->getOpenInNewWindow() === true) {
            $js = "window.open('{$url}', '_blank'); $oControllerJs.navBack();";
            $linkProps = "target=\\\"_blank\\\"";
        } else {
            $js = "window.location.href = '{$url}';";
            $linkProps = '';
        }
        $controller->addOnRouteMatchedScript($js, $this->getId());
        
        return <<<JS

            new sap.m.MessagePage('{$this->getId()}', {
    			title: "{i18n>WIDGET.REDIRECT.TITLE}",
    			icon: "sap-icon://shortcut",
    			text: "{$this->escapeJsTextValue($this->getCaption())}",
    			description: "{i18n>WIDGET.REDIRECT.FALLBACK} <a href=\"{$widget->getUrl()}\" {$linkProps}>{i18n>WIDGET.REDIRECT.FALLBACK_LINK_TEXT}</a>",
    			showNavButton: true,
                enableFormattedText: true,
    			navButtonPress: [oController.navBack, $oControllerJs]
    		})                            
JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::getCaption()
     */
    protected function getCaption() : string
    {
        $caption = parent::getCaption();
        if ($caption === '') {
            $caption = $this->translate('WIDGET.REDIRECT.CAPTION');
        }
        return $caption;
    }
}