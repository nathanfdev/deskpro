<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Json batch configuration.
 *
 * Class BatchConfig
 */
class BatchConfig
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $id = 1;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $date_created;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $date_modified;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $has_remaining = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateCreated(\DateTime $date_created = null)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateModified()
    {
        return $this->date_modified;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateModified(\DateTime $date_modified = null)
    {
        $this->date_modified = $date_modified;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasRemaining($has_remaining)
    {
        $this->has_remaining = (bool) $has_remaining;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRemaining()
    {
        return $this->has_remaining;
    }
}
