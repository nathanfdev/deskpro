<?php
namespace DpTest\DeskPRO\Application\Tickets\Actions;


use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AbstractSetCustomField;

use Application\DeskPRO\Tickets\Actions\SetTicketField;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DpTest\DeskProTestCase;
use Orb\Util\CheckedOptionsException;

class SetTicketFieldTest extends DeskProTestCase
{
    public function testEnsureRequired()
    {
        $expectedException = null;
        try {
            new SetTicketField();
        } catch (\Exception $e) {
            $expectedException = $e;
        }

        $this->assertInstanceOf(CheckedOptionsException::class, $expectedException);
        $this->assertEquals('Missing required options: field_id, value', $expectedException->getMessage());
    }

}

