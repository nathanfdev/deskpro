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
use Application\ImportBundle\Writer\Helper\CustomDataHelper;
use Application\ImportBundle\Writer\Helper\LabelHelper;
use Application\ImportBundle\Writer\Mapper\MapperRegistry;
use Psr\Log\LoggerInterface;

/**
 * DeskPRO feedback importer.
 *
 * Class Feedback
 */
class FeedbackHandler extends AbstractEntityHandler
{
    /**
     * @var BlobAdapter
     */
    private $blobAdapter;

    /**
     * Constructor.
     *
     * @param MapperRegistry  $mappers
     * @param BlobAdapter     $blobAdapter
     * @param LoggerInterface $logger
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
        return Model\Feedback::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Feedback $model
     */
    public function prepare(Model\ImportModelInterface $model, $entityId = null)
    {
        $entity = $this->mappers->getFeedbackMapper()->findOneByTitle($model->getTitle(), false) ?: new DeskPROEntity\Feedback();
        $entity
            ->setTitle($model->getTitle())
            ->setContent($model->getContent())
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($model->getPerson()))
            ->setLanguage($this->findLanguage($model->getLanguage()))
            ->setCategory($this->findOrCreateFeedbackCategory($model->getCategory()))
            ->setDateCreated($model->getDateCreated())
            ->setDatePublished($model->getDatePublished())
            ->setViewCount($model->getViewCount())
        ;

        foreach ($model->getAttachments() as $attachment) {
            $entity->addAttachment($this->createAttachment(
                $attachment,
                $model->getPerson()
            ));
        }

        $labelsHelper = new LabelHelper($this->logger);
        $labelsHelper->updateLabels($model, $entity, DeskPROEntity\LabelFeedback::class);

        $customDataHelper = new CustomDataHelper($this->mappers->getFeedbackCustomDefMapper(), $this->logger);
        $customDataHelper->updateCustomData($model, $entity, $this->records);

        $this->records->setPrimaryEntity($entity);
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
            $category = $this->mappers->getFeedbackCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logger->debug(sprintf('Found existing feedback category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\FeedbackCategory();
                $category->setRealTitle($title);

                $this->records->addRelatedEntity($category);
                $this->logger->info(sprintf('New feedback category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns the importing DeskPRO doctrine feedback attachment entity.
     *
     * @param Model\Attachment $entity
     * @param string           $person_email
     *
     * @return DeskPROEntity\FeedbackAttachment
     */
    private function createAttachment(Model\Attachment $entity, $person_email)
    {
        $email = $entity->getPerson() ?: $person_email;
        $blob  = $this->blobAdapter->createByBlob($entity);

        $attachment = new DeskPROEntity\FeedbackAttachment();
        $attachment
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($email))
            ->setBlob($blob)
        ;

        $this->records->addRelatedEntity($attachment);
        $this->records->addRelatedEntity($blob);

        return $attachment;
    }
}
