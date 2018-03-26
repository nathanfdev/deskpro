<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\AgentTeam;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Data\ContentTypes;

class AgentTeamsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $ava_map = [
            null,
            '/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars/superman_.jpg',
            '/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars/captain_america.gif',
        ];

        $bs = $this->container->get('deskpro.blob_storage');

        foreach (['Support', 'Level 1', 'Level 2'] as $k => $title) {
            $team       = new AgentTeam();
            $team->name = $title;
            $this->addReference('team.'.$k, $team);
            $manager->persist($team);

            $ava = $ava_map[$k];
            if ($ava) {
                $blob         = $bs->createBlobRecordFromFile(DP_ROOT.$ava, basename($ava), ContentTypes::getContentTypeFromFilename($ava));
                $team->avatar = $blob;
                $manager->persist($team);
            }
        }

        $manager->flush();
    }
}
