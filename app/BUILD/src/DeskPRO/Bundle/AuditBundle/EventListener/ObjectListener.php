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

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use DeskPRO\Component\Util\TypeUtils;

class ObjectListener
{
    public function setObject(LogEvent $event)
    {
        $log = $event->getLog();
        /** @var EntityInterface|DomainObject $entity */
        $entity = $event->getEntity();
        $log->setObjectId($entity->getId())->setObjectType(TypeUtils::getBaseTypeName($entity));
        $this->writeObjectName($log, $entity);
    }

    private function writeObjectName(AuditLog $log, $entity)
    {
        switch (true) {
            case method_exists($entity, 'getDisplayName'):
                $name = $entity->getDisplayName();
                break;
            case method_exists($entity, 'getName'):
                $name = $entity->getName();
                break;
            case method_exists($entity, 'getTitle'):
                $name = $entity->getTitle();
                break;
            default:
                $name = sprintf('%s-%s', $log->getObjectType(), $log->getObjectId());
                break;
        }

        $log->setObjectName($name);
    }
}
