<?php

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
