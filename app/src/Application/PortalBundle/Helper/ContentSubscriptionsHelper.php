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

namespace Application\PortalBundle\Helper;


use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\ORM\EntityManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class ContentSubscriptionsHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AuthorizationChecker
     */
    private $authorization_checker;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param CategoryAbstract $category
     * @param Person $person
     * @return bool
     */
    public function isSubscribedCategory(CategoryAbstract $category, Person $person)
    {
        return (bool) $this->getDb()->fetchColumn("
                SELECT id
                FROM {$this->getSubscriptionsTableName($category)}
                WHERE person_id = ? AND category_id = ?
            ", array($person->getId(), $category->getId()));
    }

    /**
     * @param ContentAbstract $content
     * @param Person $person
     * @return bool
     */
    public function isSubscribedContent(ContentAbstract $content, Person $person)
    {
        return (bool) $this->getDb()->fetchColumn("
                SELECT id
                FROM {$this->getSubscriptionsTableName($content)}
                WHERE person_id = ? AND {$this->getSubscriptionsIdName($content)} = ?
            ", array($person->getId(), $content->getId()));
    }

    /**
     * @param CategoryAbstract $category
     * @param Person $person
     * @return bool
     */
    public function subscribeToCategory(CategoryAbstract $category, Person $person)
    {
        if ($this->isSubscribedCategory($category, $person)) {
            return true;
        }

        $this->getDb()->insert($this->getSubscriptionsTableName($category), array(
            'person_id' => $person->getId(),
            'category_id' => $category->getId()
        ));

        return true;
    }

    /**
     * @param CategoryAbstract $category
     * @param Person $person
     * @return bool
     */
    public function unsubscribeFromCategory(CategoryAbstract $category, Person $person)
    {
        if ($this->isSubscribedCategory($category, $person)) {
            $this->getDb()->delete($this->getSubscriptionsTableName($category), array(
                'person_id' => $person->getId(),
                'category_id' => $category->getId()
            ));

            return true;
        }

        return true;
    }

    /**
     * @param ContentAbstract $content
     * @param Person $person
     * @return bool
     */
    public function subscribeToContent(ContentAbstract $content, Person $person)
    {
        if ($this->isSubscribedContent($content, $person)) {
            return true;
        }

        $this->getDb()->insert($this->getSubscriptionsTableName($content), array(
            'person_id' => $person->getId(),
            $this->getSubscriptionsIdName($content) => $content->getId()
        ));

        return true;
    }

    /**
     * @param ContentAbstract $content
     * @param Person $person
     * @return bool
     */
    public function unsubscribeFromContent(ContentAbstract $content, Person $person)
    {
        if ($this->isSubscribedContent($content, $person)) {
            $this->getDb()->delete($this->getSubscriptionsTableName($content), array(
                'person_id' => $person->getId(),
                $this->getSubscriptionsIdName($content) => $content->getId()
            ));

            return true;
        }

        return true;
    }

    /**
     * @param $content_type
     * @param Person $person
     */
    public function unsubscribeFromAll($content_type, Person $person)
    {
        $this->getDb()->delete($this->getSubscriptionsTableName($content_type), array('person_id' => $person->getId()));
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

        throw new \InvalidArgumentException(sprintf('could not find subscriptions table name for input "%s"', $input));
    }

    /**
     * @param $setting
     * @param null $default
     * @return mixed
     */
    protected function getBrandSetting($setting, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($setting, $default);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDb()
    {
        return $this->em->getConnection();
    }
}
