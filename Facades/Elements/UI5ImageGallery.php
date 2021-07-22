<?php
namespace exface\UI5Facade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\SlickGalleryTrait;
use exface\UI5Facade\Facades\Elements\Traits\UI5DataElementTrait;
use exface\UI5Facade\Facades\Interfaces\UI5ControllerInterface;
use exface\Core\DataTypes\StringDataType;
use exface\Core\Facades\AbstractAjaxFacade\Formatters\JsDateFormatter;
use exface\Core\Factories\DataTypeFactory;
use exface\Core\DataTypes\DateTimeDataType;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JsUploaderTrait;
use exface\Core\CommonLogic\DataSheets\DataColumn;
use exface\Core\Widgets\Parts\Uploader;

/**
 * 
 * @method exface\Core\Widgets\Chart getWidget()
 * @method UI5ControllerInterface getController()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5ImageGallery extends UI5AbstractElement
{
    use SlickGalleryTrait, UI5DataElementTrait {
        SlickGalleryTrait::buildJsValueGetter insteadof UI5DataElementTrait;
        SlickGalleryTrait::buildJsDataGetter insteadof UI5DataElementTrait;
        SlickGalleryTrait::buildJsDataResetter insteadof UI5DataElementTrait;
        UI5DataElementTrait::buildJsDataLoaderOnLoaded as buildJsDataLoaderOnLoadedViaTrait;
    }
    
    use JsUploaderTrait;
    
    /**
     * 
     * @param string $oControllerJs
     * @return string
     */
    public function buildJsConstructorForControl($oControllerJs = 'oController') : string
    {
        $this->getConfiguratorElement()->registerFiltersWithApplyOnChange($this);
        $this->addCarouselFeatureButtons($this->getWidget()->getToolbarMain()->getButtonGroup(0), 1);
        
        $controller = $this->getController(); 
        $this->registerExternalModules($controller);
        
        // **IMPORTANT:** it seems, an outer div with the id of the control is required because
        // otherwise the map is not rendered at all after navigating to a view via routing. The map
        // gets rendered even before the view is shown, but once the view is visible, the leaflet-div
        // is empty while the leaflet-var is initialized, which is very strange.
        // I guess, this has something to do with the so-called "preserved content" of sap.ui.core.HTML 
        // (see for an explanation for possible causes: https://github.com/SAP/openui5/issues/1162).
        $chart = <<<JS

                new sap.ui.core.HTML("{$this->getId()}", {
                    content: "<div id=\"{$this->getId()}\" style=\"height: calc({$this->getHeight()} - 2.75rem - 2px);\"><div id=\"{$this->getIdOfSlick()}\" class=\"slick-carousel horizontal\" style=\"height: 100%\"></div></div>",
                    afterRendering: function(oEvent) { 

                        sap.ui.core.ResizeHandler.register(sap.ui.getCore().byId('{$this->getId()}').getParent(), function(){
                            //TODO
                        });

                        {$this->buildJsSlickInit()}

                        sap.ui.getCore().byId('{$this->getId()}').getParent().addStyleClass('sapUiNoContentPadding');
                    }
                })

JS;
                        
        return $this->buildJsPanelWrapper($chart, $oControllerJs);
    }
    
    public function getIdOfSlick() : string
    {
        return $this->getId() . '_slick';
    }
    
    protected function buildJsDataLoaderOnLoaded(string $oModelJs = 'oModel') : string
    {
        return $this->buildJsDataLoaderOnLoadedViaTrait($oModelJs) . <<<JS

                var carousel = $('#{$this->getIdOfSlick()}');
                    
                {$this->buildJsSlickSlidesFromData('carousel', 'oModel.getData()')}

                {$this->buildJsUploaderInit('carousel')}

JS;
    }
    
    protected function buildJsUploadSend(string $oParamsJs, string $onUploadCompleteJs) : string
    {
        $uploader = $this->getUploader();
        $uploadAction = $uploader->getInstantUploadAction();
        $onUploadCompleteJs = <<<JS
        
            if (oUploadModel.getProperty('/success') !== undefined){
           		{$this->buildJsShowMessageSuccess("oUploadModel.getProperty('/success')")}
			}

            {$onUploadCompleteJs}
            
JS;
        return <<<JS

                var oUploadModel = new sap.ui.model.json.JSONModel();
                oUploadModel.setData($oParamsJs.data);
                {$this->buildJsDataLoaderOnLoadedHandleWidgetLinks('oUploadModel')}
                {$this->getServerAdapter()->buildJsServerRequest($uploadAction, 'oUploadModel', 'oParams', $onUploadCompleteJs, $onUploadCompleteJs)}

JS;
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
            
            $name = 'libs.exface.slick.' . $name;
            $controller->addExternalModule($name, $src);
        }
        
        foreach ($this->getCssIncludes() as $src) {
            $controller->addExternalCss($src);
        }
        
        return $this;
    }
        
    /**
     * 
     * @return array
     */
    protected function getJsIncludes() : array
    {
        $htmlTagsArray = $this->buildHtmlHeadTagsSlick();
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
        $htmlTagsArray = $this->buildHtmlHeadTagsSlick();
        $tags = implode('', $htmlTagsArray);
        $jsTags = [];
        preg_match_all('#<link[^>]*href="([^"]*)"[^>]*/?>#is', $tags, $jsTags);
        return $jsTags[1];
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
        return ''; // TODO
    }
    
    /**
     * @see UI5DataElementTrait
     */
    protected function hasPaginator() : bool
    {
        return false;
    }
    
    /**
     *
     * @see JsUploaderTrait::getUploader()
     */
    protected function getUploader() : Uploader
    {
        return $this->getWidget()->getUploader();
    }
    
    /**
     * Makes a slick carousel have a default height of 6
     *
     * @see AbstractJqueryElement::buildCssHeightDefaultValue()
     */
    protected function buildCssHeightDefaultValue()
    {
        return $this->getWidget()->isHorizontal() ? ($this->getHeightRelativeUnit() * 8) . 'px' : '100%';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5AbstractElement::getNeedsContainerContentPadding()
     */
    public function getNeedsContainerContentPadding() : bool
    {
        return false;
    }
}