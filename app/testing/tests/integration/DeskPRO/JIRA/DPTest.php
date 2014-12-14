<?php

namespace DpIntegrationTests\DeskPRO\JIRA;

use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\JIRA\WebhookHandler;
use Application\DeskPRO\Service\JIRA;
use Doctrine\ORM\EntityRepository;

class APITest extends \DpIntegrationTestCase
{
    /**
     * @var EntityRepository
     */
    protected $rep;

    /**
     * @var JIRA
     */
    protected $service;

    protected $data;

    public function runBefore()
    {
        $this->helper->enableDestructiveDatabaseSet('ApiSampleDb');
        $this->helper->loadFixtures('JIRA/AppData');
        $this->helper->loadFixtures('JIRA/TriggerData');

        $this->data = array(
            'duedate' => new \DateTime('+1 week'),
            'user' => 'test-user',
            'summary' => 'Auto test issue',
            'url' => 'http://example.com',
            'ticket_global_id' => 'deskpro_ticket_test',

            'webhook' => array(

            ),
        );
    }

    /**
     * @return JIRA
     */
    protected function js()
    {
        if (!$this->service) {
            $this->service = $this->helper->getSymfonyContainer()->get(JIRA::NAME);
        }
        return $this->service;
    }

    public function testJIRAFunctions()
    {
        $em = $this->helper->getSymfonyContainer()->getEm();
        $service = $this->js();
        $api = $service->getApi();
        $service->updateMeta(array('default_fields_summary' => array('comment')));
        /** @var Ticket $ticket */
        $ticket = $em->find('DeskPRO:Ticket', 1);
        /** @var Person $person */
        $person = $em->find('DeskPRO:Person', 1);

        $issue = array(
            'fields' => array(
                'duedate' => $this->data['duedate']->format('Y-m-d'),
                'issuetype' => array('id' => 10100),
                'project' => array('id' => 10200),
                'summary' => $this->data['summary'],
            ),
        );

        $response = $this->js()->createIssueJson(json_encode($issue));
        $this->assertArrayHasKey('id', $response);
        $service->link($ticket, $response['id'], $person);

        /** @var JiraIssue $issue */
        $issue = $em->getRepository('DeskPRO:JiraIssue')->findOneBy(array());
        $this->assertInstanceOf('Application\DeskPRO\Entity\JiraIssue', $issue);
        $this->assertEquals($ticket, $issue->ticket);

        $service->addComment('test comment message', $ticket['id'], $person, $issue['issue_id']);

        // test get ticket issues list
        $issues = $service->issues($ticket['id']);
        $this->assertArrayHasKey('issues', $issues);
        $this->assertArrayHasKey('total', $issues);
        $this->assertEquals(1, $issues['total']);

        $issueData = $issues['issues'][0];
        $this->assertEquals($issue['issue_id'], $issueData['id']);
        $this->assertArrayHasKey('fields', $issueData);
        $this->assertArrayHasKey('comment', $issueData['fields']);
        $this->assertArrayHasKey('comments', $issueData['fields']['comment']);
        $this->assertCount(1, $issueData['fields']['comment']['comments']);
        $body = $issueData['fields']['comment']['comments'][0]['body'];

        // test comment
        $pattern = '/^\[(' . $person->getDisplayName() . ') via DeskPRO \#(' . $ticket['id'] . ')[^\]]+\]\: (test comment message)$/';
        $this->assertEquals(1, preg_match_all($pattern, $body, $matches));



        // cleanup
        $this->assertTrue($service->removeRemoteIssueLink($issue));
        $api->delete('/issue/' . $issue['issue_id']);
    }

    public function testWebhookHandler()
    {
        // test webhook
        $handler = new WebhookHandler($this->helper->getSymfonyContainer());
        foreach ($this->data['webhook'] as $data) {
            $this->assertTrue($handler->handle($data));
        }
    }
}
