<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryButtonTrait;

/**
 * Generates sap.m.MenuButton for MenuButton widgets
 *
 * @method \exface\Core\Widgets\MenuButton getWidget()
 * 
 * @author Andrej Kabachnik
 *        
 */
class UI5MenuButton extends UI5AbstractElement
{
    use JqueryButtonTrait;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $this->registerConditionalProperties();
        return <<<JS

    new sap.m.MenuButton("{$this->getId()}", {
        text: "{$this->getCaption()}",
        {$this->buildJsPropertyIcon()}
        menu: [
            new sap.m.Menu({
                items: [
                    {$this->buildJsMenuItems()}
                ]
            })
		]
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
        if ($caption == '') {
            $caption = '...';
        }
        return $caption;
    }
        
    /**
     * 
     * @return string
     */
    protected function buildJsMenuItems() : string
    {
        $js = '';
        $last_parent = null;
        $start_section = false;
        /* @var $b \exface\Core\Widgets\Button */
        foreach ($this->getWidget()->getButtons() as $b) {
            if (is_null($last_parent)){
                $last_parent = $b->getParent();
            }
            
            // Create a menu entry: a link for actions or a separator for empty buttons
            if (! $b->getCaption() && ! $b->getAction()){
                $start_section = true;
            } else {
                $btnElement = new UI5MenuItem($b, $this->getFacade());
                
                if ($b->getParent() !== $last_parent){
                    $start_section = true;
                    $last_parent = $b->getParent();
                }
                
                $btnElement->setStartsSection($start_section);
                
                $js .= $btnElement->buildJsConstructor();
            }
        }
        return $js;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsPropertyIcon()
    {
        $widget = $this->getWidget();
        return ($widget->getIcon() ? 'icon: "' . $this->getIconSrc($widget->getIcon()) . '", ' : '');
    }
    
    /**
     *
     * {@inheritdoc}
     * @see JqueryButtonTrait::buildJsCloseDialog()
     */
    protected function buildJsCloseDialog($widget, $input_element)
    {
        return '';
    }
}