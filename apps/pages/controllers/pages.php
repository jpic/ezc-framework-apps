<?php
class aiiPagesController extends ezcMvcController
{
    public function doRead()
    {
        $pos = ezcPersistentSessionInstance::get();
        $q = $pos->createFindQuery( 'aiiPagesClass' );
        
        $where = array(
            $q->expr->eq(
                'slug',
                $q->bindValue( $this->request->variables['slug'] )
            ),
        );

        if ( array_key_exists( 'site', $this->request->variables ) )
        {
            $where[] = $q->expr->eq( 
                'site_id',
                $q->bindValue( $this->request->variables['site']->id )
            );
        }

        call_user_func_array( array( $q, 'where' ), $where );

        $pages = $pos->find( $q, 'aiiPagesClass' );
        $page = current( $pages );

        $ret = new aiiPagesResult;
        $ret->variables['page'] = $page;

        if ( array_key_exists( 'site', $this->request->variables ) )
        {
            $ret->variables['site'] = $this->request->variables['site'];
        }
        return $ret;
    }
}
?>
