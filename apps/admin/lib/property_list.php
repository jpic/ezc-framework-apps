<?php
/**
 * File holding class aiiPersistentObjectFilterablePropertyList.
 * 
 * @package PersistentObjectDatabaseSchemaTiein
 * @version //autogen//
 * @copyright Copyright (C) 2008 James Pic. All rights reserved.
 * @author James Pic 
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Class allowing to apply various filters on a persistent object property list.
 *
 * Objects of this classes are intended to be use in an emepherical way in templates.
 * Such an object has all the properties of a class when instanciated, *all* means :
 * - id property,
 * - *table* properties,
 * - *virtual* properties.
 * *Table* properties have a column in the table, using the persistent
 * object property propertyName.
 * *Virtual* properties are properties resulting of a relation,
 * using the relation's propertyName.
 * 
 * This object extends ArrayObject :
 * - it's countable : count( $this ) will return the total number of properties.
 * - it's accessible like an array : $this[$propertyName] returns the definition of $propertyName.
 * - it's iteratable using foreach, which will traverse this properties.
 *
 * This object provides various filters that will make the list to contain
 * just the properties required in a template; making the template way more readable than
 * by reading the database schema or the persistent object definition directly.
 *
 * A list of examples of filter's usage :
 * <code>
 * <?php
 * $list = new aiiPersistentObjectFilterablePropertyList( $persistentDefinition, $databaseSchema );
 *
 * // In templates, it's better to use the factory method of aiiPersistentObjectDatabaseSchemaTool
 * // allowing to get such an instance without arguments :
 * $list = $tool->getFilterableList();
 *
 * // Remove all properties expecting a string value :
 * $list->filterType( ezcPersistentObjectProperty::PHP_TYPE_STRING );
 *
 * // Remove all properties *not* expecting a string value :
 * $list->filterType( ezcPersistentObjectProperty::PHP_TYPE_STRING, false );
 *
 * // Remove any property which column is used as sourceColumn for the
 * // columnMap of a ManyToOne or OneToOne relation :
 * $list->filterRelationsColumns( 'ToOne' );
 *
 * // Remove any property which column is *not* used as sourceColumn
 * // for the columnMap of a ManyToOne relation :
 * $list->filterRelationsColumns( 'ManyToOne', false );
 *
 * // Remove all *virtual* properties :
 * $list->filterRelationsVirtual();
 *
 * // Remove all properties that are not *virtual*, aka mapped to a table
 * // column :
 * $list->filterRelationsVirtual( 'any', false );
 *
 * // Keep only the properties that are *virtual* and which are created
 * // because of a ManyToMany or OneToMany relation :
 * $list->filterRelationsVirtual( 'ToMany', false );

 * // Remove all properties that are used in any relation :
 * $list->filterRelationsAll();
 *
 * // Keep all properties involved in a OneToOne or ManyToOne relation
 * // and remove all other :
 * $list->filterRelationsAll( 'ToOne', false );
 *
 * // Remove all properties accepting a null value :
 * $list->filterNull();
 *
 * // Remove all properties that don't accept a null value :
 * $list->filterNull( false );
 *
 * // Remove the id-property :
 * $list->filterIdProperty();
 *
 * // Make sure that the id-property is in the list :
 * $list->restoreIdProperty();
 *
 * // At this point, count( $list ) === 0 of course.
 * ?>
 * </code>
 *
 * Are are some real-world examples, extracted from templates/class.php :
 * <code>
 * <?php
 * if ( $strings =(array) $tool->getFilterableList()->filterType( ezcPersistentObjectProperty::PHP_TYPE_STRING, false )->filterNull( false )->filterRelationsColumns() ):
 *     foreach ( $strings as $name => $def ):
 * ?>
 *             case '<?php echo $name; ?>':
 * <?php
 *     endforeach;
 * ?>
 *                 if ( !is_string( $propertyValue ) && !is_null( $propertyValue ) )
 *                 {
 *                     throw new ezcBaseValueException($propertyName, $propertyValue, 'Strings');
 *                 }
 *                 return $this->state[$propertyName] = $propertyValue;
 * <?php
 * endif;
 * if ( $floats = (array) $tool->getFilterableList()->filterType( ezcPersistentObjectProperty::PHP_TYPE_FLOAT, false )->filterNull()->filterRelationsColumns() ):
 *     foreach ( $floats as $name => $def ):
 * ?>
 *             case '<?php echo $name; ?>':
 * <?php
 *     endforeach;
 * ?>
 *                 if ( !is_float( $propertyValue ) )
 *                 {
 *                     throw new ezcBaseValueException($propertyName, $propertyValue, 'Floats');
 *                 }
 *                 return $this->state[$propertyName] = $propertyValue;
 * <?php
 * endif;
 * ?>
 * </code>
 *
 * @uses ArrayObject
 * @package PersistentObjectDatabaseSchemaTiein
 * @todo Continue template factorization with this.
 */
