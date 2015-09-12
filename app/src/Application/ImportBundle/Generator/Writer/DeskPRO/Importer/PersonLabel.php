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

/**
 * DeskPRO person labels importer
 *
 * Class PersonLabel
 * @package Application\ImportBundle\Generator\Writer\DeskPRO\Importer
 */
final class PersonLabel extends AbstractImporter
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON;
    }

    /**
     * {@inheritdoc}
     *
     * // todo refactor this
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity, $entity_id = null)
    {
        if ( ! $entity instanceof Entity\Person) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $oldEntity = $this->getPersonMapper()->findOneByEmails($entity->getEmails());
        $type = 'person';
        $newLabels = $entity->getLabels();

        foreach ($oldEntity->labels as $labelEntity) {
            if (false === $k = array_search($labelEntity->label, $newLabels)) {
                $oldEntity->labels->removeElement($labelEntity);
                $this->removeEntity($labelEntity);
            } else {
                unset($newLabels[$k]);
            }
        }

        foreach ($newLabels as $label) {
            $oldEntity->addLabel($this->createLabel($label));
            $this->logDebug(sprintf(
                'Creating a new label `%s` for %s with oid `%d`',
                $label, $type, $oldEntity->getId()
            ));
        }

        return $this->records;
    }

    /**
     * Returns a new person label entity
     *
     * @param string $label
     * @return DeskPROEntity\LabelPerson
     */
    private function createLabel($label)
    {
        $entity = new DeskPROEntity\LabelPerson();
        $entity->setLabel($label);

        $this->records->addRelatedEntity($entity);
        return $entity;
    }
}
