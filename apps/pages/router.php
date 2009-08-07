<?php
class aiiPagesRouter extends ezcMvcRouter
{
    public function createRoutes(  )
    {
        $routes = array(  );

        $routes['pages_read'] = new ezcMvcRailsRoute(
            ':slug', 
            'aiiPagesController',
            'read'
        );

        return $routes;
    }
}
