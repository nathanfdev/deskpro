<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PublishFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    const NUM_PUBLISH    = 100;
    const NUM_CATEGORIES = 10;
    const NUM_APC        = 10;
    const MIN_COMMENTS   = 1;
    const MAX_COMMENTS   = 5;
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
     * @var int[]
     */
    private $usergroups = [];

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
            'ids'               => [],
            'category_table'    => self::TABLE_ARTICLE_CATEGORIES,
            'comments_table'    => self::TABLE_ARTICLE_COMMENTS,
            'permissions_table' => 'article_category2usergroup',
            'categories'        => [],
        ],
        self::TABLE_NEWS => [
            'ids'               => [],
            'category_table'    => self::TABLE_NEWS_CATEGORIES,
            'comments_table'    => self::TABLE_NEWS_COMMENTS,
            'permissions_table' => 'news_category2usergroup',
            'categories'        => [],
        ],
        self::TABLE_DOWNLOADS => [
            'ids'               => [],
            'category_table'    => self::TABLE_DOWNLOAD_CATEGORIES,
            'comments_table'    => self::TABLE_DOWNLOAD_COMMENTS,
            'permissions_table' => 'download_category2usergroup',
            'categories'        => [],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 90;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager    = $manager;
        $this->tr         = $this->container->get('deskpro.core.translate');
        $this->admin      = $this->getReference('admin');
        $this->people     = $this->fetchIds(self::TABLE_PEOPLE);
        $this->languages  = $this->fetchIds(self::TABLE_LANGUAGES);
        $this->usergroups = $this->fetchIds(self::TABLE_USERGROUPS);

        $this->loadExampleArticle();
        $this->loadExampleNew();
        $this->loadAPC();
        $manager->flush();

        foreach ($this->content as $content => $params) {
            $this->loadGeneratedCategories($content);
            $this->loadCategoryPermissions($content);
            $this->loadGenerated($content);
            $this->loadComments($content);
        }
        $this->linkArticlesWithCategories();

        $this->loadKbStats();
    }

    private function loadExampleArticle()
    {
        $content = new Article();
        $content
            ->setPerson($this->admin)
            ->setTitle($this->tr->phrase('user.defaults.article_example_title'))
            ->setContent($this->tr->phrase('user.defaults.article_example_content'))
            ->setStatus(ContentAbstract::STATUS_PUBLISHED);
        $content->addToCategory($this->getReference('article_category_general'));
        $this->manager->persist($content);
    }

    private function loadExampleNew()
    {
        $content = new News();
        $content
            ->setPerson($this->admin)
            ->setTitle($this->tr->phrase('user.defaults.news_example_title'))
            ->setContent($this->tr->phrase('user.defaults.news_example_content'))
            ->setStatus(ContentAbstract::STATUS_PUBLISHED);
        $content->setCategory($this->getReference('news_category_general'));
        $this->manager->persist($content);
    }

    private function setStatus(array $values)
    {
        $date                    = $this->faker->dateTimeBetween('-10 days', '-1 days')->format('Y-m-d H:i:s');
        $values['status']        = $this->faker->randomElement($this->statuses);
        $values['num_comments']  = 0;
        $values['hidden_status'] = $values['status'] === ContentAbstract::STATUS_HIDDEN
            ? $this->faker->randomElement($this->hiddenStatuses) : null;
        if ($values['hidden_status'] !== ContentAbstract::HIDDEN_STATUS_UNPUBLISHED) {
            $values['view_count']     = rand(0, 100);
            $values['num_ratings']    = rand(0, 20);
            $values['total_rating']   = rand(0, 20);
            $values['date_published'] = $date;
            $values['date_updated']   = $date;
        } else {
            $values['view_count']     = 0;
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
                // 'parent_id'     => $this->generateParentId($i),
                'display_order' => rand(1, 2),
                'depth'         => 1,
            ];
            if ($categoryTable === self::TABLE_ARTICLE_CATEGORIES) {
                $values['is_agent'] = rand(0, 1);
                $values['is_book']  = rand(0, 1);
            }
            $values             = $this->setTitleAndSlug($values, 3);
            $values['brand_id'] = $this->getReference('brand')->getId();
            $batch[]            = $values;
        }
        $this->db->batchInsert($categoryTable, $batch, true);
        $this->content[$content]['categories'] = $this->fetchIds($categoryTable);
    }

    private function generateParentId($i)
    {
        if ($i > 2) {
            $parentId = rand(0, $i - 1);

            return $parentId ? $parentId : null;
        }

        return;
    }

    private function loadCategoryPermissions($content)
    {
        $batch      = [];
        $categories = $this->content[$content]['categories'];
        foreach ($categories as $category) {
            $values = [
                'category_id'  => $category,
                'usergroup_id' => $this->faker->randomElement($this->usergroups),
            ];
            $batch[] = $values;
        }
        $table = $this->content[$content]['permissions_table'];
        $this->db->batchInsert($table, $batch, true);
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

            if ($content === self::TABLE_DOWNLOADS) {
                $file_info = $this->faker->randomElement(
                    [
                        ['name' => 'file.txt', 'ext' => 'txt', 'type' => 'text/plain', 'content' => 'example file'],
                        [
                            'name' => 'file.zip',
                            'ext'  => 'zip',
                            'type' => 'application/zip',
                            'file' => DP_APP_DIR.'/src/Application/AdminInterfaceBundle/Resources/assets/Bulk-Add-Agents-Spreadsheet-Template.zip',
                        ],
                        [
                            'name' => 'file.pdf',
                            'ext'  => 'pdf',
                            'type' => 'application/pdf',
                            'file' => DP_APP_DIR.'/src/Application/AgentBundle/Resources/assets/agent-quickstart/en_US.pdf',
                        ],
                        [
                            'name' => 'file.jpg',
                            'ext'  => 'jpg',
                            'type' => 'image/jpeg',
                            'file' => DP_APP_DIR.'/src/Application/DeskPRO/Resources/assets/avatar-man-face.png',
                        ],
                    ]
                );
                $blob_info = $this->container->get('blob.storage')->createBlobRecordFromString(
                    @$file_info['content'] ?: file_get_contents($file_info['file']),
                    $file_info['name'],
                    $file_info['type']
                );
                $values['filename'] = trim(substr($values['slug'], 0, 10), '-').".{$file_info['ext']}";
                $values['blob_id']  = $blob_info['id'];
            }

            $batch[] = $values;
        }
        $this->db->batchInsert($content, $batch, true);
        $this->content[$content]['ids'] = $this->fetchIds($content);
    }

    private function loadComments($content)
    {
        $batch = [];
        foreach ($this->content[$content]['ids'] as $id) {
            $i            = 0;
            $num_comments = rand(self::MIN_COMMENTS, self::MAX_COMMENTS);
            if ($content === self::TABLE_ARTICLES) {
                /** @var Article $article */
                $article = $this->manager->getRepository('DeskPRO:Article')->find($id);
                if ($article->getHiddenStatus() === ContentAbstract::HIDDEN_STATUS_UNPUBLISHED) {
                    continue;
                }
                $article->setNumComments($num_comments);
                $this->manager->persist($article);
            } elseif ($content === self::TABLE_NEWS) {
                /** @var News $new */
                $new = $this->manager->getRepository('DeskPRO:News')->find($id);
                if ($new->getHiddenStatus() === ContentAbstract::HIDDEN_STATUS_UNPUBLISHED) {
                    continue;
                }
                $new->setNumComments($num_comments);
                $this->manager->persist($new);
            } elseif ($content === self::TABLE_DOWNLOADS) {
                /** @var Download $download */
                $download = $this->manager->getRepository('DeskPRO:Download')->find($id);
                if ($download->getHiddenStatus() === ContentAbstract::HIDDEN_STATUS_UNPUBLISHED) {
                    continue;
                }
                $download->setNumComments($num_comments);
                $this->manager->persist($download);
            }
            while ($i++ < $num_comments) {
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
                    $values['article_id'] = $id;
                } elseif ($content === self::TABLE_NEWS) {
                    $values['news_id'] = $id;
                } elseif ($content === self::TABLE_DOWNLOADS) {
                    $values['download_id'] = $id;
                }
                $batch[] = $values;
            }
        }
        $this->db->batchInsert($this->content[$content]['comments_table'], $batch, true);
        $this->manager->flush();
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

    private function loadAPC()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < self::NUM_APC) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');
            $values      = [
                'comment'            => $this->faker->realText(300),
                'person_id'          => $this->getReference('admin')->getId(),
                'assigned_person_id' => $this->faker->randomElement($this->people),
                'date_created'       => $dateCreated,
            ];
            $batch[] = $values;
        }
        $this->db->batchInsert(self::TABLE_ARTICLE_PENDING_CREATE, $batch, true);
    }

    private function loadKbStats()
    {
        // views
        $batch = [];
        foreach ($this->content[self::TABLE_ARTICLES]['ids'] as $id) {
            $max = mt_rand(2, 200);
            for ($i = 0; $i < $max; ++$i) {
                $batch[] = [
                    'visitor_id'   => uniqid(),
                    'ip_address'   => '127.198.1.1',
                    'page_type'    => 'deskpro.kb_view',
                    'page_id'      => $id,
                    'url'          => 'http://example.com/kb/articles/'.$id,
                    'referrer'     => '',
                    'user_agent'   => 'Dev Fixture',
                    'geo_country'  => 'GB',
                    'meta'         => json_encode(['pageTitle' => $this->faker->words(3, true)]),
                    'date_created' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->batchInsert('hit_record', $batch);

        // Searches
        $batch = [];
        foreach ([$this->faker->words(3), $this->faker->words(3), $this->faker->words(3), $this->faker->words(3)] as $words) {
            $words = implode(' ', $words);
            $max   = mt_rand(2, 200);
            for ($i = 0; $i < $max; ++$i) {
                $batch[] = [
                    'person_id'    => null,
                    'visitor_id'   => uniqid(),
                    'ip_address'   => '127.198.1.1',
                    'query'        => $words,
                    'num_results'  => mt_rand(0, 20),
                    'date_created' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->batchInsert('searchlog', $batch);
    }
}
