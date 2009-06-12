<?php
class aiiPagesRouter extends ezcMvcRouter
{
    public function createRoutes(  )
    {
        $routes = array(  );

        $routes[] = new ezcMvcRailsRoute(
            ':slug', 
            'aiiPagesController',
            'read'
        );

        return $routes;
    }
}
