<?php

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
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Topic;

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
    protected $departmentTicketIds;

    /**
     * @var array just a list of allowed department ids for chat
     */
    protected $departmentChatIds;

    /**
     * @var array just a list of all allowed
     */
    protected $departmentIds;

    /**
     * @var array allowed feedback categories
     */
    protected $feedbackCategories;

    /**
     * @var array allowed news categories
     */
    protected $newsCategories;

    /**
     * @var array allowed article categories
     */
    protected $articleCategories;

    /**
     * @var array allowed download categories
     */
    protected $downloadCategories;

    /**
     * @var array allow guides
     */
    protected $guides;

    /**
     * Constructor.
     *
     * @param Permission[] $permissions
     * @param array        $departmentTicketIds
     * @param array        $departmentChatIds
     * @param array        $feedbackCategoryIds
     * @param array        $newsCategoryIds
     * @param array        $articleCategoryIds
     * @param array        $downloadCategoryIds
     * @param array        $guides
     */
    public function __construct(
        array $permissions = [],
        array $departmentTicketIds = [],
        array $departmentChatIds = [],
        array $feedbackCategoryIds = [],
        array $newsCategoryIds = [],
        array $articleCategoryIds = [],
        array $downloadCategoryIds = [],
        array $guides = []
    ) {
        $this->setArray($permissions);
        $this->setAllowedTicketDepartmentIds($departmentTicketIds);
        $this->setAllowedChatDepartmentIds($departmentChatIds);
        $this->setAllowedDepartmentsIds(array_replace_recursive($departmentChatIds, $departmentTicketIds));
        $this->setAllowedFeedbackCategoryIds($feedbackCategoryIds);
        $this->setAllowedNewsCategories($newsCategoryIds);
        $this->setAllowedArticleCategories($articleCategoryIds);
        $this->setAllowedDownloadCategories($downloadCategoryIds);
        $this->setAllowedGuides($guides);
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
     * @param object $contentOrCategory a Content or ContentCategory entity
     *
     * @return bool
     */
    public function hasContentCategoryAccess($contentOrCategory)
    {
        // ARTICLES
        if ($contentOrCategory instanceof Article) {
            $categoryIds = $contentOrCategory->getCategoryIds();
            foreach ($categoryIds as $categoryId) {
                $allowedIds = $this->getAllowedArticleCategories();
                if (in_array($categoryId, $allowedIds)) {
                    return true; // one of the article categories IS allowed
                }
            }

            return false;
        } elseif ($contentOrCategory instanceof ArticleCategory) {
            return in_array($contentOrCategory->getId(), $this->getAllowedArticleCategories());

            // FEEDBACK
        } elseif ($contentOrCategory instanceof Feedback) {
            $categoryId = $contentOrCategory->getCategoryId();
            if ($categoryId) {
                return in_array($categoryId, $this->getAllowedFeedbackCategoryIds());
            }

            return false;
        } elseif ($contentOrCategory instanceof FeedbackCategory) {
            return in_array($contentOrCategory->getId(), $this->getAllowedFeedbackCategoryIds());

            // DOWNLOAD
        } elseif ($contentOrCategory instanceof Download) {
            $categoryId = $contentOrCategory->getCategoryId();
            if ($categoryId) {
                return in_array($categoryId, $this->getAllowedDownloadCategories());
            }

            return false;
        } elseif ($contentOrCategory instanceof DownloadCategory) {
            return in_array($contentOrCategory->getId(), $this->getAllowedDownloadCategories());

            // NEWS
        } elseif ($contentOrCategory instanceof News) {
            $categoryId = $contentOrCategory->getCategoryId();
            if ($categoryId) {
                return in_array($categoryId, $this->getAllowedNewsCategories());
            }

            return false;
        } elseif ($contentOrCategory instanceof NewsCategory) {
            return in_array($contentOrCategory->getId(), $this->getAllowedNewsCategories());

            // GUIDES
        } elseif ($contentOrCategory instanceof Topic) {
            $guideId = $contentOrCategory->getGuide()->getId();
            if ($guideId) {
                return in_array($guideId, $this->getAllowedGuides());
            }

            return false;
        } elseif ($contentOrCategory instanceof Guide) {
            return in_array($contentOrCategory->getId(), $this->getAllowedGuides());
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
        return array_keys($this->departmentTicketIds);
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
        return array_keys($this->departmentChatIds);
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
        return array_keys($this->departmentIds);
    }

    /**
     * @param array $departmentTicketIds
     *
     * @return $this
     */
    public function setAllowedTicketDepartmentIds(array $departmentTicketIds)
    {
        $this->departmentTicketIds = $departmentTicketIds;

        return $this;
    }

    /**
     * @param array $chatDepartmentIds
     *
     * @return $this
     */
    public function setAllowedChatDepartmentIds(array $chatDepartmentIds)
    {
        $this->departmentChatIds = $chatDepartmentIds;

        return $this;
    }

    /**
     * @param array $departmentIds
     *
     * @return $this
     */
    public function setAllowedDepartmentsIds(array $departmentIds)
    {
        $this->departmentIds = $departmentIds;

        return $this;
    }

    /**
     * @return array allowed feedback category ids
     */
    public function getAllowedFeedbackCategoryIds()
    {
        return $this->feedbackCategories;
    }

    /**
     * @param array $feedbackcategories
     *
     * @return $this
     */
    public function setAllowedFeedbackCategoryIds(array $feedbackcategories)
    {
        $this->feedbackCategories = $feedbackcategories;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedDownloadCategories()
    {
        return $this->downloadCategories;
    }

    /**
     * @param array $allowedDownloadCategories
     *
     * @return $this
     */
    public function setAllowedDownloadCategories($allowedDownloadCategories)
    {
        $this->downloadCategories = $allowedDownloadCategories;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedNewsCategories()
    {
        return $this->newsCategories;
    }

    /**
     * @param array $allowedNewsCategories
     *
     * @return $this
     */
    public function setAllowedNewsCategories($allowedNewsCategories)
    {
        $this->newsCategories = $allowedNewsCategories;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedArticleCategories()
    {
        return $this->articleCategories;
    }

    /**
     * @param array $allowedArticleCategories
     *
     * @return $this
     */
    public function setAllowedArticleCategories($allowedArticleCategories)
    {
        $this->articleCategories = $allowedArticleCategories;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedGuides()
    {
        return $this->guides;
    }

    /**
     * @param array $guides
     *
     * @return $this
     */
    public function setAllowedGuides($guides)
    {
        $this->guides = $guides;

        return $this;
    }

    /**
     * This is for the yes/no boolean permissions (e.g. tickets.reopen_resolved).
     *
     * You should probably use hasPermissions() above instead for a yes/no answer.
     *
     * @param      $key
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
     *
     * @return $this
     */
    public function setArray(array $permissions)
    {
        $this->permissions = Permission::getEffectivePermissions($permissions);

        return $this;
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
        throw new \LogicException(
            'cannot set a permission in this way. instead, change the underlying entities in the permission system. this is just a dumb bag of data.'
        );
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
        return serialize(
            [
                'permissions'       => $this->permissions,
                'feedback'          => $this->feedbackCategories,
                'news'              => $this->newsCategories,
                'article'           => $this->articleCategories,
                'download'          => $this->downloadCategories,
                'department_chat'   => $this->departmentChatIds,
                'department_ticket' => $this->departmentTicketIds,
                'departments'       => $this->departmentIds,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this->permissions         = $unserialized['permissions'];
        $this->feedbackCategories  = $unserialized['feedback'];
        $this->newsCategories      = $unserialized['news'];
        $this->articleCategories   = $unserialized['article'];
        $this->downloadCategories  = $unserialized['download'];
        $this->departmentChatIds   = $unserialized['department_chat'];
        $this->departmentTicketIds = $unserialized['department_ticket'];
        $this->departmentIds       = $unserialized['departments'];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->permissions);
    }
}
