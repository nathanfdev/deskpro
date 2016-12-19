<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
