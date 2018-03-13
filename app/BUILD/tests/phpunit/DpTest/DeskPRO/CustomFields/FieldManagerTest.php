<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
