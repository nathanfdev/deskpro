<?php

namespace DpTest\DeskPRO\CustomFields;

use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface;
use DpTest\DeskProTestCase;

class FieldManagerTest extends DeskProTestCase
{
    public function testExceptionThrownWhenAmbiguousCustomFieldAliasDetected()
    {
        $alias = $this->getMockBuilder(ObjectAliasInterface::class)->getMockForAbstractClass();
        $alias->method('getAlias')->willReturn('alias');
        $alias->method('getObjectId')->willReturn(1);
        $alias->method('getQualifiers')->willReturn([]);
        $alias->method('getQualifiedName')->willReturn('alias');

        $customFields = [
            $this->getMockBuilder(CustomDefAbstract::class)
                ->setMethods(['getAliases'])
                ->getMockForAbstractClass(),

            $this->getMockBuilder(CustomDefAbstract::class)
                ->setMethods(['getAliases'])
                ->getMockForAbstractClass()
        ];
        foreach ($customFields as $field) {
            $field->method('getAliases')->willReturn([$alias]);
        }

        /** @var FieldManager $fieldManager */
        $fieldManager = $this->getMockBuilder(FieldManager::class)
            ->disableOriginalConstructor()->setMethods(['getFields', 'getDisplayArrayForObject'])
            ->getMock()
        ;
        $fieldManager->method('getFields')->willReturn($customFields);
        $fieldManager->method('getDisplayArrayForObject')->willReturn([]);

        $expectedException = null;
        try {
            $fieldManager->setFormToObject([], new Ticket(), false);
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        $this->assertNotNull($expectedException);
        $this->assertInstanceOf(\DomainException::class, $expectedException);
    }
}
