<?php

namespace Application\AgentBundle\Controller;

/**
 * The JiraController class
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class JiraController extends AbstractController
{
    /** @var array */
    protected $meta;

    public function preAction($action, $arguments = null)
    {
        if (!$this->settings->get('core.apps_jira.enabled')) {
            throw $this->createNotFoundException();
        }

        return parent::preAction($action, $arguments);
    }

    public function exportAction($ticket_id)
    {
        try	{
            $ticket = $this->getTicketOr404($ticket_id);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $ticket_id));
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
            } else {
                throw $e;
            }
        }

        if ('POST' === $this->request->getMethod()) {
            return $this->_processPost($ticket);
        }

        $meta = $this->getMeta();
        $projects = array();

        foreach ($meta[$meta['expand']] as $projectParams) {
            $projects[] = \Orb\Jira\Entity\Project::fromArray($projectParams);
        }

        $description = array();

        foreach ($ticket->messages as $message) {
            $created = get_object_vars($message->date_created);

            if ($message->person->is_user) {
                $personType = 'User';
            } elseif ($message->person->is_agent) {
                $personType = 'Agent';
            } else {
                $personType = 'System';
            }

            $person = $message->person . '(' . $personType . ')';

            $divider = PHP_EOL . PHP_EOL . str_repeat('-', 60) . PHP_EOL . PHP_EOL;

            $description[] = $person . ' at ' .
                    $created['date'] . PHP_EOL .
                    str_repeat('-', 60) . PHP_EOL . PHP_EOL .
                    strip_tags($message->message) .
                    $divider;
        }

        return $this->render('AgentBundle:Jira:export-overlay.html.twig', array(
            'ticket'		=> $ticket,
            'description'	=> implode('', $description),
            'projects'		=> $projects,
        ));
    }

    protected function _getService()
    {
        $baseUrl	= \Application\DeskPRO\App::getSetting('core.apps_jira.baseUrl');

        $username	= \Application\DeskPRO\App::getSetting('core.apps_jira.username');

        $password	= \Application\DeskPRO\App::getSetting('core.apps_jira.password');

        $service = new \Orb\Jira\Service($baseUrl, array(
            'username'	=> $username,
            'password'	=> $password,
            'debug'		=> DP_DEBUG,
            'reg_enabled' => $this->settings->get('core.reg_enabled'),
        ), $this->em);

        return $service;
    }

    public function lookupAction()
    {
        //$param		= $this->request->get('param');
        $projectKey	= $this->request->get('projectkey');

        $assignee	= $this->_lookupAssignee($projectKey);
        $issueTypes	= $this->_lookupIssueType($projectKey);
        $priorities	= $this->_lookupPriorities($projectKey);

        $payload = array(
            'assignee'		=> $assignee,
            'issuetypes'	=> $issueTypes,
            'priorities'	=> $priorities,
        );

        //echo json_encode($payload); die;
        return $this->render('AgentBundle:Jira:lookup.html.twig', array(
            'payload'	=> $payload
        ));
    }

    protected function _lookupAssignee($projectKey)
    {
        // init memory
        $meta = $this->getMeta($projectKey);

        if( ! isset($meta['assignee']) ) {
            $service = $this->_getService();
            $meta['assignee'] = array();

            foreach($service->lookupAssignees($projectKey) as $assignee) {
                $assignee['avatarUrls']['xsmall']	= $assignee['avatarUrls']['16x16'];
                $assignee['avatarUrls']['small']	= $assignee['avatarUrls']['24x24'];
                $assignee['avatarUrls']['medium']	= $assignee['avatarUrls']['32x32'];
                $meta['assignee'][$assignee['key']] = $assignee;
            }

            $this->meta['projects'][$projectKey] = $meta;
            $this->saveMeta();
        }

        return $meta['assignee'];
    }

    /**
     * todo join with meta build (do not iterate issuetypes twice)
     * @param $projectKey
     * @return array
     */
    protected function _lookupIssueType($projectKey)
    {
        // init memory
        $meta = $this->getMeta($projectKey);

        if( ! isset($meta['issuetypes']) ) {
            $service = $this->_getService();
            $meta['issuetypes'] = array();

            foreach( $service->lookupIssueType($projectKey) as $issueType ) {
                if( $issueType['subtask'] ) continue;
                $meta['issuetypes'][$issueType['id']] = $issueType;
            }

            $this->meta['projects'][$projectKey] = $meta;
            $this->saveMeta();
        }

        return $meta['issuetypes'];
    }

    protected function _lookupPriorities($projectKey)
    {
        // init memory
        $meta = $this->getMeta($projectKey);

        if( ! isset($meta['priorities']) ) {
            $meta['priorities'] = array();
            $service = $this->_getService();
            foreach( $service->lookupPriorities($projectKey) as $priority )
                $meta['priorities'][$priority['id']] = $priority;

            $this->meta['projects'][$projectKey] = $meta;
            $this->saveMeta();
        }

        return $meta['priorities'];
    }

    protected function getMeta($projectKey = null)
    {
        /** @var \Application\DeskPRO\EntityRepository\Cache $cache */
        $cache = $this->em->getRepository('DeskPRO:Cache');
        $key = 'jira.meta';

        // memory
        if( $this->meta ) {
            $meta = $this->meta;
        // file
        } else if( ! $meta = $cache->load($key) ) {
            $meta = $this->_getService()->getCreateMeta();
            $projects = array();
            foreach( $meta['projects'] as $project ) {
                $issues = array();
                foreach( $project['issuetypes'] as $issueType )
                    $issues[$issueType['id']] = $issueType;

                $project['issuetypes'] = $issues;
                $projects[$project['key']] = $project;
            }

            $meta['projects'] = $projects;
            $this->saveMeta($meta);
        }

        $this->meta = $meta;

        if( null === $projectKey )

            return $this->meta;

        return isset($this->meta['projects'][$projectKey]) ? $this->meta['projects'][$projectKey] : null;
    }

    protected function saveMeta($meta = null)
    {
        $this->meta = $meta ?: $this->meta;
        /** @var \Doctrine\Common\Cache\FilesystemCache $cache */
        $cache = $this->em->getRepository('DeskPRO:Cache');
        $key = 'jira.meta';
        $cache->save($key, $this->meta, 86400);
    }

    protected function _processPost(\Application\DeskPRO\Entity\Ticket $ticket)
    {
        $service = $this->_getService();

        $postParams = $this->request->request->all();

        $newIssue = new \Orb\Jira\Entity\Issue();

        /**
         * Process smart tags
         *
         * @agent: The current agent</li>
         * @department: The department in which the ticket is raised.</li>
         * @ticket: The current ticket</li>
         * @customer: The customer</li>
         *
         */

        $description = $postParams['description'];

        foreach ($this->_getSmartTags() as $tag) {
            $description = str_replace($tag, $this->_processTag($tag, $ticket), $description);
        }

        $newIssue->setDescription($description)
                ->setType($service->findIssueType($postParams['issuetype']))
                ->setTitle($postParams['title'])
                ->setProject($service->findProject($postParams['project']))
                ->setPriority($service->findPriority($postParams['priority']))
                ->setLabels(explode(',', $postParams['labels']));

        //var_dump($newIssue); die;

        $response = $service->persist($newIssue);

        if ($response && isset($response['id'])) {
            $newIssue->setId($response['id']);

            $newIssue->setKey($response['key']);

            $jiraIssue = new \Application\DeskPRO\Entity\JiraIssue();

            $jiraIssue->ticket = $ticket;

            $jiraIssue->issue = $newIssue->getId();

            $em = $this->em;

            $em->persist($jiraIssue);

            $em->flush();

            return $this->createJsonResponse($response);

        }

        return $this->createJsonResponse(
            array(
                'message'	=> 'There was an error in creatign the issue, please try again'
        ), 500);
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket
     */
    protected function getTicketOr404($ticket_id, $check_perm = null)
    {
        // todo clean
        $q = $this->em->createQuery("
            SELECT t, person, person_primary_email, agent,
                agent_team, language, department, product, category, workflow, priority,
                organization, locked_by_agent
            FROM DeskPRO:Ticket t
            LEFT JOIN t.person person
            LEFT JOIN person.primary_email person_primary_email
            LEFT JOIN t.agent agent
            LEFT JOIN t.agent_team agent_team
            LEFT JOIN t.language language
            LEFT JOIN t.department department
            LEFT JOIN t.product product
            LEFT JOIN t.category category
            LEFT JOIN t.workflow workflow
            LEFT JOIN t.priority priority
            LEFT JOIN t.organization organization
            LEFT JOIN t.locked_by_agent locked_by_agent
            WHERE t.id = ?0
        ");
        $q->setParameters(array($ticket_id));

        $ticket = $q->getOneOrNullResult();

        // If no ticket, check the delete log in case it was merged since
        if (!$ticket) {
            $merged_ticket_id = $this->em->getRepository('DeskPRO:Ticket')->findTicketId($ticket_id);
            if ($merged_ticket_id) {
                return $this->getTicketOr404($merged_ticket_id, $check_perm);
            }
        }

        if (!$ticket) {
            throw $this->createNotFoundException("There is no ticket with ID $ticket_id");
        }

        if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
            throw new \Application\DeskPRO\HttpKernel\Exception\NoPermissionException("You are not allowed to view this ticket");
        }

        if ($check_perm && !$this->checkPerm($ticket, $check_perm)) {
            throw new \Application\DeskPRO\HttpKernel\Exception\NoPermissionException("There is no ticket with ID $ticket_id");
        }

        return $ticket;
    }

    /**
     * Gets the JIRA issues associated with the given ticket
     * @param  type $ticket_id The DeskPRO ticket id
     * @return type
     */
    public function getAssociatedIssuesAction($ticket_id = null)
    {
        $service = $this->_getService();

        $ticket	 = $this->_getTicketById($ticket_id);

        $repository = $this->em->getRepository('Application\DeskPRO\Entity\JiraIssue');

        $jiraIssues = $repository->findBy(
            array('ticket' => $ticket
        ));

        if (!count($jiraIssues)) {
            return $this->render('AgentBundle:Jira:issues-table.html.twig', array(
                'ticket'		=> $ticket,
                'jirabaseurl'	=> \Application\DeskPRO\App::getSetting('core.apps_jira.baseUrl'),
                'issues'		=> array()
            ));
        }

        $transformedIssues = array();

        foreach ($jiraIssues as $issue) {
            $transformedIssues[] = $service->findIssue($issue->issue);
        }

        return $this->render('AgentBundle:Jira:issues-table.html.twig', array(
            'ticket'		=> $ticket,
            'jirabaseurl'	=> \Application\DeskPRO\App::getSetting('core.apps_jira.baseUrl'),
            'issues'		=> $transformedIssues
        ));
    }

    /**
     * Fetches all the comments on all associated JIRA issues
     */
    public function fetchAllCommentAction()
    {
        //Fetch all the exported JIRA Issues
        $jiraIssueRepository = $this->em->getRepository('Application\DeskPRO\Entity\JiraIssue');

        $jiraIssues = $jiraIssueRepository->findAll();

        foreach ($jiraIssues as $jiraIssue) {
            $issue_id = $jiraIssue->issue;

            $this->_fetchCommentsByIssueId($issue_id);
        }
    }

    /**
     * Fetches comments on given JIRA issue
     *
     * @param type $issue_id JIRA issue ID
     */
    private function _fetchCommentsByIssueId($issue_id)
    {
        $service = $this->_getService();

        $service->_fetchCommentsByIssueId($issue_id);
    }

    /**
     * Gets JIRA comments on the given Ticket
     */
    public function getCommentsAction($ticket_id = null)
    {
        try	{
            $ticket = $this->getTicketOr404($ticket_id);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $ticket_id));
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
            } else {
                throw $e;
            }
        }

        // Fetch all the JIRA issues associated with this Ticket
        $jiraIssueRepository = $this->em->getRepository('Application\DeskPRO\Entity\JiraIssue');

        $jiraIssues = $jiraIssueRepository->findBy(
            array('ticket' => $ticket
        ));

        foreach ($jiraIssues as $jiraIssue) {
            $this->_fetchCommentsByIssueId($jiraIssue->issue);
        }

        return new \Symfony\Component\HttpFoundation\Response();
    }

    /**
     * Posts a comment on JIRA
     *
     * @param  int        $issue_id Issue ID to post comment to
     * @return type
     * @throws \Exception if the comment is empty
     * @throws type
     */
    public function postCommentAction($issue_id = null)
    {
        $service = $this->_getService();

        $repository = $this->em->getRepository('Application\DeskPRO\Entity\JiraIssue');

        $success = 0;

        $jiraIssues = $repository->findOneBy(
            array('issue' => $issue_id
        ));

        if ($jiraIssues && $jiraIssues instanceof \Application\DeskPRO\Entity\JiraIssue) {

            if ('POST' === $this->request->getMethod()) {
                $postParams = $this->request->request->all();

                if (!isset($postParams['comment'])) {
                    throw new \Exception('Comment can not be empty');
                }

                $comment = $postParams['comment'];

                $repository	= $service->getRepository('\Orb\Jira\Entity\Repository\IssueRepository');

                $issue		= $service->findIssue($issue_id);

                $response = $repository->postComment($issue, $comment);

                if ($response) {
                    $service->fetchAllComment();

                    $success = 1;
                }

                return $this->createJsonResponse(array(
                    'success'	=> $success
                ));
            }

            return $this->render('AgentBundle:Jira:post-comment.html.twig', array(
                'issue_id'	=> $issue_id
            ));
        }

        throw $this->createNotFoundException('Invalid Issue ID');
    }

    /**
     * Unlinks a Jira Issue
     *
     * @param int    $ticket_id The corresponding ticket id
     * @param string $issue_id  The corresponding jira issue id
     */
    public function unlinkAction($ticket_id, $issue_id)
    {
        $ticket = $this->_getTicketById($ticket_id);

        $repository = $this->em->getRepository('Application\DeskPRO\Entity\JiraIssue');

        $jiraIssue = $repository->findOneBy(array(
            'ticket'	=> $ticket,
            'issue'		=> $issue_id
        ));

        if (!$jiraIssue) {
            throw $this->createNotFoundException('We couldn\'t find any associated JIRA issues with ticket ID: ' . $ticket_id);
        }

        if ('POST' === $this->request->getMethod()) {
            $service = $this->_getService();

            $jiraServiceRepository = $service->getRepository('\Orb\Jira\Entity\Repository\IssueRepository');

            if ($jiraServiceRepository && $jiraServiceRepository instanceof \Orb\Jira\Repository) {
                $comment = $this->request->request->get('comment');

                $issue		= $service->findIssue($issue_id);

                if (!empty($comment)) {
                    $jiraServiceRepository->postComment($issue, $comment);
                }

                $jiraServiceRepository->postComment($issue, 'Issue unlinked from DeskPRO by ' . $this->person->name);

                $issue->addLabel('Unlinked');

                $service->persist($issue);

                $this->em->remove($jiraIssue);

                $this->em->flush();

                return $this->createJsonResponse(array(
                    'message'	=> 'Issue unlinked successfully!',
                    'issue_id'	=> $issue_id
                ));
            } else {
                return $this->createJsonResponse(array(
                    'message'	=> 'There was a problem in processing your request, please try again'
                ), 500);
            }
        }

        return $this->render('AgentBundle:Jira:unlink.html.twig', array(
            'ticket'	=> $ticket,
            'issue_id'	=> $issue_id
        ));
    }

    protected function _getTicketById($ticket_id)
    {
        try	{
            $ticket = $this->getTicketOr404($ticket_id);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $ticket_id));
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
            } else {
                throw $e;
            }
        }

        return $ticket;
    }

    protected function _getAssociatedIssues($ticket_id)
    {
        $ticket	 = $this->_getTicketById($ticket_id);

        $repository = $this->em->getRepository('Application\DeskPRO\Entity\JiraIssue');

        return $repository->findBy(
            array('ticket' => $ticket
        ));
    }

    protected function _getSmartTags()
    {
        return array(
            '@agent',
            '@customer',
            '@department',
            '@ticket'
        );
    }

    protected function _processTag($tag, \Application\DeskPRO\Entity\Ticket $ticket)
    {
        $validTags = $this->_getSmartTags();

        if (!in_array($tag, $validTags)) {
            return (String) $tag;
        }

        /**
         * @agent: The current agent</li>
         * @department: The department in which the ticket is raised.</li>
         * @ticket: The current ticket</li>
         * @customer: The customer</li>
         */

        switch($tag) {
            case '@agent':
                return $this->person->name . '(' . $this->person->primary_email->email . ')';

            case '@department':
                return $ticket->department->title;

            case '@customer':
                return $ticket->person->name . '(' . $ticket->person->primary_email->email . ')';

            case '@ticket':
                return $ticket->subject . '(' . $ticket->ref . ')';
        }
    }
}
