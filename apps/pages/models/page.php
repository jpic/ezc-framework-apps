<?php
/**
 * File containing the $className class.
 */
/**
 * aiiCmsPages
 *
 * @property string $contents
* @property int $id
* @property int $siteId
* @property string $slug
* @property string $title
 */
class aiiPagesClass implements ezcPersistentObject
{
    /**
     * Properties.
     * 
     * @var array(string=>mixed)
     */
    protected $properties = array();

    /**
     * Creates a new aiiCmsPages
     * 
     * @return void
     */
    public function __construct()
    {
		
	    $this->properties['contents'] = null;

	    $this->properties['id'] = null;

	    $this->properties['siteId'] = null;

	    $this->properties['slug'] = null;

	    $this->properties['title'] = null;
    }

    /**
     * Set properties after reading an object from the database. 
     * 
     * @param array(string=>mixed) $properties 
     * @return void
     *
     * @access private
     */
    public function setState( array $properties )
    {
        foreach ( $properties as $name => $value )
        {
            $this->properties[$name] = $value;
        }
    }

    /**
     * Returns the property values to store an object to the database.
     * 
     * @return array(string=>mixed)
     *
     * @access private
     */
    public function getState()
    {
        return $this->properties;
    }

    /**
     * Overloading to set properties.
     *
	 * @throws ezcBaseValueException
	 *         if the property value to set does no conform to type constraints.
	 * @throws ezcBasePropertyNotFoundException
	 *         if the desired property does not exist.
     * 
     * @param string $propertyName 
     * @param mixed $propertyValue 
     * @return void
	 *
	 * @ignore
     */
    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            
			case 'contents':
				if ( !is_string( $propertyValue ) )
				{
					throw new ezcBaseValueException(
						$propertyName,
						$propertyValue,
						'string'
					);
				}
			break;

			case 'id':
				if ( !is_int( $propertyValue ) )
				{
					throw new ezcBaseValueException(
						$propertyName,
						$propertyValue,
						'int'
					);
				}
			break;

			case 'siteId':
				if ( !is_int( $propertyValue ) )
				{
					throw new ezcBaseValueException(
						$propertyName,
						$propertyValue,
						'int'
					);
				}
			break;

			case 'slug':
				if ( !is_string( $propertyValue ) )
				{
					throw new ezcBaseValueException(
						$propertyName,
						$propertyValue,
						'string'
					);
				}
			break;

			case 'title':
				if ( !is_string( $propertyValue ) )
				{
					throw new ezcBaseValueException(
						$propertyName,
						$propertyValue,
						'string'
					);
				}
			break;

            default:
                throw new ezcBasePropertyNotFoundException(
					$propertyName,
					$propertyValue
				);
        }
        $this->properties[$propertyName] = $propertyValue;
    }

    /**
     * Overloading to get properties.
     * 
	 * @throws ezcBasePropertyNotFoundException
	 *         if the desired property does not exist.
	 *
     * @param string $propertyName 
     * @return void
	 *
	 * @ignore
     */
    public function __get( $propertyName )
    {
        if ( $this->__isset( $propertyName ) )
        {
            return $this->properties[$propertyName];
        }
        throw new ezcBasePropertyNotFoundException( $propertyName );
    }

    /**
     * Overloading for property isset() checks.
     * 
     * @param string $propertyName 
     * @return bool
	 *
	 * @ignore
     */
    public function __isset( $propertyName )
    {
        return array_key_exists( $propertyName, $this->properties );
    }
}

?>
