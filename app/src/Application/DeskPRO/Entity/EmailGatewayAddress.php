<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
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

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="run_order", type="integer")
	 */
	protected $run_order = 0;

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
