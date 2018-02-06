<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ImportBundle\Writer;

use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\ImportBundle\Model\OidAwareModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use Doctrine\ORM\EntityManager;
use Orb\Util\Util;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class EntityPersister.
 */
class EntityPersister
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketManager
     */
    private $ticketManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param TicketManager      $ticketManager
     * @param ValidatorInterface $validator
     * @param ImportMapMapper    $importMapMapper
     * @param LoggerInterface    $logger
     */
    public function __construct(
        EntityManager      $em,
        TicketManager      $ticketManager,
        ValidatorInterface $validator,
        ImportMapMapper    $importMapMapper,
        LoggerInterface    $logger
    ) {
        $this->em              = $em;
        $this->ticketManager   = $ticketManager;
        $this->validator       = $validator;
        $this->importMapMapper = $importMapMapper;
        $this->logger          = $logger;
    }

    /**
     * @param mixed $entity
     * @param mixed $model
     */
    public function persistAndFlush($entity, $model = null)
    {
        $errors = $this->validator->validate($entity);
        if (count($errors)) {
            $this->logger->error($errors);
            throw new \RuntimeException('Unable to persist entity, validation failed.');
        }

        $this->em->persist($entity);
        $this->em->flush();

        $this->logger->debug(sprintf(
            'Persisted %s #%s',

            Util::getBaseClassname($entity),
            method_exists($entity, 'getId') ? $entity->getId() : '_'
        ));

        // Save entity oid mapping
        if ($entity && method_exists($entity, 'getId')) {
            if ($model instanceof OidAwareModelInterface && $model->getOid()) {
                $this->importMapMapper->saveMapping($model, $entity);
            }
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeAndFlush($entity)
    {
        $this->logger->debug(sprintf(
            'Remove %s #%s',

            Util::getBaseClassname($entity),
            method_exists($entity, 'getId') ? $entity->getId() : '_'
        ));

        $this->em->remove($entity);
        $this->em->flush();
    }
}
