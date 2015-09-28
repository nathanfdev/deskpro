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

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\ImportMap;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Article.
 */
class Article extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $parent_category = new Entity\ArticleCategory();
        $parent_category->setRealTitle('Category 1');

        $child_category = new Entity\ArticleCategory();
        $child_category->setRealTitle('Old Sub Category 1');
        $child_category->setParent($parent_category);

        $manager->persist($parent_category);
        $manager->persist($child_category);

        $manager->flush();

        $map = new ImportMap();
        $map
            ->setTypename(ImportMap::TYPE_ZENDESK_ARTICLE_CATEGORY)
            ->setOldId(1)
            ->setNewId($parent_category->getId())
        ;

        $manager->persist($map);
        $manager->flush();

        $article = new Entity\Article();
        $article
            ->setTitle('Article 1')
            ->setContent('Some text')
            ->setCategories(array($parent_category, $child_category))
        ;

        $old_article = new Entity\Article();
        $old_article
            ->setTitle('Old Article 2')
            ->setContent('Some text')
            ->setCategories(array($child_category))
        ;

        $manager->persist($article);
        $manager->persist($old_article);
        $manager->flush();

        foreach (array('Label 1', 'Label 2') as $label) {
            $label_entity = new Entity\LabelArticle();
            $label_entity->setLabel($label);

            $article->addLabel($label_entity);
            $manager->persist($label_entity);
        }

        $manager->persist($article);

        $map = new ImportMap();
        $map
            ->setTypename(ImportMap::TYPE_ZENDESK_ARTICLE)
            ->setOldId(1)
            ->setNewId($article->getId())
        ;

        $manager->persist($map);
        $manager->flush();

        /** @var Entity\Language $language */
        $language = $manager->find('Application\DeskPRO\Entity\Language', 1);

        $manager->persist(Entity\ObjectLang::createObjectLang($language, $article, 'title', 'Title en_US'));
        $manager->persist(Entity\ObjectLang::createObjectLang($language, $article, 'content', 'Content en_US'));
    }
}
