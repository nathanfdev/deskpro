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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
     * @return null|void
     */
    public function ensureValidSlug(ContentAbstract $content)
    {
        $existing_slug = $content->getSlug();
        $expected_slug = Strings::slugifyTitle($content->getTitle());
        // we're about trim slug here, to ensure it, otherwise it will be trimmed on query an we are expecting error
        $expected_slug = substr($expected_slug, 0, 94);

        if ($existing_slug === $expected_slug) {
            return; // already valid and set, no need to do more here
        }

        // check if the expected slug is a valid one, in both content repo and in slug history repo
        $new_slug = $expected_slug;
        $i        = 1;
        while (!$this->isValidSlug($new_slug, $content)) {
            // if expected slug is not valid, keep incrementing a value at the end until we get something valid
            $new_slug = Strings::slugifyTitle($content->getTitle().' '.++$i);
        }

        $new_history = $content->setSlug($new_slug);

        return $new_history;
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
        $content_repo = $this->getEm()->getRepository($content_class_name);
        if ($content = $content_repo->findOneBy(['slug' => $slug])) {
            return $content;
        }

        $history_repo = $this->getEm()->getRepository(sprintf('%sSlugHistory', $content_class_name));
        if ($content_history = $history_repo->findOneBy(['slug' => $slug])) {
            return $content_history->getContent();
        }

        return;
    }

    protected function isValidSlug($new_slug, ContentAbstract $content)
    {
        if ($content_object = $this->getContentBySlug($new_slug, $content)) {
            // valid if it is the current slug (should be covered already in ensureValidSlug, here for sanity)
            return $content_object->getId() === $content->getId();
        }

        if ($history = $this->getSlugHistoryBySlug($new_slug, $content)) {
            return false;
        }

        return true;
    }

    protected function getContentBySlug($new_slug, ContentAbstract $content)
    {
        return $this->getRepoForContent($content)->findOneBy(['slug' => $new_slug]);
    }

    protected function getSlugHistoryBySlug($new_slug, ContentAbstract $content)
    {
        return $this->getHistoryRepoForContent($content)->findOneBy(['slug' => $new_slug]);
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
            default:
                throw new \InvalidArgumentException('no content type provided');
        }
    }
}
