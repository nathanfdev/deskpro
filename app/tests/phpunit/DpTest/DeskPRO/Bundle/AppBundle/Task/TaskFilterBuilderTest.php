<?php

namespace DpTest\Bundle\AppBundle\Task;

use DeskPRO\Bundle\AppBundle\Task\TaskFilterBuilder;
use Doctrine\ORM\EntityManager;
use DpTest\PortalTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class TaskFilterBuilderTest extends PortalTestCase
{
    const ADMIN_USER = 1;

    /**
     * @var array|ParameterBag
     */
    protected $request = [];

    /**
     * @var null|EntityManager
     */
    protected $em = null;

    /**
     * Test set-up
     */
    public function setUp()
    {
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')->getMock();
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        // Install the API dataset
        // TODO remove re-install flag
        $this->installDataSet('api');
    }

    /**
     * Test that we can get tasks assigned to a particular user
     */
    public function testValid()
    {
        // Set the request up
        $this->setRequest([
            'assigned' => 'me',                 // Check it can be assigned to "me" i.e. the admin
            'irrelevant' => 'parameter',        // Irrelevant parameters should be ignored
        ]);

        // Set the user to think of as "me"
        $person = $this->getUser(self::ADMIN_USER);

        // Create the filter and apply it
        $filter = new TaskFilterBuilder($this->em, $person);
        $results = $filter->filterRequest($this->request);

        // Validate that we get a query back
        $this->assertInstanceOf('Doctrine\ORM\Query', $results);

        $this->assertGreaterThan(0, count($results));

        // Run the query
        foreach ($results->getResult() as $result) {
            $this->assertNotEmpty($result->getAssigned());

            foreach ($result->getAssigned() as $assigned) {
                $this->assertNotEmpty($assigned->getPerson());
                $this->assertEquals(self::ADMIN_USER, $assigned->getPerson()->getId());
            };
        }
    }

    /**
     * Test that we can get tasks assigned to a particular user
     */
    public function testValidUnassigned()
    {
        // Set the request up
        $this->setRequest([
            'assigned' => 'null',            // To get unassigned tasks we set everything to null
            'assigned_team' => 'null',
            'assigned_department' => 'null',
        ]);

        // Set the user to think of as "me"
        $person = $this->getUser(self::ADMIN_USER);

        // Create the filter and apply it
        $filter = new TaskFilterBuilder($this->em, $person);
        $results = $filter->filterRequest($this->request);

        // Validate that we get a query back
        $this->assertInstanceOf('Doctrine\ORM\Query', $results);

        $this->assertGreaterThan(0, count($results));

        // Run the query
        foreach ($results->getResult() as $result) {
            $this->assertEmpty($result->getAssigned());
        }
    }

    /**
     * Set what parameters form the request
     * @param array $request
     */
    protected function setRequest(array $request)
    {
        $this->request->method('all')->willReturn($request);
    }

    /**
     * Get an example person
     * @param int $userId
     * @return mixed
     */
    private function getUser($userId)
    {
        $person = $this->em->getRepository('DeskPRO:Person')->find($userId);
        return $person;
    }
}
