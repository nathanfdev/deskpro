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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
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
            || is_object($input) && 'Application\DeskPRO\Entity\Article' === get_class($input)
            || is_object($input) && 'Application\DeskPRO\Entity\ArticleCategory' === get_class($input)
        ) {
            return 'kb_subscriptions';
        }

        if (
            'news' === $input
            || is_object($input) && 'Application\DeskPRO\Entity\News' === get_class($input)
            || is_object($input) && 'Application\DeskPRO\Entity\NewsCategory' === get_class($input)
        ) {
            return 'news_subscriptions';
        }

        if (
            'downloads' === $input
            || is_object($input) && 'Application\DeskPRO\Entity\Download' === get_class($input)
            || is_object($input) && 'Application\DeskPRO\Entity\DownloadCategory' === get_class($input)
        ) {
            return 'download_subscriptions';
        }

        if (
            'feedback' === $input
            || is_object($input) && 'Application\DeskPRO\Entity\Feedback' === get_class($input)
        ) {
            return 'feedback_subscriptions';
        }

        throw new \InvalidArgumentException(sprintf('could not find subscriptions table name for input "%s"', $input));
    }

    protected function getSubscriptionsIdName($input)
    {
        if (
            'kb' === $input
            || is_object($input) && 'Application\DeskPRO\Entity\Article' === get_class($input)
            || is_object($input) && 'Application\DeskPRO\Entity\ArticleCategory' === get_class($input)
        ) {
            return 'article_id';
        }

        if (
            'news' === $input
            || is_object($input) && 'Application\DeskPRO\Entity\News' === get_class($input)
            || is_object($input) && 'Application\DeskPRO\Entity\NewsCategory' === get_class($input)
        ) {
            return 'news_id';
        }

        if (
            'downloads' === $input
            || is_object($input) && 'Application\DeskPRO\Entity\Download' === get_class($input)
            || is_object($input) && 'Application\DeskPRO\Entity\DownloadCategory' === get_class($input)
        ) {
            return 'download_id';
        }

        if (
            'feedback' === $input
            || is_object($input) && 'Application\DeskPRO\Entity\Feedback' === get_class($input)
        ) {
            return 'feedback_id';
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
