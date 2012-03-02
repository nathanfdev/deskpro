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

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\UserRule")
 * @ORM_Mapping\Table(name="user_rules")
 */
class UserRule extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\GeneratedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * An array of email addres patterns
	 * @var array
	 * @ORM_Mapping\Column(name="email_patterns", type="array")
	 */
	protected $email_patterns = array();

	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization", cascade={"persist", "remove", "merge"})
	 * @ORM_Mapping\JoinColumn(name="add_organization_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $add_organization;

	/**
	 * @var \Application\DeskPRO\Entity\Usergroup
	 * @ORM_Mapping\ManyToOne(targetEntity="Usergroup", cascade={"persist", "remove", "merge"})
	 * @ORM_Mapping\JoinColumn(name="add_usergroup_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $add_usergroup;

	/**
	 * The order in which to runthis source
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="run_order", type="integer")
	 */
	protected $run_order = 0;


	/**
	 * Set the patterns string which is a number of patterns separated by a newline
	 *
	 * @param $patterns
	 */
	public function setPatternsString($patterns)
	{
		$items = array();

		$patterns = Strings::standardEol($patterns);
		$patterns = explode("\n", $patterns);
		foreach ($patterns as $p) {
			$p = Strings::utf8_strtolower($p);
			$items[] = trim($p);
		}

		$items = Arrays::removeFalsey($items);

		$this->setModelField('email_patterns', $items);
	}


	/**
	 * Get the patterns string
	 *
	 * @return string
	 */
	public function getPatternsString()
	{
		return implode("\n", $this->email_patterns);
	}


	/**
	 * Check if an email address to see if it matches any of the patterns in this rule.
	 *
	 * @param string $email_address
	 * @return string
	 */
	public function isEmailMatch($email_address)
	{
		$email_address = Strings::utf8_strtolower($email_address);

		foreach ($this->email_patterns as $pattern) {
			if (Strings::isStarMatch($pattern, $email_address)) {
				return true;
			}
		}

		return false;
	}
}
