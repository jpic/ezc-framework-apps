<?php
class aiiAdminRouter extends ezcMvcRouter
{
    public function createRoutes()
    {
        return array( 
            new ezcMvcRailsRoute( 
                'list/:poClass',
                'aiiAdminController',
                'list'
            ),
            new ezcMvcRailsRoute( 
                'create/:poClass',
                'aiiAdminController',
                'create'
            ),
            new ezcMvcRailsRoute( 
                'edit/:poClass/:id',
                'aiiAdminController',
                'edit'
            ),
            new ezcMvcRailsRoute( 
                'delete/:poClass/:id',
                'aiiAdminController',
                'delete'
            ),
            new ezcMvcRailsRoute( 
                'details/:poClass/:id',
                'aiiAdminController',
                'details'
            ),
        );
    }
}
