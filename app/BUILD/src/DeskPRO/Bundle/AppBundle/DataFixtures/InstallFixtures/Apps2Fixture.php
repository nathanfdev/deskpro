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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationManager;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppZipArchiveBundle;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Apps2Fixture.
 */
class Apps2Fixture extends DeskProAbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        self::installTrelloApp($this->container);
    }

    /**
     * @param ContainerInterface $container
     */
    public static function installTrelloApp(ContainerInterface $container)
    {
        $assetDir      = $container->get('deskpro.app_env')->getAppWwwAssetDir();
        $trelloAppPath = $assetDir.'/apps/v2/deskproapps-trello.zip';

        $instanceCreator = $container->get(ApplicationManager::class);
        $instanceCreator->createAppEntity(new AppZipArchiveBundle(new \ZipArchive(), new \SplFileInfo($trelloAppPath)));
    }
}
