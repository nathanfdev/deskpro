<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AddAgentNote;
use Application\DeskPRO\Tickets\ExecutorContext;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;

class AddAgentNoteTest extends DeskProTestCase
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private function getMockContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        $this->container = ContainerMock::create()
            ->withAgentData()
            ->withNullEm()
            ->withCleaner()
            ->get();

        return $this->container;
    }

    public function testAdd()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $tok    = sha1(microtime(true).mt_rand(10000, 99999));
        $action = new AddAgentNote(
            [
                'by_assigned_agent' => true,
                'by_agent_id'       => 1,
                'note_text'         => 'Test note '.$tok,
                'no_formatter'      => true,
            ]
        );
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertEquals(1, count($ticket->messages));
        $this->assertContains($tok, $ticket->messages[0]->message);
        $this->assertNotNull($ticket->messages[0]->person);
        $this->assertEquals(1, $ticket->messages[0]->person->id);
        $this->assertTrue($ticket->messages[0]->isAgentNote());
    }

    public function testEmojiTransformToHtmlEntities()
    {
        // GIVEN
        $cleaner = new \Orb\Input\Cleaner\Cleaner();
        $cleaner->addCleaner(new \Orb\Input\Cleaner\CleanerPlugin\HtmlPurifier());
        $conteiner = ContainerMock::create()
            ->withAgentData()
            ->withNullEm()
            ->withCleaner($cleaner)
            ->get();

        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new AddAgentNote(
            [
                'by_assigned_agent' => true,
                'by_agent_id'       => 1,
                'note_text'         => 'Test note 😀 😁',
                'no_formatter'      => true,
            ]
        );
        $action->setContainer($conteiner);

        // WHEN
        $action->applyAction($ticket, $exec);

        // THEN
        $this->assertEquals('Test note &#128512; &#128513;', $ticket->messages[0]->message);
    }
}
