<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use DeskPRO\Component\Util\RegexUtils;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base comments.
 *
 * @JMS\ExclusionPolicy("none")
 */
abstract class CommentAbstract extends \Application\DeskPRO\Domain\DomainObject
{
    const OBJ_PROP = '__abstract__';

    /**
     * Publicly visible.
     */
    const STATUS_VISIBLE = 'visible';

    /**
     * Not public, but visible to agents.
     */
    const STATUS_HIDDEN = 'hidden';

    /**
     * Soft-deleted. Will be cleaned up eventually.
     */
    const STATUS_DELETED = 'deleted';

    /**
     * TODO what is?
     */
    const STATUS_AGENT = 'agent';

    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"list", "details"})
     *
     * @var int
     */
    protected $id = null;

    /**
     * The id of person that wrote this comment.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\Groups({"list", "details"})
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * IP address with which comment was written.
     *
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $ip_address = '';

    /**
     * Visitor`s unique id.
     *
     * @JMS\Exclude()
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $visitor_id = '';

    /**
     * Person`s email.
     *
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $email = null;

    /**
     * Person`s name.
     *
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $name = null;

    /**
     *  Website where comment was written.
     *
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $website = null;

    /**
     * Comment`s content itself.
     *
     * @JMS\Type("string")
     * @JMS\Groups({"list", "details"})
     *
     * @var string
     */
    protected $content;

    /**
     * Comment`s status.
     *
     * @JMS\Type("string")
     * @JMS\Groups({"list", "details"})
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $status = 'visible';

    /**
     * Has this comment been reviewed by an agent?
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"list", "details"})
     *
     * @var bool
     */
    protected $is_reviewed = false;

    /**
     * When this comment was created.
     *
     * @JMS\Type("DateTime")
     * @JMS\Groups({"list", "details"})
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this['date_created'] = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->setModelField('email', $email);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->setModelField('website', $website);
    }

    /**
     * @return bool
     */
    public function isReviewed()
    {
        return $this->is_reviewed;
    }

    /**
     * @param bool $is_reviewed
     *
     * @return $this
     */
    public function setIsReviewed($is_reviewed)
    {
        $this->setModelField('is_reviewed', $is_reviewed);

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @static
     *
     * @param Person $person
     * @param bool   $use_request not used anymore?
     *
     * @return \Application\DeskPRO\Entity\CommentAbstract
     */
    public static function newForPerson(Person $person, $use_request = true)
    {
        $comment         = new static();
        $comment->person = $person;

        return $comment;
    }

    /**
     * Get the email address for the person who made the comment, trying
     * the person record first if it exists.
     *
     * @return string
     */
    public function getUserEmail()
    {
        if ($this->person) {
            return $this->person->getPrimaryEmailAddress();
        } elseif ($this->email) {
            return $this->email;
        } else {
            return '';
        }
    }

    /**
     * Get the name for the person who made the comment, trying
     * the person record first if it exists.
     *
     * @param bool $force_user If true, forces the user display name
     *
     * @return string
     */
    public function getUserName($force_user = false)
    {
        if ($this->person) {
            if (DP_INTERFACE == 'user' || $force_user) {
                return $this->person->getDisplayNameUser();
            } else {
                return $this->person->getDisplayName();
            }
        } elseif ($this->name) {
            return $this->name;
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getUserDisplayContact()
    {
        if ($this->person) {
            return $this->person->getDisplayContact();
        } else {
            $display = $this->getUserName();
            if ($this->getUserEmail()) {
                $display .= ' <'.$this->getUserEmail().'>';
            }

            return $display;
        }
    }

    /**
     * Set the Status.
     *
     * @param $new_status
     *
     * @return $this
     */
    public function setStatus($new_status)
    {
        // any time after its created and the status is set
        // to visible means someone has reviewed its
        if ($this->id && $new_status == self::STATUS_VISIBLE) {
            $this->setModelField('is_reviewed', true);
        }

        $this->setModelField('status', $new_status);

        return $this;
    }

    /**
     * @param Person|null $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->setModelField('content', $content);

        return $this;
    }

    /**
     * @return string
     */
    public function getContentHtml()
    {
        return Strings::linkify(nl2br(htmlspecialchars($this->content, \ENT_NOQUOTES, 'UTF-8')));
    }

    /**
     * @return string
     */
    public function getContentReal()
    {
        return $this->content;
    }

    public function setContentReal($content)
    {
        $this->setModelField('content', $content);
    }

    /**
     * @return string
     */
    public function getContentHtmlPlain()
    {
        return nl2br(htmlspecialchars($this->content));
    }

    /**
     * Strip all HTML from the content and convert breaks and paragraphs to linebreaks.
     * Suitable for showing a "plain text" version of the content.
     *
     * @return string
     */
    public function getContentPlain()
    {
        if (!$this->content) {
            return '';
        }
        $content = Strings::standardEol($this->content);
        $content = RegexUtils::safePregReplace("#<br\s*/?><p>#", '<p>', $content);
        $content = RegexUtils::safePregReplace("#<p></p><br\s*/?>#", '<p>', $content);
        $content = RegexUtils::safePregReplace("#</p><br\s*/?>#", '</p>', $content);
        $content = RegexUtils::safePregReplace("#<br\s*/?></p>#", '</p>', $content);
        $content = RegexUtils::safePregReplace("#<br\s*/?>?#", "\n", $content);
        $content = RegexUtils::safePregReplace("#<p>\n?#", "\n", $content);
        $content = RegexUtils::safePregReplace("#\n?</p>#", "\n", $content);
        $content = html_entity_decode(strip_tags($content), \ENT_QUOTES, 'UTF-8');
        $content = trim($content);

        $lines_raw = explode("\n", $content);
        $lines     = [];
        foreach ($lines_raw as $l) {
            $lines[] = trim($l);
        }

        $content = implode("\n", $lines);
        $content = RegexUtils::safePregReplace("#\n{3,}#", "\n\n", $content);

        return $content;
    }

    /**
     * Get the author ID.
     *
     * @return int
     */
    public function getPersonId()
    {
        if ($this->person) {
            return $this->person->getId();
        }

        return 0;
    }

    /**
     * Get the entity this comment is attached to. This is a standardized way to fetch the
     * entity when you might not know the $comment->XXX to use.
     *
     * @return mixed
     */
    public function getObject()
    {
        $prop = static::OBJ_PROP;

        return $this->$prop;
    }

    /**
     * Set the content object.
     *
     * @param mixed $obj
     */
    public function setObject($obj)
    {
        $prop        = static::OBJ_PROP;
        $this[$prop] = $obj;
    }

    /**
     * Get the base clasname of the object.
     *
     * @return string
     */
    public function getObjectType()
    {
        return Util::getBaseClassname($this->getObject());
    }

    /**
     * Get the "content-type" of the object on this comment.
     *
     * @return string
     */
    public function getObjectContentType()
    {
        return $this->getObject()->getTableName();
    }

    /**
     * Set created at.
     *
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitor_id;
    }

    /**
     * @param string $visitor_id
     */
    public function setVisitorId($visitor_id)
    {
        $this->setModelField('visitor_id', $visitor_id);
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip_address
     */
    public function setIpAddress($ip_address)
    {
        $this->setModelField('ip_address', $ip_address);
    }

    public function isVisible()
    {
        return $this->getStatus() == self::STATUS_VISIBLE;
    }
}
