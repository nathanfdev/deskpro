<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\Service;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentSlugManager 
{
    /**
     * @var EntityManager
     */
    private $em;

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
     * @return string
     */
    public function ensureValidSlug(ContentAbstract $content)
    {
        $existing_slug = $content->getSlug();
        $expected_slug = Strings::slugifyTitle($content->getTitle());

        if ($existing_slug === $expected_slug) {
            return false; // already valid and set, no need to do more here
        }

        // check if the expected slug is a valid one, in both content repo and in slug history repo
        $new_slug = $expected_slug;
        $i = 1;
        while (!$this->isValidSlug($new_slug, $content)) {
            // if expected slug is not valid, keep incrementing a value at the end until we get something valid
            $new_slug = Strings::slugifyTitle($content->getTitle() . ' ' . ++$i);
        }

        $new_history = $content->setSlug($new_slug);;

        return $new_history;
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
        return $this->getRepoForContent($content)->findOneBy(array('slug' => $new_slug));
    }

    protected function getSlugHistoryBySlug($new_slug, ContentAbstract $content)
    {
        return $this->getHistoryRepoForContent($content)->findOneBy(array('slug' => $new_slug));
    }

    /**
     * @param ContentAbstract $content
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepoForContent(ContentAbstract $content)
    {
        switch ($content->getContentType()) {
            case Article::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:Article');
            case News::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:News');
            case Feedback::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:Feedback');
            case Download::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:Download');
            default:
                throw new \InvalidArgumentException('no content type provided');
        }
    }

    /**
     * @param ContentAbstract $content
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getHistoryRepoForContent(ContentAbstract $content)
    {
        switch ($content->getContentType()) {
            case Article::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:ArticleSlugHistory');
            case News::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:NewsSlugHistory');
            case Feedback::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:FeedbackSlugHistory');
            case Download::CONTENT_TYPE:
                return $this->getEm()->getRepository('DeskPRO:DownloadSlugHistory');
            default:
                throw new \InvalidArgumentException('no content type provided');
        }
    }
}
