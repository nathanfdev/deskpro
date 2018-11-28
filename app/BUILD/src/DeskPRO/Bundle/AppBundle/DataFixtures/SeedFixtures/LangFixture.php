<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\SeedFixtures;

use Application\DeskPRO\Entity\Language;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LangFixture extends AbstractFixture implements OrderedFixtureInterface
{
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
    public function load(ObjectManager $manager)
    {
        $lang = new Language();
        $lang->setSysName('default');
        $lang->setLangCode('eng');
        $lang->setTitle('English');
        $lang->setLocale('en_US');
        $lang->setFlagImage('locale_en-US.png');
        $lang->setHasUser(true);
        $lang->setHasAgent(true);
        $lang->setHasAdmin(true);

        $manager->persist($lang);
        $manager->flush();

        $this->addReference('english', $lang);
    }
}
