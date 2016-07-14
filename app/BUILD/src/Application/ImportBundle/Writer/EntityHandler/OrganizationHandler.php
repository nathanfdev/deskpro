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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Model;
use Application\ImportBundle\Writer\Helper\BlobAdapter;
use Application\ImportBundle\Writer\Helper\ContactDataHelper;
use Application\ImportBundle\Writer\Helper\CustomDataHelper;
use Application\ImportBundle\Writer\Helper\LabelHelper;
use Application\ImportBundle\Writer\Mapper\MapperRegistry;
use Psr\Log\LoggerInterface;

/**
 * DeskPRO organization importer.
 *
 * Class Organization
 */
class OrganizationHandler extends AbstractEntityHandler
{
    /**
     * @var BlobAdapter
     */
    private $blobAdapter;

    /**
     * Constructor.
     *
     * @param MapperRegistry  $mappers
     * @param LoggerInterface $logger
     * @param BlobAdapter     $blobAdapter
     */
    public function __construct(MapperRegistry $mappers, LoggerInterface $logger, BlobAdapter $blobAdapter)
    {
        parent::__construct($mappers, $logger);
        $this->blobAdapter = $blobAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Organization::class;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Model\ImportModelInterface $model, $entityId = null)
    {
        if (!$model instanceof Model\Organization) {
            Model\UnexpectedException::throwUnexpectedEntityTypeException($model);
        }

        $entity = $this->findOrCreateOrganization($model->getName());
        $entity
            ->setImportance($model->getImportance())
            ->setDateCreated($model->getDateCreated())
            ->resetContactData()
        ;

        if ($model->getPicture()) {
            $picture = $this->blobAdapter->createByBlob($model->getPicture());

            $entity->setPicture($picture);
            $this->records->addRelatedEntity($picture);
        }
        foreach ($this->createContactData($model) as $contactEntity) {
            $entity->addContactData($contactEntity);
        }

        $labelsHelper = new LabelHelper($this->logger);
        $labelsHelper->updateLabels($model, $entity, DeskPROEntity\LabelOrganization::class);

        $customDataHelper = new CustomDataHelper($this->mappers->getOrganizationCustomDefMapper(), $this->logger);
        $customDataHelper->updateCustomData($model, $entity, $this->records);

        $this->records->setPrimaryEntity($entity);
    }

    /**
     * Returns organization contact data entities.
     *
     * @param Model\Organization $model
     *
     * @return DeskPROEntity\OrganizationContactData[]
     */
    private function createContactData(Model\Organization $model)
    {
        return (new ContactDataHelper(DeskPROEntity\PersonContactData::class))->getEntities($model->getContactData());
    }
}
