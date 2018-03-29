<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\PersonMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PersonHelper.
 */
class PersonHelper
{
    /**
     * @var PersonMapper
     */
    private $personMapper;

    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * Constructor.
     *
     * @param PersonMapper       $personMapper
     * @param ImportMapMapper    $importMapMapper
     * @param ValidatorInterface $validator
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(
        PersonMapper       $personMapper,
        ImportMapMapper    $importMapMapper,
        ValidatorInterface $validator,
        EntityPersister    $persister,
        LoggerInterface    $logger
    ) {
        $this->personMapper    = $personMapper;
        $this->importMapMapper = $importMapMapper;
        $this->validator       = $validator;
        $this->persister       = $persister;
        $this->logger          = $logger;
    }

    /**
     * @param string $personOidOrEmail
     * @param bool   $isAgent
     *
     * @return Entity\Person|null
     */
    public function findOrCreatePerson($personOidOrEmail, $isAgent = false)
    {
        if (!$personOidOrEmail) {
            return;
        }
        if (!is_scalar($personOidOrEmail)) {
            throw new \RuntimeException('Person email or id is not a scalar value.');
        }

        $entity = null;

        // try to find person by oid
        $model = new Model\Person();
        $model->setOid($personOidOrEmail);

        $entityId = $this->importMapMapper->findIdByModel($model);
        if ($entityId) {
            $entity = $this->personMapper->find($entityId);
        }

        // try to find person by emails
        if (!$entity) {
            $entity = $this->personMapper->findOneByEmail($personOidOrEmail);
            if (!$entity) {
                // try to create person with real email
                $errors = $this->validator->validate($personOidOrEmail, [
                    new Assert\Email(),
                ]);

                if (!count($errors)) {
                    $entity = new Entity\Person();
                    $entity->setEmail($personOidOrEmail);

                    // reset $model to avoid unnecessary import map entities
                    $model = null;
                }
            }
        }

        // try to find person by auto generated email
        if (!$entity) {
            $emailPart = strtolower($personOidOrEmail);
            $emailPart = preg_replace('#[^\w\d\.]#', '', $emailPart);
            $emailPart = preg_replace('#^(user|agent)_(.+)#', '$2', $emailPart);

            if ($isAgent) {
                $personEmail = "imported.agent.$emailPart@example.com";
            } else {
                $personEmail = "imported.user.$emailPart@example.com";
            }

            $entity = $this->personMapper->findOneByEmail($personEmail);
            if (!$entity) {
                $entity = new Entity\Person();
                $entity->setEmail($personEmail);
            }
        }

        if (!$entity->getId()) {
            $entity->setName($entity->getDisplayName());
            $this->persister->persistAndFlush($entity, $model);
        }

        // force set person as agent
        if ($isAgent && !$entity->isAgent()) {
            $entity->setIsAgent(true);
            $this->persister->persistAndFlush($entity, $model);
        }

        return $entity;
    }
}
