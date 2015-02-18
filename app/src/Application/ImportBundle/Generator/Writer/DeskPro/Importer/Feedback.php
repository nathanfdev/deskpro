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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPro feedback importer
 *
 * Class Feedback
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Feedback extends AbstractImporter implements SkipDuplicateInterface
{
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
     *
     * @var Entity\Feedback $entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        $this->records = new ArrayCollection();

        $feedback = new DeskPROEntity\Feedback();
        $feedback
            ->setTitle($entity->getTitle())
            ->setContent($entity->getContent())
            ->setSlug($entity->getSlug())
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setLanguage($this->findLanguage($entity->getLanguage()))
            ->setCategory($this->findOrCreateFeedbackCategory($entity->getCategory()))
            ->setDateCreated($entity->getDateCreated())
            ->setDatePublished($entity->getDatePublished())
            ->setViewsCount($entity->getViewCount());

        $this->records->add($feedback);
        return $this->records;
    }

    /**
     * {@inheritdoc}
     *
     * @var Entity\Feedback $entity
     */
    public function checkAlreadyExists(Entity\EntityInterface $entity)
    {
        if ($this->getFeedbackMapper()->findOneByTitle($entity->getTitle(), false)) {
            throw new DuplicateException();
        }
    }

    /**
     * Returns an feedback category by title
     * Creates a new feedback category if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\FeedbackCategory|null
     * @throws \Exception
     */
    private function findOrCreateFeedbackCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getFeedbackCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logInfo(sprintf('Found existing feedback category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\FeedbackCategory();
                $category->setRealTitle($title);

                $this->records->add($category);
                $this->logWarning(sprintf('New feedback category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns the feedback category mapper
     *
     * @return Mapper\FeedbackCategory
     * @throws \Exception
     */
    private function getFeedbackCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_FEEDBACK_CATEGORY);
    }
}
