<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleSlugHistory;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadSlugHistory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackSlugHistory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsSlugHistory;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicSlugHistory;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * We always track a history of content slugs (for articles, news, dls, etc). We want to redirect the old
 * slugs to the new slugs (to avoid 404s in URLs when the title of an object changes for example).
 *
 * Use this ContentSlugManager service to get content by slug string.
 */
class ContentSlugManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ContentSlugManager constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return EntityManager
     */
    protected function getEm()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * When you give this a content object, it assumes the "title" is correct.
     *
     * It then ensures that the slug that is set on the object is correct,
     * if it is not correct, it will find something valid AND SET IT on the content.
     *
     * @param ContentAbstract $content
     *
     * @throws \Exception
     *
     * @return null|object
     */
    public function ensureValidSlug(ContentAbstract $content)
    {
        $existingSlug = $content->getSlug();
        $expectedSlug = $this->slugifyTitle($content->getTitle());

        if ($expectedSlug === '') {
            $expectedSlug = strtolower(TypeUtils::getBaseTypeName($content));
        }

        if ($existingSlug === $expectedSlug) {
            return null; // already valid and set, no need to do more here
        }

        // check if the expected slug is a valid one, in both content repo and in slug history repo
        $changed = $this->getEm()->getUnitOfWork()->getEntityChangeSet($content);
        if (isset($changed['title']) || !$existingSlug) {
            $newSlug = $expectedSlug;
        } else {
            $newSlug = $existingSlug;
        }

        $i = 1;
        while (!$this->isValidSlug($newSlug, $content)) {
            // if expected slug is not valid, keep incrementing a value at the end until we get something valid
            $newSlug = sprintf('%s-%d', $this->slugifyTitle($content->getTitle()) ?: strtolower(TypeUtils::getBaseTypeName($content)), ++$i);
        }

        $newHistory = $content->setSlug($newSlug);

        return $newHistory;
    }

    /**
     * @param $title
     *
     * @return string
     */
    private function slugifyTitle($title)
    {
        return substr(Strings::slugifyTitle($title), 0, 94) ?: '';
    }

    /**
     * Given a slug string and a class name of the entity, return the content object if it can be found.
     *
     * This method is aware of slug history.
     *
     * @param $slug
     * @param $content_class_name
     *
     * @return null|object
     */
    public function findContentObjectBySlug($slug, $content_class_name)
    {
        $contentRepo = $this->getEm()->getRepository($content_class_name);
        if ($content = $contentRepo->findOneBy(['slug' => $slug])) {
            return $content;
        }

        $historyClass = sprintf('%sSlugHistory', $content_class_name);
        if (class_exists($historyClass)) {
            $historyRepo = $this->getEm()->getRepository($historyClass);
            if ($contentHistory = $historyRepo->findOneBy(['slug' => $slug])) {
                return $contentHistory->getContent();
            }
        }

        return null;
    }

    /**
     * @param string          $newSlug
     * @param ContentAbstract $content
     *
     * @return bool
     */
    protected function isValidSlug($newSlug, ContentAbstract $content)
    {
        if ($contentObject = $this->getContentBySlug($newSlug, $content)) {
            // valid if it is the current slug (should be covered already in ensureValidSlug, here for sanity)
            return $contentObject->getId() === $content->getId();
        }

        if ($history = $this->getSlugHistoryBySlug($newSlug, $content)) {
            return false;
        }

        return true;
    }

    /**
     * @param string          $newSlug
     * @param ContentAbstract $content
     *
     * @return null|object
     */
    protected function getContentBySlug($newSlug, ContentAbstract $content)
    {
        return $this->getRepoForContent($content)->findOneBy(['slug' => $newSlug]);
    }

    /**
     * @param string          $newSlug
     * @param ContentAbstract $content
     *
     * @return null|object
     */
    protected function getSlugHistoryBySlug($newSlug, ContentAbstract $content)
    {
        return $this->getHistoryRepoForContent($content)->findOneBy(['slug' => $newSlug]);
    }

    /**
     * @param ContentAbstract $content
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepoForContent(ContentAbstract $content)
    {
        $type = $content->getContentType();
        switch ($type) {
            case Article::CONTENT_TYPE:
                return $this->getEm()->getRepository(Article::class);
            case News::CONTENT_TYPE:
                return $this->getEm()->getRepository(News::class);
            case Feedback::CONTENT_TYPE:
                return $this->getEm()->getRepository(Feedback::class);
            case Download::CONTENT_TYPE:
                return $this->getEm()->getRepository(Download::class);
            case Topic::CONTENT_TYPE:
                return $this->getEm()->getRepository(Topic::class);
            default:
                throw new \InvalidArgumentException('no content type provided');
        }
    }

    /**
     * @param ContentAbstract $content
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getHistoryRepoForContent(ContentAbstract $content)
    {
        switch ($content->getContentType()) {
            case Article::CONTENT_TYPE:
                return $this->getEm()->getRepository(ArticleSlugHistory::class);
            case News::CONTENT_TYPE:
                return $this->getEm()->getRepository(NewsSlugHistory::class);
            case Feedback::CONTENT_TYPE:
                return $this->getEm()->getRepository(FeedbackSlugHistory::class);
            case Download::CONTENT_TYPE:
                return $this->getEm()->getRepository(DownloadSlugHistory::class);
            case Topic::CONTENT_TYPE:
                return $this->getEm()->getRepository(TopicSlugHistory::class);
            default:
                throw new \InvalidArgumentException('no content type provided');
        }
    }
}
