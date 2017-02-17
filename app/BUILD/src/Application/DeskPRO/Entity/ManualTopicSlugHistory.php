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

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use DateTime;

/**
 * Class ManualTopicSlugHistory.
 */
class ManualTopicSlugHistory extends DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ManualTopic
     */
    protected $manual_topic;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var DateTime
     */
    protected $date_created;

    public function __construct(ManualTopic $manualTopic, $oldSlug)
    {
        $this->setContent($manualTopic);
        $this->setSlug($oldSlug);
        $this->setModelField('date_created', new DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ManualTopicSlugHistory
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return ManualTopic
     */
    public function getContent()
    {
        return $this->manual_topic;
    }

    /**
     * @param ManualTopic $manual_topic
     *
     * @return ManualTopicSlugHistory
     */
    public function setContent(ManualTopic $manual_topic)
    {
        $this->setModelField('manual_topic', $manual_topic);

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return ManualTopicSlugHistory
     */
    public function setSlug($slug)
    {
        $this->setModelField('slug', $slug);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     *
     * @return ManualTopicSlugHistory
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }
}
