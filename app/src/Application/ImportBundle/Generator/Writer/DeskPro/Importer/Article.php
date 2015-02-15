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
 * DeskPro article importer
 *
 * Class Article
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Article extends AbstractImporter implements SkipDuplicateInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     *
     * todo add referred objects
     * $record['total_rating'] = $kbval->total_rating;
     * $record['num_comments'] = $kbval->num_comments;
     * $record['num_ratings']  = $kbval->num_ratings;
     *
     * @var Entity\Article $entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        $this->records = new ArrayCollection();

        $article = new DeskPROEntity\Article();
        $article
            ->setTitle($entity->getTitle())
            ->setContent($entity->getContent())
            ->setSlug($entity->getSlug())
            ->setStatus($entity->getStatus())
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setLanguage($this->findLanguage($entity->getLanguage()))
            ->setDateCreated($entity->getDateCreated())
            ->setDatePublished($entity->getDatePublished())
            ->setDateEnd($entity->getDateEnd())
            ->setViewsCount($entity->getViewCount());

        foreach ($entity->getCategories() as $category) {
            $article->addToCategory($this->findOrCreateArticleCategory($category));
        }

        $this->records->add($article);
        return $this->records;
    }

    /**
     * {@inheritdoc}
     *
     * @var Entity\Article $entity
     */
    public function checkAlreadyExists(Entity\EntityInterface $entity)
    {
        $this->logInfo(sprintf(
            'Looking for existing article with title `%s`, oid `%d`',
            $entity->getTitle(), $entity->getOid()
        ));

        if ($this->getArticleMapper()->findOneByTitle($entity->getTitle(), false)) {
            throw new DuplicateException();
        }
    }

    /**
     * Returns an article category by title
     * Creates a new article category if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\ArticleCategory|null
     * @throws \Exception
     */
    private function findOrCreateArticleCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getArticleCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logInfo(sprintf('Found existing article category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\ArticleCategory();
                $category->setRealTitle($title);

                $this->records->add($category);
                $this->logWarning(sprintf('New article category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns the article category mapper
     *
     * @return Mapper\ArticleCategory
     * @throws \Exception
     */
    private function getArticleCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ARTICLE_CATEGORY);
    }
}
