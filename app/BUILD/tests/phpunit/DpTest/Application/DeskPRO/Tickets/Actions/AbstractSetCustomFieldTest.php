<?php
namespace DpTest\DeskPRO\Application\Tickets\Actions;


use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AbstractSetCustomField;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DpTest\DeskProTestCase;

class AbstractSetCustomFieldTest extends DeskProTestCase
{
    public function testResolveFieldId()
    {
        $ticket = new Ticket();
        $context = $this->getMockBuilder(ExecutorContextInterface::class)->getMockForAbstractClass();

        $operators = ['set', 'unset'];
        $idFields = ['field_id', 'field'];

        foreach ($operators as $operator) {
            foreach ($idFields as $idFieldKey) {

                $expectedValue = $operator === 'set' ? 'test' : null;
                $expectedKey = $idFieldKey === 'field_id' ? 'field_1' : 'fieldAlias';

                $actionOptions = [
                    $idFieldKey => $idFieldKey === 'field_id' ? '1' : 'fieldAlias',
                    'value' => $expectedValue,
                    'op' => $operator
                ];

                $manager = $this->getMockBuilder(FieldManager::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['saveFormToObject'])
                    ->getMock()
                ;
                $manager->method('saveFormToObject')->with($this->equalTo([$expectedKey => $expectedValue]));

                $action = $this->getMockBuilder(AbstractSetCustomField::class)
                    ->setConstructorArgs([$actionOptions])
                    ->setMethods(['getFieldManager', 'getApplicableObject'])
                    ->getMockForAbstractClass()
                ;
                $action->method('getFieldManager')->willReturn($manager);
                $action->method('getApplicableObject')->willReturn($ticket);

                $action->applyAction($ticket, $context);

            }
        }

        $operators = ['unset-list'];
        $idFields = ['field_id', 'field'];

        foreach ($operators as $operator) {
            foreach ($idFields as $idFieldKey) {

                $expectedFieldIdValue = $idFieldKey === 'field_id' ? '1' : 'fieldAlias';
                $actualCustomDef = new CustomDefTicket();

                $actionOptions = [
                    $idFieldKey => $expectedFieldIdValue,
                    'value' => '200',
                    'op' => $operator
                ];

                $manager = $this->getMockBuilder(FieldManager::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getFieldFromId', 'removeSomeCustomDataOnObjectAndFlushChanges'])
                    ->getMock()
                ;
                $manager->method('getFieldFromId')->with($this->equalTo($expectedFieldIdValue))->willReturn($actualCustomDef);
                $manager->method('removeSomeCustomDataOnObjectAndFlushChanges')->with(
                    $this->equalTo($ticket),
                    $this->equalTo($actualCustomDef)
                );

                $action = $this->getMockBuilder(AbstractSetCustomField::class)
                    ->setConstructorArgs([$actionOptions])
                    ->setMethods(['getFieldManager', 'getApplicableObject'])
                    ->getMockForAbstractClass()
                ;
                $action->method('getFieldManager')->willReturn($manager);
                $action->method('getApplicableObject')->willReturn($ticket);

                $action->applyAction($ticket, $context);
            }
        }
    }
}