class aiiPersistentObjectFilterablePropertyList extends ArrayObject
{
    /**
     * Persistent object's properties.
     * 
     * @var array(string=>ezcPersistentObjectProperty)
     */
    protected $properties = array();

    /**
     * Database schema.
     * 
     * @var ezcDbSchema
     */
    protected $schema = null;

    /**
     * Persistent object definition. 
     * 
     * @var ezcPersistentObjectDefinition
     */
    protected $def = null;

    /**
     * Instanciate a property-list object with the given definition.
     *
     * @param ezcPersistentObjectDefinition The definition to use.
     * @return aiiPersistentObjectFilterablePropertyList
     */
    public function __construct( ezcPersistentObjectDefinition $def,  ezcDbSchema $schema )
    {
        $this->schema = $schema;
        $this->def    = $def;

        // Add idProperty
        $this->properties[$def->idProperty->propertyName] = $def->idProperty;

        // Add columns
        foreach( $this->def->properties as $name => $def )
        {
            $this->properties[$name] = $def;
        }

        // Add virtual columns
        foreach( $this->def->relations as $name => $def )
        {
            switch( get_class( $def ) )
            {
                case 'ezcPersistentManyToManyRelation':
                case 'ezcPersistentOneToManyRelation':
                    $this->properties[$def->propertyName] = new ezcPersistentObjectProperty();
                    $this->properties[$def->propertyName]->columnName = '';
                    $this->properties[$def->propertyName]->propertyName = $def->propertyName;
                    $this->properties[$def->propertyName]->propertyType = ezcPersistentObjectProperty::PHP_TYPE_ARRAY;
                    break;
                case 'ezcPersistentOneToOneRelation':
                case 'ezcPersistentManyToOneRelation':
                    $this->properties[$def->propertyName] = new ezcPersistentObjectProperty();
                    $this->properties[$def->propertyName]->columnName = '';
                    $this->properties[$def->propertyName]->propertyName = $def->propertyName;
                    $this->properties[$def->propertyName]->propertyType = ezcPersistentObjectProperty::PHP_TYPE_OBJECT;
                    break;
            }
        }

        parent::__construct( $this->properties );
    }

    /**
     * Filters properties of the given type from the list.
     *
     * The $out parameter specifies if the filtered properties should be
     * deleted from the list, or if it should be the only properties left.
     * If $out is *true*, the filtered properties are removed from the list
     * and if it's false, all the non-filtered properties are removed.    
     *
     * This method filter properties by type, for example :
     * <code>
     * <?php
     * // Remove all properties expecting a string value :
     * $list->filterType( ezcPersistentObjectProperty::PHP_TYPE_STRING );
     *
     * // Remove all properties *not* expecting a string value :
     * $list->filterType( ezcPersistentObjectProperty::PHP_TYPE_STRING, false );
     *
     * // At this point, count( $list ) === 0 of course.
     * ?>
     * </code>
     *
     * It's not possible to make this method to exclude the id-property from
     * the filter, the method restoreIdProperty() should be used after 
     * applying the filter in that case.
     *
     * @param $type const Any of the constants of
     *                    ezcPersistentObjectProperty : PHP_TYPE_*.
     * @param $out  bool  True to filter out the properties of the given 
     *                    type from the list; false to remove all the 
     *                    properties of any other type.
     * @return aiiPersistentObjectFilterablePropertyList
     */
    public function filterType( $type, $out = true )
    {
        foreach( $this->properties as $name => $propertyDef )
        {
            if ( ( $propertyDef->propertyType == $type && $out ) || ( $propertyDef->propertyType != $type && !$out ) )
            {
                if ( array_key_exists( $name, $this ) )
                {
                    unset( $this[$name] );
                }
            }
        }

        return $this;
    }

