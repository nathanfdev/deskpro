<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

/**
 * Class AgentAlertsFixture.
 */
class AgentAlertsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 90;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Person $person */
        $person    = $this->getReference('admin');
        $generator = $this->container->get('dp.fixtures.random_agent_alert_generator');

        for ($i = 0; $i < 10; ++$i) {
            $generator->generateRandomAlert($person);
        }
    }
}
