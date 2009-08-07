<?php
/**
 * Intermediary property definition.
 */
class aiiMiddleProperty {

}

/**
 * Encapsulates intermediary properties.
 */
class aiiMiddleProperties extends aiiMiddleProperty {
    public $properties = array(  );
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
     *
     * A new intermediary property will be instanciated if no base intermediary
     * property is passed.
     */
    public function toMiddleProperty( aiiMiddleProperty $property = null );

    /**
     * Returns a component specific definition from a middle property.
     */
    public function fromMiddleProperty( aiiMiddleProperty $property );
}

/**
 * Conversions with persistent object definitions.
 */
class aiiPersistentObjectDefinitionsConverter implements aiiDefinitionConverter {
}

/**
 * Conversions with database schemas.
 */
class aiiDatabaseSchemaConverter implements aiiDefinitionConverter {

}

/**
 * Conversions with UserInput definitions.
 */
class aiiUserInputConverter implements aiiDefinitionConverter {

}
