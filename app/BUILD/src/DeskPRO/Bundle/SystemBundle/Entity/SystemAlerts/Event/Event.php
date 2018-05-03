<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use Doctrine\Common\NotifyPropertyChanged;

/**
 * Interface Event.
 */
interface Event extends EntityInterface, NotifyPropertyChanged
{
    /**
     * Get short human readable event subject description.
     *
     * Events happen with regards to various data which are called event subject. For example email failure event may
     * have a specific email address or Email entity as its' subject, Exception event subject may be concrete exception
     * class etc.
     *
     * @return string
     */
    public function getSubjectDescription();

    /**
     * Get event subject unique ID.
     *
     * We need subject unique ID to effectively process events, particularly to easily establish correspondence
     * between processed events and continuing incidents by having a map instead of iterating all incidents
     * to compare their subjects with the processed event subject.
     *
     * @return string
     */
    public function getSubjectUniqueId();

    /**
     * @return \DateTime
     */
    public function getDateCreated();

    /**
     * @param \DateTime $date
     */
    public function setDateCreated(\DateTime $date);

    /**
     * @return bool
     */
    public function isProcessed();

    /**
     * @param bool $processed
     */
    public function setProcessed($processed);

    /**
     * {@inheritdoc}
     */
    public function __toString();
}
