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
 * Email addresses attached to a person. This is a separate entity because emails are
 * roughly tied to identity (ie local login uses email as identity), and are integral
 * in many cases (notifications etc).
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\PersonEmail")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="people_emails",
 *     uniqueConstraints={@ORM_Mapping\UniqueConstraint(name="email_idx", columns={"email"})},
 *     indexes={
 *         @ORM_Mapping\Index(name="email_domain_idx", columns={"email_domain"})
 * })
 */
class PersonEmail extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var Application\DeskPRO\Entity\Person
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
	 * The email address domain
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="email_domain", type="string", length=255)
	 */
	protected $email_domain;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_validated", type="boolean")
	 */
	protected $is_validated = true;

	/**
	 * A comment or description of the email address. For example, "work" or "home."
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="comment", type="text", length=100)
	 */
	protected $comment = '';

	/**
	 * The original time the email was created. If validation is requried, this will be the time
	 * that PersonEmailValidating record was created before this one.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The time this email became valid. If validation is requried, then this is when
	 * a PersonEmailValidating becomes a a PersonEmail. If its not required, then
	 * this and date_created will be the same.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_validated",type="datetime", nullable=true)
	 */
	protected $date_validated = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->date_validated = new \DateTime();
		$this->is_validated = true;
	}

	public function getEmailDomain()
	{
		if ($this->email_domain) {
			return $this->email_domain;
		}

		return Strings::extractRegexMatch('#@(.*?)$#', $this->email, 1);
	}


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
		if ($email) {
			$this->setModelField('email', strtolower($email));
			list (, $email_domain) = explode('@', $email, 2);
		} else {
			$email = null;
		}

		$this->setModelField('email_domain', $email_domain);
	}



	public function setIsValidated($yesno)
	{
		$this->is_validated = $yesno;

		if ($yesno) {
			$this->date_validated = new \DateTime();
		} else {
			$this->date_validated = null;
		}
	}
}
