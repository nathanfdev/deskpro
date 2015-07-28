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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPRO organization importer
 *
 * Class Organization
 * @package Application\ImportBundle\Generator\Writer\DeskPRO\Importer
 */
final class Organization extends AbstractImporter
{
    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor
     *
     * @param Mapper\Collection    $mappers
     * @param BlobAdapterInterface $blob_adapter
     */
    public function __construct(Mapper\Collection $mappers, BlobAdapterInterface $blob_adapter)
    {
        parent::__construct($mappers);
        $this->blob_adapter = $blob_adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ORGANIZATION;
    }

    /**
     * {@inheritdoc}
     *
     * @var Entity\Organization $entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        $this->records = new ArrayCollection();

        $organization = $this->findOrCreateOrganization($entity);
        $organization
            ->setImportance($entity->getImportance())
            ->setPicture($entity->getPicture())
            ->resetContactData()
            ->resetLabels()
            ->resetCustomData()
        ;

        foreach ($entity->getContactData() as $contact) {
            $entity->addContact($contact);
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $custom_field = $this->createOrganizationCustomData($custom_field);
            if ($custom_field) {
                $organization->addCustomData($custom_field);
            }
        }

        $this->records->add($organization);
        return $this->records;
    }

    /**
     * Returns organization custom data entity
     *
     * @param Entity\CustomField $entity
     *
     * @return DeskPROEntity\CustomDataOrganization
     * @throws ImporterException
     */
    private function createOrganizationCustomData(Entity\CustomField $entity)
    {
        $mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_CUSTOM_DEF_ORGANIZATION);

        return $this->createCustomData($mapper, $entity, new DeskPROEntity\CustomDataOrganization());
    }
}
