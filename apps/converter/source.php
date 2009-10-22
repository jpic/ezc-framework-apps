<?php
// {{{ Exceptions
class aiiMiddleException extends Exception {

}
class aiiMissingComponentConfigException extends aiiMiddleException {
    public function __construct( $middleProperty, $componentName, $variableName ) {
        parent::__construct( "$middleProperty does not have $variableName config for $componentName" );
    }
}
// }}}

/**
 * Intermediary property. It is the relation between different
 * component definitions and values.
 *
 * As for property "type", i decided that PersistentObject would be the master,
 * that will probably be the case for other decisions of this kind.
 */
class aiiMiddleProperty {
    /**
     * Array of component specific definitions.
     * 
     * @var array( componentName => array( variableName ) )
     */
    public $definitions = array(
    );

    /**
     * Properties of this property.
     *
     * It should contain few but relevant properties to use when component
     * specific properties are missing, ie name, type ...
     * 
     * @var array
     */
    public $properties = array( 
        'name' => null,
        'type' => null,
    );

    /**
     * Value of this property, if bound to any.
     * 
     * @var mixed
     */
    public $value = null;

    /**
     * Child middle property instances.
     * 
     * @var array
     */
    private $middleProperties = array(  );

    public function getComponentDefinition( $componentName ) {
        return array_key_exists( $componentName, $this->definitions ) ?
            $this->definitions[$componentName] :
            null;
    }

    /**
     * Returns a relevant value for a component config.
     *
     * For example, getComponentConfig( "PersistentObject", "columnName" )
     * will try:
     * 
     * - $this->getPersistentObjectColumnName() to let user overload,
     * - $this->definitions["PersistentObject"]->name for the proper value,
     * - finnally it'll go through a switch to figure a relevant default,
     *   in this case $this->name.
     */
    public function getComponentConfig( $componentName, $variableName ) {
        $tryMethod = "get" . $componentName . ucfirst( $variableName );
        var_dump( $tryMethod );
        if ( method_exists( $this, $tryMethod ) ) {
            return $this->$tryMethod();
        }

        $componentDefinition = $this->getComponentDefinition( $componentName );

        if ( !is_null( $componentDefinition ) ) {
            return $componentDefinition->$variableName;
        }

        switch( $componentName ) {
            case "PersistentObject":
                switch( $variableName ) {
                    case "columnName":
                    case "propertyName":
                    case "offset":
                    case "class":
                    case "table":
                        return $this->name;
                }
            case "DatabaseSchema":
                switch( $variableName ) {
                    case "table":
                        return $this->name;
                }
        }

        throw new aiiMissingComponentConfigException( $this, $componentName, $variableName );
    }

    /**
     * Should figure with a middle type
     */
    public function getPersistentObjectPropertyType(  ) {
        trigger_error( "Not implemented, return ezcPersistentObjectProperty::PHP_TYPE_STRING" );
        return ezcPersistentObjectProperty::PHP_TYPE_STRING;
    }

    /**
     * Should figure with a middle type
     */
    public function getPersistentObjectDatabaseType(  ) {
        trigger_error( "Not implemented, return PDO::PARAM_STR" );
        return PDO::PARAM_STR;
    }

    /**
     * Should figure with a middle type
     */
    public function getPersistentObjectConverter(  ) {
        trigger_error( "Not implemented, return null" );
        return null;
    }

    public function getDatabaseSchemaType(  ) {
        trigger_error( "Not implemented, return text" );
        return "text";
    }

    public function __get( $name ) {
        return $this->properties[$name];
    }
    
    public function __set( $name, $value ) {
        $this->properties[$name] = $value;
    }

    /**
     * Converts the value for a specific component.
     *
     * @param string $destinationComponent 
     * @return void
     */
    public function convertValue( $componentName, $fromTo = "to" ) {
        switch( $destination ) {
            case "PersistentObject":
                if ( $converter = $this->getComponentConfig( "PersistentObject", "converter" ) ) {
                    if( $fromTo == "to" ) {
                        return $converter->toDatabase( $this->value );
                    } else {
                        return $converter->fromDatabase( $this->value );
                    }
                    return $converter;
                }
        }
    }

    public function getOrCreateMiddleProperty( $name, $default = null ) {
        if ( !array_key_exists( $name, $this->properties ) ) {
            if ( is_object( $default ) ) {
                $property = $default;
            } else {
                $property = new aiiMiddleProperty(  );
            }
            $property->name = $name;
            $this->middleProperties[$name] = $property;
        }

        return $this->middleProperties[$name];
    }
}

/**
 * Inter-component factory interface.
 *
 * The base component specific definitions should be passed to the constructor
 * which is not defined here because it may depend on components.
 */
interface aiiDefinitionConverter {
    /**
     * Converts a definition into an intermediary property.
     */
    public function toMiddleProperty( aiiMiddleProperty $property );

    /**
     * Returns a component specific definition from a middle property.
     */
    public function fromMiddleProperty( aiiMiddleProperty $property );
}

/**
 * Conversions with persistent object definitions.
 */
class aiiPersistentObjectDefinitionsConverter implements aiiDefinitionConverter {
    
    private $manager;
    
    private $definition;
    
