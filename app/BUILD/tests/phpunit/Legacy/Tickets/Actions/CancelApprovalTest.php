<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\CancelApproval;
use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Approval\ApprovalManager;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverCriteria;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\AppBundle\Entity\Repository\ApprovalRepository;
use DeskPRO\Bundle\AppBundle\Entity\Repository\TicketApprovalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

/**
 * Class CancelApprovalTest
 *
 * @package DpUnitTests\DeskPRO\Tickets\Actions
 */
class CancelApprovalTest extends DeskProTestCase
{
    /**
     * @throws \Exception
     */
    public function testApplyToSpecificTemplateAction()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        list ($containerMock, $approvalManagerSpy, $approvalRepoSpy) = $this->buildContainerMock($ticket, false);

        $action = new CancelApproval(['approval_template_id' => 1, 'all_approvals' => false]);
        $action->setContainer($containerMock);

        $action->applyAction($ticket, $exec);

        $approvalManagerSpy->shouldHaveReceived('cancelApproval');
        $approvalRepoSpy->shouldHaveReceived('getTicketApprovalsByTicketAndTemplate');
    }

    /**
     * @throws \Exception
     */
    public function testNotApplyToSpecificTemplateActionWhenTemplateNotFound()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        list ($containerMock, $approvalManagerSpy, $approvalRepoSpy) = $this->buildContainerMock($ticket, true);

        $action = new CancelApproval(['approval_template_id' => 1, 'all_approvals' => false]);
        $action->setContainer($containerMock);

        $action->applyAction($ticket, $exec);

        $approvalManagerSpy->shouldNotHaveReceived('cancelApproval'); // NoOp
        $approvalRepoSpy->shouldNotHaveReceived('getTicketApprovalsByTicketAndTemplate'); // NoOp
    }

    /**
     * @throws \Exception
     */
    public function testApplyToAllApprovalsAction()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        list ($containerMock, $approvalManagerSpy, $approvalRepoSpy) = $this->buildContainerMock($ticket, false);

        $action = new CancelApproval(['approval_template_id' => 1, 'all_approvals' => true]);
        $action->setContainer($containerMock);

        $action->applyAction($ticket, $exec);

        $approvalManagerSpy->shouldHaveReceived('cancelApproval');
        $approvalRepoSpy->shouldHaveReceived('getTicketApprovalsByTicket');
    }

    /**
     * @throws \Exception
     */
    public function testNotApplyToAllApprovalsActionWhereTemplateNotFound()
    {
        $ticket = new Ticket();
        $exec = new ExecutorContext();

        list ($containerMock, $approvalManagerSpy, $approvalRepoSpy) = $this->buildContainerMock($ticket, true);

        $action = new CancelApproval(['approval_template_id' => 1, 'all_approvals' => true]);
        $action->setContainer($containerMock);

        $action->applyAction($ticket, $exec);

        $approvalManagerSpy->shouldHaveReceived('cancelApproval'); // Should be received as template is redundant for this case
        $approvalRepoSpy->shouldHaveReceived('getTicketApprovalsByTicket'); // Should be received as template is redundant for this case
    }

    /**
     * @param Ticket $ticket
     * @param bool $returnNullTemplate
     * @return array
     * @throws \Exception
     */
    private function buildContainerMock(Ticket $ticket, $returnNullTemplate)
    {
        $approvals[] = (new TicketApproval())
            ->setTicket($ticket)
        ;

        $em = m::mock(EntityManagerInterface::class)->shouldIgnoreMissing();
        $approvalTemplateRepo = m::mock(EntityRepository::class)->shouldIgnoreMissing();
        $approvalRepo = m::spy(TicketApprovalRepository::class)->shouldIgnoreMissing();
        $template = m::mock(ApprovalTemplate::class)->shouldIgnoreMissing();
        $approverCriteria = m::mock(ApproverCriteria::class)->shouldIgnoreMissing();

        $approverCriteria->shouldReceive('getAgents')->withNoArgs()->andReturn([]);
        $approverCriteria->shouldReceive('getUsers')->withNoArgs()->andReturn([]);

        $template->shouldReceive('getType')->withNoArgs()->andReturn(
            m::mock(ApprovalType::class)->shouldIgnoreMissing()
        );

        $template->shouldReceive('getApproverCriteria')->withNoArgs()->andReturn($approverCriteria);

        $approvalTemplateRepo->shouldReceive('find')->withAnyArgs()->andReturn($returnNullTemplate ? null : $template);
        $approvalRepo->shouldReceive('getTicketApprovalsByTicket')->withAnyArgs()->andReturn($approvals);
        $approvalRepo->shouldReceive('getTicketApprovalsByTicketAndTemplate')->withAnyArgs()->andReturn($approvals);

        $em->shouldReceive('getRepository')->with(ApprovalTemplate::class)->andReturn($approvalTemplateRepo);
        $em->shouldReceive('getRepository')->with(TicketApproval::class)->andReturn($approvalRepo);

        $containerMock = ContainerMock::create()->get();

        $containerMock
            ->shouldReceive('getEm')
            ->withNoArgs()
            ->andReturn($em);

        $approvalManager = m::spy(ApprovalManager::class)->shouldIgnoreMissing();
        $execContext = m::mock(\DeskPRO\Bundle\AppBundle\Approval\ExecutorContext::class)->shouldIgnoreMissing();

        $approvalManager->shouldReceive('createContext')->withAnyArgs()->andReturn($execContext);

        $containerMock
            ->shouldReceive('get')
            ->with('approval.approval_manager')
            ->andReturn($approvalManager);

        return [$containerMock, $approvalManager, $approvalRepo];
    }
}
