<?php
/**
 * @property id int Object's id.
 * @property host string Object's host.
 * @property name string Object's name.
*/

class aiiSitesClass
{
    protected $state = array (
        'id' => NULL,
        'host' => '',
        'name' => '',
    );

    /**
     * Returns object's unique identifier.
     *
     * Return an all-time unique identifier for this object.
     *
     * @return string unique object's identifier.
     */
    public function __toString()
    {
        return $this->host;
    }
    
    /**
     * Returns the value of a property.
     *
     * Valid properties are described in the class-level documentation.
     * ezcPersistentObjectPropertyNotVisible is thrown for :
     *
     * @param string $properyName Name of the property to get.
     * @throw new ezcBasePropertyNotFoundException  When $properyName does not exist.
     * @throw ezcPersistentObjectPropertyNotVisible When the property should not
     *                                              be accessed directly.
     * @return mixed Value of $properyName
     */
    public function __get( $propertyName )
    {
        switch( $propertyName )
        {
            case 'id':
            case 'host':
            case 'name':
                return $this->state[$propertyName];
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
        }
    }
    
    /**
     * Sets a property to an arbitary value.
     *
     * Properties expecting an int value :
     * - id
     * Properties expecting an string value :
     * - host
     * - name
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function __set( $propertyName, $propertyValue )
    {
        switch( $propertyName )
        {
            case 'id': // idProperty
                if ( !is_int( $propertyValue ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'Ints' );
                }
                return $this->state[$propertyName] = $propertyValue; 
                break;
            case 'host':
            case 'name':
                if ( !is_string( $propertyValue ) && !is_null( $propertyValue ) )
                {
                    throw new ezcBaseValueException($propertyName, $propertyValue, 'Strings');
                }
                return $this->state[$propertyName] = $propertyValue;
            case 'site_id':
                if ( !is_int( $propertyValue ) )
                {
                    throw new ezcBaseValueException($propertyName, $propertyValue, 'Ints');
                }
                return $this->state[$propertyName] = $propertyValue; 
            // relations
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
        }
    }
    
    /**
     * Return true if $propertyName exists.
     *
     * @param string $propertyName.
     * @return bool True if object has $propertyName.
     */
    public function __isset( $propertyName )
    {
        return array_key_exists( $propertyName, $this->state );
    }
    
    /**
     * Sets $propertyName to a default value.
     *
     * - In case of a direct property
     *   - set the default value to null.
     * - In case of a relation property
     *   - remove the related object
     *   - set the related property to null.
     * 
     * @param string $propertyName.
     * @return bool True if $propertyName was correctly unset.
     */
    public function __unset( $propertyName )
    {
        switch( $propertyName )
        {
            case 'id':
            case 'host':
            case 'name':
                unset( $this->state[$propertyName] );
                return true;
            // relations
            default:
                throw new ezcBasePropertyNotFoundException( $propertyName );
        }
    }
    
    /**
     * Sanitizes $state, resets this state, use __set() to set the new state.
     *
     * @param array Object's state.
     */
    public function setState( $state )
    {
        if ( array_key_exists( 'id', $state ) )
        {
            $this->id = (int) $state['id'];
        }
        if ( array_key_exists( 'host', $state ) )
        {
            $this->host = (string) $state['host'];
        }
        if ( array_key_exists( 'name', $state ) )
        {
            $this->name = (string) $state['name'];
        }
        // relations
    }
    
    /**
     * Returns the complete object's state, including related objects.
     *
     * @return array Object's complete state.
     */
    public function getState()
    {
        $state = array();
        $state['id'] = isset( $this->id ) ? $this->id : NULL;
        $state['host'] = isset( $this->host ) ? $this->host : '';
        $state['name'] = isset( $this->name ) ? $this->name : '';
        // Relations
        // State should be clean
        $state['id'] = $this->id;
        return $state;
    }
}

?>
