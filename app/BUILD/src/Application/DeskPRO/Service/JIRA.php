<?php

namespace Application\DeskPRO\Service;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\JIRA\Api;
use Application\DeskPRO\JIRA\ApiCoreException;
use Application\DeskPRO\JIRA\Meta;
use Application\DeskPRO\Tickets\StateChangeRecorder;
use Application\DeskPRO\Tickets\TicketManager;
use Composer\CaBundle\CaBundle;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\JiraApiExceptionEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class JIRA
{
    const NAME = 'dp.jira';

    const PARAM_COMMENTS = 'comments';
    const PARAM_META     = 'meta';
    const PARAM_URL      = 'url';
    const PARAM_CONSUMER = 'consumer_key';
    const PARAM_TOKENS   = 'oauth_tokens';
    const PARAM_KEY      = 'private_key';
    const SSL_AUTHORITY  = 'ssl_authority';

    protected $allowed = [
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
        'components',
        'versions',
    ];

    protected $allowed_custom = [
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
    ];

    /**
     * @var Container
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

    /**
     * @var EventLogger
     */
    protected $logger;

    public function __construct(Container $container)
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
            return;
        }

        return $app->getSetting(self::PARAM_CONSUMER);
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        if (!$app = $this->getApp()) {
            return;
        }

        return $app->getSetting(self::PARAM_URL);
    }

    public function getSSLAuthority()
    {
        if (!$app = $this->getApp()) {
            return;
        }

        $setting = $app->getSetting(self::SSL_AUTHORITY);
        if ($setting === 'default') {
            $cert = CaBundle::getBundledCaBundlePath();
            if (file_exists($cert)) {
                $setting = $cert;
            }
        } elseif ($setting === 'disabled') {
            $setting = false;
        } else {
            $setting = true;
        }

        return $setting;
    }

    /**
     * @return string|null
     */
    public function getPrivateKey()
    {
        if (!$app = $this->getApp()) {
            return;
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
        $rep = $this->container->get('doctrine.orm.entity_manager')->getRepository('DeskPRO:AppInstance');
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
            return [];
        }

        if (!$tokens = $app->getSetting(self::PARAM_TOKENS)) {
            return [];
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
        $this->container->get('doctrine.orm.entity_manager')->flush($app);
    }

    /**
     * @param array $properties
     *
     * @throws \Exception
     *
     * @return Meta
     */
    public function updateMeta(array $properties = [])
    {
        if (!$app = $this->getApp()) {
            return;
        }

        $meta = null;

        try {
            unset($properties['projects'], $properties['statuses'], $properties['fields'], $properties['api_username']);
            $api = $this->getApi();

            if ($metadata = $app->getSetting(self::PARAM_META)) {
                $properties = array_merge($metadata, $properties);
            }

            $session                    = $api->call('rest/auth/1/session');
            $properties['api_username'] = $session['name'];
            $meta                       = Meta::fromArray($properties);

            $fields     = $api->get('/field');
            $keys       = array_flip($this->allowed);
            $keysCustom = array_flip($this->allowed_custom);
            $fields     = array_filter($fields, function ($a) use ($keys, $keysCustom) {
                return isset($keys[$a['id']]) || (isset($a['schema']['custom']) && isset($keysCustom[$a['schema']['custom']]));
            });

            $meta->setFields(array_values($fields));
            $meta->setProjects($api->get('/project'));
            $meta->setStatuses($api->get('/status'));
            $meta->setIssuetypes($api->get('/issuetype'));

            $app->setSetting(self::PARAM_META, $meta->toArray());
            $this->container->get('doctrine.orm.entity_manager')->flush($app);
        } catch (\Exception $e) {
            $this->logException($e);
        }

        return $meta;
    }

    public function getCreateMeta($projectId = null)
    {
        try {
            if ($api = $this->getApi()) {
                $projectId = (int) $projectId;
                $params    = ['expand' => 'projects.issuetypes.fields'];
                if ($projectId) {
                    $params['projectIds'] = $projectId;
                }

                return $api->get('/issue/createmeta', $params);
            }
        } catch (\Exception $e) {
            $this->logException($e);
            throw $e;
        }

        return [];
    }

    /**
     * @return Meta|null
     */
    public function getMeta()
    {
        if (!$app = $this->getApp()) {
            return;
        }

        if ($metaData = $app->getSetting(self::PARAM_META)) {
            $meta = Meta::fromArray($metaData);
        } else {
            $meta = $this->updateMeta();
        }

        return $meta;
    }

    /**
     * @param string $q
     *
     * @throws \Exception
     *
     * @return array
     */
    public function searchIssues($q)
    {
        $q     = trim($q);
        $query = sprintf('summary ~ "%s*"', $q);

        // issue key
        if (preg_match('/^[A-Za-z]+\-\d+/', $q, $matches)) {
            $query = sprintf('issuekey = %s or ', mb_strtoupper(reset($matches))).$query;
        }

        try {
            $meta = $this->getMeta();

            return $this->getApi()->searchIssues($query, array_merge($meta->getAllFields(), $meta->getSystemFields()));
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    /**
     * @param array $ids
     *
     * @return array|null
     */
    public function searchByIds(array $ids)
    {
        try {
            return $this->getApi()->searchIssues(sprintf('id IN (%s)', implode(',', $ids)), $this->getMeta()->getAllFields());
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    /**
     * @param $issueId
     * @param Person $author
     * @param Ticket $ticket
     * @param $message
     *
     * @return mixed
     */
    public function createComment($issueId, Person $author, Ticket $ticket, $message)
    {
        try {
            $message = Strings::trimHtmlAdvanced($message);
            $message = strip_tags($message);
            $url     = $this->container->get('router')->generate('agent', [], RouterInterface::ABSOLUTE_URL)
                .'#app.tickets,t.o:'.$ticket['id'];

            return $this->getApi()->post('/issue/'.$issueId.'/comment?expand=renderedBody', [
                'body' => sprintf('[%s via DeskPRO #%d|%s]: %s', $author->getDisplayName(), $ticket['id'], $url, $message),
            ]);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    /**
     * @param Ticket $ticket
     * @param $issueId
     *
     * @return mixed
     */
    public function createRemoteIssueLink(Ticket $ticket, $issueId)
    {
        try {
            $url = $this->container->get('router')->generate('agent', [], RouterInterface::ABSOLUTE_URL)
                .'#app.tickets,t.o:'.$ticket['id'];

            $data = [
                'globalId'     => 'deskpro_ticket_'.$ticket['id'],
                'relationship' => 'linked with',
                'object'       => [
                    'title'   => 'DeskPRO #'.$ticket['id'],
                    'summary' => $ticket['subject'],
                    'url'     => $url,
                ],
            ];

            return $this->getApi()->post('/issue/'.$issueId.'/remotelink', $data);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    /**
     * @param JiraIssue $issue
     *
     * @return bool
     */
    public function removeRemoteIssueLink(JiraIssue $issue)
    {
        try {
            $this->getApi()->delete(
                '/issue/'.$issue['issue_id'].'/remotelink?globalId=deskpro_ticket_'.$issue['ticket_id']
            );

            return true;
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    /**
     * @param Ticket $ticket
     * @param        $issueId
     * @param Person $byPerson
     *
     * @throws \Exception
     *
     * @return array|null
     */
    public function link(Ticket $ticket, $issueId, Person $byPerson)
    {
        $rep = $this->container->get('doctrine.orm.entity_manager')->getRepository('DeskPRO:JiraIssue');

        // already linked
        if ($issue = $rep->findOneBy(['ticket' => $ticket['id'], 'issue_id' => $issueId])) {
            return;
        }

        // api error
        if (!$result = $this->searchByIds([$issueId])) {
            return;
        }

        // issue link on DP side
        $issue             = new JiraIssue();
        $issue['issue_id'] = $issueId;
        $fields            = $result['issues'][0]['fields'];
        if (isset($fields['status'])) {
            $issue['status_id'] = $fields['status']['id'];
        }
        $issue->ticket = $ticket;

        // create remote issue link on JIRA side
        $this->createRemoteIssueLink($ticket, $issueId);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $em->persist($issue);
        $em->flush($issue);

        // trigger an update event
        $manager = $this->container->get('ticket_manager');
        $state   = $ticket->getStateChangeRecorder();
        $context = $manager->createAppExecutorContext($this->getApp(), 'issue_update');

        $state->recordData('jira.linked', $result['issues'][0]);
        $manager->markAsManaged($ticket);
        $manager->saveTicket($ticket, $context);

        return $result;
    }

    /**
     * @param Ticket $ticket
     * @param        $issueId
     *
     * @throws \Exception
     *
     * @return bool|null
     */
    public function unlink(Ticket $ticket, $issueId)
    {
        $em    = $this->container->get('doctrine.orm.entity_manager');
        $rep   = $em->getRepository('DeskPRO:JiraIssue');
        $issue = $rep->findOneBy(['ticket' => $ticket['id'], 'issue_id' => $issueId]);
        if (!$issue) {
            return;
        }

        $this->removeRemoteIssueLink($issue);

        $em->remove($issue);
        $em->flush($issue);

        return true;
    }

    /**
     * @param $ticketId
     *
     * @return array|null
     */
    public function issues($ticketId)
    {
        $em     = $this->container->get('doctrine.orm.entity_manager');
        $issues = $em->getRepository('DeskPRO:JiraIssue')->findBy(['ticket' => $ticketId]);
        $map    = [];
        foreach ($issues as $issue) {
            $map[$issue['issue_id']] = $issue;
        }

        $result = null;
        if (!$map) {
            return $result;
        }

        try {
            $result = $this->searchByIds(array_keys($map));

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
     *
     * @return array
     */
    public function createIssueJson($data)
    {
        return $this->getApi()->createIssueJson($data);
    }

    /**
     * @param string $message
     * @param int    $ticketId
     * @param Person $performer
     * @param int    $issueId
     *
     * @throws \Exception
     *
     * @return array|void
     */
    public function addComment($message, $ticketId, Person $performer, $issueId = null)
    {
        $rep = $this->container->get('doctrine.orm.entity_manager')->getRepository('DeskPRO:JiraIssue');

        if (!$issueId) {
            if (!$issues = $rep->findBy(['ticket' => $ticketId])) {
                throw new NotFoundHttpException();
            }
        } else {
            if (!$issue = $rep->findOneBy(['ticket' => $ticketId, 'issue_id' => $issueId])) {
                throw new NotFoundHttpException();
            }
            $issues = [$issue];
        }

        $response = ['body' => ''];
        /** @var Ticket $ticket */
        $ticket = null;

        foreach ($issues as $issue) {
            $response = $this->createComment($issue['issue_id'], $performer, $issue->ticket, $message);
            $ticket   = $ticket ?: $issue->ticket;
        }

        /** @var TicketManager $manager */
        $manager = $this->container->get('ticket_manager');
        /** @var StateChangeRecorder $state */
        $state   = $issue->ticket->getStateChangeRecorder();
        $context = $manager->createAppExecutorContext($this->getApp(), 'issue_update');

        $state->recordData('jira.comment', $response);
        $context->getUserVars()->set('jira.comment', $response['body']);
        $manager->markAsManaged($ticket);
        $manager->saveTicket($ticket, $context);

        return $response;
    }

    /**
     * just a proxy for integration testing.
     *
     * @param $id
     * @param $json
     *
     * @return mixed
     */
    public function updateIssueJson($id, $json)
    {
        return $this->getApi()->updateIssueJson($id, $json);
    }

    /**
     * @param \Exception $e
     */
    protected function logException(\Exception $e)
    {
        if (!$this->logger) {
            /* @var EventLogger logger */
            $this->logger = $this->container->get('dp_sys.alerts.event_logger');
        }

        $this->logger->log(new JiraApiExceptionEvent($e));
    }
}
