<?php
class aiiTestView extends ezcMvcView
{
    function createZones( $layout )
    {
        $zones = array();
        $zones[] = new ezcMvcPhpViewHandler( 'content', '../templates/downloadTest.php' );
        return $zones;
    }
}
?>
