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
        $article = new Entity\Article();
        $article
            ->setTitle('Article 1')
            ->setContent('Some text')
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
    }
}