    /**
     * Filters *table* properties which column is used as sourceColumn in a relation.
     *
     * This method filters properties which have columns used as *sourceColumn*
     * in a relation, which means that it can only filter *table* properties and
     * not *virtual* properties.
     *
     * The $type argument refers to the relation-type to filter, it's matched
     * against the relation definition class name, which means that any of these
     * values are valid :
     * - 'any'
     * - 'ManyToMany'
     * - 'ManyTo'
     * - 'ToOne'
     * - ...
     * The special type 'any' allows to filter *any* kind of relation.
     *
     * The $out parameter specifies if the filtered properties should be
     * deleted from the list, or if it should be the only properties left.
     * If $out is *true*, the filtered properties are removed from the list
     * and if it's false, all the non-filtered properties are removed.
     *
     * It's not possible to make this method to exclude the id-property from
     * the filter, the method restoreIdProperty() should be used after 
     * applying the filter in that case.
     *
     * <code>
     * <?php
     * // Remove any property which column is used as sourceColumn for the
     * // columnMap of a ManyToOne or OneToOne relation :
     * $list->filterRelationsColumns( 'ToOne' );
     *
     * // Remove any property which column is *not* used as sourceColumn
     * // for the columnMap of a ManyToOne relation :
     * $list->filterRelationsColumns( 'ManyToOne', false );
     * ?>
     * </code>
     *
     * @param string $type The type of the relations to filter source columns.
     * @param $out bool True to filter out the relation properties
     *                  from the list; false to remove all other.
     * @return aiiPersistentObjectFilterablePropertyList
     */
    public function filterRelationsColumns( $type = 'any', $out = true )
    {
        $relationProperties = array();
        foreach ( $this->def->relations as $name => $def )
        {
            if ( $type === 'any' || stripos( get_class( $def ), $type ) !== false )
            {
                foreach( $def->columnMap as $map )
                {
                    $relationProperties[] = $map->sourceColumn;
                }
            }
        }

        foreach ( $this->properties as $name => $def )
        {
            if ( $out === in_array( $name, $relationProperties ) )
            {
                if ( array_key_exists( $name, $this ) )
                {
                    unset( $this[$name] );
                }
            }
        }

        return $this;
    }

    /**
     * Filters properties that do *not* have a column in the table : *virtual* properties.
     *
     * This method filters *virtual* properties : those that hold one or
     * several related objects.
     *
     * The $type argument refers to the relation-type to filter, it's matched
     * against the relation definition class name, which means that any of these
     * values are valid :
     * - 'any'
     * - 'ManyToMany'
     * - 'ManyTo'
     * - 'ToOne'
     * - ...
     * The special type 'any' allows to filter *any* kind of relation.
     *
     * The $out parameter specifies if the filtered properties should be
     * deleted from the list, or if it should be the only properties left.
     * If $out is *true*, the filtered properties are removed from the list
     * and if it's false, all the non-filtered properties are removed.
     *
     * <code>
     * <?php
     * // Remove all *virtual* properties :
     * $list->filterRelationsVirtual();
     *
     * // Remove all properties that are not *virtual*, aka mapped to a table
     * // column :
     * $list->filterRelationsVirtual( 'any', false );
     *
     * // Keep only the properties that are *virtual* and which are created
     * // because of a ManyToMany or OneToMany relation :
     * $list->filterRelationsVirtual( 'ToMany', false );
     * ?>
     * </code>
     *
     * @param string $type The type of the relations to filter source columns.
     * @param $out bool True to filter out the relation properties
     *                  from the list; false to remove all other.
     * @return aiiPersistentObjectFilterablePropertyList
     */
    public function filterRelationsVirtual( $type = 'any', $out = true )
    {
        $relationProperties = array();

        foreach ( $this->def->relations as $name => $def )
        {
            foreach( $def->columnMap as $map )
            {
                if ( $type === 'any' || stripos( get_class( $def ), $type ) !== false )
                {
                    $relationProperties[] = $def->propertyName;
                }
            }
        }

        foreach ( $this->properties as $name => $def )
        {
            if ( $out === in_array( $name, $relationProperties ) )
            {
                if ( array_key_exists( $name, $this ) )
                {
                    unset( $this[$name] );
                }
            }
        }

        return $this;
    }

