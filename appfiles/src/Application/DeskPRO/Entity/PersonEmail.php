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
use Orb\Util\Arrays;

/**
 * Email addresses attached to a person. This is a separate entity because emails are
 * roughly tied to identity (ie local login uses email as identity), and are integral
 * in many cases (notifications etc).
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="people_emails",
 *     indexes={
 *         @orm:Index(name="email_domain_idx", columns={"email_domain"})
 * })
 */
class PersonEmail extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * The email address
	 *
	 * @var string
	 * @orm:Column(name="email", type="string", length=255)
	 */
	protected $email;

	/**
	 * The email address domain
	 *
	 * @var string
	 * @orm:Column(name="email_domain", type="string", length=255)
	 */
	protected $email_domain;

	/**
	 * @var bool
	 * @orm:Column(name="is_validated", type="boolean")
	 */
	protected $is_validated = false;

	/**
	 * A comment or description of the email address. For example, "work" or "home."
	 *
	 * @var string
	 * @orm:Column(name="comment", type="text", length=100)
	 */
	protected $comment = '';

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_validated",type="datetime", nullable=true)
	 */
	protected $date_validated = null;



	/**
	 * Gets the gravatar URL for this email
	 *
	 * @return string
	 */
	public function getGravatarUrl()
	{
		$hash = strtolower(md5($this->email));
		$url = 'http://www.gravatar.com/avatar/' . $hash . '?d=identicon';

		return $url;
	}



	/**
	 * Checks to see if the gravatar for this email address is actually a real avatar (not a default).
	 *
	 * @return bool
	 */
	public function hasGravatar()
	{
		static $is_real = null;

		if ($is_real === null) {
			$is_real = false;

			$hash = strtolower(md5($this->email));
			$check_url = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';

			$headers = @get_headers($check_url);
			if ($headers AND !empty($headers[0])) {
				if (strpos($headers[0], '200') !== false) {
					$is_real = true;
				}
			}
		}

		return $is_real;
	}



	/**
	 * Set email
	 *
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		list (, $this->email_domain) = explode('@', $email, 2);
	}



	/** @orm:PrePersist */
	public function incCreatedAt()
	{
		$this->date_created = new \DateTime();
		if ($this->is_validated AND !$this->date_validated) {
			$this->date_validated = new \DateTime();
		}
	}

	/** @PreSave */
	public function incValidatedAt()
	{
		if ($this->is_validated AND !$this->date_validated) {
			$this->date_validated = new \DateTime();
		}
	}
}