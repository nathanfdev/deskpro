<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Entity;

/**
 * Ticket message mapper.
 * 
 * Class TicketMessage
 */
final class TicketMessage extends AbstractImportMapMapper
{
    /**
     * @var EntityRepository\TicketMessage
     */
    private $ticket_message_repository;

    /**
     * Constructor.
     *
     * @param EntityRepository\TicketMessage $ticket_message_repository
     * @param EntityRepository\ImportMap     $import_map_repository
     */
    public function __construct(EntityRepository\TicketMessage $ticket_message_repository, EntityRepository\ImportMap $import_map_repository)
    {
        $this->ticket_message_repository = $ticket_message_repository;
        $this->import_map_repository     = $import_map_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_TICKET_MESSAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        $record = null;
        $id     = $this->findImportMapNewId($criteria);

        if ($id) {
            $record = $this->ticket_message_repository->find($id);
        } else {
            if (isset($criteria['entity'])) {
                unset($criteria['entity']);
            }

            if (!empty($criteria)) {
                $record = $this->ticket_message_repository->findOneBy($criteria);
            }
        }

        /** @var DeskPROEntity\TicketMessage $record */
        if (!$record && $throw_exception) {
            throw new MapperException('Ticket message not found', $criteria);
        }

        return $record;
    }
}
