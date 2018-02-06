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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\OrganizationMapper;
use Psr\Log\LoggerInterface;

/**
 * Class OrganizationHelper.
 */
class OrganizationHelper
{
    /**
     * @var OrganizationMapper
     */
    private $organizationMapper;

    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param OrganizationMapper $organizationMapper
     * @param ImportMapMapper    $importMapMapper
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(
        OrganizationMapper $organizationMapper,
        ImportMapMapper    $importMapMapper,
        EntityPersister    $persister,
        LoggerInterface    $logger
    ) {
        $this->organizationMapper = $organizationMapper;
        $this->importMapMapper    = $importMapMapper;
        $this->persister          = $persister;
        $this->logger             = $logger;
    }

    /**
     * @param string $organizationOidOrName
     *
     * @return Entity\Organization
     */
    public function findOrCreateOrganization($organizationOidOrName)
    {
        if (!$organizationOidOrName) {
            return;
        }
        if (!is_scalar($organizationOidOrName)) {
            throw new \RuntimeException('Person email or id is not scalar value.');
        }

        $entity = null;

        if (is_int($organizationOidOrName) || ctype_digit($organizationOidOrName)) {
            $model = new Model\Organization();
            $model->setOid($organizationOidOrName);

            $entityId = $this->importMapMapper->findIdByModel($model);
            if ($entityId) {
                $entity = $this->organizationMapper->find($entityId);
            }
            if (!$entity) {
                $organizationName = 'Organization'.$organizationOidOrName;

                $entity = $this->organizationMapper->findOneByTitle($organizationName);
                if (!$entity) {
                    $entity = new Entity\Organization();
                    $entity->setName($organizationName);
                }
            }
        } else {
            $model  = null;
            $entity = $this->organizationMapper->findOneByTitle($organizationOidOrName);
            if (!$entity) {
                $entity = new Entity\Organization();
                $entity->setName($organizationOidOrName);
            }
        }

        if (!$entity->getId()) {
            $this->persister->persistAndFlush($entity, $model);
        }

        return $entity;
    }
}
