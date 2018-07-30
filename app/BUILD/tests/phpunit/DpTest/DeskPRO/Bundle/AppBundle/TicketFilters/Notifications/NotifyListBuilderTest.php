<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\TicketChange;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications\AgentFilterSubscriptionEvent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications\AgentSubscription;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications\AgentSubscriptionSet;
use DeskPRO\Bundle\AppBundle\TicketFilters\Notifications\NotifyListBuilder;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DpTest\Bundle\AppBundle\TicketFilters\Diff\FilterData;

require_once __DIR__.'/../Diff/FilterData.php';

class NotifyListBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $resolver = new ValueResolver();
        $matcher  = new TicketMatcher($resolver, [
            new TicketBasicTermsHandler($this->getMockBuilder(Elasticsearch::class)->disableOriginalConstructor()->getMock()),
        ]);

        $filters = FilterData::getFilters();

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->status     = 'awaiting_user';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketB->status     = 'awaiting_agent';
        $ticketB->department = 1;

        $agent        = $this->makeAgent(1, [1, 2, 3], false, true, true);
        $sub1         = new AgentSubscription();
        $sub1->type   = AgentSubscription::TYPE_EMAIL;
        $sub1->events = [AgentSubscription::REPLY_ANY];

        $sub2         = new AgentSubscription();
        $sub2->type   = AgentSubscription::TYPE_BROWSER;
        $sub2->events = [AgentSubscription::REPLY_ANY];

        $sub3               = new AgentSubscription();
        $sub3->type         = AgentSubscription::TYPE_EMAIL;
        $sub3->events       = [];
        $filterSub1         = new AgentFilterSubscriptionEvent();
        $filterSub1->filter = 1; // my tickets
        $filterSub1->events = [AgentFilterSubscriptionEvent::FILTER_MATCH];
        $sub3->filterEvents = [$filterSub1];

        $subset                = new AgentSubscriptionSet();
        $subset->agent         = $agent;
        $subset->subscriptions = [$sub1, $sub2, $sub3];

        // no sub
        $ticketChange = new TicketChange($ticketA, $ticketB);
        $listBuilder  = new NotifyListBuilder([$subset], $matcher, $filters);
        $res          = $listBuilder->getNotifyList($ticketChange);
        $this->assertEmpty($res->getAgentIds());

        // new reply sub
        $ticketB->new_messages[] = 1;
        $ticketChange            = new TicketChange($ticketA, $ticketB);
        $listBuilder             = new NotifyListBuilder([$subset], $matcher, $filters);
        $res                     = $listBuilder->getNotifyList($ticketChange);
        $this->assertEquals([1], $res->getAgentIds());
        $this->assertEquals([AgentSubscription::TYPE_EMAIL, AgentSubscription::TYPE_BROWSER], $res->getAgentTypes(1));

        // entering my tickets
        // stupid sub because you'd just sub on assign to self, but just using it to
        // test filter checking
        $ticketB->new_messages = []; // reset from above
        $ticketB->agent        = 1;
        $ticketChange          = new TicketChange($ticketA, $ticketB);
        $listBuilder           = new NotifyListBuilder([$subset], $matcher, $filters);
        $res                   = $listBuilder->getNotifyList($ticketChange);
        $this->assertEquals([1], $res->getAgentIds());
        $this->assertEquals([AgentSubscription::TYPE_EMAIL], $res->getAgentTypes(1));
    }

    private function makeAgent($id, $depids, $all, $other, $unassigned)
    {
        $agent                      = new Agent();
        $agent->id                  = $id;
        $agent->allowed_departments = $depids;
        if ($all) {
            $agent->all_departments_allowed = true;
        }
        $agent->view_assigned   = $other;
        $agent->view_unassigned = $unassigned;

        return $agent;
    }
}
