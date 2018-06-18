<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\CustomFieldSet;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomData;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\OrgModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\PersonModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketSlaModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\CustomFieldsTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketDateTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketSlaTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\FilterQueryLanguage\Parser;

class TestValueResolver extends ValueResolver
{
    /**
     * {@inheritdoc}
     */
    public function fnNow()
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', '2017-12-04 14:09:00');
    }
}

class TicketMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TicketModel
     */
    private $ticket1;

    /**
     * @var TicketModel
     */
    private $ticket2;

    /**
     * @var Agent
     */
    private $agentContext;

    /**
     * @var Context
     */
    private $matcherContext;

    /**
     * @var TicketMatcher
     */
    private $matcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $resolver                  = new TestValueResolver();
        $this->agentContext        = new Agent();
        $this->agentContext->id    = 1;
        $this->agentContext->teams = [1, 2, 3];

        $this->matcherContext = new Context($this->agentContext);

        $this->matcher = new TicketMatcher($resolver, [
            new TicketBasicTermsHandler($this->getMockBuilder(Elasticsearch::class)->disableOriginalConstructor()->getMock()),
            new TicketSlaTermsHandler(),
            new TicketDateTermsHandler(),
            new CustomFieldsTermsHandler(new CustomFieldSet([
                new CustomField(1, 'choice', ['my_choice']),
                new CustomField(2, 'choice', ['my_other_choice']),
                new CustomField(3, 'text', ['my_text']),
                new CustomField(4, 'date', ['my_date']),
                new CustomField(5, 'toggle', ['my_toggle']),
                new CustomField(6, 'toggle', ['my_other_toggle']),
            ])),
        ]);

        // Ticket 1
        $this->ticket1                = new TicketModel();
        $this->ticket1->date_created  = \DateTime::createFromFormat('Y-m-d H:i:s', '2017-12-04 14:00:00');
        $this->ticket1->id            = 1;
        $this->ticket1->agent         = 1;
        $this->ticket1->agent_team    = 1;
        $this->ticket1->department    = 1;
        $this->ticket1->followers     = [100, 102];
        $this->ticket1->labels        = ['label1', 'label2'];
        $this->ticket1->custom_fields = [
            new CustomData(1, [10]),
            new CustomData(2, [20]),
            new CustomData(3, 'foo'),
            new CustomData(4, strtotime('2018-04-09 01:00:00')),
            new CustomData(5, 1),
        ];

        $this->ticket1->person         = new PersonModel();
        $this->ticket1->person->labels = ['plabel1', 'plabel2'];

        $this->ticket1->organization         = new OrgModel();
        $this->ticket1->organization->labels = ['olabel1', 'olabel2'];

        $this->ticket1->slas = [1, 2, 3];

        $slaPass         = new TicketSlaModel();
        $slaPass->sla_id = 1;
        $slaPass->status = 'ok';

        $slaWarn         = new TicketSlaModel();
        $slaWarn->sla_id = 2;
        $slaWarn->status = 'warning';

        $slaFail         = new TicketSlaModel();
        $slaFail->sla_id = 3;
        $slaFail->status = 'fail';

        $this->ticket1->slasInfo = [$slaPass, $slaWarn, $slaFail];

        // Ticket 2
        $this->ticket2                = new TicketModel();
        $this->ticket2->id            = 2;
        $this->ticket2->agent         = 2;
        $this->ticket2->agent_team    = 2;
        $this->ticket2->department    = 2;
        $this->ticket2->followers     = [103, 104];
        $this->ticket2->labels        = ['label3', 'label4'];
        $this->ticket2->custom_fields = [
            new CustomData(1, [10]),
            new CustomData(4, strtotime('2018-01-01 01:00:00')),
            new CustomData(5, 0),
        ];

        $this->ticket2->person         = new PersonModel();
        $this->ticket2->person->labels = ['plabel3', 'plabel4'];

        $this->ticket2->organization         = new OrgModel();
        $this->ticket2->organization->labels = ['olabel3', 'olabel4'];

        // Agent context
        $this->agentContext                      = new Agent();
        $this->agentContext->id                  = 2;
        $this->agentContext->allowed_departments = [1, 100];
        $this->agentContext->view_assigned       = true;
        $this->agentContext->view_unassigned     = true;
        $this->agentContext->teams               = [2, 3];
    }

    public function test_id_match()
    {
        $this->assertTrue($this->runTicket1Query('ticket.id = 1'));
        $this->assertTrue($this->runTicket1Query('ticket.id != 2'));
        $this->assertFalse($this->runTicket1Query('ticket.id != 1'));
        $this->assertFalse($this->runTicket1Query('ticket.id = 2'));
        $this->assertTrue($this->runTicket1Query('ticket.id < 10'));
        $this->assertFalse($this->runTicket1Query('ticket.id > 10'));
        $this->assertTrue($this->runTicket1Query('ticket.id <= 1'));
        $this->assertTrue($this->runTicket1Query('ticket.id BETWEEN 1 AND 100'));

        $this->assertFalse($this->runTicket2Query('ticket.id = 1'));
    }

    public function test_fn_match()
    {
        $this->assertTrue($this->runTicket1Query('ticket.date_created < NOW()'));
        $this->assertFalse($this->runTicket1Query('ticket.date_created > NOW()'));
        $this->assertTrue($this->runTicket1Query('ticket.date_created BETWEEN DATE("2017-12-04 13:00:00") AND DATE("2017-12-04 15:00:00")'));
    }

    public function test_sla_match()
    {
        $this->assertTrue($this->runTicket1Query('ticket.slas IN (1, 2, 3)'));
        $this->assertTrue($this->runTicket1Query('ticket.slas IN (4, 1, 2, 3)'));
        $this->assertFalse($this->runTicket1Query('ticket.slas IN (4, 5)'));
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS 1'));
        $this->assertFalse($this->runTicket1Query('ticket.slas HAS 4'));
    }

    public function test_sla_passing()
    {
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS passingSlas()'));
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS passingSlas(1, 3)'));
        $this->assertFalse($this->runTicket1Query('ticket.slas HAS passingSlas(3)'));
    }

    public function test_sla_warning()
    {
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS warningSlas()'));
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS warningSlas(2, 3)'));
        $this->assertFalse($this->runTicket1Query('ticket.slas HAS warningSlas(3)'));
    }

    public function test_sla_failing()
    {
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS failedSlas()'));
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS failedSlas(2, 3)'));
        $this->assertFalse($this->runTicket1Query('ticket.slas HAS failedSlas(1)'));
    }

    public function test_sla_multicheck()
    {
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS passingSlas() AND ticket.slas HAS warningSlas() AND ticket.slas HAS failedSlas()'));
        $this->assertFalse($this->runTicket1Query('ticket.slas HAS passingSlas() AND NOT ticket.slas HAS warningSlas()'));
        $this->assertTrue($this->runTicket1Query('ticket.slas HAS passingSlas() AND NOT ticket.slas HAS warningSlas(1)'));
    }

    public function test_null_check()
    {
        $this->assertTrue($this->runTicket1Query('ticket.product IS NULL'));
        $this->assertTrue($this->runTicket1Query('ticket.product IS NULL AND ticket.priority IS NULL'));
    }

    public function test_custom_choice_checks()
    {
        $this->assertTrue($this->runTicket1Query('ticket.data.1 HAS 10'));
        $this->assertTrue($this->runTicket1Query('ticket.data.1 = 10'));
        $this->assertTrue($this->runTicket1Query('ticket.data.1 != 11'));
        $this->assertTrue($this->runTicket1Query('ticket.data.1 IN (10, 11)'));
        $this->assertTrue($this->runTicket1Query('ticket.data.my_choice HAS 10'));
        $this->assertFalse($this->runTicket1Query('ticket.data.my_choice HAS 15'));

        $this->assertTrue($this->runTicket1Query('ticket.data.my_other_toggle != 1'));
        $this->assertTrue($this->runTicket1Query('ticket.data.my_other_toggle = 0'));
        $this->assertFalse($this->runTicket1Query('ticket.data.my_other_toggle != 0'));
        $this->assertFalse($this->runTicket1Query('ticket.data.my_other_toggle = 1'));

        $this->assertTrue($this->runTicket1Query('ticket.data.2 HAS 20'));
        $this->assertTrue($this->runTicket1Query('ticket.data.2 IN (20, 10)'));
        $this->assertTrue($this->runTicket1Query('ticket.data.my_other_choice HAS 20'));
        $this->assertFalse($this->runTicket1Query('ticket.data.my_other_choice HAS 15'));
    }

    public function test_custom_date_checks()
    {
        $this->assertTrue($this->runTicket1Query('ticket.data.4 < "2018-04-10"'));
        $this->assertTrue($this->runTicket1Query('ticket.data.4 > "2018-01-01"'));
        $this->assertTrue($this->runTicket1Query('ticket.data.my_date < "2018-04-10"'));
        $this->assertTrue($this->runTicket1Query('ticket.data.my_date > "2018-01-01"'));
    }

    public function test_custom_toggle_check()
    {
        $this->assertTrue($this->runTicket1Query('ticket.data.my_toggle = 1'));
        $this->assertFalse($this->runTicket1Query('ticket.data.my_toggle = 0'));

        $this->assertFalse($this->runTicket2Query('ticket.data.my_toggle = 1'));
        $this->assertTrue($this->runTicket2Query('ticket.data.my_toggle = 0'));
    }

    private function runTicket1Query($fql)
    {
        return $this->matcher->doesQueryMatch(
            $this->parseFql($fql),
            $this->ticket1,
            $this->matcherContext
        );
    }

    private function runTicket2Query($fql)
    {
        return $this->matcher->doesQueryMatch(
            $this->parseFql($fql),
            $this->ticket2,
            $this->matcherContext
        );
    }

    /**
     * @param $fql
     *
     * @return array
     */
    private function parseFql($fql)
    {
        $parser = new Parser();

        return $parser->parseQuery($fql);
    }
}
