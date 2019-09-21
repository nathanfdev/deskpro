<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketActions\AddApprovalAction;
use DeskPRO\Bundle\AppBundle\Approval\ApprovalManager;
use DeskPRO\Bundle\AppBundle\Approval\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverSelectionCriteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

/**
 * Class AddApprovalActionTest
 *
 * @package DpUnitTests\DeskPRO\Tickets\Actions
 */
class AddApprovalActionTest extends DeskProTestCase
{
    /**
     * @var DeskproContainer
     */
    protected $containerBefore;

    public function setUp()
    {
        $this->containerBefore = App::$container;
    }

    public function tearDown()
    {
        App::$container = $this->containerBefore;
    }

    /**
     * @throws \Exception
     */
    public function testApply()
    {
        $approvalManagerSpy = $this->setUpContainer(false);

        $ticket = new Ticket();

        $action = new AddApprovalAction(
            1,
            'test description'
        );

        $action->apply($ticket);

        $approvalManagerSpy->shouldHaveReceived('saveApproval');

        $this->assertEquals('test description', $action->getApproval()->getDescription());
    }

    /**
     * @throws \Exception
     */
    public function testApplyWithTemplateNotFound()
    {
        $approvalManager = $this->setUpContainer(true);

        $ticket = new Ticket();

        $action = new AddApprovalAction(
            1,
            'test description'
        );

        $action->apply($ticket);

        $approvalManager->shouldNotHaveReceived('saveApproval'); // NoOp
    }

    /**
     * @param bool $returnNullTemplate
     * @return m\Mock|ApprovalManager
     */
    private function setUpContainer($returnNullTemplate)
    {
        $em = m::mock(EntityManagerInterface::class)->shouldIgnoreMissing();
        $repo = m::mock(EntityRepository::class)->shouldIgnoreMissing();
        $template = m::mock(ApprovalTemplate::class)->shouldIgnoreMissing();
        $approverCriteria = m::mock(ApproverSelectionCriteria::class)->shouldIgnoreMissing();

        $approverCriteria->shouldReceive('getAgents')->withNoArgs()->andReturn([]);
        $approverCriteria->shouldReceive('getUsers')->withNoArgs()->andReturn([]);

        $template->shouldReceive('getType')->withNoArgs()->andReturn(
            m::mock(ApprovalType::class)->shouldIgnoreMissing()
        );

        $template->shouldReceive('getApproverCriteria')->withNoArgs()->andReturn($approverCriteria);

        $repo->shouldReceive('find')->withAnyArgs()->andReturn($returnNullTemplate ? null : $template);
        $em->shouldReceive('getRepository')->with(ApprovalTemplate::class)->andReturn($repo);

        $containerMock = ContainerMock::create()->get();

        $containerMock
            ->shouldReceive('getEm')
            ->withNoArgs()
            ->andReturn($em)
        ;

        $approvalManager = m::spy(ApprovalManager::class)->shouldIgnoreMissing();
        $execContext = m::mock(ExecutorContext::class)->shouldIgnoreMissing();

        $approvalManager->shouldReceive('createContext')->withAnyArgs()->andReturn($execContext);

        $containerMock
            ->shouldReceive('get')
            ->with('approval.approval_manager')
            ->andReturn($approvalManager)
        ;

        App::$container = $containerMock;

        return $approvalManager;
    }
}
