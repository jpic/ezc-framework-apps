<?php
class aiiAdminRouter extends ezcMvcRouter
{
    public function createRoutes()
    {
        return array( 
            'admin_list' => new ezcMvcRailsRoute( 
                'list/:poClass',
                'aiiAdminController',
                'list'
            ),
            'admin_create' => new ezcMvcRailsRoute( 
                'create/:poClass',
                'aiiAdminController',
                'create'
            ),
            'admin_edit' => new ezcMvcRailsRoute( 
                'edit/:poClass/:id',
                'aiiAdminController',
                'edit'
            ),
            'admin_delete' => new ezcMvcRailsRoute( 
                'delete/:poClass/:id',
                'aiiAdminController',
                'delete'
            ),
            'admin_details' => new ezcMvcRailsRoute( 
                'details/:poClass/:id',
                'aiiAdminController',
                'details'
            ),
        );
    }
}
