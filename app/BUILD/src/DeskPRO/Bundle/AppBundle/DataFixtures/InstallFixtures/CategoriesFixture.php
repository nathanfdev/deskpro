<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\DownloadCategory;
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
        // Initial custom community forums:

        //------------------------------

        foreach ([
            'Suggestion' => 'A forum for suggestion about product from customers which we will investigate',
            'Feature Request' => 'A forum for feature requests like special custom fields, new email handlers or widgets for reports',
            'Bug Report' => 'A forum for bug reporting and error messages. Do not forget to attach any log files and STR lists',
            ] as $title => $desc) {
            $forum = new CommunityForum();
            $forum->setTitle($title)->setDescription($desc);

            $manager->persist($forum);

            $id = str_replace(' ', '_', strtolower($title));
            $this->setReference('community_forum_'.$id, $forum);
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
            ['id' => 'article_category_general', 'table' => 'article_category2usergroup', 'cat_attribute' => 'category_id'],
            ['id' => 'news_category_general', 'table' => 'news_category2usergroup', 'cat_attribute' => 'category_id'],
            ['id' => 'downloads_category_general', 'table' => 'download_category2usergroup', 'cat_attribute' => 'category_id'],
            ['id' => 'community_forum_suggestion', 'table' => 'community_forum2usergroup', 'cat_attribute' => 'community_forum_id'],
            ['id' => 'community_forum_feature_request', 'table' => 'community_forum2usergroup', 'cat_attribute' => 'community_forum_id'],
            ['id' => 'community_forum_bug_report', 'table' => 'community_forum2usergroup', 'cat_attribute' => 'community_forum_id'],
        ];

        foreach ($ref_perms as $perm) {
            $rec = $this->getReference($perm['id']);
            $db->insert($perm['table'], [$perm['cat_attribute'] => $rec->getId(), 'usergroup_id' => $g->getId()]);
        }
    }
}
