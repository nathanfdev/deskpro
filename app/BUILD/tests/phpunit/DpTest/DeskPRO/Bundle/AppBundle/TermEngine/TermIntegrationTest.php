<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine;

use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalTermEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Log\TermEngineBufferHandler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DpTest\ApiTestCase;
use Faker\Factory;
use Faker\Generator;

/**
 * Class TermIntegrationTest.
 *
 * Base class for Term integration (i.e. involving DB and real checks) testing.
 *
 * Typically a Term's integration test will consist of:
 *
 * - setting up test data
 * - adding DBAL engine tests (use assertTermSelectsTickets())
 * - adding PHP engine tests (use assertTermSatisfies() and assertTermSatisfies())
 * - adding tests involving TicketsSelectCriteria, which is used in API to convert GET criteria into a Term
 *   (use assertTermSelectsTickets())
 */
abstract class TermIntegrationTest extends ApiTestCase
{
    const LOG_SERVICE = 'term_engine.log_handler';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Generator
     */
    protected $faker;

    /**
     * TermIntegrationTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (!$this->get(self::LOG_SERVICE) instanceof TermEngineBufferHandler) {
            $this->getContainer()->set(self::LOG_SERVICE, new TermEngineBufferHandler());
        }

        $this->em = $this->get('doctrine.orm.entity_manager');
    }

    /**
     * Show term engine log.
     */
    protected function showLog()
    {
        echo "\nTerm Engine log: ----------------- \n";
        $records = $this->get(self::LOG_SERVICE)->getRecords();
        foreach ($records as $record) {
            echo $record['level_name'], ': ', $record['formatted'], "\n";
        }
        echo "\n---------------------------------- \n\n";
    }

    /**
     * @param array $data
     *
     * @return Ticket
     */
    protected function dummyTicket($data = [])
    {
        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        $ticket->subject = $this->faker->text();
        foreach ($data as $prop => $value) {
            if ($prop === 'problems' && !$value instanceof ArrayCollection) {
                $value = new ArrayCollection($value);
            }

            $ticket->$prop = $value;
        }

        return $ticket;
    }

    /**
     * @param array $data
     *
     * @return Person
     */
    protected function dummyPerson($data = [])
    {
        $person       = new Person();
        $person->name = $this->faker->name;
        foreach ($data as $prop => $value) {
            $person->$prop = $value;
        }

        return $person;
    }

    /**
     * @param array $data
     *
     * @return Person
     */
    protected function dummyOrganization($data = [])
    {
        $organization       = new Organization();
        $organization->name = $this->faker->name;
        foreach ($data as $prop => $value) {
            $organization->$prop = $value;
        }

        return $organization;
    }

    /**
     * @param array $data
     *
     * @return Problem
     */
    protected function dummyProblem($data = [])
    {
        $problem        = new Problem();
        $problem->title = $this->faker->name;
        foreach ($data as $prop => $value) {
            $problem->$prop = $value;
        }

        return $problem;
    }

    /**
     * @param array $data
     *
     * @return CustomDefTicket
     */
    protected function dummyCustomDefTicket($data = [])
    {
        $def = new CustomDefTicket();
        $def->setTitle($this->faker->text());
        foreach ($data as $prop => $value) {
            $def->$prop = $value;
        }

        return $def;
    }

    /**
     * @param Ticket          $ticket
     * @param CustomDefTicket $def
     * @param string          $type
     * @param string          $value
     *
     * @throws \Exception
     *
     * @return CustomDataTicket
     */
    protected function dummyCustomDataTicket(Ticket $ticket, CustomDefTicket $def, $type, $value)
    {
        if (!in_array($type, ['value', 'input'])) {
            throw new \Exception('You can set only value or input on a CustomDataTicket instance');
        }
        $data = new CustomDataTicket();
        $data->setTicket($ticket);
        $data->setRootField($def);
        $data->setField($def);
        if ($type === 'value') {
            $data->setValue($value);
        } else {
            $data->setInput($value);
        }

        return $data;
    }

    /**
     * @param TermInterface $term
     * @param array         $tickets
     * @param Person|null   $me
     */
    protected function assertTermSelectsTickets(TermInterface $term, array $tickets, Person $me = null)
    {
        $tickets = array_map(function ($ticket) {
            return $ticket instanceof Ticket ? $ticket->getId() : (int) $ticket;
        }, $tickets);

        $selected = $this->execTerm($term, $me);
        $selected = array_map(function ($id) {
            return (int) $id;
        }, $selected);

        sort($tickets);
        sort($selected);

        // show log for a failing assertion
        if ($tickets !== $selected) {
            $this->showLog();
        }

        $this->assertEquals($tickets, $selected);
    }

    /**
     * @param TermInterface $term
     * @param Ticket        $ticket
     * @param Person|null   $me
     */
    protected function assertTermSatisfies(TermInterface $term, Ticket $ticket, Person $me = null)
    {
        $satisfies = $this->checkTerm($term, $ticket, $me);
        if (!$satisfies) {
            $this->showLog();
        }
        $this->assertTrue($satisfies);
    }

    /**
     * @param TermInterface $term
     * @param Ticket        $ticket
     * @param Person|null   $me
     */
    protected function assertTermNotSatisfies(TermInterface $term, Ticket $ticket, Person $me = null)
    {
        $satisfies = $this->checkTerm($term, $ticket, $me);
        if ($satisfies) {
            $this->showLog();
        }
        $this->assertFalse($satisfies);
    }

    /**
     * @param TermInterface $term
     * @param Person|null   $me
     *
     * @return array
     */
    private function execTerm(TermInterface $term, Person $me = null)
    {
        $me or $me = $this->dummyPerson();

        /** @var DbalTermEngine $engine */
        $engine        = $this->get('term_engine.dbal.engine');
        $context       = new TermEngineContext($me);
        $tickets_query = $engine->evaluate($term, $context);
        $ids           = $tickets_query->fetchIds();

        return $ids;
    }

    /**
     * @param TermInterface $term
     * @param Ticket        $ticket
     * @param Person|null   $me
     *
     * @return bool
     */
    private function checkTerm(TermInterface $term, Ticket $ticket, Person $me = null)
    {
        $me or $me = $this->dummyPerson();

        $filter             = new TicketFilter();
        $reflectionFilter   = new \ReflectionObject($filter);
        $reflectionFilterId = $reflectionFilter->getProperty('id');
        $reflectionFilterId->setAccessible(true);
        $reflectionFilterId->setValue($filter, uniqid());
        $filter->setTerm($term);

        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerEngine $engine */
        $engine  = $this->get('term_engine.php_ticket_checker.engine');
        $context = new TermEngineContext($me);
        $checker = $engine->evaluate($filter, $context);

        return $checker->isTicketMatch($ticket);
    }
}
