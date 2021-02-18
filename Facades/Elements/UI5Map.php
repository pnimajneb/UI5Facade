<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\LeafletTrait;
use exface\UI5Facade\Facades\Elements\Traits\UI5DataElementTrait;
use exface\Core\Widgets\Data;
use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;
use exface\Core\DataTypes\StringDataType;

/**
 * 
 * @method exface\Core\Widgets\Chart getWidget()
 * @method UI5ControllerInterface getController()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5Map extends UI5AbstractElement
{
    use LeafletTrait, UI5DataElementTrait {
        buildJsConfiguratorButtonConstructor as buildJsConfiguratorButtonConstructorViaTrait;
        buildJsDataLoaderOnLoaded as buildJsDataLoaderOnLoadedViaTrait;
        //LeafletTrait::buildJsDataResetter insteadof UI5DataElementTrait;
        LeafletTrait::buildJsValueGetter insteadof UI5DataElementTrait;
        LeafletTrait::buildJsDataGetter insteadof UI5DataElementTrait;
        UI5DataElementTrait::buildJsGetSelectedRows insteadof LeafletTrait;
    }
    
    private $leafletVarTemp = null;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructorForControl($oControllerJs = 'oController') : string
    {
        $this->initLeaflet();
        
        $this->getConfiguratorElement()->registerFiltersWithApplyOnChange($this);
        
        $controller = $this->getController(); 
        $this->registerExternalModules($controller);
        
        $this->leafletVarTemp = 'oController.' . $controller->buildJsObjectName('leaflet', $this);
        $chart = <<<JS

                new sap.ui.core.HTML("{$this->getId()}", {
                    content: "<div id=\"{$this->getIdLeaflet()}\" class=\"exf-chart\" style=\"height:100%; min-height: 100px; overflow: hidden;\"></div>",
                    afterRendering: function(oEvent) {   
                        {$this->buildJsLeafletInit()};                     

                        sap.ui.core.ResizeHandler.register(sap.ui.getCore().byId('{$this->getId()}').getParent(), function(){
                            {$this->buildJsLeafletResize()}
                        });
                    }
                })

JS;
        $this->leafletVarTemp = null;
                        
        return $this->buildJsPanelWrapper($chart, $oControllerJs);
    }
    
    protected function buildJsLeafletDataLoader(string $oRequestParamsJs, string $aResultRowsJs, string $onLoadedJs) : string
    {
        return <<<JS

        {$this->getController()->buildJsMethodCallFromController('onLoadData', $this, '')}
        .then(function(oModel) {
            var $aResultRowsJs = oModel.getData().rows || [];

            $onLoadedJs
        })

JS;
    }
    
    public function getIdLeaflet() : string
    {
        return $this->getId() . '_leaflet';
    }
    
    public function buildJsLeafletVar(string $oControllerJs = null) : string
    {
        if ($this->leafletVarTemp !== null) {
            return $this->leafletVarTemp;
        }
        $controller = $this->getController();
        if (! $controller->hasDependent('leaflet', $this)) {
            $controller->addProperty($controller->buildJsObjectName('leaflet', $this), 'null');
        }
        return $controller->buildJsDependentObjectGetter('leaflet', $this, $oControllerJs);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::registerExternalModules()
     */
    public function registerExternalModules(UI5ControllerInterface $controller) : UI5AbstractElement
    {
        $f = $this->getFacade();
        $mainSrc = $f->buildUrlToSource('LIBS.LEAFLET.JS');
        $controller->addExternalModule('libs.exface.leaflet', $mainSrc, null, 'L');
        
        foreach ($this->getJsIncludes() as $src) {
            if ($src === $mainSrc) {
                continue;
            }
            $name = StringDataType::substringAfter($src, '/', $src, false, true);
            $name = str_replace('-', '_', $name);
            
            $name = 'libs.exface.leaflet.' . $name;
            $controller->addExternalModule($name, $src);
        }
        
        foreach ($this->getCssIncludes() as $src) {
            $controller->addExternalCss($src);
        }
        
        foreach ($this->getWidget()->getDataLayers() as $layer) {
            foreach ($layer->getDataWidget()->getColumns() as $col) {
                $f->getElement($col)->registerExternalModules($controller);
            }
        }
        return $this;
    }
        
    /**
     * 
     * @return array
     */
    protected function getJsIncludes() : array
    {
        $htmlTagsArray = $this->buildHtmlHeadTagsLeaflet();
        $tags = implode('', $htmlTagsArray);
        $jsTags = [];
        preg_match_all('#<script[^>]*src="([^"]*)"[^>]*></script>#is', $tags, $jsTags);
        return $jsTags[1];
    }
    
    /**
     *
     * @return array
     */
    protected function getCssIncludes() : array
    {
        $htmlTagsArray = $this->buildHtmlHeadTagsLeaflet();
        $tags = implode('', $htmlTagsArray);
        $jsTags = [];
        preg_match_all('#<link[^>]*href="([^"]*)"[^>]*/?>#is', $tags, $jsTags);
        return $jsTags[1];
    }
        
    /**
     *
     * {@inheritDoc}
     * @see exface\Core\Facades\AbstractAjaxFacade\Elements\LeafletTrait::buildJsRefresh()
     */
    public function buildJsRefresh() : string
    {
        return $this->buildJsLeafletRefresh();
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsQuickSearchConstructor() : string
    {
        return <<<JS

                    new sap.m.OverflowToolbarButton({
                        icon: "sap-icon://refresh",
                        press: {$this->getController()->buildJsMethodCallFromView('onLoadData', $this)}
                    })

JS;
    }
    
    /**
     * 
     * @see UI5DataElementTrait
     */
    protected function getDataWidget() : Data
    {
        return $this->getWidget()->getDataLayers()[0]->getDataWidget();
    }
    
    /**
     * {@inheritdoc}
     * @see \exface\UI5Facade\Facades\Elements\Traits\UI5DataElementTrait::buildJsShowMessageOverlay()
     */
    protected function buildJsShowMessageOverlay(string $message) : string
    {
        return '';
    }
    
    /**
     *
     * {@inheritdoc}
     * @see UI5DataElementTrait::buildJsOfflineHint()
     */
    protected function buildJsOfflineHint(string $oTableJs = 'oTable') : string
    {
        return '';
    }
    
    /**
     * 
     * @see UI5DataElementTrait::isEditable()
     */
    protected function isEditable()
    {
        return false;
    }
    
    /**
     * 
     * @see UI5DataElementTrait::buildJsGetSelectedRows()
     */
    protected function buildJsGetSelectedRows(string $oControlJs) : string
    {
        return $this->buildJsLeafletGetSelectedRows();
    }
    
    protected function hasPaginator() : bool
    {
        return false;
    }
}
