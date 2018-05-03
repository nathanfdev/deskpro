<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\App\Native\NativeAppsSync;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\InstallBundle\Data\DefaultDataProcessor;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultDataFixture extends AbstractDpFixture implements OrderedFixtureInterface
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
        $dataProcessor->setExtraOptions(['objectManager' => $manager, 'referenceRepository' => $this->referenceRepository]);
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
