<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Comment;

use Application\DeskPRO\Entity\CommentAbstract as CommentAbstractEntity;
use Application\DeskPRO\Entity\Person;
use DateTime;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
abstract class CommentAbstract
{
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
     * @var Person
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
    protected $ipAddress = '';

    /**
     * Visitor`s unique id.
     *
     * @JMS\Exclude()
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $visitorId = '';

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
     * Comment`s content in HTML.
     *
     * @JMS\Type("string")
     * @JMS\Groups({"details"})
     *
     * @var string
     */
    protected $contentHtml;

    /**
     * Comment`s status.
     *
     * @JMS\Type("string")
     * @JMS\Groups({"list", "details"})
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
    protected $isReviewed = false;

    /**
     * When this comment was created.
     *
     * @JMS\Type("DateTime")
     * @JMS\Groups({"list", "details"})
     *
     * @var DateTime
     */
    protected $dateCreated;

    /**
     * The user contact details.
     *
     * @JMS\Type("string")
     * @JMS\Groups({"list", "details"})
     *
     * @var string
     */
    protected $userDisplayContact;

    /**
     * Constructor.
     *
     * @param CommentAbstractEntity $entity
     */
    public function __construct($entity)
    {
        $this->id                 = $entity->getId();
        $this->person             = $entity->getPerson();
        $this->ipAddress          = $entity->getIpAddress();
        $this->email              = $entity->getEmail();
        $this->name               = $entity->getName();
        $this->website            = $entity->getWebsite();
        $this->content            = $entity->getContent();
        $this->contentHtml        = $entity->getContentHtml();
        $this->status             = $entity->getStatus();
        $this->isReviewed         = $entity->isReviewed();
        $this->dateCreated        = $entity->getDateCreated();
        $this->userDisplayContact = $entity->getUserDisplayContact();
    }
}
