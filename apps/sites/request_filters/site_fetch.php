<?php
class aiiSitesFetchRequestFilter
{
    public function runPreRoutingFilters( ezcMvcRequest $request )
    {
        $pos = ezcPersistentSessionInstance::get();
        $q = $pos->createFindQuery( 'aiiSitesClass' );
        $q->where( 
            $q->expr->eq(
                'host',
                $q->bindValue( $request->host )
            )
        );

        $sites = $pos->find( $q, 'aiiSitesClass' );

        return current( $sites );
    }
}
