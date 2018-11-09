<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleValidator;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DpTest\AbstractKernelAwareTestCase;
use JsonSchema\Validator;

class AppBundleValidatorTest extends AbstractKernelAwareTestCase
{

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getApiKernel()->getContainer();
    }

    /**
     * @return FileLocator
     */
    protected function getFileLocator()
    {
        $kernel = $this->getApiKernel();
        return new FileLocator($kernel);
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

    /**
     * @test
     */
    public function test_previous_version_manifests_pass_validation()
    {
        $latestManifestVersion = $this->getContainer()->getParameter('manifest.current.version');
        $validator = Infrastructure\Services::createAppBundleValidator($this->getFileLocator(), $latestManifestVersion);

        $previousVersions = [
            '2.0.0',
            '2.1.0',
            '2.2.0',
            '2.3.0'
        ];

        foreach ($previousVersions as $version) {
            $filePath = sprintf('@AppStoreBundle/Resources/manifest/app-manifest.%s.example.json', $version);
            $manifestLocation = $this->locateFile($filePath);
            $manifestContents = file_get_contents($manifestLocation);

            $zipBundle = Infrastructure\AppBundleAdapters\ZipArchiveBundleWriter::fromTmp()->setManifest($manifestContents)->build();
            $isValid = $validator->validateBundle($zipBundle);
            $this->assertTrue($isValid, sprintf('a bundle with a valid %s manifest should pass validation', $version));
        }

        $this->assertNotEmpty($previousVersions);
    }

    /**
     * @test
     */
    public function test_valid_manifest_passes_validation()
    {
        $manifestLocation = $this->locateFile('@AppStoreBundle/Resources/manifest/app-manifest.current.example.json');
        $manifestContents = file_get_contents($manifestLocation);

        $latestManifestVersion = $this->getContainer()->getParameter('manifest.current.version');
        $validator = Infrastructure\Services::createAppBundleValidator($this->getFileLocator(), $latestManifestVersion);

        $zipBundle = Infrastructure\AppBundleAdapters\ZipArchiveBundleWriter::fromTmp()->setManifest($manifestContents)->build();
        $isValid = $validator->validateBundle($zipBundle);

        $this->assertTrue($isValid, 'a bundle with a manifest conforming with the default manifest schema should pass validation');
    }

    /**
     * @test
     */
    public function test_empty_manifest_fails_validation()
    {
        $manifestContents = json_encode(new \stdClass());
        $zipBundle = Infrastructure\AppBundleAdapters\ZipArchiveBundleWriter::fromTmp()->setManifest($manifestContents)->build();

        $latestManifestVersion = $this->getContainer()->getParameter('manifest.current.version');
        $validator = Infrastructure\Services::createAppBundleValidator($this->getFileLocator(), $latestManifestVersion);
        $isValid = $validator->validateBundle($zipBundle);

        $this->assertFalse($isValid, 'a bundle with an empty manifest should not pass validation');
    }

    /**
     * @test
     */
    public function test_current_schema_rejects_version_number()
    {
        $filePath = $this->locateFile(sprintf('@AppStoreBundle/Resources/manifest/schema.%s.json', "current"));
        $schema = json_decode(file_get_contents($filePath));

        $filePath = $this->locateFile(sprintf('@AppStoreBundle/Resources/manifest/app-manifest.%s.example.json', "current"));
        $manifest = json_decode(file_get_contents($filePath));
        $manifest->appVersion = "not a semver";

        $validator = new Validator();
        $validator->validate($manifest, (object)$schema);
        $this->assertFalse($validator->isValid(), "a manifest with a wrong type of appVersion should not pass validation");
    }


}


