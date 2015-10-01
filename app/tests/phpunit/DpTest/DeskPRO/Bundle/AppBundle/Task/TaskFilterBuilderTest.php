<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpTest\Bundle\AppBundle\Task;

use DeskPRO\Bundle\AppBundle\Task\TaskFilterBuilder;
use Doctrine\ORM\EntityManager;
use DpTest\PortalTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class TaskFilterBuilderTest extends PortalTestCase
{
    const ADMIN_USER = 1;

    /**
     * @var null|EntityManager
     */
    protected $em = null;

    /**
     * Test set-up.
     */
    public function setUp()
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->em = $this->em->create(
            $this->em->getConnection(),
            $this->em->getConfiguration()
        );

        // Install the API dataset
        $this->installDataSet('api');
    }

    /**
     * Test that we can get tasks assigned to a particular user.
     */
    public function testValid()
    {
        // Set the request up
        $request = new ParameterBag([
            'assigned'   => 'me',                 // Check it can be assigned to "me" i.e. the admin
            'irrelevant' => 'parameter',          // Irrelevant parameters should be ignored
        ]);

        // Set the user to think of as "me"
        $person = $this->getUser(self::ADMIN_USER);

        // Create the filter and apply it
        $filter  = new TaskFilterBuilder($this->em, $person);
        $query = $filter->filterRequest($request);

        // Validate that we get a query back
        $this->assertInstanceOf('Doctrine\ORM\Query', $query);

        // Run the query
        $results = $query->getResult();
        $this->assertGreaterThan(0, count($results));
        foreach ($results as $result) {
            $this->assertNotEmpty($result->getAssigned());

            foreach ($result->getAssigned() as $assigned) {
                $this->assertNotEmpty($assigned->getPerson());
                $this->assertEquals(self::ADMIN_USER, $assigned->getPerson()->getId());
            };
        }
    }

    /**
     * Test that we can get tasks assigned to a particular user.
     */
    public function testValidUnassigned()
    {
        // Set the request up
        $request = new ParameterBag([
            'assigned'            => 'null',            // To get unassigned tasks we set everything to null
            'assigned_team'       => 'null',
            'assigned_department' => 'null',
        ]);

        // Set the user to think of as "me"
        $person = $this->getUser(self::ADMIN_USER);

        // Create the filter and apply it
        $filter  = new TaskFilterBuilder($this->em, $person);
        $results = $filter->filterRequest($request);

        // Validate that we get a query back
        $this->assertInstanceOf('Doctrine\ORM\Query', $results);

        $this->assertGreaterThan(0, count($results));

        // Run the query
        foreach ($results->getResult() as $result) {
            $this->assertEmpty($result->getAssigned());
        }
    }

    /**
     * Get an example person.
     *
     * @param int $userId
     *
     * @return mixed
     */
    private function getUser($userId)
    {
        $person = $this->em->getRepository('DeskPRO:Person')->find($userId);

        return $person;
    }
}
