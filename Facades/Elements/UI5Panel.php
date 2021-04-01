<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Interfaces\Widgets\iFillEntireContainer;
use exface\UI5Facade\Facades\Interfaces\UI5ControlWithToolbarInterface;
use exface\Core\Widgets\Panel;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryLayoutTrait;
use exface\UI5Facade\Facades\Elements\Traits\UI5HelpButtonTrait;
use exface\Core\Interfaces\Widgets\iContainOtherWidgets;
use exface\Core\Interfaces\WidgetInterface;
use exface\Core\Widgets\Message;
use exface\Core\Interfaces\Widgets\iTakeInput;

/**
 * 
 * @author Andrej Kabachnik
 * 
 * @method Panel getWidget()
 *
 */
class UI5Panel extends UI5Container
{
    use JqueryLayoutTrait;
    use UI5HelpButtonTrait;
    
    private $gridClasses = [];
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        $panel = <<<JS

                new sap.m.Panel("{$this->getId()}", {
                    {$this->buildJsPropertyHeight()}
                    content: [
                        {$this->buildJsChildrenConstructors(false)}
                    ],
                    {$this->buildJsProperties()}
                }).addStyleClass("sapUiNoContentPadding {$this->buildCssElementClass()} {$this->buildCssGridClass()}")

JS;
        if ($this->hasPageWrapper() === true) {
            $headerContent = $this->getWidget()->getHideHelpButton() === false ? $this->buildJsHelpButtonConstructor($oControllerJs) : '';
            return $this->buildJsPageWrapper($panel, '', $headerContent);
        }
        
