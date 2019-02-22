<?php

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
    }

    public function finishWriting()
    {
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
