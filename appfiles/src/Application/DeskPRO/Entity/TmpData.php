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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A general data store
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TmpData")
 * @ORM_Mapping\Table(name="tmp_data")
 */
class TmpData extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * The authcode for the session to verify an id
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="auth", type="string", length=15)
	 */
	protected $auth;

	/**
	 * Data
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_expire",type="datetime")
	 */
	protected $date_expire;

	/**
	 * @param string $type
	 * @param array $data
	 * @return \Application\DeskPRO\Entity\TmpData
	 */
	public static function create($type, array $data = array(), $expire = '+1 week')
	{
		$tmpdata = new self();
		$tmpdata->setType($type);

		foreach ($data as $k => $v) {
			$tmpdata->setData($k, $v);
		}

		$tmpdata['date_expire'] = new \DateTime($expire);

		return $tmpdata;
	}

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