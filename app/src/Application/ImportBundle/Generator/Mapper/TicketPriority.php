<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Mapper;

use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository;

/**
 * Ticket priority record mapper
 *
 * Class TicketPriority
 * @package Application\ImportBundle\Generator\Mapper
 */
class TicketPriority implements MapperInterface
{
    /**
     * @var EntityRepository\TicketPriority
     */
    private $repository;

    /**
     * Constructor
     *
     * @param EntityRepository\TicketPriority $repository
     */
    public function __construct(EntityRepository\TicketPriority $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_TICKET_PRIORITY;
    }

    /**
     * {@inheritdoc}
     */
    public function findIdByValue($value)
    {
        /** @var Entity\TicketPriority $record */
        $record = $this->repository->findOneBy(array('title' => $value));
        if (!$record) {
            throw new MapperException(sprintf('Ticket priority `%s` not found', $value));
        }

        return $record->getId();
    }
}
