<?php

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