    /**
     * Filters any property from any kind of relation.
     * 
     * This method filters *virtual* *and* *table* properties : those that 
     * hold one or several related objects or are mapped to a table column.
     *
     * The $type argument refers to the relation-type to filter, it's matched
     * against the relation definition class name, which means that any of these
     * values are valid :
     * - 'any'
     * - 'ManyToMany'
     * - 'ManyTo'
     * - 'ToOne'
     * - ...
     * The special type 'any' allows to filter *any* kind of relation.
     *
     * The $out parameter specifies if the filtered properties should be
     * deleted from the list, or if it should be the only properties left.
     * If $out is *true*, the filtered properties are removed from the list
     * and if it's false, all the non-filtered properties are removed.
     *
     * <code>
     * <?php
     * // Remove all properties that are used in any relation :
     * $list->filterRelationsAll();
     *
     * // Keep all properties involved in a OneToOne or ManyToOne relation
     * // and remove all other :
     * $list->filterRelationsAll( 'ToOne', false );
     * ?>
     * </code>
     * 
     * @param string $type The type of the relations to filter source columns.
     * @param bool   $out  True to filter out matched properties; false to filter all other out.
     * @return object $this
     */
    public function filterRelationsAll( $type = 'any', $out = true )
    {
        $relationProperties = array();

        foreach ( $this->def->relations as $name => $def )
        {
            foreach( $def->columnMap as $map )
            {
                if ( $type === 'any' || stripos( get_class( $def ), $type ) !== false )
                {
                    $relationProperties[] = $def->propertyName;
                    $relationProperties[] = $map->sourceColumn;
                }
            }
        }

        foreach ( $this->properties as $name => $def )
        {
            if ( $out === in_array( $name, $relationProperties ) )
            {
                if ( array_key_exists( $name, $this ) )
                {
                    unset( $this[$name] );
                }
            }
        }

        return $this;
    }

    /**
     * Filter properties that accept a null value.
     *
     * Note that ToOne relations involve a *virtual* property that
     * accepts a NULL value; except if the ToOne is mapped to a local
     * *table* column that does not accept null values.
     *
     * The $out parameter specifies if the filtered properties should be
     * deleted from the list, or if it should be the only properties left.
     * If $out is *true*, the filtered properties are removed from the list
     * and if it's false, all the non-filtered properties are removed.
     *
     * <code>
     * <?php
     * // Remove all properties accepting a null value :
     * $list->filterNull();
     *
     * // Remove all properties that don't accept a null value :
     * $list->filterNull( false );
     * ?>
     * </code>
     *
     * @param $out bool True to filter out all the properties accepting
     *                  null value; false to remove all other.
     * @return aiiPersistentObjectFilterablePropertyList $this
     */
    public function filterNull( $out = true )
    {
        $schema = $this->schema->getSchema();

        foreach( $this->properties as $name => $def )
        {
            if ( array_key_exists( $name, $schema[$this->def->table]->fields ) )
            {
                $notNull = $schema[$this->def->table]->fields[$name]->notNull;
            }
            else
            {
                $notNull = $def->propertyType != ezcPersistentObjectProperty::PHP_TYPE_OBJECT;
            }

            if ( $out != $notNull )
            {
                if ( array_key_exists( $name, $this ) )
                {
                    unset( $this[$name] );
                }
            }
        }

        return $this;
    }
    
    /**
     * Removes the idProperty from the list.
     *
     * @todo Test this method
     * @return $this
     */
    public function filterIdProperty()
    {
        unset( $this[$this->def->idProperty->propertyName] );
        return $this;
    }

    /**
     * Makes sure that the idProperty is in the list.
     *
     * Usefull when a filter *might* have removed it, like filterRelationsAll().
     * 
     * @todo Test this method
     * @return $this
     */
    public function restoreIdProperty()
    {
        if ( ! array_key_exists( $this->def->idProperty->propertyName, $this ) )
        {
            $this[$this->def->idProperty->propertyName] = $this->def->idProperty;
        }

        return $this;
    }
}
?>
