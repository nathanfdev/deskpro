<?php

namespace DeskPRO\Bundle\AppBundle\Cache;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DpTest\DeskProTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class EtagGeneratorTest.
 */
class EtagGeneratorTest extends DeskProTestCase
{
    /**
     * @var EtagGenerator
     */
    protected $etagGenerator;

    public function setUp()
    {
        $parameterBag = new ParameterBag();
        $parameterBag->set('api.cache.global_version', 'global_version');

        $settings_resolver = $this->prophesize(SettingsResolver::class);
        $settings_resolver->getGlobalSettings()->willReturn($parameterBag);
        $settings_resolver = $settings_resolver->reveal();

        /* @var SettingsResolver $settings_resolver */
        $this->etagGenerator = new EtagGenerator($settings_resolver);
    }

    /**
     * @dataProvider getTestCreateSegments()
     */
    public function testCreateSegments($parametes, $excected)
    {
        $reflection           = new \ReflectionClass(EtagGenerator::class);
        $createSegmentsMethod = $reflection->getMethod('createSegments');
        $createSegmentsMethod->setAccessible(true);
        $segments      = $createSegmentsMethod->invoke($this->etagGenerator, $parametes);
        $flattenMethod = $reflection->getMethod('flatten');
        $flattenMethod->setAccessible(true);
        $segments = $flattenMethod->invoke($this->etagGenerator, $segments);

        $this->assertEquals($excected, $segments);
    }

    /**
     * @return array
     */
    public function getTestCreateSegments()
    {
        $domainOjectEntity     = new MockDomainObject();
        $domainOjectEntity->id = 1;

        $mockEntity     = new MockEntity();
        $mockEntity->id = 2;

        $object           = new \stdClass();
        $object->id       = 5;
        $object->property = 'some_string';

        return [
            [[], []],
            [[1, [2, 3]], [1, 2, 3]],
            [[1, [2, 3 => [3, 4]]], [1, 2, 3, 4]],
            [[$object], ['std_class(id=>5;property=>some_string)']],
            [[1, [$domainOjectEntity, 3]], [1, 'mock_domain_object(id=>1)', 3]],
            [[1, [$domainOjectEntity, $mockEntity]], [1, 'mock_domain_object(id=>1)', 'mock_entity(id=>2)']],
            [['a' => 'b'], ['a=>b']],
            [['a' => 'b', 'c'], ['a=>b', 'c']],
            [
                [
                    1,
                    [
                        'do'       => $domainOjectEntity,
                        'entities' => [
                            $mockEntity,
                        ],
                    ],
                ],
                [
                    1,
                    'do=>mock_domain_object(id=>1)',
                    'mock_entity(id=>2)',
                ],
            ],
            [
                [
                    1,
                    [
                        'do'       => $domainOjectEntity,
                        'entities' => [
                            $mockEntity,
                        ],
                    ],
                ],
                [
                    1,
                    'do=>mock_domain_object(id=>1)',
                    'mock_entity(id=>2)',
                ],
            ],

        ];
    }
}

/**
 * Class MockDomainObject.
 */
class MockDomainObject extends DomainObject
{
    /**
     * @var
     */
    public $id;
}

/**
 * Class MockEntity.
 */
class MockEntity implements EntityInterface
{
    /**
     * @var
     */
    public $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
