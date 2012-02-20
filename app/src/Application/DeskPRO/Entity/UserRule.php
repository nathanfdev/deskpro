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