        return $panel;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsProperties()
     */
    public function buildJsProperties()
    {
        return parent::buildJsProperties() . $this->buildjsPropertyHeaderText();
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsPropertyHeaderText() : string
    {
        if ($this->hasHeaderToolbar() === false && $caption = $this->getCaption()) {
            return 'headerText: "' . $caption . '",';
        }
        return '';
    }
    
    /**
     * 
     * @return bool
     */
    protected function hasHeaderToolbar() : bool
    {
        return false;
    }
                
    /**
     * 
     * @param string $content
     * @param bool $useFormLayout
     * @return string
     */
    public function buildJsLayoutConstructor(string $content = null, bool $useFormLayout = true) : string
    {
        $widget = $this->getWidget();
        $content = $content ?? $this->buildJsChildrenConstructors($useFormLayout);
        if ($widget->countWidgetsVisible() === 1 && ($widget->getWidgetFirst() instanceof iFillEntireContainer)) {
            return $content;
        } elseif ($useFormLayout) {
            return $this->buildJsLayoutForm($content);
        } else {
            return $this->buildJsLayoutGrid($content);
        }
    }
    
    protected function hasChildrenCaption() : bool
    {
        foreach ($this->getWidget()->getWidgets() as $widget) {
            if ($widget->isHidden() === false && ! $widget->getHideCaption() && $widget->getCaption() !== null && $widget->getCaption() !== '') {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsChildrenConstructors()
     */
    public function buildJsChildrenConstructors(bool $useFormLayout = true) : string
    {
        $js = '';
        $firstVisibleWidget = null;
        $childrenHaveCaptions = $this->hasChildrenCaption();
        foreach ($this->getWidget()->getWidgets() as $widget) {
            $element = $this->getFacade()->getElement($widget);
             
            if ($widget->isHidden() === false && $useFormLayout === true) {
                if (! $childrenHaveCaptions && $element instanceof UI5Value) {
                    $element->setRemoveLabelIfNoCaption(true);
                }
                // Larger widgets need a Title before them to make SimpleForm generate a new FormContainer
                if ($this->needsFormRowDelimiter($widget, ($firstVisibleWidget === null))) {
                    $js .= ($js ? ",\n" : '') . $this->buildJsFormRowDelimiter();
                }
                $firstVisibleWidget = $widget;
            }
            $js .= ($js ? ",\n" : '') . $element->buildJsConstructor();
        }
        
        return $js;
    }
    
    public function needsFormRowDelimiter(WidgetInterface $widget, bool $isFirstInForm) : bool
    {
        switch (true) {
            case $widget instanceof iFillEntireContainer && ! $isFirstInForm:
            case $widget->getWidth()->isMax() && ! $isFirstInForm:
                return true;
            case $widget instanceof Message:
                $this->addCssGridClass('exf-simpleform-hide-first-cell');
                return true;
        }
        return false;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsFormRowDelimiter() : string
    {
        return 'new sap.ui.core.Title()';
    }
    
    /**
     * Tunes the configuration of responsive grids used in sap.m.SimpleForm.
     * 
     * The sap.m.SimpleForm uses sap.m.Grid internally with containerQuery=true, which causes
     * forms in small dialogs or split panels to use full-width labels. This does not look
     * ver very nice on large screens. Stand-alone sap.m.Grid controls have a special property
     * `containerQuery` used to determine if a small container on a large screen is to be
     * treated as a small screen. Unfortunately there does not seem to be a way to change
     * that property within a form, so this method injects some hacky JS to deal with it.
     * Different UI5 elements may have different implementations depending on how the grid
     * is used there.
     * 
     * @return string
     */
    protected function buildJsLayoutFormFixes() : string
    {
        $fixContainerQueryJs = <<<JS
        
                    var oGrid = sap.ui.getCore().byId($("#{$this->getId()}--Layout > .sapUiRespGrid").attr("id"));
                    if (oGrid !== undefined) {
                        oGrid.setContainerQuery(false);
                    }
                    
JS;
        $this->addPseudoEventHandler('onAfterRendering', $fixContainerQueryJs);
        // Also call the fix after the view was rendered because the pseudo event does not seem
        // to work on the LoginForm if placed in the app directly and not in a dialog.
        $this->getController()->addOnInitScript('setTimeout(function(){' . $fixContainerQueryJs . '}, 100);');
        
        return '';
    }
    
    /**
     * 
     * @param string $content
     * @param string $toolbarConstructor
     * @param string $id
     * @return string
     */
    protected function buildJsLayoutForm($content, string $toolbarConstructor = null, string $id = null)
    {
        $this->buildJsLayoutFormFixes();
        
        $cols = $this->getNumberOfColumns();
        $id = $id === null ? '' : "'{$id}',";
        
        switch ($cols) {
            case $cols > 3:
                $properties = <<<JS

                columnsXL: {$cols},
    			columnsL: 3,
    			columnsM: 2,  

JS;
            break;
            case 3:
                $properties = <<<JS
                
                columnsXL: {$cols},
    			columnsL: {$cols},
    			columnsM: 2,
    			
JS;
                break;
            default:
                $properties = <<<JS
                
                columnsXL: {$cols},
    			columnsL: {$cols},
    			columnsM: {$cols},
    			
JS;
        }
        
        if ($toolbarConstructor !== null && $toolbarConstructor !== '') {
            $toolbar = 'toolbar: ' . $toolbarConstructor;
        }
        
        $phoneLabelSpan = $this->isEditable() ? '12' : '5';
        $editable = $this->isEditable() ? 'true' : 'false';
        
        return <<<JS
        
            new sap.ui.layout.form.SimpleForm({$id} {
                editable: $editable,
                layout: "ResponsiveGridLayout",
                adjustLabelSpan: false,
    			labelSpanXL: 5,
    			labelSpanL: 4,
    			labelSpanM: 4,
    			labelSpanS: {$phoneLabelSpan},
    			emptySpanXL: 0,
    			emptySpanL: 0,
    			emptySpanM: 0,
    			emptySpanS: 0,
                {$properties}
    			singleContainerFullSize: true,
                content: [
                    {$content}
                ],
                {$toolbar}
            }).addStyleClass('{$this->buildCssElementClass()} {$this->buildCssGridClass()}')
            {$this->buildJsPseudoEventHandlers()}
            
JS;
    }
    
    /**
     * Returns TRUE if the form/panel contains active inputs and FALSE otherwise
     * 
     * A UI5-form is marked editable if it contains at least one visible input widget.
     * Non-editable forms are more compact, so it is a good idea only to use editable
     * ones if really editing.
     * 
     * @return bool
     */
    protected function isEditable() : bool
    {
        if ($this->getWidget()->isReadonly()) {
            return false;
        }
        foreach ($this->getWidget()->getInputWidgets() as $input){
            if (! $input->isHidden() && ! $input->isReadOnly()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @param string $content
     * @return string
     */
    protected function buildJsLayoutGrid(string $content)
    {
        return <<<JS

            new sap.ui.layout.Grid({
                height: "100%",
                defaultSpan: "XL4 L4 M6 S12",
                containerQuery: false,
                content: [
                    {$content}
				]
            })
            {$this->buildJsPseudoEventHandlers()}

JS;
    }
                    
    /**
     * Returns the default number of columns to layout this widget.
     *
     * @return integer
     */
    public function getNumberOfColumnsByDefault() : int
    {
        return $this->getFacade()->getConfig()->getOption("WIDGET.PANEL.COLUMNS_BY_DEFAULT");
    }
    
    /**
     * Returns if the the number of columns of this widget depends on the number of columns
     * of the parent layout widget.
     *
     * @return boolean
     */
    public function inheritsNumberOfColumns() : bool
    {
        return true;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryLayoutTrait::buildJsLayouter()
     */
    public function buildJsLayouter() : string
    {
        return '';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::buildCssElementClass()
     */
    public function buildCssElementClass()
    {
        return 'exf-panel';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsPropertyHeight()
     */
    protected function buildJsPropertyHeight() : string
    {
        $widget = $this->getWidget();
        if (! $widget->getHeight()->isUndefined()) {
            return "height: '{$this->getHeight()}',";
        }
        $parent = $widget->getParent();
        if ($parent && ($parent instanceof iContainOtherWidgets)) {
            $visibleSiblingsCnt = $parent->countWidgets(function($child){
               return $child->isHidden() === false; 
            });
            if ($visibleSiblingsCnt > 1) {
                return '';
            }
        }
        return parent::buildJsPropertyHeight();
    }
    
    protected function addCssGridClass(string $className) : UI5Panel
    {
        $this->gridClasses[] = $className;
        return $this;
    }
    
    protected function buildCssGridClass() : string
    {
        return implode(' ', array_unique($this->gridClasses));
    }
}