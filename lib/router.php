<?php
class aiiRouter extends ezcMvcRouter
{
    public function createRoutes()
    {
        return array(
            new ezcMvcRailsRoute( '/downloadTest', 'aiiTestController', 'download' ),
            new ezcMvcRailsRoute( '/:name', 'aiiController', 'greetPersonally' ),
            new ezcMvcRailsRoute( '/', 'aiiController', 'greet' ),
        );
    }
}
?>
