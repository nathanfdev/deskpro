<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\DeskPRO\Service;


use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\JIRA\Api;
use Application\DeskPRO\JIRA\ApiCoreException;
use Application\DeskPRO\JIRA\Meta;
use Application\DeskPRO\Tickets\ExecutorContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JIRA
{
    const NAME = 'dp.jira';

	const PARAM_COMMENTS    = 'comments';
	const PARAM_META        = 'meta';
	const PARAM_URL         = 'url';
	const PARAM_CONSUMER    = 'consumer_key';
	const PARAM_TOKENS      = 'oauth_tokens';
    const PARAM_KEY         = 'private_key';

    protected $allowed = array(
        'project',
        'issuetype',
        'summary',
        'description',
        'created',
        'updated',
        'priority',
        'status',
        'labels',
        'environment',
        'comment',
        'resolution',
        'duedate',
    );

    protected $allowed_custom = array(
        'com.atlassian.jira.plugin.system.customfieldtypes:textfield',
        'com.atlassian.jira.plugin.system.customfieldtypes:textarea',
        'com.atlassian.jira.plugin.system.customfieldtypes:select',
        'com.atlassian.jira.plugin.system.customfieldtypes:multiselect',
        'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes',
        'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons',
        'com.atlassian.jira.plugin.system.customfieldtypes:labels',
        'com.atlassian.jira.plugin.system.customfieldtypes:datepicker',
        'com.atlassian.jira.plugin.system.customfieldtypes:datetime',
        'com.atlassian.jira.plugin.system.customfieldtypes:float',
        'com.atlassian.jira.plugin.system.customfieldtypes:url',
    );

    /**
	 * @var DeskproContainer
	 */
	protected $container;

	/**
	 * @var Api|null
	 */
	protected $api;

	/**
	 * @var AppInstance
	 */
	protected $app = false;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}

	/**
	 * @return mixed
	 */
	public function isEnabled()
	{
		return $this->getApp() && $this->getApp()->getSetting(self::PARAM_TOKENS);
	}

	/**
	 * @return string|null
	 */
	public function getConsumerKey()
	{
		if (!$app = $this->getApp()) {
			return null;
		}
		return $app->getSetting(self::PARAM_CONSUMER);
	}

	/**
	 * @return string|null
	 */
	public function getUrl()
	{
		if (!$app = $this->getApp()) {
			return null;
		}
		return $app->getSetting(self::PARAM_URL);
	}

	/**
	 * @return string|null
	 */
	public function getPrivateKey()
	{
		if (!$app = $this->getApp()) {
			return null;
		}

		return $app->getSetting(self::PARAM_KEY);
	}

	/**
	 * @return Api
	 */
	public function getApi()
	{
		if (!$this->api) {
			$this->api = new Api($this);
		}

		return $this->api;
	}

	/**
	 * @return AppInstance|null
	 */
	protected function getApp()
	{
		$rep = $this->container->getEm()->getRepository('DeskPRO:AppInstance');
		if (false === $this->app) {
			$this->app = $rep->getInstanceByName('deskpro_jira');
		}

		return $this->app;
	}

	/**
	 * @return mixed|null
	 */
	public function getTokens()
	{
		if (!$app = $this->getApp()) {
			return array();
		}

		if (!$tokens = $app->getSetting(self::PARAM_TOKENS)) {
			return array();
		}

		return $tokens;
	}

	/**
	 * @param array $tokens
	 */
	public function setTokens(array $tokens)
	{
		if (!$app = $this->getApp()) {
			return;
		}

		$app->setSetting(self::PARAM_TOKENS, $tokens);
		$this->container->getEm()->flush($app);
	}

	/**
	 * @param array $properties
	 * @return Meta
	 * @throws \Exception
	 */
	public function updateMeta(array $properties = array())
	{
		if (!$app = $this->getApp()) {
			return null;
		}

		try {

            unset($properties['projects'], $properties['fields'], $properties['api_username']);
            if ($metadata = $app->getSetting(self::PARAM_META)) {
                $properties = array_merge($metadata, $properties);
            }

			$session = $this->getApi()->call('rest/auth/1/session');
			$properties['api_username'] = $session['name'];

			$res = $this->getApi()->get('/issue/createmeta', array('expand' => 'projects.issuetypes.fields'));
			$priority = $this->getApi()->get('/priority');
			$fields = $this->getApi()->get('/field');
			$properties['statuses'] = $this->getApi()->get('/status');
			$properties['projects'] = isset($res['projects']) ? $res['projects'] : array();
			$properties['priorities'] = $priority;

            $keys = array_flip($this->allowed);
            $keysCustom = array_flip($this->allowed_custom);
            $fields = array_filter($fields, function($a) use ($keys, $keysCustom) {
                return isset($keys[$a['id']]) || (isset($a['schema']['custom']) && isset($keysCustom[$a['schema']['custom']]));
            });
			$properties['fields'] = array_values($fields);

			$meta = Meta::fromArray($properties);

			$app->setSetting(self::PARAM_META, $meta->toArray());
			$this->container->getEm()->flush($app);

		} catch (\Exception $e) {
			// todo
			throw $e;
		}

		return $meta;
	}

	/**
	 * @return Meta|null
	 */
	public function getMeta()
	{
		if (!$app = $this->getApp()) {
			return null;
		}

		if ($metaData = $app->getSetting(self::PARAM_META)) {
			$meta = Meta::fromArray($metaData);
		} else {
			$meta = $this->updateMeta();
		}

		return $meta;
	}


    /**
     * @param $jql
     * @return array
     * @throws \Exception
     */
    public function searchIssues($q)
    {
        $q = trim($q);
        $query = sprintf('summary ~ "%s*"', $q);

        // issue key
        if (preg_match('/^[A-Za-z]+\-\d+/', $q, $matches)) {
            $query = sprintf('issuekey = %s or ', mb_strtoupper(reset($matches))) . $query;
        }

        try {
            $meta = $this->getMeta();
            return $this->getApi()->searchIssues($query, array_merge($meta->getAllFields(), $meta->getSystemFields()));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param array $ids
     * @return array|null
     */
    public function searchByIds(array $ids)
    {
        try {
            return $this->getApi()->searchIssues(sprintf('id IN (%s)', implode(',', $ids)), $this->getMeta()->getAllFields());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param        $issueId
     * @param Person $author
     * @param Ticket $ticket
     * @param        $message
     * @throws \Exception
     * @throws \Exceptions
     */
	public function createComment($issueId, Person $author, Ticket $ticket, $message)
	{
        $url = $this->container->get('router')->generateUrl('agent', array(), true)
            . '#app.tickets,t.o:' . $ticket['id'];

		try {

			return $this->getApi()->post('/issue/' . $issueId . '/comment?expand=renderedBody', array(
				'body' => sprintf('[%s via DeskPRO #%d|%s]: %s', $author->getDisplayName(), $ticket['id'], $url, $message),
			));

		} catch (\Exceptions $e) {
			// todo
			throw $e;
		}
	}

    /**
     * @param Ticket $ticket
     * @param $issueId
     * @throws \Exception
     * @throws \Exceptions
     */
    public function createRemoteIssueLink(Ticket $ticket, $issueId)
    {
        try {

            $url = $this->container->get('router')->generateUrl('agent', array(), true)
                . '#app.tickets,t.o:' . $ticket['id'];

            $data = array(
                'globalId' => 'deskpro_ticket_' . $ticket['id'],
                'relationship' => 'linked with',
                'object' => array(
                    'title' => 'DeskPRO #' . $ticket['id'],
                    'summary' => $ticket['subject'],
                    'url' => $url,
                ),
            );

            return $this->getApi()->post('/issue/' . $issueId . '/remotelink', $data);

        } catch (\Exceptions $e) {
            // todo
            throw $e;
        }
    }

    /**
     * @param JiraIssue $issue
     * @throws \Exception
     * @throws \Exceptions
     */
    public function removeRemoteIssueLink(JiraIssue $issue)
    {
        try {
            $this->getApi()->delete(
                '/issue/' . $issue['issue_id'] . '/remotelink?globalId=deskpro_ticket_' . $issue['ticket_id']
            );
            return true;

        } catch (\Exceptions $e) {
            // todo
            throw $e;
        }
    }

    /**
     * @param Ticket $ticket
     * @param        $issueId
     * @param Person $byPerson
     * @return array|null
     * @throws \Exception
     * @throws \Exceptions
     */
    public function link(Ticket $ticket, $issueId, Person $byPerson)
    {
        $rep = $this->container->getEm()->getRepository('DeskPRO:JiraIssue');

        // already linked
        if ($issue = $rep->findOneBy(array('ticket' => $ticket['id'], 'issue_id' => $issueId))) {
            return null;
        }

        // api error
        if (!$result = $this->searchByIds(array($issueId))) {
            return null;
        }

        // issue link on DP side
        $issue = new JiraIssue();
        $issue['issue_id'] = $issueId;
        $fields = $result['issues'][0]['fields'];
        if (isset($fields['status'])) {
            $issue['status_id'] = $fields['status']['id'];
        }
        $issue->ticket = $ticket;

        // create remote issue link on JIRA side
        $this->createRemoteIssueLink($ticket, $issueId);

        $em = $this->container->getEm();

        $em->persist($issue);
        $em->flush($issue);

        // trigger an update event
        $manager = $this->container->getTicketManager();
        $state = $ticket->getStateChangeRecorder();
        $context = $manager->createAppExecutorContext($this->getApp(), 'issue_update');

        $state->recordData('jira.linked', $result['issues'][0]);
        $manager->markAsManaged($ticket);
        $manager->saveTicket($ticket, $context);

        return $result;
    }

    /**
     * @param Ticket $ticket
     * @param        $issueId
     * @return bool|null
     * @throws \Exception
     * @throws \Exceptions
     */
    public function unlink(Ticket $ticket, $issueId)
    {
        $em = $this->container->getEm();
        $rep = $em->getRepository('DeskPRO:JiraIssue');
        $issue = $rep->findOneBy(array('ticket' => $ticket['id'], 'issue_id' => $issueId));
        if (!$issue) {
            return null;
        }

        $this->removeRemoteIssueLink($issue);

        $em->remove($issue);
        $em->flush($issue);

        return true;
    }

    /**
     * @param $ticketId
     * @return array|null
     */
    public function issues($ticketId)
    {
        $em = $this->container->getEm();
        $issues = $em->getRepository('DeskPRO:JiraIssue')->findBy(array('ticket' => $ticketId));
        $map = array();
        foreach ($issues as $issue) {
            $map[$issue['issue_id']] = $issue;
        }

        $result = null;
        if (!$map) {
            return $result;
        }

        try {

            $result = $this->searchIssues(sprintf('id IN (%s)', implode(',', array_keys($map))));
            return $result;

        } catch (ApiCoreException $e) {

            foreach ($e->errors as $error) {
                if (!preg_match('/A value with ID \'(\d+)\' does not exist for the field \'id\'\./', $error, $matches)) {
                    continue;
                }
                if (isset($map[$matches[1]])) {
                    $em->remove($map[$matches[1]]);
                }
            }
            $em->flush();

            return $this->issues($ticketId);
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function createIssueJson($data)
    {
        return $this->getApi()->createIssueJson($data);
    }

    /**
     * @param        $message
     * @param        $ticketId
     * @param Person $performer
     * @param null   $issueId
     * @return array|void
     * @throws \Exception
     * @throws \Exceptions
     */
    public function addComment($message, $ticketId, Person $performer, $issueId = null)
    {
        $rep = $this->container->getEm()->getRepository('DeskPRO:JiraIssue');

        if (!$issueId) {
            if (!$issues = $rep->findBy(array('ticket' => $ticketId))) {
                throw new NotFoundHttpException;
            }
        } else {
            if (!$issue = $rep->findOneBy(array('ticket' => $ticketId, 'issue_id' => $issueId))) {
                throw new NotFoundHttpException;
            }
            $issues = array($issue);
        }

        $response = array('body' => '');
        /** @var Ticket $ticket */
        $ticket = null;

        foreach ($issues as $issue) {
            $response = $this->createComment($issue['issue_id'], $performer, $issue->ticket, $message);
            $ticket = $ticket ?: $issue->ticket;
        }

        $manager = $this->container->getTicketManager();
        $state = $issue->ticket->getStateChangeRecorder();
        $context = $manager->createAppExecutorContext($this->getApp(), 'issue_update');

        $state->recordData('jira.comment', $response);
        $context->getUserVars()->set('jira.comment', $response['body']);
        $manager->markAsManaged($ticket);
        $manager->saveTicket($ticket, $context);

        return $response;
    }

    /**
     * just a proxy for integration testing
     * @param $id
     * @param $json
     */
    public function updateIssueJson($id, $json)
    {
        return $this->getApi()->updateIssueJson($id, $json);
    }
} 