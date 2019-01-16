<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Portal;

use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Publish\Structure as PublishStructure;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;

class SitemapGenerator
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Application\DeskPRO\Publish\Structure
     */
    protected $structure;

    /**
     * @var array
     */
    protected $items = null;

    public function __construct(EntityManager $em, RouterInterface $router)
    {
        $this->em     = $em;
        $this->db     = $em->getConnection();
        $this->router = $router;

        $person          = new PersonGuest();
        $this->structure = new PublishStructure(
            $person,
            $this->em,
            new \Doctrine\Common\Cache\ArrayCache()
        );
    }

    /**
     * Get sitemap.xml.
     *
     * @return string
     */
    public function getXml()
    {
        $xml   = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $attributes = ['loc', 'changefreq', 'lastmod', 'priority'];

        foreach ($this->getItems() as $item) {
            $xml[] = '<url>';
            foreach ($attributes as $attr) {
                if (!empty($item[$attr])) {
                    $val   = $item[$attr];
                    $xml[] = "\t<$attr>$val</$attr>";
                }
            }
            $xml[] = '</url>';
        }

        $xml[] = '</urlset>';
        $xml[] = '';

        $xml = implode("\n", $xml);

        return $xml;
    }

    /**
     * Get items.
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $this->items = array_merge(
            $this->getSiteItems(),
            $this->getArticleItems(),
            $this->getFeedbackItems(),
            $this->getDownloadItems(),
            $this->getNewsItems()
        );

        return $this->items;
    }

    /**
     * @return array
     */
    protected function getSiteItems()
    {
        $items   = [];
        $items[] = [
            'loc'        => $this->router->generate('portal_home', [], RouterInterface::ABSOLUTE_URL),
            'changefreq' => 'daily',
        ];

        $items[] = [
            'loc'        => $this->router->generate('portal_new_ticket', [], RouterInterface::ABSOLUTE_URL),
            'changefreq' => 'monthly',
        ];

        $items[] = [
            'loc'        => $this->router->generate('portal_feedback', [], RouterInterface::ABSOLUTE_URL),
            'changefreq' => 'daily',
        ];

        return $items;
    }

    /**
     * @return array
     */
    protected function getArticleItems()
    {
        $cat_ids = $this->structure->getArticleCategoryIds();
        if (!$cat_ids) {
            return [];
        }

        $items = [];

        $items[] = [
            'loc'        => $this->router->generate('portal_kb', [], RouterInterface::ABSOLUTE_URL),
            'changefreq' => 'daily',
        ];

        //------------------------------
        // Categories
        //------------------------------

        $cats = $this->structure->getArticleCategories();

        foreach ($cats as $cat) {
            if ($cat->getSlug()) {
                $items[] = [
                    'loc'        => $this->router->generate('portal_kb_browse', ['slug' => $cat->getSlug()], RouterInterface::ABSOLUTE_URL),
                    'changefreq' => 'daily',
                ];
            }
        }

        //------------------------------
        // Articles
        //------------------------------

        $articles = $this->em->createQuery(
            "
            SELECT PARTIAL art.{id,slug,title}
            FROM DeskPRO:Article art
            LEFT JOIN art.categories cat
            WHERE art.status = 'published' AND cat.id IN (?0)
        "
        )->execute([$cat_ids]);

        foreach ($articles as $a) {
            if ($a->getSlug()) {
                $items[] = [
                    'loc'        => $this->router->generate('portal_kb_view', ['slug' => $a->getSlug()], RouterInterface::ABSOLUTE_URL),
                    'changefreq' => 'weekly',
                ];
            }
        }

        return $items;
    }

    /**
     * @return array
     */
    protected function getNewsItems()
    {
        $cat_ids = $this->structure->getNewsCategoryIds();
        if (!$cat_ids) {
            return [];
        }

        $items = [];

        $items[] = [
            'loc'        => $this->router->generate('portal_news', [], RouterInterface::ABSOLUTE_URL),
            'changefreq' => 'daily',
        ];

        //------------------------------
        // Categories
        //------------------------------

        $cats = $this->structure->getNewsCategories();

        foreach ($cats as $cat) {
            if ($cat->getSlug()) {
                $items[] = [
                    'loc'        => $this->router->generate('portal_news_browse', ['slug' => $cat->getSlug()], RouterInterface::ABSOLUTE_URL),
                    'changefreq' => 'daily',
                ];
            }
        }

        //------------------------------
        // News
        //------------------------------

        if ($cat_ids) {
            $news = $this->em->createQuery(
                "
                SELECT PARTIAL news.{id,slug,title}
                FROM DeskPRO:News news
                WHERE news.status = 'published' AND news.category IN (?0)
            "
            )->execute([$cat_ids]);

            foreach ($news as $n) {
                if ($n->getSlug()) {
                    $items[] = [
                        'loc'        => $this->router->generate('portal_news_view', ['slug' => $n->getSlug()], RouterInterface::ABSOLUTE_URL),
                        'changefreq' => 'weekly',
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * @return array
     */
    protected function getDownloadItems()
    {
        $cat_ids = $this->structure->getDownloadCategoryIds();
        if (!$cat_ids) {
            return [];
        }

        $items = [];

        $items[] = [
            'loc'        => $this->router->generate('portal_downloads', [], RouterInterface::ABSOLUTE_URL),
            'changefreq' => 'daily',
        ];

        //------------------------------
        // Categories
        //------------------------------

        $cats = $this->structure->getDownloadCategories();

        foreach ($cats as $cat) {
            if ($cat->getSlug()) {
                $items[] = [
                    'loc'        => $this->router->generate('portal_downloads_browse', ['slug' => $cat->getSlug()], RouterInterface::ABSOLUTE_URL),
                    'changefreq' => 'daily',
                ];
            }
        }

        //------------------------------
        // Downloads
        //------------------------------

        $downloads = $this->em->createQuery(
            "
            SELECT PARTIAL download.{id,slug,title}
            FROM DeskPRO:Download download
            WHERE download.status = 'published' AND download.category IN (?0)
        "
        )->execute([$cat_ids]);

        foreach ($downloads as $d) {
            if ($d->getSlug()) {
                $items[] = [
                    'loc'        => $this->router->generate('portal_downloads_view', ['slug' => $d->getSlug()], RouterInterface::ABSOLUTE_URL),
                    'changefreq' => 'weekly',
                ];
            }
        }

        return $items;
    }

    /**
     * @return array
     */
    protected function getFeedbackItems()
    {
        $cat_ids = $this->structure->getFeedbackCategoryIds();
        if (!$cat_ids) {
            return [];
        }

        $items = [];

        $items[] = [
            'loc'        => $this->router->generate('portal_feedback', [], RouterInterface::ABSOLUTE_URL),
            'changefreq' => 'daily',
        ];

        //------------------------------
        // Downloads
        //------------------------------

        $feedback = $this->em->createQuery(
            '
            SELECT PARTIAL feedback.{id,slug,title}
            FROM DeskPRO:Feedback feedback
            WHERE feedback.hidden_status IS NULL AND feedback.category IN (?0)
        '
        )->execute([$cat_ids]);

        foreach ($feedback as $f) {
            if ($f->getSlug()) {
                $items[] = [
                    'loc'        => $this->router->generate('portal_feedback_view', ['slug' => $f->getSlug()], RouterInterface::ABSOLUTE_URL),
                    'changefreq' => 'weekly',
                ];
            }
        }

        return $items;
    }
}
