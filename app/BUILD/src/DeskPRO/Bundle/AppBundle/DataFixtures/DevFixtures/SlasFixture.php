<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Sla;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SlasFixture extends AbstractDpFixture implements OrderedFixtureInterface
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
        $options = [
            [
                'title'      => 'First',
                'sla_type'   => 'first_response',
                'apply_type' => 'all',
            ],
            [
                'title'      => 'Second',
                'sla_type'   => 'resolution',
                'apply_type' => 'manual',
            ],
            [
                'title'      => 'Third',
                'sla_type'   => 'waiting_time',
                'apply_type' => 'manual',
            ],
        ];
        foreach ($options as $item) {
            $sla = new Sla();
            $sla
                ->setTitle($item['title'])
                ->setSlaType($item['sla_type'])
                ->setApplyType($item['apply_type']);
            $manager->persist($sla);
        }
        $manager->flush();
    }
}
