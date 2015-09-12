<?php

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\ImportMap;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Article
 * @package DpFixtures\Import
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

        $manager->persist($article);
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
