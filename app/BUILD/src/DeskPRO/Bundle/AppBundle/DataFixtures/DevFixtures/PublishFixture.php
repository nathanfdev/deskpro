<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Data\ContentTypes;

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
     * @var int[]
     */
    private $splashImages = [];

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
        $this->manager      = $manager;
        $this->tr           = $this->container->get('deskpro.core.translate');
        $this->admin        = $this->getReference('admin');
        $this->people       = $this->fetchIds(self::TABLE_PEOPLE);
        $this->languages    = $this->fetchIds(self::TABLE_LANGUAGES);
        $this->usergroups   = $this->fetchIds(self::TABLE_USERGROUPS);
        $this->splashImages = [];

        $this->loadSplashImages();
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

        $this->loadGuides();
    }

    private function loadSplashImages()
    {
        $flags = \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS
            | \FilesystemIterator::UNIX_PATHS;

        $iter  = new \FilesystemIterator(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/splash_images/', $flags);
        $files = iterator_to_array($iter, false);
        foreach ($files as $file) {
            /** @var $file \SplFileInfo */
            if (is_file($file->getRealPath())) {
                $splashBlob          = $this->container->get('deskpro.blob_storage')->createBlobRecordFromFile(
                    $file->getRealPath(),
                    $file->getFilename(),
                    ContentTypes::getContentTypeFromFilename($file->getFilename())
                );
                $splashImageProperty = new SplashImageProperty();
                $splashImageProperty->setBlob($splashBlob)->setUrn(SplashImageProperty::$blobNs.':'.$splashBlob->getAuthId());
                $this->manager->persist($splashImageProperty);
                $this->manager->flush();
                $this->splashImages[] = $splashImageProperty->getId();
            }
        }
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
            if (in_array($content, [self::TABLE_NEWS])) {
                $values['splash_image_property_id'] = $this->faker->randomElement($this->splashImages);
            }
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

    private function loadGuides()
    {
        $guide = new Guide();
        $guide->setTitle('Test Guide');
        /** @var Brand $brand */
        $brand = $this->getReference('brand');
        $guide->setBrand($brand);
        $this->manager->persist($guide);
        $this->manager->flush();

        $section1 = new Topic();
        $section1->setTitle('The Agent interface');
        $section1->setGuide($guide);
        $section1->setStatus(ContentAbstract::STATUS_PUBLISHED);
        $section1->setNoContent(true);
        $this->manager->persist($section1);

        $section2 = new Topic();
        $section2->setTitle('Tickets');
        $section2->setGuide($guide);
        $section2->setStatus(ContentAbstract::STATUS_PUBLISHED);
        $section2->setNoContent(true);
        $this->manager->persist($section2);

        $this->manager->flush();

        $topics = [
            [
                'title'   => 'Introduction and Overview',
                'content' => <<<'CONTENT'
<p>Here is an overview of the agent interface.</p>
<p><img src="{{ img(2786NDAZNMYJCP2785894E4059F/Interface-more-complex.png) }}" alt="Interface-more-complex.png" /></p>
<p>The app bar is where you select which Deskpro app you want to use.</p>
<p>The {{ content_link(topic,4) }} lets you select groups of items to be displayed in the <strong>list pane</strong>, such as tickets, users etc. The app you’re using determines what sort of items you’ll see in these panes.</p>
<p>The {{ content_link(topic,6) }} is where you can view and work with individual items. The tabbed interface lets you have multiple items from different apps open at the same time.</p>
<p>The {{ content_link(topic,3) }} offers quick access to important functions: the search bar, your account preferences, logging into user chat, chatting with other Agents via the Agent IM. It’s also where you can switch to the {{ content_link(topic,7) }} that combines the filter and content panes.</p>
<p><img src="{{ img(910CWKGXBGBCK9097837847B7/image.png) }}" alt="image.png" /></p>
<h1>App bar</h1>
<p>The <strong>app bar</strong> is where you select which Deskpro application you want to use. Each app lets you work on a different type of helpdesk content.</p>
<p>The apps are: <a href="{{ content(topic,9) }}">Tickets</a></p>
<ul>
<li><a href="{{ content(topic,9) }}">Tickets</a> - the main application you will be using as an Agent to communicate with users about their Tickets.</li>
<li>{{ content_link(topic,248) }} - this is where you communicate with Users who have requested real-time text Chat through your Portal or website, and view logs of previous chats.</li>
<li>{{ content_link(topic,256) }} - used to view a unified record of each User and their history with the helpdesk, such as all previous tickets.</li>
<li>{{ content_link(topic,276) }} - view and manage crowdsourced feedback; Users submit suggestions about your products/services via the web Portal, and others can vote so you can identify the most popular ideas.</li>
<li>{{ content_link(topic,265) }} - create and manage the help content on your web Portal; this can include News posts, Downloads, Guides and Knowledgebase Articles</li>
<li>{{ content_link(topic,282) }} - keep track of any Tasks that you or your fellow Agents need to carry out.</li>
</ul>
CONTENT,
                'content_input' => <<<'CONTENT'
Here is an overview of the agent interface.

![Interface-more-complex.png]({{ img(2786NDAZNMYJCP2785894E4059F/Interface-more-complex.png) }})

The app bar is where you select which Deskpro app you want to use.

The {{ content_link(topic,4) }} lets you select groups of items to be displayed in the **list pane**, such as tickets, users etc. The app you’re using determines what sort of items you’ll see in these panes.

The {{ content_link(topic,6) }} is where you can view and work with individual items. The tabbed interface lets you have multiple items from different apps open at the same time.

The {{ content_link(topic,3) }} offers quick access to important functions: the search bar, your account preferences, logging into user chat, chatting with other Agents via the Agent IM. It’s also where you can switch to the {{ content_link(topic,7) }} that combines the filter and content panes.

![image.png]({{ img(910CWKGXBGBCK9097837847B7/image.png) }})

# App bar

The **app bar** is where you select which Deskpro application you want to use. Each app lets you work on a different type of helpdesk content.

The apps are: [Tickets]({{ content(topic,9) }})
*   [Tickets]({{ content(topic,9) }}) - the main application you will be using as an Agent to communicate with users about their Tickets.
*   {{ content_link(topic,248) }} - this is where you communicate with Users who have requested real-time text Chat through your Portal or website, and view logs of previous chats.
*   {{ content_link(topic,256) }} - used to view a unified record of each User and their history with the helpdesk, such as all previous tickets.
*   {{ content_link(topic,276) }} - view and manage crowdsourced feedback; Users submit suggestions about your products/services via the web Portal, and others can vote so you can identify the most popular ideas.
*   {{ content_link(topic,265) }} - create and manage the help content on your web Portal; this can include News posts, Downloads, Guides and Knowledgebase Articles
*   {{ content_link(topic,282) }} - keep track of any Tasks that you or your fellow Agents need to carry out.
CONTENT,
                'no_content'    => 0,
                'guide'         => $guide,
                'parent'        => $section1,
            ],
            [
                'title'   => 'Header',
                'content' => <<<'CONTENT'
<p>The header is at the top of the agent interface and gives you quick access to common tasks like creating new tickets, adding new content, searching the helpdesk and updating your agent profile and preferences.</p>
<p><img src="{{ img(2786YDXARHHBKT2785956CE20D8/Toolbar.png) }}" alt="Toolbar.png" /></p>
<p>There is a short walk through to show you the ins and outs of the different features you can access from here.</p>
<p><img src="{{ img(910SSWSJPSBAG90981978D474/header-tut.gif) }}" alt="header-tut.gif" /></p>
<h1>Live Chat Status</h1>
<p>If you are an agent that responds to live chats, this icon will appear in the top right of the header. You can change the volume of your chat notifications and see a list of other agents who are online, split by department if you wish.</p>
<p><img src="{{ img(5569RDXMSGMXNS5568765E9EA84/Chat-Status.jpg) }}" alt="Chat-Status.jpg" width="325" height="300" /></p>
<h1>Agent Profile</h1>
<p>Clicking on the avatar in the top right-hand corner of the agent interface gives you access to set your chat online/offline status, view help information, change your account preferences and log out of the helpdek. You can find out more about each of these options below.</p>
<p><img src="{{ img(5569ZSBZXTSGKY5568766F426CE/Agent-Profile.jpg) }}" alt="Agent-Profile.jpg" width="400" height="400" /></p>
<h1>Status</h1>
<p>This is where you can set yourself as online or offline for User chat. To set yourself online, you must selection 'Online' in the drop-down menu <strong>and</strong> click the Chat tick box, so the chat icon turns green.</p>
<h1>Help</h1>
<p>This section tells you how to contact Deskpro Support, has links to some useful documentation and gives you information about the keyboard shortcuts that are supported in the agent interface (depending on whether your helpdesk Admins have keyboard shortcuts enabled).</p>
<p><img src="{{ img(5569ZDABTJSZBP556874262DF29/Help.jpg) }}" alt="Help.jpg" width="500" height="400" /></p>
<h1>Log Out</h1>
<p><img src="{{ img(911TQTMQTRAKN910527FA2DDE/image.png) }}" alt="image.png" /></p>
<p>Click here to log out of your Deskpro account.
You can just close your browser to log out, unless you selected the <strong>Remember me</strong> option when you logged in.</p>
<h1>Preferences</h1>
<p><img src="{{ img(911CKHAGRYSAP910570C1682C/image.png) }}" alt="image.png" /></p>
<p>The Preferences tab allows you to customize your agent profile and notification settings.</p>
<p>You can:</p>
<ul>
<li>Edit your agent profile</li>
<li>Add a signature</li>
<li>Edit your ticket email and browser notifications (if your admin permits this)</li>
<li>Edit notifications for other helpdesk activity</li>
<li>Create and manage your Macros</li>
<li>Create and manage your Filters</li>
<li>Manage the view of any SLAs</li>
</ul>
<p><img src="{{ img(911XTTYPAGMRS910569484E0F/Preferences.gif) }}" alt="Preferences.gif" /></p>
<h1>Notifications</h1>
<p>The Notifications button is the bell icon at the right of the search bar next to your profile image and preferences button.</p>
<p>This icon shows a count of your outstanding <strong>browser notifications</strong>:</p>
<p><img src="{{ img(911MRSXBJZQTR910674BDFBDB/Screen-Shot-2017-04-07-at-12.02.15.png) }}" alt="Screen-Shot-2017-04-07-at-12.02.15.png" /></p>
<p>Click the icon to expand the notification window:</p>
<p><img src="{{ img(911DDGHNTWAJQ910679D57867/Screen-Shot-2017-04-07-at-12.12.10.png) }}" alt="Screen-Shot-2017-04-07-at-12.12.10.png" /></p>
<p>You can control which events create a browser notification here using your {{ content_link(topic,227) }} or the gear icon at top right of the notification window. (You may not have permission to change your notification settings).</p>
<p>Click <strong>Dismiss All Notifications</strong> to clear all notifications or click the icon next to any individual message to remove it. You can see dismissed notifications again using <strong>View Dismissed</strong>.</p>
<h1>Ticket Screen Toggle</h1>
<p>Use this button to change the view of your ticket interface from a 3-column view, to a 2-column view without the list pane.</p>
<p><img src="{{ img(911PHMJYPYRHS910621A0BE51/toggle2.gif) }}" alt="toggle2.gif" /></p>
<p>This can be useful if you are working on a relatively small screen, or if you just want to focus on a task in one or two panes.</p>
<p>See the section on {{ content_link(topic,226) }}) for more details.</p>
<h1>+ Add Button</h1>
<p>This button allows you to create a new:</p>
<ul>
<li>Ticket</li>
<li>Person</li>
<li>Organization</li>
<li>Knowledgebase Article</li>
<li>News Post</li>
<li>Download</li>
<li>Community Topic</li>
<li>Guide Topic</li>
<li>Task</li>
</ul>
<p>Please note that you will only be able to create content that you have permissions for.</p>
<p><img src="{{ img(5850BZAKMYJRRR5849525134C7A/Add-Content.jpg) }}" alt="Add-Content.jpg" width="200" height="330" /></p>
<h1>Recent Activity Button</h1>
<p>Easily find what you were just viewing or working on with the list of your recent activity. The Recent Activity button is the clock icon next to the [+] Add button.</p>
<p>Items are ordered by the time since you last viewed them, with the most recent at the top. You can type to filter matching items.</p>
<p><img src="{{ img(5569CCSXTHZQBW55686771A2959/Recent-Activity.jpg) }}" alt="Recent-Activity.jpg" width="1000" /></p>
<p>This is useful if you want to go back to an item you’ve recently closed.</p>
<h1>Search bar</h1>
<p>The <strong>search bar</strong> is at the left of the header.</p>
<p>You can use this bar for full-text search of tickets and any other items in your helpdesk such as live chat transcripts, articles and organization profiles.</p>
<p><img src="{{ img(5569GJCYTWMWWR556868863C119/Search.jpg) }}" alt="Search.jpg" width="1000" /></p>
<p>You can sort results by 'Best Match', 'Last Activity' and 'Date Created' which makes it easier to find any specific content that you are looking for.</p>
<p>If you need a more advanced search, you can search from the filter pane of most individual apps. The filter pane search, lets you specify more complex search criteria for the items that you want.</p>
<p>See the section on {{ content_link(topic,242) }} for details of using advanced search to find tickets.</p>
<div class="block warning">
<p>If your organization uses Deskpro On-Premise and your Admin's have not yet installed the Elasticsearch technology, you will not have full-text search. In this case, the sort options are not available, and you can only search for items by:</p>
<ul>
<li><strong>ID</strong></li>
<li><strong>Ref code</strong> (tickets)</li>
<li><strong>Subject</strong> (tickets)</li>
<li><strong>Name</strong> (users/organizations)</li>
<li><strong>Email address</strong> (tickets/users/organizations)</li>
<li><strong>Title</strong> (articles/news/downloads/feedback)</li>
<li><strong>Labels</strong></li>
</ul>
</div>
CONTENT,
                'content_input' => <<<'CONTENT'
The header is at the top of the agent interface and gives you quick access to common tasks like creating new tickets, adding new content, searching the helpdesk and updating your agent profile and preferences.

![Toolbar.png]({{ img(2786YDXARHHBKT2785956CE20D8/Toolbar.png) }})

There is a short walk through to show you the ins and outs of the different features you can access from here.

![header-tut.gif]({{ img(910SSWSJPSBAG90981978D474/header-tut.gif) }})

# Live Chat Status

If you are an agent that responds to live chats, this icon will appear in the top right of the header. You can change the volume of your chat notifications and see a list of other agents who are online, split by department if you wish.

![Chat-Status.jpg]({{ img(5569RDXMSGMXNS5568765E9EA84/Chat-Status.jpg) }} =325x300)

# Agent Profile

Clicking on the avatar in the top right-hand corner of the agent interface gives you access to set your chat online/offline status, view help information, change your account preferences and log out of the helpdek. You can find out more about each of these options below.

![Agent-Profile.jpg]({{ img(5569ZSBZXTSGKY5568766F426CE/Agent-Profile.jpg) }} =400x400)

# Status

This is where you can set yourself as online or offline for User chat. To set yourself online, you must selection 'Online' in the drop-down menu **and** click the Chat tick box, so the chat icon turns green.

# Help

This section tells you how to contact Deskpro Support, has links to some useful documentation and gives you information about the keyboard shortcuts that are supported in the agent interface (depending on whether your helpdesk Admins have keyboard shortcuts enabled).

![Help.jpg]({{ img(5569ZDABTJSZBP556874262DF29/Help.jpg) }} =500x400)


# Log Out
![image.png]({{ img(911TQTMQTRAKN910527FA2DDE/image.png) }})

Click here to log out of your Deskpro account.
You can just close your browser to log out, unless you selected the **Remember me** option when you logged in.

# Preferences
![image.png]({{ img(911CKHAGRYSAP910570C1682C/image.png) }})

The Preferences tab allows you to customize your agent profile and notification settings.

You can:
- Edit your agent profile
- Add a signature
- Edit your ticket email and browser notifications (if your admin permits this)
- Edit notifications for other helpdesk activity
- Create and manage your Macros
- Create and manage your Filters
- Manage the view of any SLAs

![Preferences.gif]({{ img(911XTTYPAGMRS910569484E0F/Preferences.gif) }})

# Notifications

The Notifications button is the bell icon at the right of the search bar next to your profile image and preferences button.

This icon shows a count of your outstanding **browser notifications**:

![Screen-Shot-2017-04-07-at-12.02.15.png]({{ img(911MRSXBJZQTR910674BDFBDB/Screen-Shot-2017-04-07-at-12.02.15.png) }})

Click the icon to expand the notification window:

![Screen-Shot-2017-04-07-at-12.12.10.png]({{ img(911DDGHNTWAJQ910679D57867/Screen-Shot-2017-04-07-at-12.12.10.png) }})

You can control which events create a browser notification here using your {{ content_link(topic,227) }} or the gear icon at top right of the notification window. (You may not have permission to change your notification settings).

Click **Dismiss All Notifications** to clear all notifications or click the icon next to any individual message to remove it. You can see dismissed notifications again using **View Dismissed**.

# Ticket Screen Toggle

Use this button to change the view of your ticket interface from a 3-column view, to a 2-column view without the list pane.

![toggle2.gif]({{ img(911PHMJYPYRHS910621A0BE51/toggle2.gif) }})

This can be useful if you are working on a relatively small screen, or if you just want to focus on a task in one or two panes.

See the section on {{ content_link(topic,226) }}) for more details.

# + Add Button

This button allows you to create a new:

- Ticket
- Person
- Organization
- Knowledgebase Article
- News Post
- Download
- Community Topic
- Guide Topic
- Task

Please note that you will only be able to create content that you have permissions for.

![Add-Content.jpg]({{ img(5850BZAKMYJRRR5849525134C7A/Add-Content.jpg) }} =200x330)

# Recent Activity Button

Easily find what you were just viewing or working on with the list of your recent activity. The Recent Activity button is the clock icon next to the [+] Add button.

Items are ordered by the time since you last viewed them, with the most recent at the top. You can type to filter matching items.

![Recent-Activity.jpg]({{ img(5569CCSXTHZQBW55686771A2959/Recent-Activity.jpg) }} =1000x)

This is useful if you want to go back to an item you’ve recently closed.

# Search bar

The **search bar** is at the left of the header.

You can use this bar for full-text search of tickets and any other items in your helpdesk such as live chat transcripts, articles and organization profiles.

![Search.jpg]({{ img(5569GJCYTWMWWR556868863C119/Search.jpg) }} =1000x)

You can sort results by 'Best Match', 'Last Activity' and 'Date Created' which makes it easier to find any specific content that you are looking for.

If you need a more advanced search, you can search from the filter pane of most individual apps. The filter pane search, lets you specify more complex search criteria for the items that you want.

See the section on {{ content_link(topic,242) }} for details of using advanced search to find tickets.

::: warning
If your organization uses Deskpro On-Premise and your Admin's have not yet installed the Elasticsearch technology, you will not have full-text search. In this case, the sort options are not available, and you can only search for items by:

*   **ID**
*   **Ref code** (tickets)
*   **Subject** (tickets)
*   **Name** (users/organizations)
*   **Email address** (tickets/users/organizations)
*   **Title** (articles/news/downloads/feedback)
*   **Labels**
:::
CONTENT,
                'no_content'    => 0,
                'guide'         => $guide,
                'parent'        => $section1,
            ],
            [
                'title'   => 'Filter pane',
                'content' => <<<'CONTENT'
<p>In each app, the <strong>filter pane</strong> on the left is used to filter the items that you’re dealing with: tickets in the tickets app, people and organization records in the CRM app, and so on.</p>
<p>Selecting a filter lets you select a group of items to work with in the list pane.</p>
<p><img src="{{ img(5570DXZQYCAJBW55694212BBA7B/Filters-Pane.png) }}" alt="Filters-Pane.png" /></p>
<p>Each filter shows only the items which match certain criteria - the number to the right of the filter shows how many match.</p>
<p>The <strong>My Tickets</strong> filter shows you tickets that are assigned to you in Awaiting Agent status. You are responsible for replying to the users who created these tickets, or otherwise helping to resolve the problem.</p>
<p>In most apps, you can also view items by <strong>labels</strong>: text tags entered by agents to help group similar items.</p>
<p><img src="{{ img(5570AKRHTMYPGY5569033857D87/Labels-Filters-Pane.jpg) }}" alt="Labels-Filters-Pane.jpg" width="250" height="250" /></p>
<p>For more information about using filters in particular apps, see:</p>
<ul>
<li>{{ content_link(topic,240) }}</li>
<li>{{ content_link(topic,252) }}</li>
<li>{{ content_link(topic,257) }}</li>
<li>{{ content_link(topic,273) }}</li>
</ul>
<h1>Collapsing the filter pane</h1>
<p>Click the <strong>&lt;</strong> icon at the top right to collapse the filter pane.</p>
<p><img src="{{ img(5570SZJWZWCJHY556941862984E/Filters-Pane-Open-and-Closed.jpg) }}" alt="Filters-Pane-Open-and-Closed.jpg" /></p>
<p>This is useful if you are working on a smaller screen and want to free up screen space, or if you prefer to focus on other parts of the interface and don’t want to see the filters.</p>
<p>Mouse over the collapsed filter pane to temporarily expand it and select a different filter. It will collapse again when you move the mouse away, unless you click the lock icon in the top right of the pane.</p>
<p><img src="{{ img(5570YZACBJHBYY5569378580A93/Lock-Filters-Pane.jpg) }}" alt="Lock-Filters-Pane.jpg" width="200" height="300" /></p>
<p>Clicking the lock keeps the filter pane expanded.</p>
CONTENT,
                'content_input' => <<<'CONTENT'
In each app, the **filter pane** on the left is used to filter the items that you’re dealing with: tickets in the tickets app, people and organization records in the CRM app, and so on.

Selecting a filter lets you select a group of items to work with in the list pane.

![Filters-Pane.png]({{ img(5570DXZQYCAJBW55694212BBA7B/Filters-Pane.png) }})

Each filter shows only the items which match certain criteria - the number to the right of the filter shows how many match.

The **My Tickets** filter shows you tickets that are assigned to you in Awaiting Agent status. You are responsible for replying to the users who created these tickets, or otherwise helping to resolve the problem.

In most apps, you can also view items by **labels**: text tags entered by agents to help group similar items.

![Labels-Filters-Pane.jpg]({{ img(5570AKRHTMYPGY5569033857D87/Labels-Filters-Pane.jpg) }} =250x250)

For more information about using filters in particular apps, see:

*   {{ content_link(topic,240) }}
*   {{ content_link(topic,252) }}
*   {{ content_link(topic,257) }}
*   {{ content_link(topic,273) }}

# Collapsing the filter pane

Click the **<** icon at the top right to collapse the filter pane.

![Filters-Pane-Open-and-Closed.jpg]({{ img(5570SZJWZWCJHY556941862984E/Filters-Pane-Open-and-Closed.jpg) }})

This is useful if you are working on a smaller screen and want to free up screen space, or if you prefer to focus on other parts of the interface and don’t want to see the filters.

Mouse over the collapsed filter pane to temporarily expand it and select a different filter. It will collapse again when you move the mouse away, unless you click the lock icon in the top right of the pane.

![Lock-Filters-Pane.jpg]({{ img(5570YZACBJHBYY5569378580A93/Lock-Filters-Pane.jpg) }} =200x300)

Clicking the lock keeps the filter pane expanded.
CONTENT,
                'no_content'    => 0,
                'guide'         => $guide,
                'parent'        => $section1,
            ],
            [
                'title'   => 'List pane',
                'content' => <<<'CONTENT'
<p>The result of your selection in the filter pane is displayed in the list pane.</p>
<p>For example, if you click on a ticket filter, you will see the list of tickets that match the criteria of the filter in the list pane. You can think of it as being like a list of search results.</p>
<p><img src="{{ img(5570QRAKAPMZHJ5569532B9BFE4/List-Pane.jpg) }}" alt="List-Pane.jpg" width="750" height="325" /></p>
<p>In some apps, you can change the ordering of items in the list with controls at the top of the list pane.</p>
<p><img src="{{ img(5570HNPCDDMJGD5569552B92BD7/Ordered-by.jpg) }}" alt="Ordered-by.jpg" width="300" height="425" /></p>
<p>If you have more than 50 items in the list pane, the list will be paginated. Use the arrow controls at the bottom to move between the pages of results in the list pane.</p>
<p><img src="{{ img(5570SKHBDAXMXX55696664FEEF1/List-Pane-Pagination.jpg) }}" alt="List-Pane-Pagination.jpg" width="600" height="125" /></p>
<h1>Viewing properties</h1>
<p>In the <strong>Tickets</strong>, <strong>CRM</strong> and <strong>Feedback</strong> apps, properties are shown for each item using gray text labels.</p>
<p><img src="{{ img(5570QDGHSQZRYR5569954B092AD/Properties.jpg) }}" alt="Properties.jpg" width="450" height="100" /></p>
<p>You can customize the properties which are displayed using the cog control at the top right of the list pane. Use this to display the properties that are most relevant to you. These changes will only apply for your agent account, and won't affect the display for any other agents in your helpdesk.</p>
<p><img src="{{ img(5571HYJHTYYJSP5570028A8BC58/Display-Options.jpg) }}" alt="Display-Options.jpg" /></p>
<p>The display options you display can be different for each filter. For example, you could have the 'Agent' information appear in the properties for tickets in the All Tickets filter list, but not have that display for tickets in the My Tickets filter list (where the Agent would always be listed as you).</p>
<p>Simply click on the filter you want to edit the display options for and click on the cog icon. The filter you are editing will display at the top of the pop up window.</p>
<p><img src="{{ img(5571GZAHKYJPMP55702293258F5/Display-Options-Open.jpg) }}" alt="Display-Options-Open.jpg" width="800" height="300" /></p>
<h1>Opening items</h1>
<p>You can <strong>open</strong> items from the list pane into the content pane to view them in more detail. You can have multiple items open at a time.</p>
<p>You open an item by clicking on it - it turns blue to show that it is open, and is displayed in a new tab in the content pane, in front of the tabs for any other items you have open.</p>
<p><img src="{{ img(5570KKWBGKWBMB556990198FD9F/Ticket-Open-List-Pane.jpg) }}" alt="Ticket-Open-List-Pane.jpg" width="700" height="200" /></p>
<p>Clicking an item that’s already open <em>and</em> is currently selected in the content pane closes it.</p>
<p>If you click an item that’s already open but <em>is not</em> the active tab selected in the content pane, i will become the active tab.</p>
<p>In {{ content_link(topic,226) }}, clicking an item from the list view opens it in the combined pane. You can go back to the list by clicking the following icon:</p>
<p><img src="{{ img(5571QJMKYRSMPN5570317659AB0/2-Column-icon.jpg) }}" alt="2-Column-icon.jpg" width="450" height="100" /></p>
<h1>Multiple selections and mass actions</h1>
<p>In the <strong>Tickets</strong>, <strong>Tasks</strong> and <strong>Publish</strong> apps, you can select multiple items from the list pane using the checkboxes at the left. This enables you to perform <strong>mass actions</strong> on up to 50 items at once. For example, if you need to delete a lot of items at the same time, you don’t have to open them one by one in the content pane.</p>
<p><img src="{{ img(883NYWGCSWSKY882358C27B0B/listpane-multipleselect.png) }}" alt="../_images/listpane-multipleselect.png" /></p>
<h1>Viewing a list as a table</h1>
<p>In the <strong>Tickets</strong> and <strong>CRM</strong> apps, you can view the items in the list pane as a table by clicking on this button:</p>
<p><img src="{{ img(5571YDAXKPPJHB5570092E912E4/List-View.jpg) }}" alt="List-View.jpg" /></p>
<p>This is useful to get an overview of a long list of items.</p>
<p><img src="{{ img(5571JXHNKMWDKX5570129335F75/Table-View.jpg) }}" alt="Table-View.jpg" width="700" height="500" /></p>
<p>When you’re in table view, you can export the items from the list to CSV format (accepted by spreadsheet programs) by clicking the 'Export to CSV' icon at the bottom left of the list pane.</p>
<p><img src="{{ img(5571JPYPYZPTMN5570167DA3B89/Export-as-CSV.jpg) }}" alt="Export-as-CSV.jpg" /></p>
<p>Note that the CSV exported will contain many fields about the tickets/users, not just the ones displayed in the table view.</p>
<h1>Resizing the list pane</h1>
<p>You can change the relative size of the list and content pane. Simply mouse over the top of the divider between them, click, and drag to resize.</p>
<p><img src="{{ img(5571KBQJCPHJTQ5570219D01470/Resize-List-Pane.jpg) }}" alt="Resize-List-Pane.jpg" width="300" height="100" /></p>
CONTENT,
                'content_input' => <<<'CONTENT'
The result of your selection in the filter pane is displayed in the list pane.

For example, if you click on a ticket filter, you will see the list of tickets that match the criteria of the filter in the list pane. You can think of it as being like a list of search results.

![List-Pane.jpg]({{ img(5570QRAKAPMZHJ5569532B9BFE4/List-Pane.jpg) }} =750x325)

In some apps, you can change the ordering of items in the list with controls at the top of the list pane.

![Ordered-by.jpg]({{ img(5570HNPCDDMJGD5569552B92BD7/Ordered-by.jpg) }} =300x425)

If you have more than 50 items in the list pane, the list will be paginated. Use the arrow controls at the bottom to move between the pages of results in the list pane.

![List-Pane-Pagination.jpg]({{ img(5570SKHBDAXMXX55696664FEEF1/List-Pane-Pagination.jpg) }} =600x125)

# Viewing properties

In the **Tickets**, **CRM** and **Feedback** apps, properties are shown for each item using gray text labels.

![Properties.jpg]({{ img(5570QDGHSQZRYR5569954B092AD/Properties.jpg) }} =450x100)

You can customize the properties which are displayed using the cog control at the top right of the list pane. Use this to display the properties that are most relevant to you. These changes will only apply for your agent account, and won't affect the display for any other agents in your helpdesk.

![Display-Options.jpg]({{ img(5571HYJHTYYJSP5570028A8BC58/Display-Options.jpg) }})

The display options you display can be different for each filter. For example, you could have the 'Agent' information appear in the properties for tickets in the All Tickets filter list, but not have that display for tickets in the My Tickets filter list (where the Agent would always be listed as you).

Simply click on the filter you want to edit the display options for and click on the cog icon. The filter you are editing will display at the top of the pop up window.

![Display-Options-Open.jpg]({{ img(5571GZAHKYJPMP55702293258F5/Display-Options-Open.jpg) }} =800x300)

# Opening items

You can **open** items from the list pane into the content pane to view them in more detail. You can have multiple items open at a time.

You open an item by clicking on it - it turns blue to show that it is open, and is displayed in a new tab in the content pane, in front of the tabs for any other items you have open.

![Ticket-Open-List-Pane.jpg]({{ img(5570KKWBGKWBMB556990198FD9F/Ticket-Open-List-Pane.jpg) }} =700x200)

Clicking an item that’s already open _and_ is currently selected in the content pane closes it.

If you click an item that’s already open but _is not_ the active tab selected in the content pane, i will become the active tab.

In {{ content_link(topic,226) }}, clicking an item from the list view opens it in the combined pane. You can go back to the list by clicking the following icon:

![2-Column-icon.jpg]({{ img(5571QJMKYRSMPN5570317659AB0/2-Column-icon.jpg) }} =450x100)

# Multiple selections and mass actions

In the **Tickets**, **Tasks** and **Publish** apps, you can select multiple items from the list pane using the checkboxes at the left. This enables you to perform **mass actions** on up to 50 items at once. For example, if you need to delete a lot of items at the same time, you don’t have to open them one by one in the content pane.

![../_images/listpane-multipleselect.png]({{ img(883NYWGCSWSKY882358C27B0B/listpane-multipleselect.png) }})

# Viewing a list as a table

In the **Tickets** and **CRM** apps, you can view the items in the list pane as a table by clicking on this button:

![List-View.jpg]({{ img(5571YDAXKPPJHB5570092E912E4/List-View.jpg) }})

This is useful to get an overview of a long list of items.

![Table-View.jpg]({{ img(5571JXHNKMWDKX5570129335F75/Table-View.jpg) }} =700x500)

When you’re in table view, you can export the items from the list to CSV format (accepted by spreadsheet programs) by clicking the 'Export to CSV' icon at the bottom left of the list pane.

![Export-as-CSV.jpg]({{ img(5571JPYPYZPTMN5570167DA3B89/Export-as-CSV.jpg) }})

Note that the CSV exported will contain many fields about the tickets/users, not just the ones displayed in the table view.

# Resizing the list pane

You can change the relative size of the list and content pane. Simply mouse over the top of the divider between them, click, and drag to resize.

![Resize-List-Pane.jpg]({{ img(5571KBQJCPHJTQ5570219D01470/Resize-List-Pane.jpg) }} =300x100)
CONTENT,
                'no_content'    => 0,
                'guide'         => $guide,
                'parent'        => $section1,
            ],
            [
                'title'   => 'Content pane',
                'content' => <<<'CONTENT'
<p>The content pane is where you work with and create individual items (tickets, user records, articles etc.).</p>
<p>Note that, no matter what app you have selected, a mixture of types of items can be open in the content pane. You can switch between them by clicking on the tabs.</p>
<p><img src="{{ img(883GRDBHNSPDB882381ECF955/contentpane-tabs-mixed.png) }}" alt="../_images/contentpane-tabs-mixed.png" /></p>
<p>If you have more tabs than can fit in your browser window, you can scroll with the arrows at the edge of the tab bar.</p>
<p><img src="{{ img(883ZMNDXXDRGB882382270E52/tabs-scroll.png) }}" alt="../_images/tabs-scroll.png" /></p>
<p>If the content of a tab changes while it’s not at the front, (for example, a new message is added to a chat conversation), its borders will flash.</p>
<p>You can close a tab using the small X button that appears when you mouse over it:</p>
<p><img src="{{ img(883MAPKGMCNTS8823831079CD/closing-tab.png) }}" alt="../_images/closing-tab.png" /></p>
<p>If you right-click on a tab (or anywhere on the tab bar), you are given more options:</p>
<p><img src="{{ img(883TPDRKCWMYX882384119E2A/contentp-tabs-rclick.png) }}" alt="../_images/contentp-tabs-rclick.png" /></p>
<p>As well as closing the tab, you can <strong>Close all tabs</strong> or <strong>Close all other tabs</strong> (apart from the one you right-clicked).</p>
<p>If you have recently closed tabs, the right-click menu will also offer you the option to reopen them:</p>
<p><img src="{{ img(883QQPSNAXMKJ8823852A3C9D/reopen-tabs.png) }}" alt="../_images/reopen-tabs.png" /></p>
<div class="block warning">
<p>Warning</p>
<p>Generally, the performance of the agent interface is not affected by the number of tabs you have open. The exception is that having many tabs with content from the <strong>Publish</strong> app (e.g. Knowledgebase articles) can cause an issue where  articles don’t fully load.</p>
</div>
<h1>Creating a new content item</h1>
<p>Mouse over the <strong>+ ADD</strong> button at the far left of the content pane, then click on the type of item you want to create.</p>
<p><img src="{{ img(5850JQXNBPDCGJ5849482B0E23D/Add-Content.jpg) }}" alt="Add-Content.jpg" width="200" height="330" /></p>
<p>Alternatively, you can make sure your cursor is not in a text input box and then use the following shortcut keys to do the same thing:</p>
<table>
<thead>
<tr>
<th>Shortcut</th>
<th>Command</th>
</tr>
</thead>
<tbody>
<tr>
<td>t</td>
<td>Create new ticket</td>
</tr>
<tr>
<td>p</td>
<td>Create new user (person)</td>
</tr>
<tr>
<td>o</td>
<td>Create new organization</td>
</tr>
<tr>
<td>k</td>
<td>Create new task</td>
</tr>
<tr>
<td>a</td>
<td>Create new article</td>
</tr>
<tr>
<td>i</td>
<td>Create new community topic</td>
</tr>
<tr>
<td>n</td>
<td>Create new news post</td>
</tr>
<tr>
<td>d</td>
<td>Create new download</td>
</tr>
</tbody>
</table>
<h1>Resizing the content pane</h1>
<p>You can change the relative size of the list and content panes. Simply mouse over the top of the divider between them, click, and drag to resize.</p>
<p><img src="{{ img(5578ZNSGTZHMPG5577849369A1A/Resize-List-Pane.jpg) }}" alt="Resize-List-Pane.jpg" width="300" height="100" /></p>
CONTENT,
                'content_input' => <<<'CONTENT'
The content pane is where you work with and create individual items (tickets, user records, articles etc.).

Note that, no matter what app you have selected, a mixture of types of items can be open in the content pane. You can switch between them by clicking on the tabs.

![../_images/contentpane-tabs-mixed.png]({{ img(883GRDBHNSPDB882381ECF955/contentpane-tabs-mixed.png) }})

If you have more tabs than can fit in your browser window, you can scroll with the arrows at the edge of the tab bar.

![../_images/tabs-scroll.png]({{ img(883ZMNDXXDRGB882382270E52/tabs-scroll.png) }})

If the content of a tab changes while it’s not at the front, (for example, a new message is added to a chat conversation), its borders will flash.

You can close a tab using the small X button that appears when you mouse over it:

![../_images/closing-tab.png]({{ img(883MAPKGMCNTS8823831079CD/closing-tab.png) }})

If you right-click on a tab (or anywhere on the tab bar), you are given more options:

![../_images/contentp-tabs-rclick.png]({{ img(883TPDRKCWMYX882384119E2A/contentp-tabs-rclick.png) }})

As well as closing the tab, you can **Close all tabs** or **Close all other tabs** (apart from the one you right-clicked).

If you have recently closed tabs, the right-click menu will also offer you the option to reopen them:

![../_images/reopen-tabs.png]({{ img(883QQPSNAXMKJ8823852A3C9D/reopen-tabs.png) }})

::: warning
Warning

Generally, the performance of the agent interface is not affected by the number of tabs you have open. The exception is that having many tabs with content from the **Publish** app (e.g. Knowledgebase articles) can cause an issue where  articles don’t fully load.

:::



# Creating a new content item

Mouse over the **+ ADD** button at the far left of the content pane, then click on the type of item you want to create.

![Add-Content.jpg]({{ img(5850JQXNBPDCGJ5849482B0E23D/Add-Content.jpg) }} =200x330)

Alternatively, you can make sure your cursor is not in a text input box and then use the following shortcut keys to do the same thing:

| Shortcut | Command |
| --- | --- |
| t | Create new ticket |
| p | Create new user (person) |
| o | Create new organization |
| k | Create new task |
| a | Create new article |
| i | Create new community topic |
| n | Create new news post |
| d | Create new download |

# Resizing the content pane

You can change the relative size of the list and content panes. Simply mouse over the top of the divider between them, click, and drag to resize.

![Resize-List-Pane.jpg]({{ img(5578ZNSGTZHMPG5577849369A1A/Resize-List-Pane.jpg) }} =300x100)
CONTENT,
                'no_content'    => 0,
                'guide'         => $guide,
                'parent'        => $section1,
            ],
            [
                'title'   => '2-column view',
                'content' => <<<'CONTENT'
<p>The default <strong>3-column view</strong> shows both the filters pane, the list pane and the content pane.</p>
<p>There is an alternative <strong>2-column view</strong> where the list and content panes are merged into a single <strong>combined pane</strong> which displays <em>either</em> an individual item <em>or</em> the list of items matching the current filter.</p>
<p>This view is useful in several situations:</p>
<ul>
<li>You want to concentrate on the details of an individual item, without being distracted by what’s going on in the list pane.</li>
<li>You have a relatively narrow display and the default view makes columns too cramped.</li>
<li>Your helpdesk has an app installed that adds an extra pane on the right of the interface (e.g. JIRA integration).</li>
</ul>
<p>Use the control at the top right of the toolbar to switch to <strong>2-column view</strong>. (It is also possible to collapse the filter pane to create a 1-column view).</p>
<p><img src="{{ img(1245PKMWDCWAHX12440978E7A0A/2-panel-view.png) }}" alt="2-panel-view.png" /></p>
<p>This is an individual item shown in 2-column view:</p>
<p><img src="{{ img(1245NXTBDJXSQC124409800232B/Single-Ticket.png) }}" alt="Single-Ticket.png" /></p>
<p>To view the list of items, click the hamburger icon at the top left of the combined pane.</p>
<p><img src="{{ img(5578MNZXBAHDHK557789283C004/image.png) }}" alt="image.png" /></p>
<p>The view will switch to display the list pane.</p>
<p><img src="{{ img(1245DGMRZZTRJB124409955110E/List-Panel.png) }}" alt="List-Panel.png" /></p>
<p>Clicking an item from this list opens it in a new tab and focuses it (or just focuses it, if it’s already open).</p>
<p>If you want to open items as separate tabs without focusing them, hold down <strong>Shift</strong> and click on them. (This is not currently supported for all types of content.)</p>
<p>The control to the right of the icon displays the items in the list as a pull-down - you can use this even when you have an individual item displayed in the combined pane.</p>
<p><img src="{{ img(1245HKRHQBYGSN1244100C0C716/List-drop-down.png) }}" alt="List-drop-down.png" /></p>
<p>To open an individual item from the list (<em>replacing</em> the current focused item), click its title.</p>
<p>To open an individual item from the list in a new tab, click the <img src="{{ img(883BXMPATWSGH88239423176E/open-item-newtab-icon.png) }}" alt="image5" /> icon (or hold down <strong>Shift</strong> and click on it).</p>
<p>Items that are already open are shown in gray on the list. Clicking them focuses their tab.</p>
<p><img src="{{ img(883XJZXRGHCJP882395FE1BD5/1-col-pulldown-open.png) }}" alt="../_images/1-col-pulldown-open.png" /></p>
CONTENT,
                'content_input' => <<<'CONTENT'
The default **3-column view** shows both the filters pane, the list pane and the content pane.

There is an alternative **2-column view** where the list and content panes are merged into a single **combined pane** which displays _either_ an individual item _or_ the list of items matching the current filter.

This view is useful in several situations:

*   You want to concentrate on the details of an individual item, without being distracted by what’s going on in the list pane.
*   You have a relatively narrow display and the default view makes columns too cramped.
*   Your helpdesk has an app installed that adds an extra pane on the right of the interface (e.g. JIRA integration).

Use the control at the top right of the toolbar to switch to **2-column view**. (It is also possible to collapse the filter pane to create a 1-column view).

![2-panel-view.png]({{ img(1245PKMWDCWAHX12440978E7A0A/2-panel-view.png) }})

This is an individual item shown in 2-column view:

![Single-Ticket.png]({{ img(1245NXTBDJXSQC124409800232B/Single-Ticket.png) }})

To view the list of items, click the hamburger icon at the top left of the combined pane.

![image.png]({{ img(5578MNZXBAHDHK557789283C004/image.png) }})

The view will switch to display the list pane.

![List-Panel.png]({{ img(1245DGMRZZTRJB124409955110E/List-Panel.png) }})

Clicking an item from this list opens it in a new tab and focuses it (or just focuses it, if it’s already open).

If you want to open items as separate tabs without focusing them, hold down **Shift** and click on them. (This is not currently supported for all types of content.)

The control to the right of the icon displays the items in the list as a pull-down - you can use this even when you have an individual item displayed in the combined pane.

![List-drop-down.png]({{ img(1245HKRHQBYGSN1244100C0C716/List-drop-down.png) }})

To open an individual item from the list (_replacing_ the current focused item), click its title.

To open an individual item from the list in a new tab, click the ![image5]({{ img(883BXMPATWSGH88239423176E/open-item-newtab-icon.png) }}) icon (or hold down **Shift** and click on it).

Items that are already open are shown in gray on the list. Clicking them focuses their tab.

![../_images/1-col-pulldown-open.png]({{ img(883XJZXRGHCJP882395FE1BD5/1-col-pulldown-open.png) }})
CONTENT,
                'no_content'    => 0,
                'guide'         => $guide,
                'parent'        => $section1,
            ],
            [
                'title'   => 'Account preferences',
                'content' => <<<'CONTENT'
<p>You can edit your account preferences from the <strong>Preferences</strong> link within the profile drop-down menu at the top-right of the agent interface header.</p>
<p><img src="{{ img(5578WKQBKQCMNP5577996642595/Account-Preferences.jpg) }}" alt="Account-Preferences.jpg" width="250" height="300" /></p>
<p>A pop up will appear displaying your profile settings:</p>
<p><img src="{{ img(1245YXWNTTQAWM1244101D7ABDA/Profile-pic.png) }}" alt="Profile-pic.png" /></p>
<h1>Account profile</h1>
<p>In the account profile, you can manage your account and set the following options:</p>
<ul>
<li><strong>Name</strong> - this is displayed to your fellow agents in the agent interface; by default it will be used in email messages to users and on the web portal.</li>
<li><strong>Override Name</strong> - a name that is displayed to users instead of the name your fellow agents see. If you enter an override name, it will be used instead of the <strong>Name</strong> field in all user-facing communications (both in email and on the portal).</li>
<li><strong>Email</strong> - your main email address and any additional email addresses you want to use.</li>
<li><strong>Phone Number</strong> - if your helpdesk is configured to send SMS text notifications, you must enter your cellular/mobile phone number here to receive them</li>
<li><strong>Change Password</strong> - you can use this to change your account password</li>
<li><strong>Timezone</strong> - specify what timezone your helpdesk is in to ensure times are displayed correctly. Users can have their own timezone settings, so you should set this to the timezone where you actually work. Daylight Savings/Summer Time is handled automatically.</li>
<li><strong>Language</strong> - set your preferred language. For some languages, this will change the language of the agent interface, provided your admins have installed the correct language pack.</li>
<li><strong>Picture</strong> - depending on your permissions, you may be able to upload a picture to represent you. This will be displayed both in the agent/admin interface and on the user portal. You can change your picture by uploading a new one at any time.</li>
<li><strong>Desktop Notifications</strong> - with some browsers, you can receive pop-up notifications on your computer desktop about helpdesk events (such as when a ticket is assigned to you). See the section on {{ content_link(topic,227) }} below for more details.</li>
<li><strong>Tickets</strong> - you can change the behavior of the agent interface for replying to tickets here. The options are:
<ul>
<li><strong>Automatically close ticket tabs</strong> when replying/adding a note - this closes the ticket as soon as you reply to it or write a <strong>note</strong> (see {{ content_link(topic,235) }}) to fellow agents.</li>
<li><strong>Show newest messages first</strong> - by default, ticket messages are shown with newer messages at the top of the list; deselect this to reverse the order.</li>
<li><strong>Automatically load the next ticket in the list after replying</strong> means that if you reply to a ticket and <strong>Automatically close ticket tabs</strong> is enabled, the next ticket in the list pane will automatically open; enabling this is useful if you often have to work through a list of tickets in order.</li>
</ul>
</li>
<li><strong>Chat</strong> - you can choose to immediately dismiss a notification that a user has requested a {{ content_link(topic,447) }} session when another agent answers</li>
<li><strong>Default team</strong> - {{ content_link(topic,342) }} are a way to assign tickets to groups of agents. When you start to automate the helpdesk, some automatic processes will assign a ticket to your default team. If you are a member of more than one team, you should set this to be your main or most important team.</li>
<li><strong>API Token</strong> reset - this is for integration between Deskpro and external apps and services; use this option if your admins tell you to.</li>
<li><strong>Setup device token</strong> - you can use this QR code to log into the helpdesk.</li>
</ul>
<h1>Signature</h1>
<p>You can create a rich text signature that’s automatically displayed at the bottom of your messages when you reply to a ticket. You should include a sign off and your name so that you don’t have to type them every time you reply to a user.</p>
<p>If you have enabled an <strong>override name</strong>, remember to use the same name in your signature.</p>
<p><img src="{{ img(883MWHRSWMBTT8824112230CB/signature-editor.png) }}" alt="../_images/signature-editor.png" /></p>
<p>The <img src="{{ img(883TCJQANNPZZ882412FD8B53/img-icon.png) }}" alt="image6" /> button enables you to insert an image into the body of the signature. You can either <strong>Upload</strong> an image file from your desktop, or select <strong>Link</strong> to provide the web address of an image file. If you use a web address, make sure it’s on a site your organization controls.</p>
<p><img src="{{ img(883SYSSZNDADQ882413844BE7/img-upload-sig.png) }}" alt="../_images/img-upload-sig.png" /></p>
<p>The <img src="{{ img(883RZACSQCCQX8824146E89F8/link-icon.png) }}" alt="link icon" /> button enables you to insert a clickable web link or email address into your signature. See the section {{ content_link(topic,235) }} for details.</p>
<h1>Notification preferences</h1>
<p>These settings control what notifications you get about helpdesk events. Your admins may not have given you permission to change your notifications.</p>
<p><img src="{{ img(883ZBBDZRYASC882415F0AE6C/notification-prefs-annotated.png) }}" alt="../_images/notification-prefs-annotated.png" /></p>
<p>There are two main kinds of notifications that Deskpro can send: <strong>email notifications</strong> (which are simply emails sent to your Deskpro account address) and <strong>browser notifications</strong>.</p>
<p>Browser notifications are shown in the notification tray to the right of the toolbar. The tab icon in your browser will also display the number of notifications and will flash when you have a new notification. You’ll only see browser notifications if you’re logged in to the agent interface.</p>
<p><img src="{{ img(1245XCZQYJCHAX12441629B5E71/Notifications.png) }}" alt="Notifications.png" /></p>
<p>If you’ve enabled <strong>desktop</strong> notifications, when you receive a browser notification you will also get a pop-up alert on your computer desktop. The exact appearance will vary depending on your operating system.</p>
<p><img src="{{ img(1245YRPPKYCAZY1244161075397/Browser-Notification.png) }}" alt="Browser-Notification.png" /></p>
<p>There are two notifications tabs:</p>
<ul>
<li><strong>Ticket Notifications</strong> - set email and browser notifications about ticket events and @mentions (see {{ content_link(topic,235) }} details about @mentions). Note that you can set different notification options for tickets that match certain filters.</li>
<li><strong>Notifications</strong> - set what notifications you want to receive about non-ticket events: chats, tasks, feedback, publish content (new feedback and comments), CRM (new user registration). You can also opt to receive an email every time your account is logged into or there’s a failed login attempt.</li>
</ul>
<h1>Macro preferences</h1>
<p>From here you can create {{ content_link(topic,290) }} to automatically perform sequences of actions. You can either create personal macros that only you can use, or shared macros that everyone can access. If other agents have to do the same task that you’ve written a macro for, consider making it shared, to save them having to come up with their own version.</p>
<p><img src="{{ img(883KGSMNCYJCQ8824189E5394/macros-prefs.png) }}" alt="../_images/macros-prefs.png" /></p>
<p>Your <strong>Admins</strong> can see everyone’s macros, so contact them if you need help making a macro work.</p>
<div class="block info">
<p>Note that you can’t run a macro if you don’t have permissions to do all the actions it includes. If you run a macro and don’t have the right permissions, you will receive a pop-up warning. Speak to your <strong>Admin</strong> to resolve this issue.</p>
</div>
<h1>Filter preferences</h1>
<p>This shows your {{ content_link(topic,461) }}. You can create filters here or from the filter pane of the <strong>Tickets</strong> app.</p>
<p><img src="{{ img(883ASDHDNQMRS8824196E592C/filters-prefs.png) }}" alt="../_images/filters-prefs.png" /></p>
<p>You can hide custom filters from here if you don’t want to see them in the filter pane.</p>
<h1>SLA preferences</h1>
<p>This option lets you control which {{ content_link(topic,376) }} (<em>Service Level Agreements</em>) you see in the Ticket app. If there are SLAs which are not relevant to you, you can choose not to see when tickets are warning or failing because of them.</p>
<p><img src="{{ img(883ZWHJPPZZGB8824204F0629/sla-prefs.png) }}" alt="../_images/sla-prefs.png" /></p>
<p>You can hide SLAs just as you can with filters. You can also choose whether to show all the tickets for a certain SLA, or just those that are assigned to you or to your one of your teams.</p>
CONTENT,
                'content_input' => <<<'CONTENT'
You can edit your account preferences from the **Preferences** link within the profile drop-down menu at the top-right of the agent interface header.

![Account-Preferences.jpg]({{ img(5578WKQBKQCMNP5577996642595/Account-Preferences.jpg) }} =250x300)

A pop up will appear displaying your profile settings:

![Profile-pic.png]({{ img(1245YXWNTTQAWM1244101D7ABDA/Profile-pic.png) }})

# Account profile

In the account profile, you can manage your account and set the following options:

*   **Name** - this is displayed to your fellow agents in the agent interface; by default it will be used in email messages to users and on the web portal.
*   **Override Name** - a name that is displayed to users instead of the name your fellow agents see. If you enter an override name, it will be used instead of the **Name** field in all user-facing communications (both in email and on the portal).
*   **Email** - your main email address and any additional email addresses you want to use.
*   **Phone Number** - if your helpdesk is configured to send SMS text notifications, you must enter your cellular/mobile phone number here to receive them
*   **Change Password** - you can use this to change your account password
*   **Timezone** - specify what timezone your helpdesk is in to ensure times are displayed correctly. Users can have their own timezone settings, so you should set this to the timezone where you actually work. Daylight Savings/Summer Time is handled automatically.
*   **Language** - set your preferred language. For some languages, this will change the language of the agent interface, provided your admins have installed the correct language pack.
*   **Picture** - depending on your permissions, you may be able to upload a picture to represent you. This will be displayed both in the agent/admin interface and on the user portal. You can change your picture by uploading a new one at any time.
*   **Desktop Notifications** - with some browsers, you can receive pop-up notifications on your computer desktop about helpdesk events (such as when a ticket is assigned to you). See the section on {{ content_link(topic,227) }} below for more details.
*   **Tickets** - you can change the behavior of the agent interface for replying to tickets here. The options are:
    *   **Automatically close ticket tabs** when replying/adding a note - this closes the ticket as soon as you reply to it or write a **note** (see {{ content_link(topic,235) }}) to fellow agents.
    *   **Show newest messages first** - by default, ticket messages are shown with newer messages at the top of the list; deselect this to reverse the order.
    *   **Automatically load the next ticket in the list after replying** means that if you reply to a ticket and **Automatically close ticket tabs** is enabled, the next ticket in the list pane will automatically open; enabling this is useful if you often have to work through a list of tickets in order.
*   **Chat** - you can choose to immediately dismiss a notification that a user has requested a {{ content_link(topic,447) }} session when another agent answers
*   **Default team** - {{ content_link(topic,342) }} are a way to assign tickets to groups of agents. When you start to automate the helpdesk, some automatic processes will assign a ticket to your default team. If you are a member of more than one team, you should set this to be your main or most important team.
*   **API Token** reset - this is for integration between Deskpro and external apps and services; use this option if your admins tell you to.
*   **Setup device token** - you can use this QR code to log into the helpdesk.

# Signature

You can create a rich text signature that’s automatically displayed at the bottom of your messages when you reply to a ticket. You should include a sign off and your name so that you don’t have to type them every time you reply to a user.

If you have enabled an **override name**, remember to use the same name in your signature.

![../_images/signature-editor.png]({{ img(883MWHRSWMBTT8824112230CB/signature-editor.png) }})

The ![image6]({{ img(883TCJQANNPZZ882412FD8B53/img-icon.png) }}) button enables you to insert an image into the body of the signature. You can either **Upload** an image file from your desktop, or select **Link** to provide the web address of an image file. If you use a web address, make sure it’s on a site your organization controls.

![../_images/img-upload-sig.png]({{ img(883SYSSZNDADQ882413844BE7/img-upload-sig.png) }})

The ![link icon]({{ img(883RZACSQCCQX8824146E89F8/link-icon.png) }}) button enables you to insert a clickable web link or email address into your signature. See the section {{ content_link(topic,235) }} for details.

# Notification preferences

These settings control what notifications you get about helpdesk events. Your admins may not have given you permission to change your notifications.

![../_images/notification-prefs-annotated.png]({{ img(883ZBBDZRYASC882415F0AE6C/notification-prefs-annotated.png) }})

There are two main kinds of notifications that Deskpro can send: **email notifications** (which are simply emails sent to your Deskpro account address) and **browser notifications**.

Browser notifications are shown in the notification tray to the right of the toolbar. The tab icon in your browser will also display the number of notifications and will flash when you have a new notification. You’ll only see browser notifications if you’re logged in to the agent interface.

![Notifications.png]({{ img(1245XCZQYJCHAX12441629B5E71/Notifications.png) }})

If you’ve enabled **desktop** notifications, when you receive a browser notification you will also get a pop-up alert on your computer desktop. The exact appearance will vary depending on your operating system.

![Browser-Notification.png]({{ img(1245YRPPKYCAZY1244161075397/Browser-Notification.png) }})

There are two notifications tabs:

*   **Ticket Notifications** - set email and browser notifications about ticket events and @mentions (see {{ content_link(topic,235) }} details about @mentions). Note that you can set different notification options for tickets that match certain filters.
*   **Notifications** - set what notifications you want to receive about non-ticket events: chats, tasks, feedback, publish content (new feedback and comments), CRM (new user registration). You can also opt to receive an email every time your account is logged into or there’s a failed login attempt.

# Macro preferences

From here you can create {{ content_link(topic,290) }} to automatically perform sequences of actions. You can either create personal macros that only you can use, or shared macros that everyone can access. If other agents have to do the same task that you’ve written a macro for, consider making it shared, to save them having to come up with their own version.

![../_images/macros-prefs.png]({{ img(883KGSMNCYJCQ8824189E5394/macros-prefs.png) }})

Your **Admins** can see everyone’s macros, so contact them if you need help making a macro work.

::: info
Note that you can’t run a macro if you don’t have permissions to do all the actions it includes. If you run a macro and don’t have the right permissions, you will receive a pop-up warning. Speak to your **Admin** to resolve this issue.
:::

# Filter preferences

This shows your {{ content_link(topic,461) }}. You can create filters here or from the filter pane of the **Tickets** app.

![../_images/filters-prefs.png]({{ img(883ASDHDNQMRS8824196E592C/filters-prefs.png) }})

You can hide custom filters from here if you don’t want to see them in the filter pane.

# SLA preferences

This option lets you control which {{ content_link(topic,376) }} (_Service Level Agreements_) you see in the Ticket app. If there are SLAs which are not relevant to you, you can choose not to see when tickets are warning or failing because of them.

![../_images/sla-prefs.png]({{ img(883ZWHJPPZZGB8824204F0629/sla-prefs.png) }})

You can hide SLAs just as you can with filters. You can also choose whether to show all the tickets for a certain SLA, or just those that are assigned to you or to your one of your teams.
CONTENT,
                'no_content'    => 0,
                'guide'         => $guide,
                'parent'        => $section1,
            ],
            [
                'title'            => 'Elements of a ticket',
                'content'          => '',
                'content_input'    => '',
                'no_content'       => 1,
                'guide'            => $guide,
                'parent'           => $section2,
            ],
        ];
        $objects = [];
        foreach ($topics as $t) {
            $topic = new Topic();
            $topic->setTitle($t['title']);
            $topic->setGuide($t['guide']);
            $topic->setParent($t['parent']);
            $topic->setNoContent(!!$t['no_content']);
            $topic->setContent($t['content']);
            $topic->setContentInput($t['content_input']);
            $topic->setStatus(ContentAbstract::STATUS_PUBLISHED);
            $this->manager->persist($topic);
            array_push($objects, $topic);
        }
        $more = new Topic();
        $more->setTitle('Ticket Properties');
        $more->setGuide($guide);
        $more->setParent($objects[count($objects) - 1]);
        $more->setNoContent(false);
        $more->setContent(<<<'CONTENT'
<p>When you open an individual ticket in the content pane, you can see the information Deskpro stores about it.</p>
<p><img src="{{ img(5554TZCXPHZWKX5553834F6F496/image.png) }}" alt="image.png"></p>
<p>The fields you see will vary:</p>
<ul>
<li>Some built-in {{ content_link(topic,233) }} are optional.</li>
<li>Your admins can add {{ content_link(topic,369) }}.</li>
<li>The required fields can be different for tickets in different&nbsp;{{ content_link(topic,232) }}</li>
</ul>
<p>Clicking on any ticket property will allow you to edit the entry, including custom ticket fields.</p>
<h1>IDs and ref codes</h1>
<p>Every ticket has a unique&nbsp;<strong>ID</strong>&nbsp;number. These are assigned in the order tickets are created.</p>
<p><img src="{{ img(1245CNKRQZBJTP1244163373B15/ticket-numbers.png) }}" alt="ticket-numbers.png"></p>
<p>You may notice that other items in Deskpro have their own ID numbers. For example, users and knowledgebase articles have IDs. ID numbers for different items are separate - for example, you can have a ticket with ID 17 and a user with ID 17.</p>
<p>Your admins may have set your helpdesk so that users see a unique&nbsp;<strong>ref code</strong>&nbsp;instead of the ID. The ref code is usually a string of numbers and letters (e.g.&nbsp;<code class="hljs">WHPL-4979-OBBV</code>). Your admins may have customized the format. Click on a ticket’s ID to toggle to displaying the ref code instead.</p>
<p>The ref code format can be customized to include information about the date and time that the ticket was created, e.g.&nbsp;<code class="hljs">2014-07-WHPL-4979</code>.</p>
<p>Another advantage of ref codes is that they make it harder for your users and competitors to work out how many (or how few) tickets you have.</p>
<div class="block info">
<p>Note</p>
<p>Put your mouse cursor over an ID or ref code and a yellow clipboard icon appears. Click the icon. The ID or ref is now copied to your clipboard, for easy pasting.</p>
<p><img src="{{ img(883TXBKZRXWYW882431387798/tickets-copy-id.png) }}" alt="../_images/tickets-copy-id.png"></p>
</div>
<p>You can find a particular ticket quickly by putting its ID or ref code into the search bar. Emails Deskpro sends regarding tickets will include either an ID or ref code for the relevant ticket. You can access the ref code of a ticket by clicking on the ID number of the ticket, which then reveals the ref code.</p>
<p><img src="{{ img(1245WWNNJCPDAW1244164B1A79D/Ticket-ID.png) }}" alt="Ticket-ID.png"></p>
CONTENT
);
        $more->setContentInput(<<<'CONTENT'
When you open an individual ticket in the content pane, you can see the information Deskpro stores about it.

![image.png]({{ img(5554TZCXPHZWKX5553834F6F496/image.png) }})

The fields you see will vary:

*   Some built-in {{ content_link(topic,233) }} are optional.
*   Your admins can add {{ content_link(topic,369) }}.
*   The required fields can be different for tickets in different {{ content_link(topic,232) }}

Clicking on any ticket property will allow you to edit the entry, including custom ticket fields.

# IDs and ref codes

Every ticket has a unique **ID** number. These are assigned in the order tickets are created.

![ticket-numbers.png]({{ img(1245CNKRQZBJTP1244163373B15/ticket-numbers.png) }})

You may notice that other items in Deskpro have their own ID numbers. For example, users and knowledgebase articles have IDs. ID numbers for different items are separate - for example, you can have a ticket with ID 17 and a user with ID 17.

Your admins may have set your helpdesk so that users see a unique **ref code** instead of the ID. The ref code is usually a string of numbers and letters (e.g. `WHPL-4979-OBBV`). Your admins may have customized the format. Click on a ticket’s ID to toggle to displaying the ref code instead.

The ref code format can be customized to include information about the date and time that the ticket was created, e.g. `2014-07-WHPL-4979`.

Another advantage of ref codes is that they make it harder for your users and competitors to work out how many (or how few) tickets you have.

::: info
Note

Put your mouse cursor over an ID or ref code and a yellow clipboard icon appears. Click the icon. The ID or ref is now copied to your clipboard, for easy pasting.

![../_images/tickets-copy-id.png]({{ img(883TXBKZRXWYW882431387798/tickets-copy-id.png) }})
:::

You can find a particular ticket quickly by putting its ID or ref code into the search bar. Emails Deskpro sends regarding tickets will include either an ID or ref code for the relevant ticket. You can access the ref code of a ticket by clicking on the ID number of the ticket, which then reveals the ref code.

![Ticket-ID.png]({{ img(1245WWNNJCPDAW1244164B1A79D/Ticket-ID.png) }})
CONTENT
);
        $more->setStatus(ContentAbstract::STATUS_PUBLISHED);
        $this->manager->persist($more);
        $this->manager->flush();
    }
}
