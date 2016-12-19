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

namespace DeskPRO\Bundle\AuditBundle\Storage;

use DeskPRO\Bundle\AuditBundle\Document\AuditLogData;
use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use DeskPRO\Bundle\AuditBundle\Log\LoggableInterface;

/**
 * Class AbstractTransformer.
 */
abstract class AbstractTransformer implements TransformerInterface
{
    /**
     * @param AuditLog $log
     *
     * @return AuditLogEntity
     */
    public function transform(AuditLog $log)
    {
        $concreteLog = $this->createAuditLog();

        $concreteLog
            ->setId($log->getId())
            ->setDateCreated($log->getDateCreated())
            ->setObjectName($log->getObjectName())
            ->setObjectId($log->getObjectId())
            ->setObjectType($log->getObjectType())
            ->setDescription($log->getDescription())
            ->setAction($log->getAction())
            ->setPerformerId($log->getPerformerId())
            ->setPerformerName($log->getPerformerName())
            ->setApiKey($log->getApiKey());

        $concreteLog->setData($log->getData() ?: new AuditLogData());

        return $concreteLog;
    }

    /**
     * @param LoggableInterface $loggable
     *
     * @return AuditLog
     */
    public function reverseTransform(LoggableInterface $loggable)
    {
        /** @var AuditLogEntity$loggable */
        $log = new AuditLog();

        $log
            ->setId($loggable->getId())
            ->setDateCreated($loggable->getDateCreated())
            ->setObjectName($loggable->getObjectName())
            ->setObjectId($loggable->getObjectId())
            ->setObjectType($loggable->getObjectType())
            ->setDescription($loggable->getDescription())
            ->setAction($loggable->getAction())
            ->setPerformerId($loggable->getPerformerId())
            ->setPerformerName($loggable->getPerformerName())
            ->setApiKey($loggable->getApiKey());

        $log->setData($loggable->getData());

        return $log;
    }

    /**
     * @param array $collection
     *
     * @return array
     */
    public function reverseTransformCollection(array $collection)
    {
        $transformed = [];
        foreach ($collection as $item) {
            $transformed[] = $this->reverseTransform($item);
        }

        return $transformed;
    }

    /**
     * the mongo document just extends entity, so we can relay on entity interface.
     *
     * @return AuditLogEntity
     */
    abstract protected function createAuditLog();

    public function updateLog(LoggableInterface $loggable, AuditLog $log)
    {
        $loggable
            ->setAction($log->getAction())
            ->setApiKey($log->getApiKey())
            ->setData($log->getData())
            ->setDateCreated($log->getDateCreated())
            ->setDescription($log->getDescription())
            ->setObjectId($log->getObjectId())
            ->setObjectName($log->getObjectName())
            ->setObjectType($log->getObjectType())
            ->setPerformerId($log->getPerformerId())
            ->setPerformerName($log->getPerformerName());

        return $loggable;
    }
}
