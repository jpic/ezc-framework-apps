<?php
class aiiPagesView extends ezcMvcView
{
    function createZones( $layout )
    {
        $zones = array();

        $zones[] = new ezcMvcTemplateViewHandler(
            // @todo: agree on namings or we'll end like django apps
            'mainZone', 
            new aiiTemplateLocation( 'page.ezt', $this->request->host )
        );
       
        return $zones;
    }
}
?>
