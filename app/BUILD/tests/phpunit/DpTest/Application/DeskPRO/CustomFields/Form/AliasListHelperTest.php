<?php

namespace DpTest\Application\DeskPRO\CustomFields\Form\AliasListHelperTest;
use Application\DeskPRO\CustomFields\Form\AliasListHelper;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Aliases;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\CustomPeopleFieldDefinitionAlias;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\CustomTicketFieldDefinitionAlias;
use DpTest\DeskProTestCase;

class AliasListHelperTest extends DeskProTestCase
{
    public function testFindAdminAliasThrowsException()
    {
        $anAlias = new CustomPeopleFieldDefinitionAlias();
        $anAlias->setAlias('hei ho');
        $anAlias->setObject(new CustomDefPerson());


        $anotherAlias = new CustomTicketFieldDefinitionAlias();
        $anotherAlias->setAlias('hei ho');
        $anotherAlias->setObject(new CustomDefTicket());


        /** @var \PHPUnit_Framework_MockObject_MockObject | CustomDefAbstract $mock */
        $mock = $this->getMockBuilder(CustomDefAbstract::class)->setMethods(['getAliases'])->getMockForAbstractClass();
        $mock->method('getAliases')->willReturn([
            $anAlias,
            $anotherAlias
        ]);

        $actual = null;
        $helper = new AliasListHelper();
        try {
            $helper->findAdminAlias($mock);
        } catch (\RuntimeException $e) {
            $actual = $e;
        }

        $this->assertNotNull($actual);
    }

    public function testFindAdminAliasReturnsExpectedAlias()
    {
        $anAlias = new CustomPeopleFieldDefinitionAlias();
        $anAlias->setAlias('hei ho');
        $anAlias->setObject(new CustomDefPerson());


        $anotherAlias = new CustomTicketFieldDefinitionAlias();
        $anotherAlias->setAlias('hei yo');
        $anotherAlias->setObject(new CustomDefTicket());
        $anotherAlias->setAppInstance(new AppInstance());


        /** @var \PHPUnit_Framework_MockObject_MockObject | CustomDefAbstract $mock */
        $mock = $this->getMockBuilder(CustomDefAbstract::class)->setMethods(['getAliases'])->getMockForAbstractClass();
        $mock->method('getAliases')->willReturn([
            $anAlias,
            $anotherAlias
        ]);


        $helper = new AliasListHelper();
        $actual = $helper->findAdminAlias($mock);
        $this->assertEquals('hei ho', $actual);
    }

    public function testChangeAdminAliasThrowsException()
    {
        $anAlias = new CustomPeopleFieldDefinitionAlias();
        $anAlias->setAlias('hei ho');
        $anAlias->setObject(new CustomDefPerson());


        $anotherAlias = new CustomTicketFieldDefinitionAlias();
        $anotherAlias->setAlias('hei ho');
        $anotherAlias->setObject(new CustomDefTicket());


        /** @var \PHPUnit_Framework_MockObject_MockObject | CustomDefAbstract $mock */
        $mock = $this->getMockBuilder(CustomDefAbstract::class)->setMethods(['getAliases'])->getMockForAbstractClass();
        $mock->method('getAliases')->willReturn([
            $anAlias,
            $anotherAlias
        ]);

        $actual = null;
        $helper = new AliasListHelper();
        try {
            $helper->findAdminAlias($mock);
        } catch (\RuntimeException $e) {
            $actual = $e;
        }

        $this->assertNotNull($actual);
    }

    public function testChangeAdminAliasRemovesAlias()
    {
        $anAlias = new CustomPeopleFieldDefinitionAlias();
        $anAlias->setAlias('hei ho');
        $anAlias->setObject(new CustomDefPerson());


        $anotherAlias = new CustomTicketFieldDefinitionAlias();
        $anotherAlias->setAlias('hei yo');
        $anotherAlias->setObject(new CustomDefTicket());
        $anotherAlias->setAppInstance(new AppInstance());


        /** @var \PHPUnit_Framework_MockObject_MockObject | CustomDefAbstract $mock */
        $mock = $this->getMockBuilder(CustomDefAbstract::class)->setMethods(['getAliases'])->getMockForAbstractClass();
        $mock->method('getAliases')->willReturn([
            $anAlias,
            $anotherAlias
        ]);


        $helper = new AliasListHelper();
        $actual = $helper->changeAdminAlias($mock, '');
        $this->assertEquals(['hei yo'], $actual);
    }

    public function testChangeAdminAliasReplacesAlias()
    {
        $anAlias = new CustomPeopleFieldDefinitionAlias();
        $anAlias->setAlias('hei ho');
        $anAlias->setObject(new CustomDefPerson());


        $anotherAlias = new CustomTicketFieldDefinitionAlias();
        $anotherAlias->setAlias('hei yo');
        $anotherAlias->setObject(new CustomDefTicket());
        $anotherAlias->setAppInstance(new AppInstance());


        /** @var \PHPUnit_Framework_MockObject_MockObject | CustomDefAbstract $mock */
        $mock = $this->getMockBuilder(CustomDefAbstract::class)->setMethods(['getAliases'])->getMockForAbstractClass();
        $mock->method('getAliases')->willReturn([
            $anAlias,
            $anotherAlias
        ]);


        $helper = new AliasListHelper();
        $actual = $helper->changeAdminAlias($mock, 'hei go');
        $this->assertEquals(['hei go', 'hei yo'], $actual);
    }
}

