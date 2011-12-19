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

/**
 * Describes which addresses an email gateway expects
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\EmailGatewayAddress")
 * @ORM_Mapping\Table(name="email_gateway_addresses")
 */
class EmailGatewayAddress extends \Application\DeskPRO\Domain\DomainObject
{
	const MATCH_TYPE_EXACT  = 'exact';
	const MATCH_TYPE_DOMAIN = 'domain';
	const MATCH_TYPE_REGEX  = 'regex';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var Application\DeskPRO\Entity\EmailGateway
	 * @ORM_Mapping\ManyToOne(targetEntity="EmailGateway", inversedBy="addresses")
	 * @ORM_Mapping\JoinColumn(name="email_gateway_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $gateway;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="match_type", type="string", length=15)
	 */
	protected $match_type = 'exact';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="match_pattern", type="string", length=255)
	 */
	protected $match_pattern = '';

	public function getTitle()
	{
		switch ($this->match_type) {
			case self::MATCH_TYPE_EXACT: return $this->match_pattern;
			case self::MATCH_TYPE_DOMAIN: return '*@' . $this->match_pattern;
			case self::MATCH_TYPE_REGEX: return $this->match_pattern;
		}

		return '';
	}

	public function __toString()
	{
		return $this->getTitle();
	}
}
