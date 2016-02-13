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
namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PublishFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures\FirstAdminFixture',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var \Application\DeskPRO\Translate\Translate $tr */
        $tr = $this->container->get('deskpro.core.translate');

        /** @var \Application\DeskPRO\Entity\Person $admin */
        $admin = $this->getReference('admin');

        #------------------------------
        # KB
        #------------------------------

        $cat        = new ArticleCategory();
        $cat->title = $tr->phrase('user.defaults.article_category_general');
        $manager->persist($cat);

        $content          = new \Application\DeskPRO\Entity\Article();
        $content->person  = $admin;
        $content->title   = $tr->phrase('user.defaults.article_example_title');
        $content->content = $tr->phrase('user.defaults.article_example_content');
        $content->status  = 'published';
        $content->addToCategory($cat);
        $manager->persist($content);

        #------------------------------
        # Downloads
        #------------------------------

        $cat        = new DownloadCategory();
        $cat->title = $tr->phrase('user.defaults.downloads_category_general');
        $manager->persist($cat);

        #------------------------------
        # News
        #------------------------------

        $cat        = new NewsCategory();
        $cat->title = $tr->phrase('user.defaults.news_category_general');
        $manager->persist($cat);

        $content          = new \Application\DeskPRO\Entity\News();
        $content->person  = $admin;
        $content->title   = $tr->phrase('user.defaults.news_example_title');
        $content->content = $tr->phrase('user.defaults.news_example_content');
        $content->status  = 'published';
        $content->setCategory($cat);
        $manager->persist($content);

        #------------------------------
        # Feedback
        #------------------------------

        foreach (['Suggestion', 'Feature Request', 'Bug Report'] as $title) {
            $cat        = new FeedbackCategory();
            $cat->title = $title;
            $manager->persist($cat);
        }

        $cat_field                = new CustomDefFeedback();
        $cat_field->sys_name      = 'cat';
        $cat_field->title         = 'Category';
        $cat_field->description   = 'e.g., maybe Windows, Mac, Linux.';
        $cat_field->handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';
        $manager->persist($cat_field);

        foreach ([
            'active' => ['Gathering Feedback', 'Planning', 'Started', 'Under Review'],
            'closed' => ['Completed', 'Duplicate', 'Declined'],
                 ] as $status => $titles) {
            foreach ($titles as $title) {
                $cat              = new FeedbackStatusCategory();
                $cat->status_type = $status;
                $cat->title       = $title;
                $manager->persist($cat);
            }
        }

        #------------------------------

        $manager->flush();
    }
}
