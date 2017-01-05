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

namespace DeskPRO\Bundle\AuditBundle\Configuration;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Log\Performer;

/**
 * Class AuditContext.
 */
class AuditContext
{
    /**
     * @var EntityInterface|DomainObject
     */
    private $entity;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $changeSet;

    /**
     * @var Performer
     */
    private $performer;

    /**
     * AuditContext constructor.
     *
     * @param string                       $action
     * @param Performer                    $performer
     * @param EntityInterface|DomainObject $entity
     * @param array                        $changeSet
     */
    public function __construct($action, Performer $performer, $entity = null, array $changeSet = [])
    {
        // entity is set and it's not and object OR it's neither EntityInterface nor DomainObject instance
        if (
            $entity &&
            !(is_object($entity) && ($entity instanceof EntityInterface || $entity instanceof DomainObject))
        ) {
            throw new \InvalidArgumentException(
                'An entity passed to AuditContext should be an EntityInterface or DomainObject instance'
            );
        }

        $this->action    = $action;
        $this->entity    = $entity;
        $this->performer = $performer;
        $this->changeSet = $changeSet;
    }

    /**
     * @return DomainObject|EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @return Performer
     */
    public function getPerformer()
    {
        return $this->performer;
    }
}
