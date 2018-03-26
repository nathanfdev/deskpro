<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Util;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Util\EntityUtils;
use DpTest\DeskProTestCase;

class EntityUtilsTest extends DeskProTestCase
{
    public function invalidValues()
    {
        return [
            [new MockValueObject()],
            [''],
            [9],
            [9.0],
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider invalidValues
     * @expectedException \InvalidArgumentException
     */
    public function testGetIdentifierInvalidArgumentException($non_entity)
    {
        EntityUtils::getIdentifier($non_entity);
    }

    public function testGetIdentifierForScalars()
    {
        $this->assertSame(90, EntityUtils::getIdentifier(new MockEntity()), 'getIdentifier EntityInterface works with ints');
        $this->assertSame(101, EntityUtils::getIdentifier(new MockDomainObject()), 'getIdentifier DomainObject works with ints');

        $string_entity = $this->prophesize(EntityInterface::class);
        $string_entity->getId()->willReturn(0);
        $this->assertSame(0, EntityUtils::getIdentifier($string_entity->reveal()), 'getIdentifier EntityInterface 0 is a valid id');

        $string_entity = $this->prophesize(MockDomainObject::class);
        $string_entity->getId()->willReturn(0);
        $this->assertSame(0, EntityUtils::getIdentifier($string_entity->reveal()), 'getIdentifier DomainObject 0 is a valid id');

        $string_entity = $this->prophesize(EntityInterface::class);
        $string_entity->getId()->willReturn('string_id');
        $this->assertSame('string_id', EntityUtils::getIdentifier($string_entity->reveal()), 'getIdentifier EntityInterface works with strings');

        $string_domain_object = $this->prophesize(MockDomainObject::class);
        $string_domain_object->getId()->willReturn('string_id_2');
        $this->assertSame('string_id_2', EntityUtils::getIdentifier($string_domain_object->reveal()), 'getIdentifier DomainObject works with strings');
    }

    public function getScalarArrays()
    {
        return [
            [
                ['single key array id'],
                'single key array id',
            ],
            [
                [1, 'some name'],
                '1|some name',
            ],
            [
                [42, 6],
                '42|6',
            ],
            [
                [4, 26],
                '4|26',
            ],
            [
                [4, 'id', 26],
                '4|id|26',
            ],
            [
                [2],
                '2',
            ],
        ];
    }

    /**
     * @dataProvider getScalarArrays
     */
    public function testGetIdentifierForScalarArrays($id, $expected_identifier)
    {
        $string_entity = $this->prophesize(EntityInterface::class);
        $string_entity->getId()->willReturn($id);
        $this->assertSame(
            $expected_identifier,
            EntityUtils::getIdentifier($string_entity->reveal()),
            'getIdentifier EntityInterface works with strings'
        );

        $string_domain_object = $this->prophesize(Ticket::class);
        $string_domain_object->getId()->willReturn($id);
        $this->assertSame(
            $expected_identifier,
            EntityUtils::getIdentifier($string_domain_object->reveal()),
            'getIdentifier EntityInterface works with strings'
        );
    }

    public function getExampleNullIds()
    {
        return [
            [true, 'true bool'],
            [false, 'false bool'],
            [null, 'null'],
            ['', 'empty string'],
            [[], 'empty array'],
            [[true], 'array of bool true'],
            [[true, false], 'array of bools'],
            [[false], 'array of bool false'],
            [[''], 'array of emptry string'],
            [['', ''], 'empty strings'],
            [[null], 'array of nulls'],
            [[null, null], 'array of nulls'],
            [[[]], 'array of an empty array'],
            [[[], []], 'array of empty arrays'],
            [[false, [], false, true, '', null, null, []], 'array of a bunch of null values'],
        ];
    }

    /**
     * @dataProvider getExampleNullIds
     */
    public function testGetIdentifierThrowsExceptionForInvalidIdValues($id, $what)
    {
        $string_entity = $this->prophesize(EntityInterface::class);
        $string_entity->getId()->willReturn($id);
        $this->assertNull(EntityUtils::getIdentifier($string_entity->reveal()),
            'EntityInterface null id if id is ('.$what.')');

        $string_entity = $this->prophesize(MockDomainObject::class);
        $string_entity->getId()->willReturn($id);
        $this->assertNull(EntityUtils::getIdentifier($string_entity->reveal()),
            'DomainObject null id if id is ('.$what.')');
    }
}

class MockEntity implements EntityInterface
{
    public function getId()
    {
        return 90;
    }
}

class MockDomainObject extends DomainObject
{
    public function getId()
    {
        return 101;
    }
}

class MockValueObject
{
}
