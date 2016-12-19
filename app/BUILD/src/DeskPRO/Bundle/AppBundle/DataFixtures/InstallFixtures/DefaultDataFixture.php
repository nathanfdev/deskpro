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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\App\Native\NativeAppsSync;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\InstallBundle\Data\DefaultDataProcessor;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultDataFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 110;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // Create Email templateSet to store email assets
        $emailTemplateTheme = new ThemeSet();
        $emailTemplateTheme->setThemeId('email_templates');
        $manager->persist($emailTemplateTheme);

        $manager->flush();

        $this->appsSync($manager);
        $dataProcessor = new DefaultDataProcessor($this->container);
        $dataProcessor->runInstall();
    }

    private function appsSync($manager)
    {
        $appSyncer = new NativeAppsSync(
            $this->container,
            $this->container->getAppManager(),
            new PackageInstaller($manager, $this->container->getBlobStorage(), $this->container->getImagine()),
            null
        );
        $appSyncer->runSync();
    }
}
