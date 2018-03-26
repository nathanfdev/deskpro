<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Records a log of a sync job for a particular usersource.
 */
class UsersourceSyncLog extends \Application\DeskPRO\Domain\DomainObject
{
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR     = 'connection error';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING   = 'pending';

    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The usersource.
     *
     * @var Usersource
     */
    protected $usersource;

    /**
     * The number of synced records.
     *
     * @var int
     */
    protected $record_count = 0;

    /**
     * The start datetime of phase one of this sync.
     *
     * @var \DateTime
     */
    protected $date_start;

    /**
     * The end datetime of phase one of this sync.
     *
     * @var \DateTime
     */
    protected $date_end;

    /**
     * End of the second phase of syncing.
     *
     * @var \DateTime
     */
    protected $date_phase_2_start;

    /**
     * End of the second phase of syncing.
     *
     * @var \DateTime
     */
    protected $date_phase_2_end;

    /**
     * @var string
     */
    protected $status;

    public function __construct()
    {
        $this->setStatus(self::STATUS_PENDING);
    }

    public function incrementRecordCount()
    {
        $this->setModelField('record_count', $this->getRecordCount() + 1);
    }

    public function isInProgress()
    {
        return null === $this->getDateEnd();
    }

    public function startPhaseOne()
    {
        $this->setDateStart(new \DateTime());
    }

    public function endPhaseOne()
    {
        $this->setDateEnd(new \DateTime());
    }

    public function startPhaseTwo()
    {
        $this->setDateStartPhaseTwo(new \DateTime());
    }

    public function endPhaseTwo()
    {
        $this->setDateEndPhaseTwo(new \DateTime());
    }

    public function getPhaseOneTimeInSeconds()
    {
        if ($this->date_start && $this->date_end) {
            $diff = $this->date_end->getTimestamp() - $this->date_start->getTimestamp();

            return $diff < 0 ? 0 : $diff;
        }

        return;
    }

    public function getPhaseTwoTimeInSeconds()
    {
        if ($this->date_phase_2_start && $this->date_phase_2_end) {
            $diff = $this->date_phase_2_end->getTimestamp() - $this->date_phase_2_start->getTimestamp();

            return $diff < 0 ? 0 : $diff;
        }

        return;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Usersource
     */
    public function getUsersource()
    {
        return $this->usersource;
    }

    /**
     * @param Usersource $usersource
     */
    public function setUsersource(Usersource $usersource)
    {
        $this->setModelField('usersource', $usersource);
    }

    /**
     * @return int
     */
    public function getRecordCount()
    {
        return $this->record_count;
    }

    /**
     * @param int $record_count
     */
    public function setRecordCount($record_count)
    {
        $this->setModelField('record_count', $record_count);
    }

    /**
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->date_start;
    }

    /**
     * @param \DateTime $date_start
     */
    public function setDateStart($date_start)
    {
        $this->setModelField('date_start', $date_start);
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->date_end;
    }

    /**
     * @param \DateTime $date_end
     */
    public function setDateEnd($date_end)
    {
        $this->setModelField('date_end', $date_end);
    }

    /**
     * @return \DateTime
     */
    public function getDateStartPhaseTwo()
    {
        return $this->date_start;
    }

    /**
     * @param \DateTime $date_start
     */
    public function setDateStartPhaseTwo($date_start)
    {
        $this->setModelField('date_phase_2_start', $date_start);
    }

    /**
     * @return \DateTime
     */
    public function getDateEndPhaseTwo()
    {
        return $this->date_end;
    }

    /**
     * @param \DateTime $date_end
     */
    public function setDateEndPhaseTwo($date_end)
    {
        $this->setModelField('date_phase_2_end', $date_end);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);
    }

    public function markErrorStatus()
    {
        $this->setStatus(self::STATUS_ERROR);
    }

    public function markCompletedStatus()
    {
        $this->setStatus(self::STATUS_COMPLETED);
    }

    public function markCancelledStatus()
    {
        $this->setStatus(self::STATUS_CANCELLED);
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder
            ->setTable('usersource_sync_log')
            ->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\UsersourceSyncLog')
            ->setChangeTrackingPolicyNotify();
        $builder->mapId();

        $metadata->mapManyToOne([
            'fieldName'    => 'usersource',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Usersource',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'usersource_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);

        $builder->mapInteger('record_count', false);
        $builder->mapDateTime('date_start');
        $builder->mapDateTime('date_end');
        $builder->mapDateTime('date_phase_2_start');
        $builder->mapDateTime('date_phase_2_end');
        $builder->mapString('status');
    }
}
