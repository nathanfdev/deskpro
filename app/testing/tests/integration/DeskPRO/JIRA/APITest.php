<?php

namespace DpIntegrationTests\DeskPRO\JIRA;

use Application\DeskPRO\JIRA\ApiErrorsException;
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

    public function runBefore()
    {
        $this->helper->enableDestructiveDatabaseSet('ApiSampleDb');
        $this->helper->loadFixtures('JIRA/AppData');
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

    public function testJiraAPI()
    {
        $service = $this->js();
        $api = $service->getApi();

        // test API connection
        $response = $this->js()->getApi()->call('rest/auth/1/session', 'GET');
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('loginInfo', $response);
        $this->assertArrayHasKey('self', $response);
        $this->assertEquals('test-user', $response['name']);
        $this->assertEquals('https://deskpro.atlassian.net/rest/api/latest/user?username=test-user', $response['self']);

        // test meta
        $meta = $this->js()->getMeta();
        $this->assertInstanceOf('Application\DeskPRO\JIRA\Meta', $meta);
        $data = $meta->toArray();
        $this->assertEquals('test-user', @$data['api_username']);
        $this->assertArrayHasKey('projects', $data);

        $project = null;
        $issuetype = null;

        foreach ($data['projects'] as $val) {
            if ('Sandbox' === $val['name']) {
                $project = $val;
            }
        }

        $this->assertNotNull($project);
        $this->assertArrayHasKey('issuetypes', $project);

        foreach ($project['issuetypes'] as $val) {
            if ('My Issue Type' === $val['name']) {
                $issuetype = $val;
            }
        }

        $this->assertNotNull($issuetype);
        $this->assertArrayHasKey('fields', $issuetype);




        /** issues tests */

        $service->updateMeta(array('default_fields_summary' => array('comment')));

        $duedate = new \DateTime('+1 week');
        $user = 'test-user';
        $summary = 'Auto test issue';
        $url = 'http://example.com';
        $ticketGlobalId = 'deskpro_ticket_test';
        $commentBody = sprintf('[%s via DeskPRO #%s|%s]: test comment message', $user, $ticketGlobalId, $url);
        $renderedBody = '<p><a href="http://example.com" class="external-link" rel="nofollow">test-user via DeskPRO #deskpro_ticket_test</a>: test comment message</p>';

        // test create issue
        $issue = array(
            'fields' => array(
                'duedate' => $duedate->format('Y-m-d'),
                'issuetype' => array('id' => 10100),
                'project' => array('id' => 10200),
                'summary' => $summary,
            ),
        );


        // test validation error
        $invalidIssue = $issue;
        $invalidIssue['fields']['duedate'] = time() + 3600;
        $invalidIssue['fields']['summary'] = null;
        try {
            $service->createIssueJson(json_encode($invalidIssue));
        } catch (ApiErrorsException $e) {
            $this->assertArrayHasKey('duedate', $e->errors);
            $this->assertArrayHasKey('summary', $e->errors);
        }


        $issue = $service->createIssueJson(json_encode($issue));
        $this->assertArrayHasKey('id', $issue);
        $this->assertArrayHasKey('key', $issue);
        $this->assertArrayHasKey('self', $issue);

        // test add comment
        $api->post('/issue/' . $issue['id'] . '/comment?expand=renderedBody', array(
            'body' => $commentBody,
        ));

        // test search issue
        $searchResult = $service->searchIssues($issue['key']);
        $this->assertArrayHasKey('issues', $searchResult);
        $this->assertArrayHasKey(0, $searchResult['issues']);
        $issue = $searchResult['issues'][0];
        unset($searchResult);

        // test comment
        $this->assertArrayHasKey('fields', $issue);
        $this->assertArrayHasKey('comment', $issue['fields']);
        $this->assertArrayHasKey('comments', $issue['fields']['comment']);
        $this->assertCount(1, $issue['fields']['comment']['comments']);
        $this->assertArrayHasKey('renderedFields', $issue);
        $this->assertArrayHasKey('comment', $issue['renderedFields']);
        $this->assertArrayHasKey('comments', $issue['renderedFields']['comment']);
        $this->assertCount(1, $issue['renderedFields']['comment']['comments']);
        $this->assertEquals($renderedBody, $issue['renderedFields']['comment']['comments'][0]['body']);
        $comment = $issue['fields']['comment']['comments'][0];
        $this->assertEquals($commentBody, $comment['body']);
        $this->assertArrayHasKey('author', $comment);
        $this->assertArrayHasKey('name', $comment['author']);
        $this->assertEquals($user, $comment['author']['name']);

        // test create remote link
        $link = array(
            'globalId' => $ticketGlobalId,
            'relationship' => 'linked with',
            'object' => array(
                'title' => 'DeskPRO #test',
                'summary' => $summary,
                'url' => $url,
            ),
        );
        $link = $api->post('/issue/' . $issue['id'] . '/remotelink', $link);
        $this->assertArrayHasKey('id', $link);
        $this->assertArrayHasKey('self', $link);

        // test remove remote link
        $api->delete('/issue/' . $issue['id'] . '/remotelink?globalId=' . $ticketGlobalId);

        // cleanup
        $api->delete('/issue/' . $issue['id']);
    }
}