    public function __construct( $definition, $manager ) {
        $this->manager = $manager;
        $this->definition = $definition;
    }

    /**
     * Just append a property to $property->properties for each persistent
     * object properties.
     *
     * @todo relations
     * @todo idProperty
     */
    public function toMiddleProperty( aiiMiddleProperty $middleProperty ) {
        $middleProperty->definitions["PersistentObject"] = $this->definition;

        foreach( $this->definition->properties as $propertyDef ) {
            $middleChildProperty = $middleProperty->getOrCreateMiddleProperty( 
                $propertyDef->propertyName
            );
            $middleChildProperty->definitions["PersistentObject"] = $propertyDef;
            $middleChildProperty->name = $propertyDef->propertyName;
        }

        return $middleProperty;
    }

    /**
     * Makes a persistent object definition for a middle property.
     */
    public function fromMiddleProperty( aiiMiddleProperty $middleProperty ) {
        $this->definition->table = $middleProperty->getComponentConfig(
            "PersistentObject",
            "table"
        );

        $this->definition->class = $middleProperty->getComponentConfig(
            "PersistentObject",
            "class"
        );

        foreach( $middleProperty->middleProperties as $middleChildProperty ) {
            $propertyDef = new ezcPersistentObjectChildProperty(
                $middleChildProperty->getComponentConfig( 
                    "PersistentObject",
                    "columnName"
                ),
                $middleChildProperty->getComponentConfig( 
                    "PersistentObject",
                    "propertyName"
                ),
                $middleChildProperty->getComponentConfig( 
                    "PersistentObject",
                    "propertyType"
                ),
                $middleChildProperty->getComponentConfig( 
                    "PersistentObject",
                    "converter"
                ),
                $middleChildProperty->getComponentConfig( 
                    "PersistentObject",
                    "databaseType"
                )
            );

            $offset = $middleChildProperty->getComponentConfig( 
                "PersistentObject",
                "offset"
            );

            $this->definition->properties[$offset] = $propertyDef;
            $middleChildProperty->definitions["PersistentObject"] = $propertyDef;
        }

        return $this->definition;
    }
}

/**
 * Conversions with database schemas tables.
 */
class aiiDatabaseSchemaTableConverter implements aiiDefinitionConverter {
    public $table;
    
    public function __construct( ezcDbSchemaTable $table ) {
        $this->table = $table;
    }
    
    public function toMiddleProperty( aiiMiddleProperty $middleProperty ) {
        $middleProperty->definitions["DatabaseSchema"] = $this->table;

        foreach( $this->table->fields as $propertyName => $propertyDef ) {
            $middleChildProperty = $middleProperty->getOrCreateMiddleProperty( 
                $propertyName
            );
            $middleChildProperty->definitions["DatabaseSchema"] = $propertyDef;
            $middleChildProperty->name = $propertyName;
        }

        return $middleProperty;
    }

    public function fromMiddleProperty( aiiMiddleProperty $middleProperty ) {
        foreach( $middleProperty->middleProperties as $middleChildProperty ) {
            $propertyDef = new ezcDbSchemaField(
                $middleChildProperty->getComponentConfig( 
                    "DatabaseSchema",
                    "type"
                )
            );

            $middleChildProperty->definitions["DatabaseSchema"] = $propertyDef;

            $offset = $middleChildProperty->getComponentConfig( 
                "DatabaseSchema",
                "fieldName"
            );

            $this->table->fields[$offset] = $propertyDef;
        }

        return $propertiesDef;
    }
}

/**
 * Conversions with UserInput definitions.
 *
 * Because ezcInputForm is not flexible enough to support changing
 * its definition, it has to be re-created in fromMiddleProperty().
 */
class aiiUserInputConverter implements aiiDefinitionConverter {
    public $form = null;
    public function __construct( ezcInputForm $form ) {
        $this->form = $form;
    }
    
    public function toMiddleProperty( aiiMiddleProperty $middleProperty ) {
        foreach( $this->form->definition as $propertyName => $propertyDef ) {
            $middleChildProperty = $middleProperty->getOrCreateMiddleProperty( 
                $propertyName
            );
            $middleChildProperty->value = $this->form->$propertyName;
        }
    }

    public function createForm( $definition ) {
        // support user subclassing
        $formClass = get_class( $this->form );
        $form = new $formClass( 
            $this->form->inputSource,
            $definition,
            $this->form->characterEncoding
        );
        return $form;
    }
    
    public function fromMiddleProperty( aiiMiddleProperty $middleProperty ) {
        $definition = array(  );

        foreach( $middleProperty->middleProperties as $middleChildProperty ) {
            $definition[$middleChildProperty->name] = new ezcInputFormDefinitionElement(
                $middleChildProperty->getComponentConfig( 
                    "UserInput",
                    "type"
                ),
                $middleChildProperty->getComponentConfig( 
                    "UserInput",
                    "filterName"
                ),
                $middleChildProperty->getComponentConfig( 
                    "UserInput",
                    "options"
                ),
                $middleChildProperty->getComponentConfig( 
                    "UserInput",
                    "flags"
                ),
                $middleChildProperty->getComponentConfig( 
                    "UserInput",
                    "hint"
                )
            );
        }

        return $this->createForm( $definition );
    }
}
