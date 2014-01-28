<?php

namespace Orb\Jira;

/**
 * The Base Entity class
 * 
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
abstract class Entity
{
    /**
     * Entity ID
	 * 
     * @var int
     */
    protected $_id;    
	
	/**
     * The Machine Name of the entity
	 * 
     * @var String
     */
    protected $_key;
    
    /**
     * Human Readable name of the entity
	 * 
     * @var String
     */
    protected $_name;
    
    /**
     * Entity Avatars
	 * An array of relative URLs of the avatar on your Jira site
	 * 
     * @var array
     */
    protected $_avatars;
    
    /**
     * Entity Description
	 * 
     * @var String
     */
    protected $_description;
	
	/**
	 * The default constructor<br/>
	 * takes array of values and constucts the entity
	 * 
	 * @param type $array An array of entity values
	 * @return boolean|\Jira\Entity\Entity
	 */
	abstract public function __construct(array $params = array());


	/**
	 * Factory method which initializes the entity<br/>
	 * from a given array
	 * 
	 * @param array $array The params array
	 * @return Entity The new entity object
	 */
	static public function fromArray(array $array = array())
	{
		return new static($array);
	}

	/**
     * Get the ID.
	 * 
     * @return int The Entity ID.
     */
    public function getId()
    {
        return $this->_id;
    }
	
	/**
	 * Set the ID
	 * 
	 * @param type $id
	 * @return \Orb\Jira\Entity
	 */
    public function setId($id)
    {
        $this->_id = $id;
		
		return $this;
    }
    
    /**
     * Get the Key
	 * 
     * @return The Entity Key
     */
    public function getKey()
    {
        return $this->_key;
    }
	
	/**
	 * Get the name.
	 * 
	 * @return String The Entity Name.
	 */
	public function getName()
	{
		return $this->_name;
	}
	
	/**
	 * Gets the Avatars.
	 * 
	 * @return array An array of Avatar URLs.
	 */
	public function getAvatars()
	{
		return $this->_avatars;
	}
	
	/**
	 * Gets the description.
	 * 
	 * @return String The Entity description.
	 */
	public function getDescription()
	{
		return $this->_description;
	}
	
	/**
	 * Sets the key
	 * 
	 * @param String $key The key to set
	 * @return \Jira\Entity
	 */
	public function setKey($key)
	{
		$this->_key = $key;
		
		return $this;
	}
	
	/**
	 * Sets the name
	 * 
	 * @param String $name They name to set
	 * @return \Jira\Entity
	 */
	public function setName($name)
	{
		$this->_name = $name;
		
		return $this;
	}
	
	/**
	 * Sets the description
	 * 
	 * @param type $description The description to set
	 * @return \Jira\Entity
	 */
	public function setDescription($description)
	{
		$this->_description = $description;
		
		return $this;
	}
	
	/**
	 * Adds an avatar URL
	 * 
	 * @param String $url The Avatar URL
	 */
	public function addAvatar($url)
	{
		$this->_avatars[] = $url;
	}
	
	/**
	 * Serializes an Entity to an Array
	 * 
	 * @return array The serialized array
	 */
	public function toArray()
	{
		$vars = get_object_vars($this);
		
		$objectArray = array();
		
		foreach ($vars as $key => $value) {
			$key = strtolower($key);
			
			if (substr($key, 0, 1) === '_') {
				$key = substr($key, 1);
			}
			
			if (is_object($value) && method_exists($value, 'toArray')) {
				$value = $value->toArray();
			}
			
			$objectArray[$key] = $value;
		}
		
		return $objectArray;
	}
	
	/**
	 * Serializes an Entity to JSON
	 * 
	 * @return String The encode JSON String
	 */
	public function toJson()
	{
		$objectArray = $this->toArray();
		
		return json_encode($objectArray);
	}
}