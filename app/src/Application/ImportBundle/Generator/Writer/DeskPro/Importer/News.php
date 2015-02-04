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
 * DeskPro news importer
 *
 * Class News
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class News extends AbstractImporter
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_NEWS;
    }

    /**
     * {@inheritdoc}
     *
     * todo add referred objects
     * 'total_rating'   => $nval->total_rating,
     * 'num_comments'   => $nval->num_comments,
     * 'num_ratings'    => $nval->num_ratings,
     * 'view_count'     => $nval->view_count
     *
     * @var Entity\News $importing_entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $importing_entity)
    {
        $this->records = new ArrayCollection();
        $exist_news    = $this->getNewsMapper()->findOneByTitle($importing_entity->getTitle(), false);
        if ($exist_news) {
            $this->logWarning(sprintf(
                'A News item with the title `%s` already exists (skipping)',
                $importing_entity->getTitle()
            ));
        } else {
            $news = new DeskPROEntity\News();
            $news
                ->setTitle($importing_entity->getTitle())
                ->setContent($importing_entity->getContent())
                ->setSlug($importing_entity->getSlug())
                ->setPerson($this->getPersonMapper()->findOneByEmail($importing_entity->getPersonEmail()))
                ->setLanguage($this->findLanguage($importing_entity->getLanguage()))
                ->setCategory($this->findOrCreateNewsCategory($importing_entity->getCategory()))
                ->setDateCreated($importing_entity->getDateCreated())
                ->setDatePublished($importing_entity->getDatePublished());

            foreach ($importing_entity->getLabels() as $label) {
                $news->addLabel($this->createNewsLabel($label));
            }

            $this->records->add($news);
        }

        return $this->records;
    }

    /**
     * Returns an feedback category by title
     * Creates a new feedback category if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\NewsCategory|null
     * @throws \Exception
     */
    private function findOrCreateNewsCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getNewsCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logInfo(sprintf('Found existing news category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\NewsCategory();
                $category->setRealTitle($title);

                $this->records->add($category);
                $this->logWarning(sprintf('New news category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns a new news label entity
     *
     * @param string $label
     * @return DeskPROEntity\LabelNews
     */
    private function createNewsLabel($label)
    {
        $entity = new DeskPROEntity\LabelNews();
        $entity->setLabel($label);

        $this->records->add($entity);
        return $entity;
    }

    /**
     * Returns the news mapper
     *
     * @return Mapper\News
     * @throws \Exception
     */
    private function getNewsMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_NEWS);
    }

    /**
     * Returns the news category mapper
     *
     * @return Mapper\NewsCategory
     * @throws \Exception
     */
    private function getNewsCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_NEWS_CATEGORY);
    }
}
