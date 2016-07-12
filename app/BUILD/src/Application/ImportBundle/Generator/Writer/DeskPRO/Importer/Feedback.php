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
 * DeskPRO feedback importer.
 *
 * Class Feedback
 */
final class Feedback extends AbstractImporter implements SkipDuplicateInterface
{
    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor.
     *
     * @param Mapper\Collection    $mappers
     * @param BlobAdapterInterface $blobAdapter
     */
    public function __construct(Mapper\Collection $mappers, BlobAdapterInterface $blobAdapter)
    {
        parent::__construct($mappers);
        $this->blob_adapter = $blobAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_FEEDBACK;
    }

    /**
     * {@inheritdoc}
     *
     * todo add referred objects
     * $record['total_rating']		= $fval->total_rating;
     * $record['num_comments']		= $fval->num_comments;
     * $record['num_ratings']		= $fval->num_ratings;
     * $record['popularity']		= $fval->popularity;
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        if (!$entity instanceof Entity\Feedback) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $feedback = new DeskPROEntity\Feedback();
        $feedback
            ->setTitle($entity->getTitle())
            ->setContent($entity->getContent())
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setLanguage($this->findLanguage($entity->getLanguage()))
            ->setCategory($this->findOrCreateFeedbackCategory($entity->getCategory()))
            ->setDateCreated($entity->getDateCreated())
            ->setDatePublished($entity->getDatePublished())
            ->setViewCount($entity->getViewCount())
            ->resetCustomData()
        ;

        foreach ($entity->getAttachments() as $attachment) {
            $feedback->addAttachment($this->createAttachment(
                $attachment,
                $entity->getPersonEmail()
            ));
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $custom_field = $this->createFeedbackCustomData($custom_field);
            if ($custom_field) {
                $feedback->addCustomData($custom_field);
            }
        }

        $this->records->setPrimaryEntity($feedback);
    }

    /**
     * {@inheritdoc}
     *
     * @var Entity\Feedback
     */
    public function checkAlreadyExists(Entity\EntityInterface $entity)
    {
        if ($this->getFeedbackMapper()->findOneByTitle($entity->getTitle(), false)) {
            throw new DuplicateException();
        }
    }

    /**
     * Returns an feedback category by title.
     * Creates a new feedback category if not found.
     *
     * @param string $title
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\FeedbackCategory|null
     */
    private function findOrCreateFeedbackCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getFeedbackCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logDebug(sprintf('Found existing feedback category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\FeedbackCategory();
                $category->setRealTitle($title);

                $this->records->addRelatedEntity($category);
                $this->logInfo(sprintf('New feedback category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns the importing DeskPRO doctrine feedback attachment entity.
     *
     * @param Entity\Attachment $entity
     * @param string            $person_email
     *
     * @return DeskPROEntity\FeedbackAttachment
     */
    private function createAttachment(Entity\Attachment $entity, $person_email)
    {
        $email = $entity->getPersonEmail() ?: $person_email;
        $blob  = $this->blob_adapter->createByBlob($entity);

        $attachment = new DeskPROEntity\FeedbackAttachment();
        $attachment
            ->setPerson($this->getPersonMapper()->findOneByEmail($email))
            ->setBlob($blob)
        ;

        $this->records->addRelatedEntity($attachment);
        $this->records->addRelatedEntity($blob);

        return $attachment;
    }

    /**
     * Returns custom def feedback entity.
     *
     * @param Entity\CustomField $entity
     *
     * @return DeskPROEntity\CustomDataFeedback
     */
    private function createFeedbackCustomData(Entity\CustomField $entity)
    {
        return $this->createCustomData($this->getFeedbackCustomDefMapper(), $entity, new DeskPROEntity\CustomDataFeedback());
    }
}
