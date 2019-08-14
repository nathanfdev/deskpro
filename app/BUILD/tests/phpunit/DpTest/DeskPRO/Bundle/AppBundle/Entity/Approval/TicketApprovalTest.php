<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;

/**
 * Class TicketApprovalTest
 *
 * @package DpTest\DeskPRO\Bundle\AppBundle\Entity\Approval
 */
class TicketApprovalTest extends \PHPUnit_Framework_TestCase
{
    public function testApprovalStartsAsPending()
    {
        $approval = new TicketApproval();

        $this->assertEquals(TicketApproval::STATUS_PENDING, $approval->getStatus());
    }

    public function testApprovalCanBeCancelledIfPending()
    {
        $approval = new TicketApproval();

        $approval->cancel();

        $this->assertEquals(TicketApproval::STATUS_CANCELLED, $approval->getStatus());
        $this->assertNotNull($approval->getCancelledAt());
    }

    /**
     * @testWith ["pending", false]
     *           ["approved", true]
     *           ["rejected", true]
     *           ["cancelled", true]
     *
     * @param string $status
     * @param bool $expectsException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testApprovalCannotBeCancelledIfCompletedOrCancelled($status, $expectsException)
    {
        if ($expectsException) {
            $this->setExpectedException(
                \DomainException::class,
                'Cannot cancel approval if it is already completed or cancelled'
            );
        }

        $approval = new TicketApproval();

        $statusRef = (new \ReflectionObject($approval))
            ->getProperty('status')
        ;
        $statusRef->setAccessible(true);
        $statusRef->setValue($approval, $status);

        $approval->cancel();
    }

    /**
     * @testWith [[true, true, true, false, false], 3]
     *           [[true, true, true, true, true], 5]
     *           [[false, false, false, false, false], 0]
     *
     * @param bool[] $responses
     * @param int $expectedCount
     * @throws \ReflectionException
     */
    public function testGetApprovalResponsesOnlyGetsApprovalResponses($responses, $expectedCount)
    {
        $approval = new TicketApproval();

        $responsesRef = (new \ReflectionObject($approval))
            ->getProperty('responses')
        ;
        $responsesRef->setAccessible(true);

        $responsesRef->setValue($approval, new ArrayCollection(array_map(function ($outcome) {
            ($mock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing())
                ->shouldReceive('isApproved')->withNoArgs()->andReturn($outcome)
            ;
            return $mock;
        }, $responses)));

        $this->assertCount($expectedCount, $approval->getApproveResponses());
    }

    /**
     * @testWith [[true, true, true, false, false], 3]
     *           [[true, true, true, true, true], 5]
     *           [[false, false, false, false, false], 0]
     *
     * @param bool[] $responses
     * @param int $expectedCount
     * @throws \ReflectionException
     */
    public function testGetRejectResponsesOnlyGetsRejectResponses($responses, $expectedCount)
    {
        $approval = new TicketApproval();

        $responsesRef = (new \ReflectionObject($approval))
            ->getProperty('responses')
        ;
        $responsesRef->setAccessible(true);

        $responsesRef->setValue($approval, new ArrayCollection(array_map(function ($outcome) {
            ($mock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing())
                ->shouldReceive('isRejected')->withNoArgs()->andReturn($outcome)
            ;
            return $mock;
        }, $responses)));

        $this->assertCount($expectedCount, $approval->getRejectResponses());
    }

    public function testAddResponseOnlyWhenApprovalIsPending()
    {
        $approval = new TicketApproval();

        $this->assertEquals(TicketApproval::STATUS_PENDING, $approval->getStatus());
        $this->assertCount(0, $approval->getResponses());

        $approverMock = m::mock(Person::class)->shouldIgnoreMissing();
        $approverMock->shouldReceive('getId')->withNoArgs()->andReturn(1);

        $approval->addApproverId(1);

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);

        $approval->addResponse($responseMock);

        $this->assertCount(1, $approval->getResponses());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Person [4] is not listed as an approver for this approval
     *
     * @throws \Exception
     */
    public function testCannotAddResponseIfApproverIsNotInListOfApprovers()
    {
        $approval = new TicketApproval();

        $approval->addApproverId(1);
        $approval->addApproverId(2);
        $approval->addApproverId(3);

        $this->assertCount(0, $approval->getResponses());

        $approverMock = m::mock(Person::class)->shouldIgnoreMissing();
        $approverMock->shouldReceive('getId')->withNoArgs()->andReturn(4);

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);

        $approval->addResponse($responseMock);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Approver [2] has responded to this approval before
     *
     * @throws \Exception
     */
    public function testCannotAddResponseIfApproverHasAlreadyResponded()
    {
        $approval = new TicketApproval();

        $approval->addApproverId(1);
        $approval->addApproverId(2);

        $this->assertCount(0, $approval->getResponses());

        $approverMock = m::mock(Person::class)->shouldIgnoreMissing();
        $approverMock->shouldReceive('getId')->withNoArgs()->andReturn(2);

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);

        $approval->addResponse($responseMock);
        $this->assertCount(1, $approval->getResponses());
        $approval->addResponse($responseMock);

    }

    public function testAddResponseTimestampForLastApprovingResponse()
    {
        $approval = new TicketApproval();

        $approval->addApproverId(1);

        $approverMock = m::mock(Person::class)->shouldIgnoreMissing();
        $approverMock->shouldReceive('getId')->withNoArgs()->andReturn(1);

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);
        $responseMock->shouldReceive('isApproved')->withNoArgs()->andReturn(true);
        $responseMock->shouldReceive('isRejected')->withNoArgs()->andReturn(false);

        $approval->addResponse($responseMock);

        $this->assertNotNull($approval->getLastApprovedResponseAt());
        $this->assertNull($approval->getLastRejectResponseAt());
    }

    public function testAddResponseTimestampForLastRejectResponse()
    {
        $approval = new TicketApproval();

        $approval->addApproverId(1);

        $approverMock = m::mock(Person::class)->shouldIgnoreMissing();
        $approverMock->shouldReceive('getId')->withNoArgs()->andReturn(1);

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);
        $responseMock->shouldReceive('isApproved')->withNoArgs()->andReturn(false);
        $responseMock->shouldReceive('isRejected')->withNoArgs()->andReturn(true);

        $approval->addResponse($responseMock);

        $this->assertNull($approval->getLastApprovedResponseAt());
        $this->assertNotNull($approval->getLastRejectResponseAt());
    }

    public function testAddResponseAndComplete()
    {
        // todo: wrote test after AbstractBaseApproval::determineOutcome() is written
    }
}
