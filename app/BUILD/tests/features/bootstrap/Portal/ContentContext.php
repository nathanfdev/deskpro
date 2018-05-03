<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;

/**
 * Class ContentContext.
 */
class ContentContext extends BasePortalContext
{
    /**
     * @Given there are no articles in the Knowledge Base
     */
    public function noArticlesInKb()
    {
        $articles = $this->repository(Article::class)->findAll();
        foreach ($articles as $article) {
            $this->em()->remove($article);
        }
        $this->em()->flush();
    }

    /**
     * @Given I have ":title" article
     * @Given I add ":title" article
     */
    public function haveAnArticle($title)
    {
        $article = $this->repository(Article::class)->findOneBy(['title' => $title]);
        if ($article) {
            return;
        }
        $article = new Article();
        /** @var \DpTestSrc\TestBundle\UserDetailsRepo $user_details */
        $user_details = $this->get('user_details');
        $person       = $user_details->getWho('agent');
        $article->setPerson($person);
        $article->setTitle($title);
        $article->setCategories(
            $this->repository(ArticleCategory::class)->findAll()
        );
        $article->setStatus(Article::STATUS_PUBLISHED);

        $this->persistAndFlush($article);
    }

    /**
     * @Given I have ":title" news
     */
    public function haveANews($title)
    {
        $news = $this->repository(News::class)->findOneBy(['title' => $title]);
        if ($news) {
            return;
        }
        $news = new News();

        /** @var \DpTestSrc\TestBundle\UserDetailsRepo $user_details */
        $user_details = $this->get('user_details');
        $person       = $user_details->getWho('agent');
        $news
            ->setPerson($person);
        $news->setTitle($title);
        $news->setCategory(
            $this->repository(NewsCategory::class)->findOneBy(['slug' => 'general'])
        );
        $news->setStatus(News::STATUS_PUBLISHED);
        $this->persistAndFlush($news);
    }
}
