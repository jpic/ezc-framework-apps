<?php
class aiiCoreRouteCustomBlock implements ezcTemplateCustomBlock {
    public static function getCustomBlockDefinition( $name )
    {
        switch ( $name )
        {
            case "route":
                $def = new ezcTemplateCustomBlockDefinition;
                
                $def->class = __CLASS__;
                $def->method = "reverseRoute";
                
                $def->hasCloseTag = false;
                $def->startExpressionName = "to";
                $def->requiredParameters = array( "to" );
                $def->optionalParameters = array( "with" );

                return $def;
        }

        return false;
    }


    /**
     * Generates the string url for a route and arguments.
     *
     * The "to" string parameter is the route name.
     * The "with" associative array parameter is the route arguments.
     *
     * Example usage:
     * <code>
     * {route "admin_create" with array( 'poClass' => 'yourClass' )}
     * </code>
     * 
     * @param mixed $params array with "to" and eventually "with"
     * @return string Generated url.
     */
    public static function reverseRoute( $params )
    {
        $project = aiiProjectConfiguration::instance( );
        $routes = $project->createRouter( new ezcMvcRequest(  ) )->createRoutes(  );
        $route = $routes[$params["to"]];
        $url = $route->generateUrl( $params["with"] );
        return $url;
    } 
}
