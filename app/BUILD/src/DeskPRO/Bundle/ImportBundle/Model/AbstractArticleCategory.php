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

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractArticleCategory.
 */
abstract class AbstractArticleCategory implements OidAwareModelInterface, UsergroupAwareModelInterface
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_agent = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_book = false;

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $user_groups = [];

    /**
     * @var ArticleSubCategory[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\ArticleSubCategory>")
     *
     * @Assert\Valid()
     */
    private $categories = [];

    /**
     * Returns article category title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAgent()
    {
        return $this->is_agent;
    }

    /**
     * @param bool $is_agent
     *
     * @return $this
     */
    public function setAsAgent($is_agent)
    {
        $this->is_agent = (bool) $is_agent;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBook()
    {
        return $this->is_book;
    }

    /**
     * @param bool $is_book
     *
     * @return $this
     */
    public function setAsBook($is_book)
    {
        $this->is_book = (bool) $is_book;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroups()
    {
        return $this->user_groups;
    }

    /**
     * Add an user group.
     *
     * @param string $user_group
     *
     * @return $this
     */
    public function addUserGroup($user_group)
    {
        $this->user_groups[] = $user_group;

        return $this;
    }

    /**
     * Returns a collection of child categories.
     *
     * @return ArticleSubCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add a child category.
     *
     * @param ArticleSubCategory $category
     *
     * @return $this
     */
    public function addCategory(ArticleSubCategory $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Set a collection of child categories.
     *
     * @param ArticleCategory[] $categories
     *
     * @return $this
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;

        return $this;
    }
}
