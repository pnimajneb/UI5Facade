<?php
namespace exface\UI5Facade\Actions;

use exface\Core\Actions\CustomFacadeScript;
use exface\Core\Interfaces\Facades\FacadeInterface;
use exface\Core\Factories\FacadeFactory;
use exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement;
use exface\UI5Facade\Facades\Elements\UI5AbstractElement;
use exface\Core\CommonLogic\Constants\Icons;

class ScanDocument extends CustomFacadeScript
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Actions\CustomFacadeScript::init()
     */
    protected function init()
    {
        $this->setScriptLanguage('javascript');
        $this->setIcon("sap-icon://pdf-reader");
        $this->setName('Scan');
    }

    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Actions\CustomFacadeScript::getIncludes()
     */
    public function getIncludes(FacadeInterface $facade) : array
    {
        $path = "exface/Core/Facades/AbstractAjaxFacade/js/docScanner";
        $includes = [];
        $includes[] = $facade->buildUrlToVendorFile($path . '/docScanner.js');
        $includes[] = $facade->buildUrlToVendorFile($path . '/jspdf.min.js');
        $includes[] = $facade->buildUrlToVendorFile($path . '/opencv.js');
        return $includes;
    }
    
    /**
     *
     * @see \exface\Core\Interfaces\Actions\iRunFacadeScript::buildScript()
     */
    public function buildScript($widget_id, AbstractJqueryElement $element = null)
    {
        $script = parent::buildScript($widget_id);
        $script .= $this->buildDocScanScript($widget_id, $element);
        return $script;
    }
    
    protected function buildDocScanScript($widget_id, AbstractJqueryElement $element)
    {
        $js = '';
        if ($element instanceof UI5AbstractElement) {
            $elementId = $element->getId();
            $html = '<div style="height: 95%;"><div id="viewSwitch" style="height: 100%;"></div></div>';            
            $component = $element->getController()->buildJsComponentGetter();
            $uploaderId = $elementId . '_uploader';
            $js = <<<JS
var {$elementId}_closeButton = new sap.m.Button({
	icon: "sap-icon://font-awesome/close",
    text: "Schließen",
    press: function() {dialog.close();},
});
var {$elementId}_transformButton = new sap.m.Button({
    icon: "sap-icon://crop",
    enabled: false,
    press: function() {docScanner.transformImage(); {$elementId}_downloadButton.setEnabled(true); {$elementId}_transformButton.setEnabled(false);},
});
var {$elementId}_downloadButton = new sap.m.Button({
    icon: "sap-icon://save",
    enabled: false,
    press: function() {docScanner.downloadPdf();},
});
var {$elementId}_addPageButton = new sap.m.Button({
    icon: "sap-icon://add",
    press: function() { $("#{$uploaderId}-fu").click();},
});
var {$elementId}_resetButton = new sap.m.Button({
    icon: "sap-icon://card",
    enabled: false,
    press: function() {docScanner.reset(); {$elementId}_downloadButton.setEnabled(false); {$elementId}_transformButton.setEnabled(true);},
});
var {$elementId}_rotateRight = new sap.m.Button({
    icon: "sap-icon://font-awesome/repeat",
    enabled: false,
});
var {$elementId}_rotateLeft = new sap.m.Button({
    icon: "sap-icon://font-awesome/undo",
    enabled: false,
});
var {$elementId}_mirror = new sap.m.Button({
    icon: "sap-icon://font-awesome/arrows-h",
    enabled: false,
});
var dialog = {$component}.showDialog('Scanner', new sap.ui.core.HTML().setContent('{$html}'), 'None', function(){}, true);
dialog.addButton({$elementId}_addPageButton);
dialog.addButton({$elementId}_transformButton);
dialog.addButton({$elementId}_rotateLeft);
dialog.addButton({$elementId}_rotateRight);
dialog.addButton({$elementId}_mirror);
dialog.addButton({$elementId}_resetButton);
dialog.addButton({$elementId}_downloadButton);
dialog.addButton({$elementId}_closeButton);

var fileUploader = new sap.ui.unified.FileUploader('$uploaderId');
//attach change handler to fileUploader element
fileUploader.attachChange(function (e) {
    var params = e.getParameters();    
    const files = params.files;
    if (files.length !== 0) {
        docScanner.processImage(files[0])
        {$elementId}_transformButton.setEnabled(true);
        {$elementId}_resetButton.setEnabled(true);
        {$elementId}_downloadButton.setEnabled(false);
    }
    return;
})
fileUploader.addStyleClass('docScannerUploader')
dialog.addContent(fileUploader);
dialog.setContentHeight('80vh').setContentWidth('calc(80vh / 1.4)');
dialog.setVerticalScrolling(false);
dialog.addStyleClass('docScannerDialog');

docScanner.init("viewSwitch");
JS;
    
        }
        return $js;
    }
    
}