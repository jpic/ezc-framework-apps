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


    public static function reverseRoute( $params )
    {
        $project = aiiProjectConfiguration::instance( );
        $routes = $project->createRouter( new ezcMvcRequest(  ) )->createRoutes(  );
        $route = $routes[$params["to"]];
        $url = $route->generateUrl( $params["with"] );
        return $url;
    } 
}
