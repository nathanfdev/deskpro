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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper;

use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository;

/**
 * Ticket label record mapper
 *
 * Class TicketLabel
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper
 */
final class TicketLabel implements MapperInterface
{
    /**
     * @var EntityRepository\LabelTicket
     */
    private $repository;

    /**
     * Constructor
     *
     * @param EntityRepository\LabelTicket $repository
     */
    public function __construct(EntityRepository\LabelTicket $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_TICKET_LABEL;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        /** @var Entity\LabelTicket $record */
        $record = $this->repository->findOneBy($criteria);
        if ( ! $record && $throw_exception) {
            throw new MapperException('Ticket label not found', $criteria);
        }

        return $record;
    }

    /**
     * Returns a collection of ticket labels
     *
     * @param int  $id
     * @param bool $throw_exception
     *
     * @return Entity\LabelTicket[]
     * @throws MapperException
     */
    public function findByTicketId($id, $throw_exception = true)
    {
        $criteria = array('ticket' => $id);
        $records  = $this->repository->findBy($criteria);

        if (empty($records) && $throw_exception) {
            throw new MapperException('Ticket labels not found', $criteria);
        }

        return $records;
    }
}
