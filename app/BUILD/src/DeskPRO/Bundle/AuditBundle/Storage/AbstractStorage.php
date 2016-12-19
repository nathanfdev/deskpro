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

use DeskPRO\Bundle\AuditBundle\Entity\AuditLog;
use DeskPRO\Bundle\AuditBundle\Log\AuditLogService;
use DeskPRO\Bundle\AuditBundle\Log\LoggableInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Class AbstractStorage.
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param LoggableInterface $log
     */
    public function write(LoggableInterface $log)
    {
        $this->manager->persist($log);
        $this->manager->flush();
    }

    /**
     * @param mixed $id
     *
     * @return AuditLog
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array|\DeskPRO\Bundle\AuditBundle\Entity\AuditLog[]
     */
    public function read($offset, $limit)
    {
        return $this->getRepository()->findBy([], [], $limit, $offset);
    }

    /**
     * @return ObjectRepository
     */
    abstract protected function getRepository();

    /**
     * @param $period
     *
     * @return \DateTime|null
     */
    protected function getDate($period)
    {
        switch ($period) {
            case AuditLogService::PERIOD_1_DAY:
                $date = new \DateTime('-1 day');
                break;
            case AuditLogService::PERIOD_1_WEEK:
                $date = new \DateTime('-7 days');
                break;
            case AuditLogService::PERIOD_1_MONTH:
                $date = new \DateTime('-30 days');
                break;
            case AuditLogService::PERIOD_3_MONTHS:
                $date = new \DateTime('-3 months');
                break;
            case AuditLogService::PERIOD_6_MONTHS:
                $date = new \DateTime('-6 months');
                break;
            case AuditLogService::PERIOD_1_YEAR:
                $date = new \DateTime('-1 year');
                break;
            default:
                $date = null;
        }

        return $date;
    }
}
