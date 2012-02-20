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
use Orb\Util\Arrays;

/**
 * Email addresses that are still waiting to be validated.
 *
 * These are multipurpose:
 * - New email addresses on an account can be be added and validated
 * - Content submissions from logged-out users that use existing email addresses
 *   will create these records and will be validated through the usual controller.
 * - New users with new email addresses will validated.
 *
 * It's important to look-up the email address first to see if it's in use by a person. Since
 * new content generally creates new Person records, we want to make sure each validating content
 * is attached to a single person and not many records.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\PersonEmailValidating")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="people_emails_validating")
 */
class PersonEmailValidating extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * The email address
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="email", type="string", length=255)
	 */
	protected $email;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="auth", type="string", length=20)
	 */
	protected $auth;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * An array of array('entityname', 'id')
	 * of content that is validating based on this email address.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="validating_content", type="array")
	 */
	protected $validating_content = array();

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->auth = Strings::random(8, Strings::CHARS_KEY);
	}

	public function getEmailDomain()
	{
		if ($this->email_domain) {
			return $this->email_domain;
		}

		return Strings::extractRegexMatch('#@(.*?)$#', $this->email, 1);
	}

	public function addValidatingContent($entity_name, $id)
	{
		$this->validating_content[] = array($entity_name, $id);
	}
}
