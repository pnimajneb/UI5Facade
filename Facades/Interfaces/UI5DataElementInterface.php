<?php
namespace exface\UI5Facade\Facades\Interfaces;

use exface\Core\Facades\AbstractAjaxFacade\Interfaces\AjaxFacadeElementInterface;

interface UI5DataElementInterface
{
    /**
     * 
     * @return bool
     */
    public function hasButtonBack() : bool;
    
    /**
     * 
     * @return bool
     */
    public function hasToolbarTop() : bool;
    
    /**
     * Returns TRUE if the table will be wrapped in a sap.f.DynamicPage to create a Fiori ListReport
     *
     * @return boolean
     */
    public function isWrappedInDynamicPage() : bool;
    
    /**
     * Returns a JS snippet that resolves to TURE if the data was edited (changed) and FALSE otherwise.
     *
     * @param string $oTableJs
     * @return string
     */
    public function buildJsEditableChangesChecker(string $oTableJs = null) : string;
    
    /**
     * Set whether the dynamic page header of this widget should be collapsed or not.
     *
     * @param bool $value
     * @return self
     */
    public function setDynamicPageHeaderCollapsed(bool $value) : AjaxFacadeElementInterface;
    
    /**
     * Setter for whether the toolbar for this page should be displayed or not.
     *
     * @param bool $trueOrFalse
     * @return self
     */
    public function setDynamicPageShowToolbar(bool $trueOrFalse) : AjaxFacadeElementInterface;
}