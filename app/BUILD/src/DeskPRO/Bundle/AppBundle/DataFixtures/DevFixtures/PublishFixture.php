<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PublishFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    const NUM_PUBLISH    = 100;
    const NUM_CATEGORIES = 10;
    const NUM_COMMENTS   = 30;
    const MIN_CATEGORIES = 0;
    const MAX_CATEGORIES = 3;

    /**
     * @var \Application\DeskPRO\Translate\Translate
     */
    private $tr;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $admin;

    /**
     * @var int[]
     */
    private $people = [];

    /**
     * @var int[]
     */
    private $languages = [];

    /**
     * @var string[]
     */
    private $statuses = [
        ContentAbstract::STATUS_PUBLISHED,
        ContentAbstract::STATUS_HIDDEN,
        ContentAbstract::STATUS_ARCHIVED,
    ];

    /**
     * @var string[]
     */
    private $hiddenStatuses = [
        ContentAbstract::HIDDEN_STATUS_DELETED,
        ContentAbstract::HIDDEN_STATUS_DRAFT,
        ContentAbstract::HIDDEN_STATUS_SPAM,
        ContentAbstract::HIDDEN_STATUS_UNPUBLISHED,
    ];

    /**
     * @var string[]
     */
    private $commentStatuses = [
        CommentAbstract::STATUS_HIDDEN,
        CommentAbstract::STATUS_DELETED,
        CommentAbstract::STATUS_VISIBLE,
    ];

    /**
     * @var string[]
     */
    private $content = [
        self::TABLE_ARTICLES => [
            'ids'            => [],
            'category_table' => self::TABLE_ARTICLE_CATEGORIES,
            'comments_table' => self::TABLE_ARTICLE_COMMENTS,
            'categories'     => [],
        ],
        self::TABLE_NEWS => [
            'ids'            => [],
            'category_table' => self::TABLE_NEWS_CATEGORIES,
            'comments_table' => self::TABLE_NEWS_COMMENTS,
            'categories'     => [],
        ],
        self::TABLE_DOWNLOADS => [
            'ids'            => [],
            'category_table' => self::TABLE_DOWNLOAD_CATEGORIES,
            'comments_table' => self::TABLE_DOWNLOAD_COMMENTS,
            'categories'     => [],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 80;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager   = $manager;
        $this->tr        = $this->container->get('deskpro.core.translate');
        $this->admin     = $this->getReference('admin');
        $this->people    = $this->fetchIds(self::TABLE_PEOPLE);
        $this->languages = $this->fetchIds(self::TABLE_LANGUAGES);

        $this->loadExampleArticle();
        $this->loadExampleNew();
        $manager->flush();

        foreach ($this->content as $content => $params) {
            $this->loadGeneratedCategories($content);
            $this->loadGenerated($content);
            $this->loadComments($content);
        }
        $this->linkArticlesWithCategories();
    }

    private function loadExampleArticle()
    {
        $content          = new Article();
        $content->person  = $this->admin;
        $content->title   = $this->tr->phrase('user.defaults.article_example_title');
        $content->content = $this->tr->phrase('user.defaults.article_example_content');
        $content->status  = ContentAbstract::STATUS_PUBLISHED;
        $content->addToCategory($this->getReference('article_category_general'));
        $this->manager->persist($content);
    }

    private function loadExampleNew()
    {
        $content          = new News();
        $content->person  = $this->admin;
        $content->title   = $this->tr->phrase('user.defaults.news_example_title');
        $content->content = $this->tr->phrase('user.defaults.news_example_content');
        $content->status  = ContentAbstract::STATUS_PUBLISHED;
        $content->setCategory($this->getReference('news_category_general'));
        $this->manager->persist($content);
    }

    private function setStatus(array $values)
    {
        $date                    = $this->faker->dateTimeBetween('-10 days', '-1 days')->format('Y-m-d H:i:s');
        $values['status']        = $this->faker->randomElement($this->statuses);
        $values['hidden_status'] = $values['status'] === ContentAbstract::STATUS_HIDDEN
            ? $this->faker->randomElement($this->hiddenStatuses) : null;
        if ($values['hidden_status'] !== ContentAbstract::HIDDEN_STATUS_UNPUBLISHED) {
            $values['view_count']     = rand(0, 100);
            $values['num_comments']   = rand(0, 100);
            $values['num_ratings']    = rand(0, 20);
            $values['total_rating']   = rand(0, 20);
            $values['date_published'] = $date;
            $values['date_updated']   = $date;
        } else {
            $values['view_count']     = 0;
            $values['num_comments']   = 0;
            $values['num_ratings']    = 0;
            $values['total_rating']   = 0;
            $values['date_published'] = null;
            $values['date_updated']   = null;
        }

        return $values;
    }

    private function loadGeneratedCategories($content)
    {
        $i             = 0;
        $batch         = [];
        $categoryTable = $this->content[$content]['category_table'];
        while ($i++ < self::NUM_CATEGORIES) {
            $values = [
//                'parent_id'     => $this->generateParentId($i),
'display_order' => rand(1, 2),
'depth'         => 1,
            ];
            if ($categoryTable === self::TABLE_ARTICLE_CATEGORIES) {
                $values['is_agent'] = rand(0, 1);
                $values['is_book']  = rand(0, 1);
            }
            $values  = $this->setTitleAndSlug($values, 3);
            $batch[] = $values;
        }
        $this->db->batchInsert($categoryTable, $batch, true);
        $this->content[$content]['categories'] = $this->fetchIds($categoryTable);
    }

    private function generateParentId($i)
    {
        echo "\nIndex: $i";
        if ($i > 2) {
            $parentId = rand(0, $i - 1);

            return $parentId ? $parentId : null;
        }

        return;
    }

    private function loadGenerated($content)
    {
        $i     = 0;
        $batch = [];
        while ($i++ < self::NUM_PUBLISH) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');
            $values      = [
                'content'      => $this->faker->realText(300),
                'person_id'    => $this->faker->randomElement($this->people),
                'language_id'  => $this->faker->randomElement($this->languages),
                'date_created' => $dateCreated,
            ];
            $values = $this->setStatus($values);
            $values = $this->setTitleAndSlug($values);
            if ($content !== self::TABLE_ARTICLES) {
                $values['category_id'] = $this->faker->randomElement($this->content[$content]['categories']);
            }
            $batch[] = $values;
        }
        $this->db->batchInsert($content, $batch, true);
        $this->content[$content]['ids'] = $this->fetchIds($content);
    }

    private function loadComments($content)
    {
        $i     = 0;
        $batch = [];
        while ($i++ < self::NUM_COMMENTS) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');
            $values      = [
                'content'      => $this->faker->realText(300),
                'person_id'    => $this->faker->randomElement($this->people),
                'ip_address'   => $this->faker->ipv4,
                'status'       => $this->faker->randomElement($this->commentStatuses),
                'is_reviewed'  => rand(0, 1),
                'date_created' => $dateCreated,
            ];
            if ($content === self::TABLE_ARTICLES) {
                $values['article_id'] = $this->faker->randomElement($this->content[$content]['ids']);
            } elseif ($content === self::TABLE_NEWS) {
                $values['news_id'] = $this->faker->randomElement($this->content[$content]['ids']);
            } elseif ($content === self::TABLE_DOWNLOADS) {
                $values['download_id'] = $this->faker->randomElement($this->content[$content]['ids']);
            }
            $batch[] = $values;
        }
        $this->db->batchInsert($this->content[$content]['comments_table'], $batch, true);
    }

    private function linkArticlesWithCategories()
    {
        $batch = [];
        /** @var array $ids */
        $ids = $this->content[self::TABLE_ARTICLES]['ids'];
        foreach ($ids as $id) {
            $num = rand(self::MIN_CATEGORIES, self::MAX_CATEGORIES);
            if ($num) {
                $batch = $this->generateLinks($num, $id, $batch);
            }
        }
        $this->db->batchInsert(self::TABLE_ARTICLE_TO_CATEGORIES, $batch, true);
    }

    /**
     * @param       $num
     * @param       $id
     * @param array $batch
     *
     * @return array
     */
    private function generateLinks($num, $id, array $batch)
    {
        $categories = $this->faker->randomElements(
            $this->content[self::TABLE_ARTICLES]['categories'],
            $num
        );
        foreach ($categories as $category) {
            $batch[] = ['article_id' => $id, 'category_id' => $category];
        }

        return $batch;
    }
}
