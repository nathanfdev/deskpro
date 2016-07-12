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
 * Class FeedbackCustomDef.
 */
final class FeedbackCustomDef extends AbstractCustomDefImporter
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_FEEDBACK_CUSTOM_DEF;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        if (!$entity instanceof Entity\FeedbackCustomDef) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $custom_def = $this->findOrCreateCustomDef($entity_id);
        $this->records->setPrimaryEntity($this->setCustomDef($custom_def, $entity));
    }

    /**
     * Find or create new custom def.
     *
     * @param int $entity_id
     *
     * @throws Mapper\MapperException
     *
     * @return DeskPROEntity\CustomDefFeedback
     */
    private function findOrCreateCustomDef($entity_id)
    {
        if ($entity_id) {
            $custom_def = $this->getCustomDefMapper()->findOneBy(['id' => $entity_id], false);

            if ($custom_def) {
                $this->logDebug(sprintf('Found existing feedback custom def, id=%s', $entity_id));

                return $custom_def;
            }
        }

        $this->logDebug('Creating a new feedback custom def');

        return new DeskPROEntity\CustomDefFeedback();
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDefMapper()
    {
        return $this->getFeedbackCustomDefMapper();
    }
}
