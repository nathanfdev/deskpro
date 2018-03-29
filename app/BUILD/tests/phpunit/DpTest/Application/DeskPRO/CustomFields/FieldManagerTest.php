<?php

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
