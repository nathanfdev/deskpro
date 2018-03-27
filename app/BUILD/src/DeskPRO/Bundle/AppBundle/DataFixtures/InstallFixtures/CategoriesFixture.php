<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CategoriesFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $translate = $this->container->get('deskpro.core.translate');

        //------------------------------
        // General KB cat : article_category_general
        //------------------------------

        $cat = new ArticleCategory();
        $cat->setTitle($translate->phrase('user.defaults.article_category_general'));
        $cat->setBrand($this->getReference('brand'));

        $this->setReference('article_category_general', $cat);
        $manager->persist($cat);

        //------------------------------
        // General News cat : news_category_general
        //------------------------------

        $cat = new NewsCategory();
        $cat->setTitle($translate->phrase('user.defaults.news_category_general'));
        $cat->setBrand($this->getReference('brand'));
        $manager->persist($cat);

        $this->setReference('news_category_general', $cat);
        $manager->persist($cat);

        //------------------------------
        // General Downloads cat : downloads_category_general
        //------------------------------

        $cat = new DownloadCategory();
        $cat->setTitle($translate->phrase('user.defaults.downloads_category_general'));
        $cat->setBrand($this->getReference('brand'));

        $this->setReference('downloads_category_general', $cat);
        $manager->persist($cat);

        //------------------------------
        // Initial feedback cats :
        // feedback_category_suggestion, feedback_category_feature_request, feedback_category_bug_report
        //------------------------------

        foreach (['Suggestion', 'Feature Request', 'Bug Report'] as $title) {
            $cat = new FeedbackCategory();
            $cat->setTitle($title);
            $manager->persist($cat);

            $id = str_replace(' ', '_', strtolower($title));
            $this->setReference('feedback_category_'.$id, $cat);
        }

        $manager->flush();

        //------------------------------
        // Enable publish perms on everyone/registered
        //------------------------------

        if ($this->hasReference('usergroup.everyone')) {
            $this->enableCatPerms($this->getReference('usergroup.everyone'));
        }
        if ($this->hasReference('usergroup.registered')) {
            $this->enableCatPerms($this->getReference('usergroup.registered'));
        }
    }

    /**
     * Inserts permissions for $g for known default categories.
     *
     * @param Usergroup $g
     */
    private function enableCatPerms(Usergroup $g)
    {
        $db = $this->container->get('database_connection');

        $ref_perms = [
            ['id' => 'article_category_general', 'table' => 'article_category2usergroup'],
            ['id' => 'news_category_general', 'table' => 'news_category2usergroup'],
            ['id' => 'downloads_category_general', 'table' => 'download_category2usergroup'],
            ['id' => 'feedback_category_suggestion', 'table' => 'feedback_category2usergroup'],
            ['id' => 'feedback_category_feature_request', 'table' => 'feedback_category2usergroup'],
            ['id' => 'feedback_category_bug_report', 'table' => 'feedback_category2usergroup'],
        ];

        foreach ($ref_perms as $perm) {
            $rec = $this->getReference($perm['id']);
            $db->insert($perm['table'], ['category_id' => $rec->getId(), 'usergroup_id' => $g->getId()]);
        }
    }
}
