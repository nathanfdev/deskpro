<?php

/**
 * DeskPRO.
 */

namespace DpTest\sys\LowScript;

use DpRun\DpEnv;
use DpSys\LowScript\ServeFileScript;

//use \Symfony\Component\HttpFoundation\Request;

class ServeFileScriptTest extends \PHPUnit_Framework_TestCase
{
    public function matchRequestTypeDataProvider()
    {
        return [
            // DB file
            [
                '/1196DYWABSMCWMAAAKN0/index.jpeg',
                'handleDbBlobRequest',
                ['1196', 'DYWABSMCWMAAAKN0', 'index.jpeg'],
            ],
            // DB file with brand
            [
                '/brand-322/1196DYWABSMCWMAAAKN0/index.jpeg',
                'handleDbBlobRequest',
                ['1196', 'DYWABSMCWMAAAKN0', 'index.jpeg'],
            ],
            // FS file
            [
                '/11DYWABSMCWM1196DAAACB/index.jpeg',
                'handleFilesystemBlobRequest',
                ['11', 'DYWABSMCWM', '1196', 'DAAACB', null, 'index.jpeg'],
            ],
            // -- trycky case (name hash starts with numbers)
            [
                '/2CGTYMWBPJX1248526AD7/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '1248', '526AD7', null, 'index.jpeg'],
            ],
            // -- lower case
            [
                '/2CGTYMWBPJX1248526ad7/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '1248', '526ad7', null, 'index.jpeg'],
            ],
            // -- short id
            [
                '/2CGTYMWBPJX1526AD7/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '1', '526AD7', null, 'index.jpeg'],
            ],
            // -- short long ID
            [
                '/2CGTYMWBPJX123456789526AD7/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '123456789', '526AD7', null, 'index.jpeg'],
            ],

            // DB ticket attachment
            [
                '/1196DYWABSMCWMAAAKN0T/index.jpeg',
                'handleDbBlobRequest',
                ['1196', 'DYWABSMCWMAAAKN0T', 'index.jpeg'],
            ],
            // DB ticket attachment with brand
            [
                '/brand-322/1196DYWABSMCWMAAAKN0T/index.jpeg',
                'handleDbBlobRequest',
                ['1196', 'DYWABSMCWMAAAKN0T', 'index.jpeg'],
            ],

            // FS ticket attachment
            [
                '/11DYWABSMCWM1196DAAACBT/index.jpeg',
                'handleFilesystemBlobRequest',
                ['11', 'DYWABSMCWM', '1196', 'DAAACB', 'T', 'index.jpeg'],
            ],
            // -- trycky case (name hash starts with numbers)
            [
                '/2CGTYMWBPJX1248526AD7T/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '1248', '526AD7', 'T', 'index.jpeg'],
            ],
            // -- lower case
            [
                '/2CGTYMWBPJX1248526ad7T/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '1248', '526ad7', 'T', 'index.jpeg'],
            ],
            // -- short id
            [
                '/2CGTYMWBPJX1526AD7T/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '1', '526AD7', 'T', 'index.jpeg'],
            ],
            // -- short long ID
            [
                '/2CGTYMWBPJX123456789526AD7T/index.jpeg',
                'handleFilesystemBlobRequest',
                ['2', 'CGTYMWBPJX', '123456789', '526AD7', 'T', 'index.jpeg'],
            ],
        ];
    }

    /**
     * @dataProvider matchRequestTypeDataProvider
     *
     * @param string $path
     * @param string $expectedMethod
     * @param array  $expectedParams
     */
    public function testMatchRequestType($path, $expectedMethod, $expectedParams)
    {
        // GIVEN
        $dpEnvMock = $this->getMockBuilder(DpEnv::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dpEnvMock->expects($this->never())->method('isDebug');

        $mockedMethods = [
            'handleFilesystemBlobRequest',
            'handleDbBlobRequest',
            'defaultAvatarAction',
            'personAvatarAction',
            'defaultOrgAvatarAction',
            'dpAsset',
            'orgAvatarAction',
            'sitemapXmlAction',
            'handleGradientRequest',
            'handleAppsRequest',
            'handleAppsV2FileRequest',
            'handleException',
        ];

        $shouldNotBeColledMethods = array_diff($mockedMethods, [$expectedMethod]);

        $mock = $this->getMockBuilder(ServeFileScript::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge($mockedMethods, ['getPathInfo']))
            ->getMock();

        $this->setProtectedProperty($mock, 'dpEnv', $dpEnvMock);
        $mock->expects($this->once())->method('getPathInfo')->willReturn($path);
        foreach ($shouldNotBeColledMethods as $method) {
            $mock->expects($this->never())->method($method);
        }

        $methodExpectation = $mock->expects($this->once())->method($expectedMethod);
        call_user_func_array([$methodExpectation, 'with'], $expectedParams);

        // WHEN/THEN
        $mock->runAction();
    }

    protected function setProtectedProperty($object, $property, $value)
    {
        $reflection          = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }
}
