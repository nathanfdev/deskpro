<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\EventListener\Doctrine\PersonEmailListener;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Email addresses attached to a person. This is a separate entity because emails are
 * roughly tied to identity (ie local login uses email as identity), and are integral
 * in many cases (notifications etc).
 *
 * @property int $id
 * @property Person $person
 * @property string $email
 * @property string $email_domain
 * @property bool $is_validated
 * @property string $comment
 * @property \DateTime $date_created
 * @property \DateTime $date_validated
 *
 * @AppAssert\Person\Email\FreeEmail()
 * @AppAssert\Person\Email\NotSystemEmail()
 * @AppAssert\Person\Email\NotBannedEmail()
 *
 * @JMS\ExclusionPolicy("all")
 */
class PersonEmail extends DomainObject
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Person owner of email.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var Person This is used by setPerson() method to store the previous value needed for validation.
     *             Not a mapped property;
     */
    private $prevPerson;

    /**
     * The email address.
     *
     * @Assert\NotBlank()
     * @Assert\Email(strict="true")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $email;

    /**
     * The email address domain.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $email_domain;

    /**
     * True if validation passed.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_validated = false;

    /**
     * A comment or description of the email address. For example, "work" or "home.".
     *
     * @var string
     */
    protected $comment = '';

    /**
     * The original time the email was created.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * The time this email became valid. If validation is requried, then this is when
     * a PersonEmailValidating becomes a a PersonEmail. If its not required, then
     * this and date_created will be the same.
     *
     * @var \DateTime
     */
    protected $date_validated = null;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_validated', new \DateTime());
        $this->setModelField('is_validated', true);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function isPrimary()
    {
        if (!$this->person) {
            return false;
        }

        return $this->person->primary_email === $this;
    }

    public function getEmailDomain()
    {
        if ($this->email_domain) {
            return $this->email_domain;
        }

        return Strings::extractRegexMatch('#@(.*?)$#', $this->email, 1);
    }

    /**
     * Gets the gravatar URL for this email.
     *
     * @param bool $secure Use secure url? null to detect automatically based on current request
     *
     * @return string
     */
    public function getGravatarUrl($secure = null)
    {
        // Null means detect
        if ($secure === null and App::isWebRequest()) {
            $request = App::getRequest();
            if ($request->isSecure()) {
                $secure = true;
            }
        }

        $hash = strtolower(md5($this->email));
        if ($secure) {
            $url = 'https://secure.gravatar.com/avatar/'.$hash.'?';
        } else {
            $url = 'http://www.gravatar.com/avatar/'.$hash.'?';
        }

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

            $hash      = strtolower(md5($this->email));
            $check_url = 'http://www.gravatar.com/avatar/'.$hash.'?d=404';

            $headers = @get_headers($check_url);
            if ($headers and !empty($headers[0])) {
                if (strpos($headers[0], '200') !== false) {
                    $is_real = true;
                }
            }
        }

        return $is_real;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $emailDomain = '';
        if ($email && strpos($email, '@')) {
            list(, $emailDomain) = explode('@', $email, 2);
        }

        $this->setModelField('email', strtolower($email));
        $this->setModelField('email_domain', $emailDomain);

        return $this;
    }

    /**
     * Returns email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function setIsValidated($yesno)
    {
        $this->setModelField('is_validated', $yesno);

        if ($yesno) {
            $this->setModelField('date_validated', new \DateTime());
        } else {
            $this->setModelField('date_validated', null);
        }

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->prevPerson = $this->person;

        $this->setModelField('person', $person);
        if ($person->is_agent) {
            $this->setIsValidated(true);
        }

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return int|null
     */
    public function getPrevPersonId()
    {
        return $this->prevPerson ? $this->prevPerson->getId() : null;
    }

    public function getPersonId()
    {
        return $this->person ? $this->person->getId() : null;
    }

    public function _postPersist()
    {
        if (!$this->person) {
            return;
        }

        $user_rule_proc = new \Application\DeskPRO\People\UserRuleProcessor(App::getOrm());
        $user_rule_proc->newEmail($this->person, $this);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PersonEmail';

        $metadata->setPrimaryTable([
            'name'    => 'people_emails',
            'indexes' => [
                'email_domain_idx' => ['columns' => ['email_domain']],
            ],
            'uniqueConstraints' => [
                'email_idx' => ['columns' => ['email']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->addLifecycleCallback('_postPersist', 'postPersist');
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'email',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email_domain',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'email_domain',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_validated',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_validated',
        ]);
        $metadata->mapField([
            'fieldName'  => 'comment',
            'type'       => 'text',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'comment',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_validated',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_validated',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => 'emails',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);

        $metadata->addEntityListener(Events::prePersist, PersonEmailListener::class, 'verifyEmailAddress');
        $metadata->addEntityListener(Events::preUpdate, PersonEmailListener::class, 'verifyEmailAddress');
    }

    /**
     * @return bool
     */
    public function isValidated()
    {
        return $this->is_validated;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->email;
    }
}
