<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryContainerTrait;

/**
 * 
 * @method \exface\Core\Widgets\MapConfigurator getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5MapConfigurator extends UI5DataConfigurator
{

    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataConfigurator::buildJsPanelsConstructors()
     */
    protected function buildJsPanelsConstructors() : string
    {
        return <<<JS
        
                {$this->buildJsTabFilters()}
JS;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataConfigurator::buildJsCreateModel()
     */
    protected function buildJsCreateModel() : string
    {
        return <<<JS
function(){
            var oModel = new sap.ui.model.json.JSONModel();
            var data = {
                "columns": [],
                "sortables": [],
                "sorters": []
            }
            oModel.setData(data);
            return oModel;
        }()
JS;
    }
    
    /**
     *
     * {@inheritdoc}
     * @see JqueryContainerTrait::buildJsResetter()
     */
    public function buildJsResetter() : string
    {
        return $this->buildJsResetModel() . $this->getMapElement()->buildJsRefresh();
    }
    
    /**
     * 
     * @return UI5Map
     */
    protected function getMapElement() : UI5Map
    {
        return $this->getFacade()->getElement($this->getWidget()->getWidgetConfigured());
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataConfigurator::hasTabAdvancedSearch()
     */
    protected function hasTabAdvancedSearch() : bool
    {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataConfigurator::hasTabSorters()
     */
    protected function hasTabSorters() : bool
    {
        return false;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5DataConfigurator::hasTabColumns()
     */
    protected function hasTabColumns() : bool
    {
        return false;
    }
}