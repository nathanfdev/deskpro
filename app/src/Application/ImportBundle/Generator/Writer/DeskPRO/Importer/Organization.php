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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * DeskPRO organization importer.
 *
 * Class Organization
 */
final class Organization extends AbstractImporter
{
    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor.
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
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        if (!$entity instanceof Entity\Organization) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $organization = $this->findOrCreateOrganization($entity->getName());
        $organization
            ->setImportance($entity->getImportance())
            ->setDateCreated($entity->getDateCreated())
            ->resetContactData()
            ->resetCustomData()
            ->resetLabels()
        ;

        if ($entity->getPicture()) {
            $picture = $this->blob_adapter->createByBlob($entity->getPicture());

            $organization->setPicture($picture);
            $this->records->addRelatedEntity($picture);
        }
        foreach ($entity->getContactData() as $contact) {
            $contact_data = $this->createContactData($contact);

            $organization->addContactData($contact_data);
            $this->records->addRelatedEntity($contact_data);
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $custom_field = $this->createOrganizationCustomData($custom_field);
            if ($custom_field) {
                $organization->addCustomData($custom_field);
            }
        }

        $this->records->setPrimaryEntity($organization);
    }

    /**
     * Returns organization contact data entity.
     *
     * @param Entity\ContactData $entity
     *
     * @return DeskPROEntity\OrganizationContactData
     */
    private function createContactData(Entity\ContactData $entity)
    {
        $contact = new DeskPROEntity\OrganizationContactData();
        $contact
            ->setContactType($entity->getContactType())
            ->setComment($entity->getComment())
            ->setField1($entity->getField1())
            ->setField2($entity->getField2())
            ->setField3($entity->getField3())
            ->setField4($entity->getField4())
            ->setField5($entity->getField5())
            ->setField6($entity->getField6())
            ->setField7($entity->getField7())
            ->setField8($entity->getField8())
            ->setField9($entity->getField9())
            ->setField10($entity->getField10())
        ;

        return $contact;
    }

    /**
     * Returns organization custom data entity.
     *
     * @param Entity\CustomField $entity
     *
     * @throws ImporterException
     *
     * @return DeskPROEntity\CustomDataOrganization
     */
    private function createOrganizationCustomData(Entity\CustomField $entity)
    {
        return $this->createCustomData($this->getOrganizationCustomDefMapper(), $entity, new DeskPROEntity\CustomDataOrganization());
    }
}
