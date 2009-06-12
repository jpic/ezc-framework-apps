<?php
/**
 * aiiPersistentObjectDatabaseSchemaTool 
 * 
 * @package PersistentObjectDatabaseSchemaTiein
 * @version //autogen//
 * @copyright Copyright (C) 2008 James Pic. All rights reserved.
 * @author James Pic 
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * A object of this class is expected in template scripts, as variable $tool.
 *
 * Directly parsing the database schema and the persistent object 
 * definition in the template scripts makes the templates not readable.
 * This class factorizes usual needs for template files.
 * An object of this class is expected to be in variable $tool in templates.
 *
 * <code><?php
 * $tool = new aiiPersistentObjectDatabaseSchemaTool( $def, $pathDef, $schemaFormat, $pathSchema );
 *
 * // Get the initial state of the object as an array :
 * $state = $tool->getInitialState();
 * 
 * // Check if a property is null using it's column and table names :
 * $null = $tool->isNullProperty( $columnName, $tableName );
 *
 * // Check if a property is a foreign-key :
 * $isFk = $tool->isFK( $propertyName );
 *
 * // Get the names of the related classes involved by a property
 * $classes = $tool->getRelatedClasses( $propertyName );
 *
 * // Get the name of the related class involved by a property
 * $class = $tool->getRelatedClass( $propertyName );
 *
 * // Get a valid fixture for a property :
 * $fixture = $tool->getFixture( $propertyName );
 * 
 * // For example, to select all relation properties, using an int for the FK,
 * // and that don't accept null-values :
 * $list = $tool->getFilterableList()
 *     ->filterType( ezcPersistentObjectProperty::PHP_TYPE_INT, false )
 *     ->filterRelations( false )
 *     ->filterNull( true );
 * </code>
 *
 * @property-read ezcPersistentObjectDefinition $def The persistent-object definition to use.
 * @property-read string $pathDef Path to the persistent-object definition *directory*.
 * @property-read string $schemaFormat Format of the database-schema, like 'array' or 'xml'.
 * @property-read string $pathSchema   Path to the database schema file.
 * @property-read array  $schema       Internal schema by reference.
 */
class aiiPersistentObjectDatabaseSchemaTool
{
    /**
     * The persistent object definition to use.
     * 
     * @var ezcPersistentObjectDefinition
     */
    protected $def = null;

    /**
     * Path to the persistent object definitions directory.
     * 
     * @var string
     */
    protected $pathDef = '';
    /**
     * The database schema to use.
     * 
     * @var mixed
     */
    protected $schema = null;

    /**
     * Path to the database schema file.
     * 
     * @var string
     */
    protected $pathSchema = '';

    /**
     * Format of the database schema file, like 'array' or 'xml'.
     * 
     * @var string
     */
    protected $schemaFormat = '';

    /**
     * Instanciates a tool. 
     * 
     * @param ezcPersistentObjectDefinition $def Definition of the persistent object.
     * @param string $pathDef Path to the persistent object definition *directory*.
     * @param string $schemaFormat Format of the database schema, like 'array' or 'xml'.
     * @param string $pathSchema Path to the database schema file.
     */
    public function __construct( ezcPersistentObjectDefinition $def, $pathDef, $schemaFormat, $pathSchema )
    {
        $this->def          = $def;
        $this->pathDef      = $pathDef;
        $this->pathSchema   = $pathSchema;
        $this->schemaFormat = $schemaFormat;
        $this->schema       = $this->getSchema();
    }

    /**
     * Magic method to return the protected properties. 
     * 
     * @param string $propertyName Property name.
     * @return mixed Property value.
     */
    public function __get( $propertyName )
    {
        switch( $propertyName )
        {
            case 'def':
            case 'pathDef':
            case 'schema':
            case 'pathSchema':
            case 'schemaFormat':
                return $this->$propertyName;
                break;
            default:
                trigger_error( 'Property not avalaible.' );
        }
    }

