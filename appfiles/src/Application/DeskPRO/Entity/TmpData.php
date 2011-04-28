<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A general data store
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TmpData")
 * @orm:Table(name="tmp_data")
 */
class TmpData extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The authcode for the session to verify an id
	 *
	 * @var string
	 * @orm:Column(name="auth", type="string", length=15)
	 */
	protected $auth;

	/**
	 * Data
	 *
	 * @var array
	 * @orm:Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_expire",type="datetime")
	 */
	protected $date_expire;

	public function __construct()
	{
		$this->auth         = Strings::random(15, Strings::CHARS_KEY);
		$this->date_created = new \DateTime();
		$this->date_expire  = new \DateTime('+1 week');
	}


	/**
	 * Get the type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->getData('_type');
	}

	
	/**
	 * Set the type
	 *
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->setData('_type', $type);
	}


	/**
	 * Get some data from the extra array
	 */
	public function getData($key, $default = null)
	{
		return (isset($this->data[$key]) ? $this->data[$key] : $default);
	}


	/**
	 * Set some data on the extra array.
	 *
	 * @param  $key
	 * @param  $value
	 * @return void
	 */
	public function setData($key, $value)
	{
		if ($value === null) {
			unset($this->data[$key]);
		} else {
			$this->data[$key] = $value;
		}
	}


	/**
	 * @return string
	 */
	public function getCode()
	{
		return Util::baseEncode($this->id, Util::LETTERS_ALPHABET) . '-' . $this->auth;
	}


	/**
	 * Splits a code into its id and auth
	 *
	 * @param  $code
	 * @return array
	 */
	public static function getPartsFromCode($code)
	{
		$parts = explode('-', $code, 2);
		if (count($parts) != 2) return null;

		$parts[0] = Util::baseDecode($parts[0], Util::LETTERS_ALPHABET);

		return array(
			'id' => $parts[0],
			'auth' => $parts[1],
		);
	}
}