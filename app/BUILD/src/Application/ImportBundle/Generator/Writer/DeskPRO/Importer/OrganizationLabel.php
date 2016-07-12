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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * DeskPRO organization label importer.
 *
 * Class OrganizationLabel
 */
final class OrganizationLabel extends AbstractImporter
{
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

        $organization = $this->getOrganizationMapper()->findOneByTitle($entity->getName());
        $organization->clearLabels();

        foreach ($entity->getLabels() as $label_name) {
            $label = new DeskPROEntity\LabelOrganization();
            $label->setLabel($label_name);

            $organization->addLabel($label);
            $this->logInfo(sprintf('Creating a new label `%s` for organization with oid `%d`', $label_name, $organization->getId()));
        }

        $this->records->setPrimaryEntity($organization);
    }
}
