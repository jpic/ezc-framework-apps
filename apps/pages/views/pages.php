<?php
class aiiPagesView extends ezcMvcView
{
    function createZones( $layout )
    {
        $zones = array();

        $zones[] = new ezcMvcTemplateViewHandler(
            'page', 
            new aiiTemplateLocation( 'page.ezt', $this->request->host )
        );
       
        return $zones;
    }
}
?>
