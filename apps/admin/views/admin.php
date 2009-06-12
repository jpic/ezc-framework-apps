<?php
class aiiAdminView extends ezcMvcView
{
    public function createZones( $layout )
    {
        $zones = array();
        switch( get_class( $this->result ) )
        {
            case 'aiiAdminListResult':
                $zones[] = new ezcMvcTemplateViewHandler( 
                    'main',
                    new aiiTemplateLocation( 'admin/list.ezt', $this->request->host )
                );
                break;
            case 'aiiAdminFormResult':
                $zones[] = new ezcMvcTemplateViewHandler( 
                    'main',
                    new aiiTemplateLocation( 'admin/create.ezt', $this->request->host )
                );
                break;
            case 'aiiAdminDeleteResult':
                $zones[] = new ezcMvcTemplateViewHandler( 
                    'main',
                    new aiiTemplateLocation( 'admin/delete.ezt', $this->request->host )
                );
                break;
        }

        if ( $layout )
        {
            $zones[] = new ezcMvcTemplateViewHandler( 
                'list',
                new aiiTemplateLocation( 'admin/layout.ezt', $this->request->host )
            );
        }

        return $zones;
    }
}
