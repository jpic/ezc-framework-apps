<?php
class aiiAdminController extends ezcMvcController
{
    public $_poDef = null;
    public $_schema = null;

    /**
     * Returns true if a table column accepts null values.
     *
     * This is a simple wrapper to the database schema in order to keep 
     * templates more readable.
     * 
     * @param string $columnName
     * @param string $tableName 
     * @return bool False if the table column refuses null values.
     */
    public function isNullProperty( $columnName, $tableName )
    {
        $schema = $this->schema->getSchema();
        
        if ( array_key_exists( $columnName, $schema[$tableName]->fields ) )
        {
            return ! $schema[$tableName]->fields[$columnName]->notNull;
        }
        if ( $this->properties[$columnName]->propertyType == ezcPersistentObjectProperty::PHP_TYPE_OBJECT )
        {
            return true;
        }
        return false;
    }
 
    /**
     * Returns a new aiiPersistentObjectFilterablePropertyList with all
     * properties of the persistent object.
     *
     * @return aiiPersistentObjectFilterablePropertyList
     */
    public function getFilterableList()
    {
        return new aiiPersistentObjectFilterablePropertyList( $this->poDef, ezcDbSchema::createFromDb( ezcDbInstance::get(  ) ) );
    }
    
    /**
     * Returns array( $className, $relationDefinition ) if $columnName is a sourceColumn for a relation, returns false otherwise.
     * 
     * @param mixed $columnName The column name.
     * @return array|bool array( $className, $relationDefinition ) or false.
     */
    public function isFK( $columnName )
    {
        if ( $columnName == $this->poDef->idProperty->columnName )
        {
            return false;
        }

        foreach( $this->poDef->relations as $relatedClassName => $relationDef )
        {
            foreach( $relationDef->columnMap as $map )
            {
                if( $map->destinationColumn == $columnName )
                {
                    return array( $relatedClassName, $relationDef );
                }
            }
        }

        return false;
    }
    
    /**
     * Returns the related class name(s) for $propertyName, or false.
     *
     * @param $propertyName string Name of the property.
     * @return array(string)|bool Names of the related class or false.
     */
    public function getRelatedClasses( $propertyName )
    {
        $classes = array();
        
        foreach( $this->poDef->relations as $name => $def )
        {
            if ( $propertyName === $def->propertyName )
            {
                $classes[] = $name;
            }
            foreach( $def->columnMap as $map )
            {
                if ( $propertyName === $map->sourceColumn )
                {
                    $classes[] = $name;
                }
            }
        }

        return $classes ? $classes : false;
    }
    
    /**
     * Returns the first related class name for $propertyName, or false.
     *
     * Usefull when you're sure to have only one result for a virtual column.
     *
     * @param $propertyName string Name of the property.
     * @return string|bool Name of the related class or false.
     */
    public function getRelatedClass( $propertyName )
    {
        $classes = array();
        
        foreach( $this->poDef->relations as $name => $def )
        {
            if ( $propertyName === $def->propertyName )
            {
                return $name;
            }
            foreach( $def->columnMap as $map )
            {
                if ( $propertyName === $map->sourceColumn )
                {
                    return $name;
                }
            }
        }

        return false;
    }

    public function doDelete()
    {
        $pos = ezcPersistentSessionInstance::get();
        $object = $pos->load( $this->poClass, $this->id );

        $ret = new aiiAdminDeleteResult(  );
        $ret->variables['object'] = $object;
        $ret->variables['deleted'] = false;

        if ( $request->protocol == 'http-post' )
        {
            if ( array_key_exists( 'confirm', $this->request->variables ) )
            {
                $pos->delete( $object );
                $ret->variables['deleted'] = true;
            }
        }

        return $ret;
    }

    public function doList()
    {
        $ret = new aiiAdminListResult;
        $ret->variables['list'] = $this->getList();
        $ret->variables['readableProperties'] = $this->getReadableProperties();
        $ret->variables['readablePropertiesNames'] = array_keys( $ret->variables['readableProperties'] );
        $ret->variables['poClass'] = $this->poClass;
        
        return $ret;
    }

    public function doEdit()
    {
        $pos = ezcPersistentSessionInstance::get();
        $object = $pos->load( $this->poClass, $this->id );

        $form = $this->getForm(  );
        if ( $this->request->protocol == 'http-post' &&
             $form->isValid( $this->request->variables ) )
        {
            foreach( $this->getEditableProperties(  ) as $propertyName => $property )
            {
                $element = $form->getElement( $propertyName );
                $object->$propertyName = $element->getValue(  );
            }

            $pos->update( $object );

            $location = sprintf( '/admin/edit/%s/%s', $this->poClass, $object->id );
            header( "Location: $location" );
            die;
        }
        else
        {
            $form->populate( $object->getState(  ) );
        }

        $ret = new aiiAdminFormResult;
        $ret->variables['form'] = $form;
        $ret->variables['readableProperties'] = $this->getReadableProperties();
        $ret->variables['readablePropertiesNames'] = array_keys( $ret->variables['readableProperties'] );
        $ret->variables['editableProperties'] = $this->getEditableProperties();
        $ret->variables['editablePropertiesName'] = array_keys( $ret->variables['editableProperties'] );
        $ret->variables['poClass'] = $this->poClass;
   
        return $ret;
    }

