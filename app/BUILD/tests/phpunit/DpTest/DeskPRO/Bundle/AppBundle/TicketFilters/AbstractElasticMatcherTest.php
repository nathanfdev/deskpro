<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\ElasticMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\PersonTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketDateTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use FOS\ElasticaBundle\Elastica\Index;

require_once __DIR__.'/AbstractMatcherTest.php';

/**
 * Class ElasticMatcherTest.
 */
abstract class AbstractElasticMatcherTest extends AbstractMatcherTest
{
    /**
     * @var Index
     */
    protected $index;

    /**
     * @var ElasticMatcher
     */
    protected $matcher;

    /**
     * @var Agent
     */
    protected $agent;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->agent        = new Agent();
        $this->agent->id    = 1;
        $this->agent->teams = [1, 2, 3];

        $this->index   = $this->getMockBuilder(Index::class)->disableOriginalConstructor()->getMock();
        $this->matcher = new ElasticMatcher(new ValueResolver(), $this->index, [
            new TicketBasicTermsHandler($this->getMockBuilder(Elasticsearch::class)->disableOriginalConstructor()->getMock()),
            new TicketDateTermsHandler(),
            new PersonTermsHandler($this->getMockBuilder(PersonRepository::class)->disableOriginalConstructor()->getMock()),
        ]);

        $this->matcher->disableContextPermissions();
    }

    /**
     * @param string $fql
     * @param array  $expectedQuery
     */
    public function assertEqualQuery($fql, array $expectedQuery)
    {
        $context = new Context($this->agent);
        $query   = $this->matcher->buildQuery($this->parseFql($fql), $context);

        $this->assertEquals($expectedQuery, $query->toArray());
    }
}
