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
    public function findStatus($statusCode, $withSubstatuses = false)
    {
        $statusType = $statusCode;
        $statusId   = null;
        if (strpos($statusType, '.')) {
            list($statusType, $statusId) = explode('.', $statusType, 2);
        }

        if ($statusId) {
            $statusEntity = $this->repository->find($statusId);
            if (!$statusEntity || $statusEntity->getStatusType() !== $statusType) {
                throw new \InvalidArgumentException(sprintf("Can't find TicketStus `%s`", $statusCode));
            }
        } else {
            if (!TicketStatus::isValidStatusType($statusType)) {
                throw new \InvalidArgumentException(sprintf('Not valid status type `%s`', $statusType));
            }
            $statusEntity = new VirtualTicketStatus($statusType);

            if ($withSubstatuses) {
                $children = $this->repository->findByStatusType($statusType);
                $statusEntity->setChildren($children);
            }
        }

        return $statusEntity;
    }

    /**
     * [
     *    ['title': ..., 'value': ...]
     * ].
     *
     * @return array
     */
    public function getFormOptions()
    {
        $res = [];

        foreach (TicketStatus::getStatusTypes() as $statusType) {
            $topLevelStatus = $this->findStatus($statusType, true);
            $res[]          = [
                'value' => $topLevelStatus->getStatusCode(),
                // translate
                'title' => $topLevelStatus->getStatusType(),
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
}
