<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\CommunityChannel;
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
        // Initial community custom channels:

        //------------------------------

        foreach (['Suggestion', 'Feature Request', 'Bug Report'] as $title) {
            $cat = new CommunityChannel();
            $cat->setTitle($title);
            $manager->persist($cat);

            $id = str_replace(' ', '_', strtolower($title));
            $this->setReference('community_custom_channel_'.$id, $cat);
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
            ['id' => 'community_channel_suggestion', 'table' => 'community_channel2usergroup', 'cat_attribute' => 'channel_id'],
            ['id' => 'community_channel_feature_request', 'table' => 'community_channel2usergroup', 'cat_attribute' => 'channel_id'],
            ['id' => 'community_channel_bug_report', 'table' => 'community_channel2usergroup', 'cat_attribute' => 'channel_id'],
        ];

        foreach ($ref_perms as $perm) {
            $rec = $this->getReference($perm['id']);
            $db->insert($perm['table'], [$perm['cat_attribute'] => $rec->getId(), 'usergroup_id' => $g->getId()]);
        }
    }
}