    public function doCreate()
    {
        $form = $this->getForm(  );

        $object = new $this->poClass;
        if ( $this->request->protocol == 'http-post' &&
             $form->isValid( $this->request->variables ) )
        {
            foreach( $this->getEditableProperties(  ) as $propertyName => $property )
            {
                $element = $form->getElement( $propertyName );
                $object->$propertyName = $element->getValue(  );
            }

            $pos = ezcPersistentSessionInstance::get();
            $pos->save( $object );

            $location = sprintf( '/admin/edit/%s/%s', $this->poClass, $object->id );

            header( "Location: $location" );
            die;
        }

        $ret = new aiiAdminFormResult;
        $ret->variables['form'] = $form;
        $ret->variables['readableProperties'] = $this->getReadableProperties();
        $ret->variables['readablePropertiesNames'] = array_keys( $ret->variables['readableProperties'] );
        $ret->variables['editableProperties'] = $this->getEditableProperties();
        $ret->variables['editablePropertiesName'] = array_keys( $ret->variables['editableProperties'] );
        $ret->variables['poClass'] = $this->poClass;
   
        return $ret;
    }

    public function __get( $name )
    {
        switch( $name )
        {
            case 'poDef':
                if ( is_null( $this->_poDef ) )
                {
                    $pos = ezcPersistentSessionInstance::get();
                    $this->_poDef = $pos->definitionManager->fetchDefinition( $this->poClass );
                }
                return $this->_poDef;
            case 'schema':
                if ( is_null( $this->_schema ) )
                {
                    $this->_schema = ezcDbSchema::createFromDb( ezcDbInstance::get(  ) );
                }
                return $this->_schema;
        }

        return parent::__get( $name );
    }

    public function getList()
    {
        $pos = ezcPersistentSessionInstance::get();
        $q = $pos->createFindQuery( $this->poClass );
        $this->queryHook( $q );
        $list = $pos->find( $q, $this->poClass );
        return $list;
    }

    public function getReadableProperties(  )
    {
        $properties = array();

        foreach( $this->poDef->properties as $property )
        {
            $properties[$property->propertyName] = $property;
        }

        return $properties;
    }

    public function getEditableProperties(  )
    {
        $properties = array();

        foreach( $this->poDef->properties as $property )
        {
            if ( $this->poDef->idProperty->propertyName != $property->propertyName )
            {
                $properties[$property->propertyName] = $property;
            }
        }

        return $properties;
    }

    public function getForm(  )
    {
        $form = new Zend_Form();
        $form->setName( $this->poClass . "_form" );

        $schema = $this->schema->getSchema(  );

        foreach( $this->getEditableProperties() as $propertyName => $property )
        {
            $methodName = 'get' . ucfirst( $propertyName ) . 'Element';
            if ( method_exists( $this, $methodName ) )
            {
                $form->addElement( $this->$methodName() );
                continue;
            }
            
            $dbField = $schema[$this->poDef->table]->fields[$property->columnName];
            $dbType  = $dbField->type;

            switch ( $dbType )
            {
                case 'integer':
                case 'timestamp':
                case 'boolean':
                    $element = new Zend_Form_Element_Text( $propertyName );
                    $element->addValidator( 'allnum' );
                    $element->addFilter( 'int' );
                    break;
                case 'float':
                case 'decimal':
                    $element = new Zend_Form_Element_Text( $propertyName );
                    break;
                case 'text':
                case 'time':
                case 'date':
                case 'blob':
                case 'clob':
                default:
                    $element = new Zend_Form_Element_Text( $propertyName );
                    break;
            }

            if ( list( $relatedClassName, $relationDef ) = $this->isFK( $property->columnName ) )
            {
                $element = new Zend_Form_Element_Select( $propertyName );

                $pos = ezcPersistentSessionInstance::get();
                $q = $pos->createFindQuery( $relatedClassName );
                $this->queryHook( $q );
                $list = $pos->find( $q, $relatedClassName );

                $element->options = $list;
                $element->addFilter( 'int' );
            }
            
            if ( !$this->isNullProperty( $property->columnName, $this->poDef->table ) ) 
            {
                $element->setRequired( true )->addValidator( 'NotEmpty' );
            }

            $element->setLabel( $propertyName );

            $form->addElement( $element );
        }

        $submit = new Zend_Form_Element_Submit( 'submit');
        $submit->setLabel( 'Submit' );
        $form->addElement( $submit );
        $form->clearDecorators();
        $form->addDecorator('FormElements')
         ->addDecorator('HtmlTag', array('tag' => '<ul>'))
         ->addDecorator('Form');
        
        $form->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors'),
            array('Description'),
            array('Label', array('separator'=>' ')),
            array('HtmlTag', array('tag' => 'li', 'class'=>'element-group')),
        ));

        // buttons do not need labels
        $submit->setDecorators(array(
            array('ViewHelper'),
            array('Description'),
            array('HtmlTag', array('tag' => 'li', 'class'=>'submit-group')),
        ));

        $form->setView( new Zend_View( ) );
        return $form;
    }

    public function queryHook( &$q )
    {
    }
}
