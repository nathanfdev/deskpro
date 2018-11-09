<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Domain\AppChanges;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppChanges\ChangeDetector;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppManifestReader;
use DpTest\AbstractKernelAwareTestCase;
use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;

class ChangesDetectorTest extends AbstractKernelAwareTestCase
{
    private $exampleManifest;

    public function testNoChangeWhenComparingManifestWithItself()
    {
        $manifestJson = $this->readFile(
            sprintf('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json')
        );
        $reader = new AppManifestReader();

        $manifest = $reader->readManifestFromJson($manifestJson);

        $changeDetector = new ChangeDetector();
        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifest);
        $this->assertNull($change, "there should not be a any difference when comparing a manifest with itself");
    }

    public function testNoChangeDetectedWhenMinorOrMajorVersionNumberChange()
    {
        $manifestJson = $this->readFile(
            sprintf('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json')
        );
        $reader = new AppManifestReader();
        $changeDetector = new ChangeDetector();

        // minor change
        $manifest = $reader->readManifestFromJson($manifestJson);
        $manifest->setAppVersion("0.6.1");
        $manifest->setSettings(["something" => "everything"]);

        $manifestOther = $reader->readManifestFromJson($manifestJson);
        $manifestOther->setAppVersion("0.7.1");

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifest);
        $this->assertNull($change, "there should not be a any difference when minor number changes");

        // major change
        $manifest = $reader->readManifestFromJson($manifestJson);
        $manifest->setAppVersion("0.6.1");

        $manifestOther = $reader->readManifestFromJson($manifestJson);
        $manifestOther->setAppVersion("1.6.1");

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifest);
        $this->assertNull($change, "there should not be a any difference when major number changes");

        // both change
        $manifest = $reader->readManifestFromJson($manifestJson);
        $manifest->setAppVersion("0.6.1");

        $manifestOther = $reader->readManifestFromJson($manifestJson);
        $manifestOther->setAppVersion("1.76.1");

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifest);
        $this->assertNull($change, "there should not be a any difference when min number changes");
    }

    public function testNoChangeDetectedWhenMinorOrMajorOrPrereleaseVersionNumberChangeWithoutSettings()
    {
        $changeDetector = new ChangeDetector();

        // minor change
        $manifest = $this->createManifest("0.6.1", []);
        $manifestPrevious = $this->createManifest("0.7.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNull($change, "there should not be a any difference when minor number changes");

        // major change
        $manifest = $this->createManifest("0.6.1", []);
        $manifestPrevious = $this->createManifest("1.6.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNull($change, "there should not be a any difference when major number changes");

        // both change
        $manifest = $this->createManifest("0.6.1", []);
        $manifestPrevious = $this->createManifest("1.7.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNull($change, "there should not be a any difference when min number changes");

        // pre-release dropped
        $manifest = $this->createManifest("0.6.1", []);
        $manifestPrevious = $this->createManifest("0.7.1-beta.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNull($change, "there should not be a any difference when min number changes");
    }

    public function testNoChangeDetectedWhenPatchVersionChanges()
    {
        $changeDetector = new ChangeDetector();

        // minor change
        $manifest = $this->createManifest("0.6.1", ['setting' => 'value']);
        $manifestPrevious = $this->createManifest("0.6.2", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNull($change, "there should not be a any difference when minor number changes");

        // major change
        $manifest = $this->createManifest("0.6.1", []);
        $manifestPrevious = $this->createManifest("0.6.3", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNull($change, "there should not be a any difference when major number changes");
    }

    public function testChangeDetectedWhenMinorOrMajorOrPrereleaseVersionNumberChangeWithSettings()
    {
        $changeDetector = new ChangeDetector();

        // minor change
        $manifest = $this->createManifest("0.6.1", ['setting' => 'value']);
        $manifestPrevious = $this->createManifest("0.7.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNotNull($change, "there should be a difference when minor number changes");
        $this->assertTrue($change->getValue());

        // major change
        $manifest = $this->createManifest("0.6.1", ['setting' => 'value']);
        $manifestPrevious = $this->createManifest("1.6.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNotNull($change, "there should be a difference when major number changes");
        $this->assertTrue($change->getValue());

        // both change
        $manifest = $this->createManifest("0.6.1", ['setting' => 'value']);
        $manifestPrevious = $this->createManifest("1.7.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNotNull($change, "there should be a difference when min number changes");
        $this->assertTrue($change->getValue());

        // pre-release dropped
        $manifest = $this->createManifest("0.6.1", ['setting' => 'value']);
        $manifestPrevious = $this->createManifest("0.7.1-beta.1", ['setting' => 'value']);

        $change = $changeDetector->forceConfigurationStatusChange($manifest, $manifestPrevious);
        $this->assertNotNull($change, "there should be a difference when min number changes");
        $this->assertTrue($change->getValue());
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getApiKernel()->getContainer();
    }

    private function createManifest($version, array $settings = null)
    {
        if (empty($this->exampleManifest)) {
            $this->exampleManifest = $this->readFile(
                '@AppStoreBundle/Resources/manifest/app-manifest.current.example.json'
            );
        }

        $reader = new AppManifestReader();

        // minor change
        $manifest = $reader->readManifestFromJson($this->exampleManifest);
        $manifest->setAppVersion($version);

        if (!is_null($settings)) {
            $manifest->setSettings($settings);
        };

        return $manifest;
    }

    private function readFile($locationRef)
    {
        $kernel = $this->getApiKernel();
        $locator = new FileLocator($kernel);

        $location = $locator->locate($locationRef);
        return file_get_contents($location);
    }
}
