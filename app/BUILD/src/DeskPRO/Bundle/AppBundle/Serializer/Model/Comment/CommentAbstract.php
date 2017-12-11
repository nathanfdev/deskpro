<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
