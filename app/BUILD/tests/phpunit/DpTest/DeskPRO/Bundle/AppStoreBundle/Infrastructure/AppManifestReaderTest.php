<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DpTest\AbstractKernelAwareTestCase;

class AppManifestReaderTest extends AbstractKernelAwareTestCase
{

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getApiKernel()->getContainer();
    }

    /**
     * @param string $locationRef
     * @return array|string
     */
    protected function locateFile($locationRef)
    {
        $kernel = $this->getApiKernel();
        $locator = new FileLocator($kernel);

        return $locator->locate($locationRef);
    }

    protected function readFile($locationRef)
    {
        $location = $this->locateFile($locationRef);
        return file_get_contents($location);
    }

    /**
     * @test
     */
    public function test_backwords_compatible_transformations_are_applied_for_v210_manifests()
    {
        $latestManifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json');
        $manifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.2.1.0.example.json');

        $reader = new Infrastructure\AppManifestReader();
        $latestManifest = $reader->readManifestFromJson($latestManifestContents);
        $manifest = $reader->readManifestFromJson($manifestContents);

        $serializer = $this->getContainer()->get('jms_serializer');
        $expectedManifest = $serializer->serialize($latestManifest, 'json');
        $actualManifest = $serializer->serialize($manifest, 'json');

        $this->assertEquals($expectedManifest, $actualManifest);
    }

    /**
     * @test
     */
    public function test_backwords_compatible_transformations_are_applied_for_v200_manifests()
    {
        $latestManifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json');
        $manifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.2.0.0.example.json');

        $reader = new Infrastructure\AppManifestReader();
        $latestManifest = $reader->readManifestFromJson($latestManifestContents);
        $latestManifest->setStorage(array()); // there is no state key in v2.0.0
        $manifest = $reader->readManifestFromJson($manifestContents);

        $serializer = $this->getContainer()->get('jms_serializer');
        $expectedManifest = $serializer->serialize($latestManifest, 'json');

        $actualManifest = $serializer->serialize($manifest, 'json');

        $this->assertEquals($expectedManifest, $actualManifest);
    }

    /**
     * @test
     */
    public function test_backwords_compatible_transformations_are_not_applied_for_current_version()
    {
        $latestManifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json');
        $expectedManifest = json_decode($latestManifestContents, true);

        $manifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json');
        $reader = new Infrastructure\AppManifestReader();
        $manifest = $reader->readManifestFromJson($manifestContents);

        $serializer = $this->getContainer()->get('jms_serializer');
        $actualManifest = json_decode($serializer->serialize($manifest, 'json'), true);

        ksort($expectedManifest);
        ksort($actualManifest);

        $this->assertEquals(print_r($expectedManifest, true), print_r($actualManifest, true));
    }
}

