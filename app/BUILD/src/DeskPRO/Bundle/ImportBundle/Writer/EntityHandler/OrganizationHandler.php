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
