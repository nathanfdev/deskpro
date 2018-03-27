<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Problem;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ProblemFixture.
 */
class ProblemFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 70;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $agents = $manager->getRepository(Person::class)->findBy([
            'is_agent' => 1,
        ]);

        for ($i = 0; $i < 100; ++$i) {
            $problem = new Problem();
            $problem
                ->setTitle($this->faker->sentence(4))
                ->setCreator($this->faker->randomElement($agents))
                ->setCreated($this->faker->dateTimeThisYear)
                ->setIsOpen($this->faker->boolean(25))
            ;

            $manager->persist($problem);
        }

        $manager->flush();
    }
}
