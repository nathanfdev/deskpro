<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DpTest\AbstractKernelAwareTestCase;

class AppManifestReaderTest extends AbstractKernelAwareTestCase
{
    private function ksortRecursive(&$array, $sort_flags = SORT_REGULAR)
    {
        if (!is_array($array)) return false;
        ksort($array, $sort_flags);
        foreach ($array as &$arr) {
            $this->ksortRecursive($arr, $sort_flags);
        }
        return true;
    }

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
    public function test_backwords_compatible_transformations_are_applied_for_older_version_manifests()
    {
        $versions = ['2.0.0', '2.1.0', '2.2.0'];

        foreach ($versions as $versionNumber) {
            $reader = new Infrastructure\AppManifestReader();

            $expectedManifestContents = $this->readFile(
                sprintf('@AppStoreBundle/Resources/manifest/app-manifest.%s.transformed.json', $versionNumber)
            );
            $expectedManifest = $reader->readManifestFromJson($expectedManifestContents);

            $actualManifestContents = $this->readFile(
                sprintf('@AppStoreBundle/Resources/manifest/app-manifest.%s.example.json', $versionNumber)
            );
            $actualManifest = $reader->readManifestFromJson($actualManifestContents);

            $serializer = $this->getContainer()->get('jms_serializer');
            $expected = $serializer->serialize($expectedManifest, 'json');
            $actual = $serializer->serialize($actualManifest, 'json');

            $this->assertEquals($expected, $actual, sprintf('failed to transform version: %s', $versionNumber));
        }
    }

//    /**
//     * @test
//     */
    public function test_backwords_compatible_transformations_are_not_applied_for_current_version()
    {
        $expectedManifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json');
        $expectedManifest = json_decode($expectedManifestContents, true);

        $manifestContents = $this->readFile('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json');
        $reader = new Infrastructure\AppManifestReader();
        $manifest = $reader->readManifestFromJson($manifestContents);

        $serializer = $this->getContainer()->get('jms_serializer');
        $actualManifest = json_decode($serializer->serialize($manifest, 'json'), true);
        $this->ksortRecursive($actualManifest);
        $this->ksortRecursive($expectedManifest);

        $this->assertEquals(print_r($expectedManifest, true), print_r($actualManifest, true));
    }
}

