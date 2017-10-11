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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use Application\ImportBundle\Model;

/**
 * DeskPRO feedback importer.
 *
 * Class Feedback
 */
class FeedbackHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Feedback::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Feedback $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var Entity\Feedback $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getFeedbackMapper(), $model);
        $entity
            ->setTitle($model->getTitle())
            ->setContent($model->getContent())
            ->setStatus($model->getStatus())
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
            ->setLanguage($this->helpers->getLanguageHelper()->findOrCreateLanguage($model->getLanguage()))
            ->setViewCount($model->getViewCount())
        ;

        if ($model->getDateCreated()) {
            $entity->setDateCreated($model->getDateCreated());
        }
        if ($model->getDatePublished()) {
            $entity->setDatePublished($model->getDatePublished());
        } else {
            $entity->setDatePublished($entity->getDateCreated());
        }

        // update feedback category
        if ($model->getCategory()) {
            $entity->setCategory($this->helpers->getCategoryHelper()->findOrCreateCategory(
                $this->mappers->getFeedbackCategoryMapper(),
                $model->getCategory(),
                $brandName
            ));
        } else {
            // use default category
            $entity->setCategory($this->mappers->getFeedbackCategoryMapper()->getDefaultCategory());
        }

        $this->helpers->getCustomDataHelper()->updateCustomData($this->mappers->getFeedbackCustomDefMapper(), $model, $entity);
        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelFeedback::class);

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);

        // persist others related entities which contains own oids
        foreach ($model->getAttachments() as $attachmentModel) {
            $this->helpers->getAttachmentHelper()->createOrUpdateAttachment(
                $this->mappers->getFeedbackAttachmentMapper(), $attachmentModel, $entity
            );
        }
    }
}
