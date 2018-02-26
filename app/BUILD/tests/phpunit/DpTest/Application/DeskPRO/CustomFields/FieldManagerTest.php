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

/**
 * DeskPRO.
 */

namespace DpTest\Application\DeskPRO\CustomFields;

use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use DpTest\DeskProTestCase;
use Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\Custom;

class FieldManagerTest extends DeskProTestCase
{
    public function testRemoveSomeCustomDataOnObject()
    {
        $expectedFieldId = 2;
        $otherFieldId = 4;

        /** @var \PHPUnit_Framework_MockObject_MockObject|CustomDefTicket $actualCustomDef */
        $actualCustomDef = $this->getMockBuilder(CustomDefTicket::class)->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $actualCustomDef->method('getId')->willReturn($expectedFieldId);

        $customData = [];
        for ($i = 0; $i < 5; $i++) {
            $customDataInstance = $this->getMockBuilder(CustomDataTicket::class)
                ->disableOriginalConstructor()
                ->setMethods(['getFieldId'])
                ->getMock()
            ;
            $customDataInstance->method('getFieldId')->willReturn( ($i+1) % 2 ? $expectedFieldId : $otherFieldId);
            $customData[] = $customDataInstance;
        }
        $customDataCollection = new PersistentCollection(
            $this->getMockForAbstractClass(EntityManagerInterface::class),
            CustomDataTicket::class,
            new ArrayCollection($customData)
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|Ticket $ticket */
        $ticket = $this->getMockBuilder(Ticket::class)->disableOriginalConstructor()->setMethods(['getCustomData'])->getMock();
        $ticket->method('getCustomData')->willReturn($customDataCollection);

        $expectedCount = 3;
        $actualCount = 0;
        /** @var FieldManager $fieldManager */
        $fieldManager = $this->getMockBuilder(FieldManager::class)->disableOriginalConstructor()->setMethods(['setContextPerson'])->getMock();
        $fieldManager->removeSomeCustomDataOnObject(
            $ticket,
            $actualCustomDef,
            function( CustomDataAbstract $customData) use (&$actualCount) {
                $actualCount = $actualCount + 1;
                return $customData;
        } );
        $this->assertEquals($expectedCount, $actualCount);
    }
}