    /**
     * Returns an array of the initial state that the object should have. 
     * 
     * Parses the definition, sets the following values :
     * - 0 for ints and floats,
     * - "" for strings,
     * - null for 1:n relation properties,
     * - array() for n:m relation properties.
     *
     * @return array(string=>mixed) Object's initial state
     */
    public function getInitialState()
    {
        $state = array();
        $state[$this->def->idProperty->propertyName] = null;

        foreach($this->def->properties as $propertyName => $propertyDef)
        {
            switch( $propertyDef->propertyType )
            {
                case ezcPersistentObjectProperty::PHP_TYPE_STRING:
                    $propertyDefaultValue = '';
                    break;
                case ezcPersistentObjectProperty::PHP_TYPE_INT:
                    $propertyDefaultValue = 0;
                    break;
                case ezcPersistentObjectProperty::PHP_TYPE_FLOAT:
                    $propertyDefaultValue = 0.0;
                    break;
                default:
                    $propertyDefaultValue = 'null';
                    break;
            }
            $state[$propertyName] = $propertyDefaultValue;
        }

        if ( isset( $this->def->relations ) && $this->def->relations )
        {
            foreach($this->def->relations as $relatedClassName => $relationDef)
            {
                $propertyName = $this->def->relations[$relatedClassName]->propertyName;
                switch ( get_class($relationDef) )
                {
                    case 'ezcPersistentManyToManyRelation':
                    case 'ezcPersistentOneToManyRelation':
                        $propertyDefaultValue = array();
                        break;
                    case 'ezcPersistentOneToOneRelation':
                        foreach ( $relationDef->columnMap as $map )
                        {
                            $state[$map->sourceColumn] = null;
                        }
                    case 'ezcPersistentManyToOneRelation':
                        $propertyDefaultValue = null;
                        break;
                }
                $state[$propertyName] = $propertyDefaultValue;
            }
        }
        return $state;
    }

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
        return new aiiPersistentObjectFilterablePropertyList( $this->def, $this->schema );
    }

    /**
     * Returns a value for the given type. 
     *
     * @param const $type Any of these constants from ezcPersistentObjectProperty : PHP_TYPE_INT, PHP_TYPE_STRING, PHP_TYPE_FLOAT.
     * @return mixed
     */
    public static function getFixture( $type )
    {
        switch( $type )
        {
            case 'int':
            case ezcPersistentObjectProperty::PHP_TYPE_INT:
                return 3;
                break;
            case 'string':
            case ezcPersistentObjectProperty::PHP_TYPE_STRING:
                return 'example string';
                break;
            case 'float':
            case ezcPersistentObjectProperty::PHP_TYPE_FLOAT:
                return 3.14;
                break;
        }
    }

    /**
     * Returns the schema reader instance for the given schema path and format. 
     * 
     * @return object The schema reader.
     */
    protected function getSchema()
    {
        $schema = null;
        $readerClass = ezcDbSchemaHandlerManager::getReaderByFormat( $this->schemaFormat );
        $reader = new $readerClass();

        switch ( true )
        {
            case ( $reader instanceof ezcDbSchemaDbReader ):
                $db = ezcDbFactory::create( $this->pathSchema );
                $schema = ezcDbSchema::createFromDb( $db );
                break;
            case ( $reader instanceof ezcDbSchemaFileReader ):
                $schema = ezcDbSchema::createFromFile( $this->schemaFormat, $this->pathSchema );
                break;
            default:
                $this->raiseError( "Reader class not supported: '{$readerClass}'." );
                break;
        }
        return $schema;
    }
    
    /**
     * Returns array( $className, $relationDefinition ) if $columnName is a sourceColumn for a relation, returns false otherwise.
     * 
     * @param mixed $columnName The column name.
     * @return array|bool array( $className, $relationDefinition ) or false.
     */
    public function isFK( $columnName )
    {
        echo "TESTING $columnName";
        if ( $propertyName == $this->def->idProperty->propertyName )
        {
            return false;
        }
        foreach( $this->def->relations as $relatedClassName => $relationDef )
        {
            foreach( $relationDef->columnMap as $map )
            {
                if( $map->sourceColumn == $columnName )
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
        
        foreach( $this->def->relations as $name => $def )
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
        
        foreach( $this->def->relations as $name => $def )
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
}
?>
