<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleValidator;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DpTest\AbstractKernelAwareTestCase;

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
    public function test_valid_manifest_passes_validation()
    {
        $manifestLocation = $this->locateFile('@AppStoreBundle/Resources/manifest/app-manifest.example.json');
        $manifestContents = file_get_contents($manifestLocation);

        $zipBundle = Infrastructure\AppZipBundleBuilder::fromTmp()->setManifest($manifestContents)->build();

        $schemaLocation = $this->locateFile('@AppStoreBundle/Resources/manifest/schema.default.json');
        $schemaFileInfo = new \SplFileInfo($schemaLocation);
        $validator = new AppBundleValidator($schemaFileInfo);
        $isValid = $validator->validateBundle($zipBundle);

        $this->assertTrue($isValid, 'a bundle with a manifest conforming with the default manifest schema should pass validation');
    }

    /**
     * @test
     */
    public function test_empty_manifest_fails_validation()
    {
        $manifestContents = json_encode(new \stdClass());
        $zipBundle = Infrastructure\AppZipBundleBuilder::fromTmp()->setManifest($manifestContents)->build();

        $schemaLocation = $this->locateFile('@AppStoreBundle/Resources/manifest/schema.default.json');
        $schemaFileInfo = new \SplFileInfo($schemaLocation);
        $validator = new AppBundleValidator($schemaFileInfo);
        $isValid = $validator->validateBundle($zipBundle);

        $this->assertFalse($isValid, 'a bundle with an empty manifest should not pass validation');
    }


}


