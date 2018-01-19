<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class GlossaryFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    const NUM_WORDS            = 20;
    const NUM_WORD_DEFINITIONS = 10;

    /**
     * @var int[]
     */
    private $definitions;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadWordDefinitions();
        $this->loadWords();
    }

    private function loadWordDefinitions()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < self::NUM_WORD_DEFINITIONS) {
            $batch[] = [
                'definition' => $this->faker->realText(100),
            ];
        }
        $this->db->batchInsert(self::TABLE_GLOSSARY_WORD_DEFINITIONS, $batch, true);
        $this->definitions = $this->fetchIds(self::TABLE_GLOSSARY_WORD_DEFINITIONS);
    }

    private function loadWords()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < self::NUM_WORDS) {
            $batch[] = [
                'definition_id' => $this->faker->randomElement($this->definitions),
                'word'          => $this->faker->realText(15),
                'brand_id'      => $this->getReference('brand')->getId(),
            ];
        }
        $this->db->batchInsert(self::TABLE_GLOSSARY_WORDS, $batch, true);
    }
}
