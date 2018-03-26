<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * DeskPRO organization importer.
 *
 * Class Organization
 */
class OrganizationHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Organization::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Organization $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var Entity\Organization $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getOrganizationMapper(), $model);
        $entity->setName($model->getName());
        $entity->setImportance($model->getImportance());

        if ($model->getDateCreated()) {
            $entity->setDateCreated($model->getDateCreated());
        }

        if ($model->getPicture()) {
            $entity->setPicture($this->helpers->getBlobAdapter()->createByBlob($model->getPicture(), false));
        } else {
            $entity->setPicture(null);
        }

        foreach ($model->getEmailDomains() as $emailDomain) {
            if ($entity->hasEmailDomain($emailDomain)) {
                continue;
            }

            $domainEntity = new Entity\OrganizationEmailDomain();
            $domainEntity->setDomain($emailDomain);

            $entity->addEmailDomain($domainEntity);
        }

        $this->helpers->getCustomDataHelper()->updateCustomData($this->mappers->getOrganizationCustomDefMapper(), $model, $entity);
        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelOrganization::class);

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);

        // persist others related entities which contains own oids
        $this->helpers->getOrganizationContactDataHelper()->updateContactData($model, $entity);
    }
}
