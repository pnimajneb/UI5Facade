<?php
namespace exface\UI5Facade\Facades\Elements;
use exface\Core\DataTypes\StringDataType;
use exface\Core\Widgets\Tile;
use exface\Core\Widgets\Tiles;

/**
 * Renders a default container for NavTiles.
 * 
 * @method \exface\Core\Widgets\NavTiles getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5NavTiles extends UI5Container
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    {
        // If the NavTiles is the root widget of a view, it will have a header with the caption
        // of the first tile group - so just hide the caption of that group to avoid duplicates.
        $widget = $this->getWidget();
        if ($widget->hasParent() === false && $widget->hasWidgets()) {
            $widget->getWidgetFirst()->setHideCaption(true);
        }
        if ($widget->isHiddenIfEmpty() && $widget->countWidgetsVisible() === 0) {
            return '';
        }
        return parent::buildJsConstructor($oControllerJs);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\UI5Facade\Facades\Elements\UI5Container::buildJsChildrenConstructors()
     */
    public function buildJsChildrenConstructors() : string
    {
        if ($this->getWidget()->isEmpty()) {
            return <<<JS

            new sap.m.FlexBox({
                height: "100%",
                width: "100%",
                justifyContent: "Center",
                alignItems: "Center",
                items: [
                    new sap.m.Text({
                        text: "{$this->getWidget()->getEmptyText()}"
                    })
                ]
            })

JS;
        }
        if ($this->hasIconTabBar() === true) {
            $navbar = $this->buildJsIconTabBar();
        } else {
            '';
        }
        return $navbar . ', ' . parent::buildJsChildrenConstructors();
    }

    protected function buildJsIconTabBar() : string
    {
        $this->getController()->addOnEventScript($this, 'TabSelect', <<<JS

            // get the selected key
            var sKey = oEvent.getParameter("key");

             // Find the corresponding panel that matches the key dynamically
            var oView = this.getView();
            var oPanel = sap.ui.getCore().byId(sKey);
            if (oPanel && oPanel.getDomRef()) {
                oPanel.getDomRef().scrollIntoView({ behavior: "smooth" });
            }
JS);

        $this->getController()->addOnEventScript($this, 'FilterTiles', <<<JS
            
            // get search query
            const sQuery = oEvent.getParameter("newValue");
        
            // Retrieve all panel IDs and slice to remove the first two IDs
            var aPanelIds = this.getView().findAggregatedObjects(true, function(oControl) {
            return oControl.isA("sap.m.Panel");
            }).map(function(oPanel) {
            return oPanel.getId();
            }).slice(2);
        
            // looping through each panel and filter based on tiles header names
            aPanelIds.forEach(function(sPanelId) {
            // retrieving the panel control based on the ID
            var oPanel = sap.ui.getCore().byId(sPanelId);
            // aTiles is an array of GenericTile controls 
            var aTiles = oPanel.getContent();
            var bAnyTileVisible = false;
        
            aTiles.forEach(function(oTile) {
                // Retrieve the header text of the current GenericTile (oTile)
                var sTileHeader = oTile.getHeader();
                // Retrieve the TileContent aggregation (it's an array)
                var aTileContent = oTile.getTileContent();
                var sContentText = ''; // To hold the content text
                        
                // Loop through the TileContent array
                aTileContent.forEach(function(oTileContent) {
                    var oContent = oTileContent.getContent(); // Get the content (could be FeedContent, Text, etc.)
                            console.log(oContent);
                    if (oContent) {
                        if (oContent.isA("sap.m.FeedContent")) {
                            sContentText = oContent.getContentText(); // Get content text from FeedContent
                        } else if (oContent.isA("sap.m.Text")) {
                            sContentText = oContent.getText(); // Get text if it's a sap.m.Text control
                        } else {
                            console.log("Different content type found:", oContent);
                        }
                    } else {
                        console.log("No content found for this tile content.");
                    }
                });
            
                // Log the retrieved header and content text
                console.log("Tile Header: ", sTileHeader);
                console.log("Tile Content: ", sContentText);
            
                // if the header text or content text contains the search query, set the tile to visible
                var bVisible = !!(sTileHeader.toLowerCase().indexOf(sQuery.toLowerCase()) !== -1 || (sContentText && sContentText.toLowerCase().indexOf(sQuery.toLowerCase()) !== -1));
                
                // If at least one tile is visible, set the panel to visible
                if (bVisible) {
                    bAnyTileVisible = true;
                }
                // Set visibility of the tile
                oTile.setVisible(bVisible);
            });

            oPanel.setVisible(bAnyTileVisible);
            }.bind(this));
            
    JS);

        return <<<JS

        new sap.m.FlexBox("{$this->getId()}_navbox", {
            justifyContent: "Center",    
            backgroundDesign: "Solid",
            items: [
                new sap.m.IconTabHeader("{$this->getId()}_iconTabHeader", {
                    mode: "Inline",
                    select: {$this->getController()->buildJsEventHandler($this, 'TabSelect', true)},
                    items: [
                        {$this->buildJsIconTabBarItems()}
                    ]
                }).addStyleClass('customHeader'),
                new sap.m.SearchField({
                    placeholder: "Search...",
                    liveChange: {$this->getController()->buildJsEventHandler($this, 'FilterTiles', true)},
                    width: "100%"
                }).addStyleClass('')
            ]
        }).addStyleClass('responsiveFlexbox')
JS;
    }

    protected function buildJsIconTabBarItems() : string
    {
        $js = '';
        foreach ($this->getWidget()->getTiles() as $i => $tileGroup) {
            if ($i === 0) {
                $tileGroup->setHidden(false);
                continue;
            }
            $js .= $this->buildJsIconTabBarItem($tileGroup);
        }
        return $js;
    }

    protected function buildJsIconTabBarItem(Tiles $tileGroup) : string
    {
        $tabCaption = StringDataType::substringAfter($tileGroup->getCaption(), ' > ');
        $tabElement = $this->getFacade()->getElement($tileGroup);
        return <<<JS

                new sap.m.IconTabFilter({
                    key: "{$tabElement->getId()}",
                    text: "{$tabCaption}"
                }),
JS;
    }

    protected function hasIconTabBar() : bool
    {
        //return $this->getWidget()->getDepth() > 1;
        return true;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::buildCssElementClass()
     */
    public function buildCssElementClass()
    {
        return 'exf-navtiles' . ($this->isFillingContainer() ? ' exf-panel-no-border' : '');
    }
}