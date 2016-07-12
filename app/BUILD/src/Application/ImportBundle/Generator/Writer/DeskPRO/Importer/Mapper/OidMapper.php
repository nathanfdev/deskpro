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
use Application\DeskPRO\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Oid mapper
 * Uses to link importing and DeskPRO entities in case to update.
 *
 * Class ImportMap
 */
class OidMapper
{
    /**
     * @var EntityRepository\ImportMap
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $entity_manager;

    /**
     * Constructor.
     *
     * @param EntityRepository\ImportMap $repository
     * @param ObjectManager              $entity_manager
     */
    public function __construct(EntityRepository\ImportMap $repository, ObjectManager $entity_manager)
    {
        $this->repository     = $repository;
        $this->entity_manager = $entity_manager;
    }

    /**
     * Find an entity mapping.
     *
     * @param string $type
     * @param int    $id
     *
     * @return string|null
     */
    public function findRefByOldId($type, $id)
    {
        /** @var Entity\ImportMap $mapping */
        $mapping = $this->repository->findOneBy([
            'old_id'   => $id,
            'typename' => $type,
        ]);

        return $mapping ? $mapping->getNewId() : null;
    }

    /**
     * Saves an entity mapping.
     *
     * @param string $type
     * @param int    $old_id
     * @param int    $ref
     *
     * @return $this
     */
    public function saveMapping($type, $old_id, $ref)
    {
        $entity = new Entity\ImportMap();
        $entity
            ->setTypename($type)
            ->setOldId($old_id)
            ->setNewId($ref)
        ;

        $this->entity_manager->persist($entity);
        $this->entity_manager->flush();

        return $this;
    }
}
