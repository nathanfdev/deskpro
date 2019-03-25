<?php

namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Ticket\VirtualTicketStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class TicketStatusDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Cached array with all TicketStatus entities.
     *
     * @var TicketStatus[]
     */
    protected $substatuses = null;

    public function __construct(EntityManager $em)
    {
        $this->em         = $em;
        $this->repository = $em->getRepository(TicketStatus::class);
    }

    /**
     * @return TicketStatus
     */
    public function getDeletedStatus()
    {
        return $this->repository->findOneBySysId('deleted');
    }

    /**
     * @return TicketStatus
     */
    public function getSpamStatus()
    {
        return $this->repository->findOneBySysId('spam');
    }

    /**
     * @param string $statusCode
     *
     * @throws \InvalidArgumentException
     *
     * @return VirtualTicketStatus
     */
    public function findStatusOrException($statusCode, $withSubstatuses = false, $withFallback = false)
    {
        $statusType = $statusCode;
        $statusId   = null;
        if (strpos($statusType, '.')) {
            list($statusType, $statusId) = explode('.', $statusType, 2);
        }

        if ($statusId) {
            if (is_numeric($statusId)) {
                $statusEntity = $this->repository->find($statusId);
            } else {
                // fallback to support `hidden.deleted` and `hidden.spam` statuses
                // for cases that has not been updated
                $statusEntity = $this->repository->findOneBySysId($statusId);
            }
            if (!$statusEntity || $statusEntity->getStatusType() !== $statusType) {
                if ($withFallback) {
                    return $this->findStatusOrException($statusType, $withSubstatuses);
                }
                throw new \InvalidArgumentException(sprintf("Can't find TicketStus `%s`", $statusCode));
            }
        } else {
            if (!TicketStatus::isValidStatusType($statusType)) {
                throw new \InvalidArgumentException(sprintf('Not valid status type `%s`', $statusType));
            }
            $statusEntity = VirtualTicketStatus::getById($statusType);

            if ($withSubstatuses) {
                $children = $this->repository->findByStatusType($statusType);
                $statusEntity->setChildren($children);
            }
        }

        return $statusEntity;
    }

    /**
     * Used in forms to get valid `status` options.
     * Result has a flat structure, just a list of possible statuses.
     *
     * [
     *    ['title': ..., 'value': ...]
     * ].
     *
     * @return array
     */
    public function getFormOptions()
    {
        $res = [];

        foreach ($this->getTopLevelStatuses(true) as $topLevelStatus) {
            $res[] = [
                'value' => $topLevelStatus->getStatusCode(),
                'title' => $topLevelStatus->getTitle(),
            ];
            foreach ($topLevelStatus->getChildren() as $subStatus) {
                $res[] = [
                    'value' => $subStatus->getStatusCode(),
                    'title' => $subStatus->getTitle(),
                ];
            }
        }

        return $res;
    }

    /**
     * @return array
     */
    public function getTopLevelStatuses($withSubstatuses = false)
    {
        $res = [];

        if ($withSubstatuses) {
            $substatuses = $this->getSubstatusesGrouppedByType();
        }

        foreach (TicketStatus::getStatusTypes() as $statusType) {
            $status = VirtualTicketStatus::getById($statusType);
            if ($withSubstatuses) {
                $status->setChildren($substatuses[$statusType]);
            }
            $res[$statusType] = $status;
        }

        return $res;
    }

    /**
     * @param string $statusCode
     * @param bool   $withFallback
     *
     * @return bool
     */
    public function isValidStatusCode($statusCode, $withFallback = false)
    {
        try {
            $status = $this->findStatusOrException($statusCode, false, $withFallback);
        } catch (\InvalidArgumentException $ex) {
            $status = null;
        }

        return $status !== null;
    }

    /**
     * @param TicketStatus $status
     * @param TicketStatus $setToStatus
     *
     * @throws \LogicException
     */
    public function deleteStatus(TicketStatus $status, TicketStatus $setToStatus = null)
    {
        if ($status->getSysId()) {
            throw new \LogicException(sprintf("Can't delete status #%s with sys_id set", $status->getStatusCode()));
        }
        if ($status instanceof VirtualTicketStatus || !$status->getId()) {
            throw new \LogicException(sprintf("Can't delete virtual status #%s", $status->getStatusCode()));
        }

        if ($setToStatus) {
            $db                = $this->em->getConnection();
            $newTicketStatusId = $setToStatus instanceof VirtualTicketStatus ? null : $setToStatus->getId();
            $db->executeUpdate('UPDATE tickets SET status = ?, ticket_status_id = ? WHERE ticket_status_id = ?', [
                $setToStatus->getStatusType(),
                $newTicketStatusId,
                $status->getId(),
            ]);
            $db->executeUpdate('UPDATE tickets_search_active SET status = ?, ticket_status_id = ? WHERE ticket_status_id = ?', [
                $setToStatus->getStatusType(),
                $newTicketStatusId,
                $status->getId(),
            ]);
        }

        $this->em->remove($status);
        $this->em->flush();
    }

    /**
     * [
     *     'awaiting_agent' => [],
     *     'awaiting_user'  => [],
     *     . . .
     * ].
     *
     * @return []
     */
    protected function getSubstatusesGrouppedByType()
    {
        $groupped = [];
        foreach (TicketStatus::getStatusTypes() as $statusType) {
            $groupped[$statusType] = [];
        }
        foreach ($this->getSubstatuses() as $substatus) {
            $groupped[$substatus->getStatusType()][] = $substatus;
        }

        return $groupped;
    }

    /**
     * @return TicketStatuses[]
     */
    protected function getSubstatuses()
    {
        if ($this->substatuses === null) {
            $this->substatuses = [];
            foreach ($this->repository->findAll() as $substatus) {
                $this->substatuses[$substatus->getId()] = $substatus;
            }
        }

        return $this->substatuses;
    }
}
