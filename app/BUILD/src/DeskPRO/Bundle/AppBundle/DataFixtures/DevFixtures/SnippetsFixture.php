<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\ObjectLang;
use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SnippetsFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    const NUM_CATEGORIES = 10;
    const NUM_SNIPPETS   = 20;

    /** @var int[] */
    private $categories;

    /** @var int[] */
    private $snippets;

    public function getOrder()
    {
        return 80;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadSnippetCategories();
        $this->loadSnippets();
        $this->loadObjectLang();
    }

    private function loadSnippetCategories()
    {
        for ($i = 0; $i < self::NUM_CATEGORIES; ++$i) {
            $category = new TextSnippetCategory();
            if ($i % 2 === 0) {
                $typename = TextSnippetCategory::TYPE_TICKET;
            } else {
                $typename = TextSnippetCategory::TYPE_CHAT;
            }
            $category->setTypename($typename)->setIsGlobal(true);
            $this->manager->persist($category);
        }
        $this->manager->flush();

        $this->categories = $this->fetchIds('text_snippet_categories');
    }

    private function loadSnippets()
    {
        $batch = [];
        foreach ($this->categories as $category) {
            for ($i = 0; $i < self::NUM_SNIPPETS; ++$i) {
                $batch[] = [
                    'category_id'   => $category,
                    'shortcut_code' => str_replace(' ', '_', $this->faker->words(2, true)),
                    'is_draft'      => 0,
                ];
            }
        }
        $this->db->batchInsert(self::TABLE_TEXT_SNIPPETS, $batch);
        $this->snippets = $this->fetchIds(self::TABLE_TEXT_SNIPPETS);
    }

    private function loadObjectLang()
    {
        /** @var Language $lang */
        $lang = $this->getReference('english');

        foreach ($this->categories as $categoryId) {
            $objLang = new ObjectLang();
            $objLang
                ->setLanguage($lang)
                ->setRef('text_snippet_categories.'.$categoryId)
                ->setPropName('title')
                ->setValue($this->faker->words(3, true));
            $this->manager->persist($objLang);
        }
        foreach ($this->snippets as $snippetId) {
            $objLang = new ObjectLang();
            $objLang
                ->setLanguage($lang)
                ->setRef(self::TABLE_TEXT_SNIPPETS.'.'.$snippetId)
                ->setPropName('title')
                ->setValue($this->faker->words(3, true));
            $this->manager->persist($objLang);

            $snippetLength = $this->faker->numberBetween(15, 500);

            $objLang = new ObjectLang();
            $objLang
                ->setLanguage($lang)
                ->setRef(self::TABLE_TEXT_SNIPPETS.'.'.$snippetId)
                ->setPropName('snippet')
                ->setValue($this->faker->realText($snippetLength));
            $this->manager->persist($objLang);
        }
        $this->manager->flush();
    }
}
