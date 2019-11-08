<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AddApproval;
use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Approval\ApprovalManager;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\SelectedApprovers;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

/**
 * Class AddApprovalTest
 *
 * @package DpUnitTests\DeskPRO\Tickets\Actions
 */
class AddApprovalTest extends DeskProTestCase
{
    /**
     * @throws \Exception
     */
    public function testApplyAction()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        list ($containerMock, $approvalManagerSpy) = $this->buildContainerMock(false);

        $action = new AddApproval(['approval_template_id' => 1, 'description' => 'action test description']);
        $action->setContainer($containerMock);

        $action->applyAction($ticket, $exec);

        $approvalManagerSpy->shouldHaveReceived('saveApproval');

        $this->assertEquals('action test description', $action->getApproval()->getDescription());
    }

    /**
     * @throws \Exception
     */
    public function testApplyActionWithTemplateNotFound()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        list ($containerMock, $approvalManagerSpy) = $this->buildContainerMock(true);

        $action = new AddApproval(['approval_template_id' => 1, 'description' => 'action test description']);
        $action->setContainer($containerMock);

        $action->applyAction($ticket, $exec);

        $approvalManagerSpy->shouldNotHaveReceived('saveApproval'); // NoOp
    }

    /**
     * @param bool $returnNullTemplate
     * @return array
     */
    private function buildContainerMock($returnNullTemplate)
    {
        $em = m::mock(EntityManagerInterface::class)->shouldIgnoreMissing();
        $repo = m::mock(EntityRepository::class)->shouldIgnoreMissing();
        $template = m::mock(ApprovalTemplate::class)->shouldIgnoreMissing();
        $selectedApprovers = m::mock(SelectedApprovers::class)->shouldIgnoreMissing();

        $selectedApprovers->shouldReceive('getPeople')->withNoArgs()->andReturn([]);

        $template->shouldReceive('getType')->withNoArgs()->andReturn(
            m::mock(ApprovalType::class)->shouldIgnoreMissing()
        );

        $template->shouldReceive('getSelectedApprovers')->withNoArgs()->andReturn($selectedApprovers);

        $repo->shouldReceive('find')->withAnyArgs()->andReturn($returnNullTemplate ? null : $template);
        $em->shouldReceive('getRepository')->with(ApprovalTemplate::class)->andReturn($repo);

        $containerMock = ContainerMock::create()->get();

        $containerMock
            ->shouldReceive('getEm')
            ->withNoArgs()
            ->andReturn($em)
        ;

        $approvalManager = m::spy(ApprovalManager::class)->shouldIgnoreMissing();
        $execContext = m::mock(\DeskPRO\Bundle\AppBundle\Approval\ExecutorContext::class)->shouldIgnoreMissing();

        $approvalManager->shouldReceive('createContext')->withAnyArgs()->andReturn($execContext);

        $containerMock
            ->shouldReceive('get')
            ->with('approval.approval_manager')
            ->andReturn($approvalManager)
        ;

        return [$containerMock, $approvalManager];
    }
}
