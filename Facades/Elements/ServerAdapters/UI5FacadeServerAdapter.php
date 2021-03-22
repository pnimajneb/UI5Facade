<?php
namespace exface\UI5Facade\Facades\Elements\ServerAdapters;

use exface\UI5Facade\Facades\Elements\UI5AbstractElement;
use exface\UI5Facade\Facades\Interfaces\UI5ServerAdapterInterface;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Actions\ReadData;
use exface\Core\Actions\ReadPrefill;
use exface\UrlDataConnector\Actions\CallOData2Operation;
use exface\Core\Interfaces\WidgetInterface;
use exface\Core\Interfaces\Widgets\iTriggerAction;
use exface\Core\Interfaces\Actions\iShowDialog;
use exface\Core\Interfaces\Model\MetaObjectInterface;
use exface\Core\Widgets\Dialog;
use exface\Core\Widgets\Button;

class UI5FacadeServerAdapter implements UI5ServerAdapterInterface
{
    private $element = null;
    
    public function __construct(UI5AbstractElement $element)
    {
        $this->element = $element;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ServerAdapterInterface::getElement()
     */
    public function getElement() : UI5AbstractElement
    {
        return $this->element;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Interfaces\UI5ServerAdapterInterface::buildJsServerRequest()
     */
    public function buildJsServerRequest(ActionInterface $action, string $oModelJs, string $oParamsJs, string $onModelLoadedJs, string $onErrorJs = '', string $onOfflineJs = '') : string
    {
        switch (true) {
            case $action instanceof ReadPrefill:
                return $this->buildJsPrefillLoader($oModelJs, $oParamsJs, $onModelLoadedJs, $onErrorJs, $onOfflineJs);
            case $action instanceof ReadData:
                return $this->buildJsDataLoader($oModelJs, $oParamsJs, $onModelLoadedJs, $onErrorJs, $onOfflineJs);
            default:
                return $this->buildJsClickCallServerAction($action, $oModelJs, $oParamsJs, $onModelLoadedJs, $onErrorJs, $onOfflineJs);
        }
    }
    
    /**
     * 
     * @param ActionInterface $action
     * @param MetaObjectInterface $prevLevelObject
     * @param string $prevLevelName
     * @return array
     */
    protected function getEffectedRelations(ActionInterface $action, MetaObjectInterface $prevLevelObject = null, string $prevLevelName = null) : array
    {
        $effects = [];
        $button = $action->isDefinedInWidget() ? $action->getWidgetDefinedIn() : null;
        if ($button) {
            if (! ($name = $button->getCaption())) {
                $name = $action->getName();
            }
            $thisLevelObject = $button->getMetaObject();
            
            if ($thisLevelObject !== $prevLevelObject) {
                if ($prevLevelName && $prevLevelObject && $prevLevelObject !== $button->getMetaObject()) {
                    $name .= ' > ' . $prevLevelName;
                }
                if ($prevLevelObject !== null && $relationFromPrev = $prevLevelObject->findRelation($thisLevelObject, true)) {
                    $effects[] = [
                        'name' => $name,
                        'relation' => $relationFromPrev
                    ];
                }
            }
            /*
             if ($action->getMetaObject() !== $button->getMetaObject() && $action->getMetaObject() !== $prevLevelObject) {
             $thisLevelObject = $action->getMetaObject();
             $effects[] = [
             'object_alias' => $thisLevelObject->getAliasWithNamespace(),
             'object_uid' => $thisLevelObject->getId(),
             'name' => $name
             ];
             }*/
            
            if ($inputWidget = $button->getInputWidget()) {
                if ($inputWidget->getMetaObject() !== $button->getMetaObject() && $inputWidget->getMetaObject() !== $prevLevelObject && $relationFromPrev = $prevLevelObject->findRelation($inputWidget->getMetaObject(), true)) {
                    $effects[] = [
                        'name' => $name,
                        'relation' => $relationFromPrev
                    ];
                }
                if ($inputDialogTrigger = $inputWidget->getParentByClass(Button::class)) {
                    if ($parentAction = $inputDialogTrigger->getAction()) {
                        $effects = array_merge($effects, $this->getEffectedRelations($parentAction, $thisLevelObject, $name));
                    }
                }
            }
        }
        
        return $effects;
    }
    
    /**
     * 
     * @param ActionInterface $action
     * @param string $oRequestParamsJs
     * @param string $aEffectsJs
     * @return string
     */
    protected function buildJsEffects(ActionInterface $action, string $oRequestParamsJs, string $aEffectsJs) : string
    {
        $thisObj = $this->getElement()->getMetaObject();
        $selfEffectName = json_encode($this->getElement()->getWidget()->getCaption());
        $effectsJs .= <<<JS
        
                            aEffects.push({
                                name: {$selfEffectName},
                                effected_object_alias: "{$thisObj->getAliasWithNamespace()}",
                                effected_object_uid: "{$thisObj->getId()}",
                                effected_object_key_alias: "{$thisObj->getUidAttributeAlias()}",
                                key_column: "{$thisObj->getUidAttributeAlias()}",
                                key_values: function(){
                                    return ({$oRequestParamsJs}.data.rows || []).map(function(row,index) {
                                        return row['{$thisObj->getUidAttributeAlias()}'];
                                    })
                                }()
                            });
JS;
        foreach ($this->getEffectedRelations($action, $thisObj) as $effect) {
            $effectedRel = $effect['relation'];
            $effectedObj = $effectedRel->getRightObject();
            $effectedKeyDataCol = $effectedRel->getLeftKeyAttribute()->getAlias();
            $effectsJs .= <<<JS
            
                            aEffects.push({
                                name: "{$effect['name']}",
                                effected_object_alias: "{$effectedObj->getAliasWithNamespace()}",
                                effected_object_uid: "{$effectedObj->getId()}",
                                effected_object_key_alias: "{$effectedRel->getRightKeyAttribute()->getAlias()}",
                                key_column: "{$effectedKeyDataCol}",
                                key_values: function(){
                                    return ({$oRequestParamsJs}.data.rows || []).map(function(row,index) {
                                        return row['{$effectedKeyDataCol}'];
                                    })
                                }()
                            });
JS;
        }
        
        return $effectsJs;
    }
    
    protected function buildJsClickCallServerAction(ActionInterface $action, string $oModelJs, string $oParamsJs, string $onModelLoadedJs, string $onErrorJs = '', string $onOfflineJs = '') : string
    {
        $headers = ! empty($this->getElement()->getAjaxHeaders()) ? 'headers: ' . json_encode($this->getElement()->getAjaxHeaders()) . ',' : '';        
        $controller = $this->getElement()->getController();
        
        $actionName = $this->getElement()->getWidget()->getCaption();
        if ($actionName === null || $actionName === '') {
            $actionName = $action->getName();
        }
        $actionNameJs = json_encode($actionName);
        $objectNameJs = json_encode($action->getMetaObject()->getName());
        
        $coreTranslator = $this->getElement()->getWorkbench()->getCoreApp()->getTranslator();
        
        return <<<JS

                            var aEffects = [];
                            {$this->buildJsEffects($action, $oParamsJs, 'aEffects')};

							$oParamsJs.webapp = '{$this->getElement()->getFacade()->getWebapp()->getRootPage()->getAliasWithNamespace()}';
                            var oComponent = {$controller->buildJsComponentGetter()};                
                            if (! navigator.onLine) {
                                if (exfPreloader) {
                                    var actionParams = {
                                        type: 'POST',
        								url: '{$this->getElement()->getAjaxUrl()}',
                                        {$headers}
        								data: {$oParamsJs}
                                    };                          
                                    exfPreloader.addAction(
                                        actionParams, 
                                        '{$action->getMetaObject()->getAliasWithNamespace()}',
                                        {$actionNameJs},
                                        {$objectNameJs},
                                        aEffects
                                    )
                                    .then(function(key) {
                                        var response = {success: '{$coreTranslator->translate('OFFLINE.ACTIONS.ACTION_QUEUED')}'};
                                        $oModelJs.setData(response);
                                        $onModelLoadedJs
                                    })
                                    .catch(function(error) {
                                        console.error(error);
                                        var response = {error: '{$coreTranslator->translate('OFFLINE.ACTIONS.ACTION_QUEUE_FAILED')}'}
                                        {$this->getElement()->buildJsShowMessageError('response.error', '"Server error"')}
                                        {$onErrorJs}
                                    })
                                    oComponent.getPreloader().updateQueueCount();
                                } else {
                                    {$onOfflineJs}
                                }
                                return $oModelJs;
                            } else {
                                return $.ajax({
    								type: 'POST',
    								url: '{$this->getElement()->getAjaxUrl()}',
                                    {$headers}
    								data: {$oParamsJs},
    								success: function(data, textStatus, jqXHR) {
                                        if (typeof data === 'object') {
                                            response = data;
                                        } else {
                                            var response = {};
        									try {
        										response = $.parseJSON(data);
        									} catch (e) {
        										response.error = data;
        									}
                                        }
    				                   	if (response.success){
                                            $oModelJs.setData(response);
    										{$onModelLoadedJs}
    				                    } else {
    										{$this->getElement()->buildJsShowMessageError('response.error', '"Server error"')}
                                            {$onErrorJs}
    				                    }
    								},
    								error: function(jqXHR, textStatus, errorThrown){
                                        {$onErrorJs}
                                        if (navigator.onLine === false) {
                                            {$onOfflineJs}
                                        } else {
                                            {$this->getElement()->getController()->buildJsComponentGetter()}.showAjaxErrorDialog(jqXHR)
                                        }
    								}
    							})
                                .then(function(){
                                    return $oModelJs;
                                });
                            }
                                        
JS;
    }
    
    protected function buildJsDataLoader(string $oModelJs, string $oParamsJs, string $onModelLoadedJs, string $onErrorJs = '', string $onOfflineJs = '') : string
    {
        $headers = ! empty($this->getElement()->getAjaxHeaders()) ? 'headers: ' . json_encode($this->getElement()->getAjaxHeaders()) . ',' : '';
        
        return <<<JS
                
                $oParamsJs.webapp = '{$this->getElement()->getFacade()->getWebapp()->getRootPage()->getAliasWithNamespace()}';                

                return $.ajax({
					type: 'GET',
					url: '{$this->getElement()->getAjaxUrl()}',
                    {$headers}
					data: {$oParamsJs},
					success: function(data, textStatus, jqXHR) {
                        if (typeof data === 'object') {
                            response = data;
                        } else {
                            var response = {};
							try {
								response = $.parseJSON(data);
							} catch (e) {
								response.error = data;
							}
                        }
	                   	if (response.success){
                            $oModelJs.setData(response);
							{$onModelLoadedJs}
	                    } else {
							if (navigator.onLine === false) {
                                if (oData.length = 0) {
                                    {$onOfflineJs}
                                } else {
                                    {$this->getElement()->getController()->buildJsComponentGetter()}.showDialog('{$this->getElement()->translate('WIDGET.DATATABLE.OFFLINE_ERROR')}', '{$this->getElement()->translate('WIDGET.DATATABLE.OFFLINE_ERROR_TITLE')}', 'Error');
                                }
                            } else {
                                {$this->getElement()->buildJsShowError('jqXHR.responseText', "(jqXHR.statusCode+' '+jqXHR.statusText)")}
                            }
                            {$onErrorJs}
	                    }
					},
					error: function(jqXHR, textStatus, errorThrown){
                        {$onErrorJs}
                        if (navigator.onLine === false) {
                            {$onOfflineJs}
                        } else {
                            {$this->getElement()->getController()->buildJsComponentGetter()}.showAjaxErrorDialog(jqXHR)
                        }
					}
				})
                .then(function(){
                    return $oModelJs;
                });
                
JS;
    }
    
    protected function buildJsPrefillLoader(string $oModelJs, string $oParamsJs, string $onModelLoadedJs, string $onErrorJs = '', string $onOfflineJs = '') : string
    {
        return <<<JS
        
            $oParamsJs.webapp = '{$this->getElement()->getFacade()->getWebapp()->getRootPage()->getAliasWithNamespace()}';                
            
            return $.ajax({
                url: "{$this->getElement()->getAjaxUrl()}",
                type: "GET",
				data: {$oParamsJs},
                success: function(response, textStatus, jqXHR) {
                    var oPrefillRow = {};
                    if (Object.keys({$oModelJs}.getData()).length !== 0) {
                        {$oModelJs}.setData({});
                    }
                    if (Array.isArray(response.rows) && response.rows.length > 0) {
                        if (response.rows.length > 1) {
                            console.warn("Ambiguos view prefill: received " + response.rows.length + " rows instead of 0 or 1! Only the first data row is visible.");
                            response.rows.forEach(function(oRow) {
                                for (var sCol in oRow) {
                                    switch (true) {
                                        case oPrefillRow[sCol] == oRow[sCol]:
                                            break;
                                        case oPrefillRow[sCol] === undefined || oPrefillRow[sCol] === '':
                                            oPrefillRow[sCol] = oRow[sCol];
                                            break;
                                        default: 
                                            oPrefillRow[sCol] += ',' + oRow[sCol];
                                    }
                                }
                            });
                            
                        } else {
                            oPrefillRow = response.rows[0];
                        }

                        {$oModelJs}.setData(oPrefillRow);
                    }
                    {$onModelLoadedJs}
                },
                error: function(jqXHR, textStatus, errorThrown){
                    {$onErrorJs}
                    if (navigator.onLine === false) {
                        {$onOfflineJs}
                    } else {
                        {$this->getElement()->getController()->buildJsComponentGetter()}.showAjaxErrorDialog(jqXHR)
                    }
                }
			})
            .then(function(){
                return $oModelJs;
            })
JS;
    }
}