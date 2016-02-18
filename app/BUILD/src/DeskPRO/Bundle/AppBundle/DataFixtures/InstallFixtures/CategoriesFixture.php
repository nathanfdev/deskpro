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
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoriesFixture extends DeskProAbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $translate = $this->container->get('deskpro.core.translate');

        $cat        = new ArticleCategory();
        $cat->title = $translate->phrase('user.defaults.article_category_general');

        $this->setReference('article_category_general', $cat);
        $manager->persist($cat);

        $cat        = new NewsCategory();
        $cat->title = $translate->phrase('user.defaults.news_category_general');
        $manager->persist($cat);

        $this->setReference('news_category_general', $cat);
        $manager->persist($cat);

        $cat        = new DownloadCategory();
        $cat->title = $translate->phrase('user.defaults.downloads_category_general');

        $this->setReference('downloads_category_general', $cat);
        $manager->persist($cat);

        foreach (['Suggestion', 'Feature Request', 'Bug Report'] as $title) {
            $cat        = new FeedbackCategory();
            $cat->title = $title;
            $manager->persist($cat);
        }

        $manager->flush();
    }
}
