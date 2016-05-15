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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Security\Permissions;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;

/**
 * The PermissionsBag acts like an immutable array, and also offers an API with methods like has('key') and get('key', 'default').
 * $permissions in this bag are the boolean yes/no permissions, but there are other methods for getting things like allowed departments, and allowed content categories.
 *
 * Using the hasPermission(name) method returns a bool: true if the bag has that permission and it is turned on, and false if not.
 */
class PermissionsBag implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
    /**
     * @var array key is a permission name, value is to be interpreted as a boolean
     */
    protected $permissions;

    /**
     * @var array just a list of allowed department ids for tickets
     */
    protected $department_ticket_ids;

    /**
     * @var array just a list of allowed department ids for chat
     */
    protected $department_chat_ids;

    /**
     * @var array just a list of all allowed
     */
    protected $department_ids;

    /**
     * @var array allowed feedback categories
     */
    protected $feedback_categories;

    /**
     * @var array allowed news categories
     */
    protected $news_categories;

    /**
     * @var array allowed article categories
     */
    protected $article_categories;

    /**
     * @var array allowed download categories
     */
    protected $download_categories;

    /**
     * Constructor.
     *
     * @param array $permissions
     * @param array $department_ticket_ids
     * @param array $department_chat_ids
     * @param array $feedback_category_ids
     * @param array $news_category_ids
     * @param array $article_category_ids
     * @param array $download_category_ids
     */
    public function __construct(
        array $permissions = [],
        array $department_ticket_ids = [],
        array $department_chat_ids = [],
        array $feedback_category_ids = [],
        array $news_category_ids = [],
        array $article_category_ids = [],
        array $download_category_ids = []
    ) {
        $this->setArray($permissions);
        $this->setAllowedTicketDepartmentIds($department_ticket_ids);
        $this->setAllowedChatDepartmentIds($department_chat_ids);
        $this->setAllowedDepartmentsIds(array_replace_recursive($department_chat_ids, $department_ticket_ids));
        $this->setAllowedFeedbackCategoryIds($feedback_category_ids);
        $this->setAllowedNewsCategories($news_category_ids);
        $this->setAllowedArticleCategories($article_category_ids);
        $this->setAllowedDownloadCategories($download_category_ids);
    }

    /**
     * This is the primary method to ask for the yes/no boolean permissions (e.g. tickets.reopen_resolved).
     *
     * @param $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (!$this->has($permission)) {
            return false;
        }

        return (bool) $this->get($permission, false);
    }

    /**
     * This takes an entity of a content or a content category and returns true
     * if the bag allows access to that category or content (via reading the category of that
     * content).
     *
     * @param object $content_or_category a Content or ContentCategory entity
     *
     * @return bool
     */
    public function hasContentCategoryAccess($content_or_category)
    {
        // ARTICLES
        if ($content_or_category instanceof Article) {
            $category_ids = $content_or_category->getCategoryIds();
            foreach ($category_ids as $category_id) {
                $allowed_ids = $this->getAllowedArticleCategories();
                if (in_array($category_id, $allowed_ids)) {
                    return true; // one of the article categories IS allowed
                }
            }

            return false;
        } elseif ($content_or_category instanceof ArticleCategory) {
            return in_array($content_or_category->getId(), $this->getAllowedArticleCategories());

        // FEEDBACK
        } elseif ($content_or_category instanceof Feedback) {
            $category_id = $content_or_category->getCategoryId();
            if ($category_id) {
                return in_array($category_id, $this->getAllowedFeedbackCategoryIds());
            }

            return false;
        } elseif ($content_or_category instanceof FeedbackCategory) {
            return in_array($content_or_category->getId(), $this->getAllowedFeedbackCategoryIds());

        // DOWNLOAD
        } elseif ($content_or_category instanceof Download) {
            $category_id = $content_or_category->getCategoryId();
            if ($category_id) {
                return in_array($category_id, $this->getAllowedDownloadCategories());
            }

            return false;
        } elseif ($content_or_category instanceof DownloadCategory) {
            return in_array($content_or_category->getId(), $this->getAllowedDownloadCategories());

        // NEWS
        } elseif ($content_or_category instanceof News) {
            $category_id = $content_or_category->getCategoryId();
            if ($category_id) {
                return in_array($category_id, $this->getAllowedNewsCategories());
            }

            return false;
        } elseif ($content_or_category instanceof NewsCategory) {
            return in_array($content_or_category->getId(), $this->getAllowedNewsCategories());
        }

        return false;
    }

    /**
     * A list of department IDs that are allowed for tickets.
     *
     * @return array
     */
    public function getAllowedTicketDepartmentIds()
    {
        // the keys are the ids, and the value is an array of permissions like "full" => 1.
        // this is just for an array of the ids, but for "deeper" questions we will make another
        // method
        return array_keys($this->department_ticket_ids);
    }

    /**
     * A list of department IDs that are allowed in chat.
     *
     * @return array
     */
    public function getAllowedChatDepartmentIds()
    {
        // the keys are the ids, and the value is an array of permissions like "full" => 1.
        // this is just for an array of the ids, but for "deeper" questions we will make another
        // method
        return array_keys($this->department_chat_ids);
    }

    /**
     * A list of department IDs that are allowed.
     *
     * @return array
     */
    public function getAllowedDepartmentIds()
    {
        // the keys are the ids, and the value is an array of permissions like "full" => 1.
        // this is just for an array of the ids, but for "deeper" questions we will make another
        // method
        return array_keys($this->department_ids);
    }

    /**
     * @param array $department_ticket_ids
     */
    public function setAllowedTicketDepartmentIds(array $department_ticket_ids)
    {
        $this->department_ticket_ids = $department_ticket_ids;
    }

    /**
     * @param array $chat_department_ids
     */
    public function setAllowedChatDepartmentIds(array $chat_department_ids)
    {
        $this->department_chat_ids = $chat_department_ids;
    }

    /**
     * @param array $department_ids
     */
    public function setAllowedDepartmentsIds(array $department_ids)
    {
        $this->department_ids = $department_ids;
    }

    /**
     * @return array allowed feedback category ids
     */
    public function getAllowedFeedbackCategoryIds()
    {
        return $this->feedback_categories;
    }

    /**
     * @param array $feedback_categories
     */
    public function setAllowedFeedbackCategoryIds(array $feedback_categories)
    {
        $this->feedback_categories = $feedback_categories;
    }

    /**
     * @return array
     */
    public function getAllowedDownloadCategories()
    {
        return $this->download_categories;
    }

    /**
     * @param array $allowed_download_categories
     */
    public function setAllowedDownloadCategories($allowed_download_categories)
    {
        $this->download_categories = $allowed_download_categories;
    }

    /**
     * @return array
     */
    public function getAllowedNewsCategories()
    {
        return $this->news_categories;
    }

    /**
     * @param array $allowed_news_categories
     */
    public function setAllowedNewsCategories($allowed_news_categories)
    {
        $this->news_categories = $allowed_news_categories;
    }

    /**
     * @return array
     */
    public function getAllowedArticleCategories()
    {
        return $this->article_categories;
    }

    /**
     * @param array $allowed_article_categories
     */
    public function setAllowedArticleCategories($allowed_article_categories)
    {
        $this->article_categories = $allowed_article_categories;
    }

    /**
     * This is for the yes/no boolean permissions (e.g. tickets.reopen_resolved).
     *
     * You should probably use hasPermissions() above instead for a yes/no answer.
     *
     * @param $key
     * @param bool $default
     *
     * @return bool
     */
    public function get($key, $default = false)
    {
        return (bool) ($this->has($key) ? $this->permissions[$key] : $default);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     */
    public function setArray(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('cannot set a permission in this way. instead, change the underlying entities in the permission system. this is just a dumb bag of data.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException(
            'cannot set a permission in this way. instead, change the underlying entities in the permission system. this is just a dumb bag of data.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            'permissions'       => $this->permissions,
            'feedback'          => $this->feedback_categories,
            'news'              => $this->news_categories,
            'article'           => $this->article_categories,
            'download'          => $this->download_categories,
            'department_chat'   => $this->department_chat_ids,
            'department_ticket' => $this->department_ticket_ids,
            'departments'       => $this->department_ids,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this->permissions           = $unserialized['permissions'];
        $this->feedback_categories   = $unserialized['feedback'];
        $this->news_categories       = $unserialized['news'];
        $this->article_categories    = $unserialized['article'];
        $this->download_categories   = $unserialized['download'];
        $this->department_chat_ids   = $unserialized['department_chat'];
        $this->department_ticket_ids = $unserialized['department_ticket'];
        $this->department_ids        = $unserialized['departments'];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->permissions);
    }
}
