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

        $approval->cancel(new Person());

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

        $approval->cancel(new Person());
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

        $approval->addApprover($this->makeApprover(1));

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);

        $approval->addResponse($responseMock);

        $this->assertCount(1, $approval->getResponses());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp  /(.*) is not listed as an approver for this approval/
     *
     * @throws \Exception
     */
    public function testCannotAddResponseIfApproverIsNotInListOfApprovers()
    {
        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));
        $approval->addApprover($this->makeApprover(2));
        $approval->addApprover($this->makeApprover(3));

        $this->assertCount(0, $approval->getResponses());

        $approverMock = m::mock(Person::class)->shouldIgnoreMissing();
        $approverMock->shouldReceive('getId')->withNoArgs()->andReturn(4);

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);

        $approval->addResponse($responseMock);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp /Approver, (.*), has responded to this approval before/
     *
     * @throws \Exception
     */
    public function testCannotAddResponseIfApproverHasAlreadyResponded()
    {
        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));
        $approval->addApprover($this->makeApprover(2));

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

        $approval->addApprover($this->makeApprover(1));

        $approval->addResponse($this->buildResponseMock(1, 'approve'));

        $this->assertNotNull($approval->getLastApprovedResponseAt());
        $this->assertNull($approval->getLastRejectResponseAt());
    }

    /**
     * @throws \Exception
     */
    public function testAddResponseTimestampForLastRejectResponse()
    {
        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));

        $approval->addResponse($this->buildResponseMock(1, 'reject'));

        $this->assertNull($approval->getLastApprovedResponseAt());
        $this->assertNotNull($approval->getLastRejectResponseAt());
    }

    public function testAddResponseAndCompleteApproved()
    {
        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));
        $approval->addApprover($this->makeApprover(2));

        $approval->setRequiredApprovals(1);
        $approval->setRequiredRejections(1);

        $approval->addResponse($this->buildResponseMock(1, 'approve'));

        $this->assertEquals(TicketApproval::STATUS_APPROVED, $approval->getStatus());

        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));
        $approval->addApprover($this->makeApprover(2));

        $approval->setRequiredApprovals(2);
        $approval->setRequiredRejections(1);

        $approval->addResponse($this->buildResponseMock(1, 'approve'));

        $this->assertEquals(TicketApproval::STATUS_PENDING, $approval->getStatus());

        $approval->addResponse($this->buildResponseMock(1, 'approve'));

        $this->assertEquals(TicketApproval::STATUS_APPROVED, $approval->getStatus());
    }

    public function testAddResponseAndCompleteRejected()
    {
        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));
        $approval->addApprover($this->makeApprover(2));

        $approval->setRequiredApprovals(1);
        $approval->setRequiredRejections(1);

        $approval->addResponse($this->buildResponseMock(1, 'reject'));

        $this->assertEquals(TicketApproval::STATUS_REJECTED, $approval->getStatus());

        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));
        $approval->addApprover($this->makeApprover(2));

        $approval->setRequiredApprovals(1);
        $approval->setRequiredRejections(2);

        $approval->addResponse($this->buildResponseMock(1, 'reject'));

        $this->assertEquals(TicketApproval::STATUS_PENDING, $approval->getStatus());

        $approval->addResponse($this->buildResponseMock(1, 'reject'));

        $this->assertEquals(TicketApproval::STATUS_REJECTED, $approval->getStatus());
    }

    public function testAddResponseAndCompleteRejectedIfApproversCountMatchesApproversRequired()
    {
        $approval = new TicketApproval();

        $approval->addApprover($this->makeApprover(1));
        $approval->addApprover($this->makeApprover(2));

        $approval->setRequiredApprovals(2);
        $approval->setRequiredRejections(0);

        $approval->addResponse($this->buildResponseMock(1, 'reject'));

        $this->assertEquals(TicketApproval::STATUS_REJECTED, $approval->getStatus());
    }

    /**
     * @param int $personId
     * @param string $type approve|reject
     * @return m\Mock|ApprovalResponse
     */
    private function buildResponseMock($personId, $type)
    {
        $approverMock = m::mock(Person::class)->shouldIgnoreMissing();
        $approverMock->shouldReceive('getId')->withNoArgs()->andReturn($personId);

        $responseMock = m::mock(ApprovalResponse::class)->shouldIgnoreMissing();
        $responseMock->shouldReceive('getApprover')->withNoArgs()->andReturn($approverMock);

        if ('approve' === $type) {
            $responseMock->shouldReceive('isApproved')->withNoArgs()->andReturn(true);
            $responseMock->shouldReceive('isRejected')->withNoArgs()->andReturn(false);
        } else {
            $responseMock->shouldReceive('isApproved')->withNoArgs()->andReturn(false);
            $responseMock->shouldReceive('isRejected')->withNoArgs()->andReturn(true);
        }

        return $responseMock;
    }

    /**
     * @param int $id
     * @return m\Mock|Person
     */
    private function makeApprover($id)
    {
        $person = m::mock(Person::class)->shouldIgnoreMissing();
        $person->shouldReceive('getId')->withNoArgs()->andReturn($id);

        return $person;
    }
}
