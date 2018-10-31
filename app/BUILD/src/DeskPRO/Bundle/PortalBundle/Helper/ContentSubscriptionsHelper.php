<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;

class ContentSubscriptionsHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BrandStack
     */
    private $brandStack;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $contentType
     * @param Person $person
     *
     * @return bool
     */
    public function isSubscribedRootCategory($contentType, Person $person)
    {
        return (bool) $this->getDb()->fetchColumn("
                SELECT id
                FROM {$this->getSubscriptionsTableName($contentType)}
                WHERE person_id = ? AND root_category = 1
            ", [$person->getId()]);
    }

    /**
     * @param CategoryAbstract $category
     * @param Person           $person
     *
     * @return bool
     */
    public function isSubscribedCategory(CategoryAbstract $category, Person $person)
    {
        return (bool) $this->getDb()->fetchColumn("
                SELECT id
                FROM {$this->getSubscriptionsTableName($category)}
                WHERE person_id = ? AND category_id = ?
            ", [$person->getId(), $category->getId()]);
    }

    /**
     * @param ContentAbstract $content
     * @param Person          $person
     *
     * @return bool
     */
    public function isSubscribedContent(ContentAbstract $content, Person $person)
    {
        return (bool) $this->getDb()->fetchColumn("
                SELECT id
                FROM {$this->getSubscriptionsTableName($content)}
                WHERE person_id = ? AND {$this->getSubscriptionsIdName($content)} = ?
            ", [$person->getId(), $content->getId()]);
    }

    /**
     * @param string $contentType
     * @param Person $person
     *
     * @return bool
     */
    public function subscribeToRootCategory($contentType, Person $person)
    {
        if ($this->isSubscribedRootCategory($contentType, $person)) {
            return true;
        }

        return (bool) $this->getDb()->insert($this->getSubscriptionsTableName($contentType), [
            'person_id'     => $person->getId(),
            'root_category' => 1,
        ]);
    }

    /**
     * @param string $contentType
     * @param Person $person
     *
     * @return bool
     */
    public function unsubscribeFromRootCategory($contentType, Person $person)
    {
        if ($this->isSubscribedRootCategory($contentType, $person)) {
            return (bool) $this->getDb()->delete($this->getSubscriptionsTableName($contentType), [
                'person_id'     => $person->getId(),
                'root_category' => 1,
            ]);
        }

        return true;
    }

    /**
     * @param CategoryAbstract $category
     * @param Person           $person
     *
     * @return bool
     */
    public function subscribeToCategory(CategoryAbstract $category, Person $person)
    {
        if ($this->isSubscribedCategory($category, $person)) {
            return true;
        }

        return (bool) $this->getDb()->insert($this->getSubscriptionsTableName($category), [
            'person_id'   => $person->getId(),
            'category_id' => $category->getId(),
        ]);
    }

    /**
     * @param CategoryAbstract $category
     * @param Person           $person
     *
     * @return bool
     */
    public function unsubscribeFromCategory(CategoryAbstract $category, Person $person)
    {
        if ($this->isSubscribedCategory($category, $person)) {
            return (bool) $this->getDb()->delete($this->getSubscriptionsTableName($category), [
                'person_id'   => $person->getId(),
                'category_id' => $category->getId(),
            ]);
        }

        return true;
    }

    /**
     * @param ContentAbstract $content
     * @param Person          $person
     *
     * @return bool
     */
    public function subscribeToContent(ContentAbstract $content, Person $person)
    {
        if ($this->isSubscribedContent($content, $person)) {
            return true;
        }

        return (bool) $this->getDb()->insert($this->getSubscriptionsTableName($content), [
            'person_id'                             => $person->getId(),
            $this->getSubscriptionsIdName($content) => $content->getId(),
        ]);
    }

    /**
     * @param ContentAbstract $content
     * @param Person          $person
     *
     * @return bool
     */
    public function unsubscribeFromContent(ContentAbstract $content, Person $person)
    {
        if ($this->isSubscribedContent($content, $person)) {
            return (bool) $this->getDb()->delete($this->getSubscriptionsTableName($content), [
                'person_id'                             => $person->getId(),
                $this->getSubscriptionsIdName($content) => $content->getId(),
            ]);
        }

        return true;
    }

    /**
     * @param        $contentType
     * @param Person $person
     *
     * @return bool
     */
    public function unsubscribeFromAll($contentType, Person $person)
    {
        return (bool) $this->getDb()->delete($this->getSubscriptionsTableName($contentType), [
            'person_id' => $person->getId(),
        ]);
    }

    protected function getSubscriptionsTableName($input)
    {
        if (
            'kb' === $input
            || is_object($input) && Article::class === get_class($input)
            || is_object($input) && ArticleCategory::class === get_class($input)
        ) {
            return 'kb_subscriptions';
        }

        if (
            'news' === $input
            || is_object($input) && News::class === get_class($input)
            || is_object($input) && NewsCategory::class === get_class($input)
        ) {
            return 'news_subscriptions';
        }

        if (
            'downloads' === $input
            || is_object($input) && Download::class === get_class($input)
            || is_object($input) && DownloadCategory::class === get_class($input)
        ) {
            return 'download_subscriptions';
        }

        if (
            'feedback' === $input
            || is_object($input) && Feedback::class === get_class($input)
        ) {
            return 'feedback_subscriptions';
        }

        if (
            'topic' === $input
            || is_object($input) && Topic::class === get_class($input)
        ) {
            return 'topic_subscriptions';
        }

        throw new \InvalidArgumentException(sprintf('could not find subscriptions table name for input "%s"', $input));
    }

    protected function getSubscriptionsIdName($input)
    {
        if (
            'kb' === $input
            || is_object($input) && Article::class === get_class($input)
            || is_object($input) && ArticleCategory::class === get_class($input)
        ) {
            return 'article_id';
        }

        if (
            'news' === $input
            || is_object($input) && News::class === get_class($input)
            || is_object($input) && NewsCategory::class === get_class($input)
        ) {
            return 'news_id';
        }

        if (
            'downloads' === $input
            || is_object($input) && Download::class === get_class($input)
            || is_object($input) && DownloadCategory::class === get_class($input)
        ) {
            return 'download_id';
        }

        if (
            'feedback' === $input
            || is_object($input) && Feedback::class === get_class($input)
        ) {
            return 'feedback_id';
        }

        if (
            'topic' === $input
            || is_object($input) && Topic::class === get_class($input)
        ) {
            return 'topic_id';
        }

        throw new \InvalidArgumentException(sprintf('could not find subscriptions table name for input "%s"', $input));
    }

    /**
     * @param      $setting
     * @param null $default
     *
     * @return mixed
     */
    protected function getBrandSetting($setting, $default = null)
    {
        return $this->brandStack->getActive()->getSetting($setting, $default);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDb()
    {
        return $this->em->getConnection();
    }
}
