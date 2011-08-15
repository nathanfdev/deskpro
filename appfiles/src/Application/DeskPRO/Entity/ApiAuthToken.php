<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="api_auth_tokens")
 */
class ApiAuthToken extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="token", type="string", length=50)
	 */
	protected $token;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="scope", type="string", length=250, nullable=true)
	 */
	protected $scope = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_expires",type="datetime")
	 */
	protected $date_expires;


	public function __construct()
	{
		$this->apikey = Strings::random(50, Strings::CHARS_KEY);
	}
}