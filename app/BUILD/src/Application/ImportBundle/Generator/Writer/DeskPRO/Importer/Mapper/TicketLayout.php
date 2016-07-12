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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper;

use Application\DeskPRO\Entity;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Ticket layout record mapper.
 *
 * Class TicketLayout
 */
final class TicketLayout implements MapperInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * Constructor.
     *
     * @param ObjectRepository $repository
     */
    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_TICKET_LAYOUT;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        /** @var Entity\TicketLayout $record */
        $record = $this->repository->findOneBy($criteria);
        if (!$record && $throw_exception) {
            throw new MapperException('Ticket layout not found', $criteria);
        }

        return $record;
    }

    /**
     * Returns all ticket layouts.
     *
     * @return Entity\TicketLayout[]
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }
}
