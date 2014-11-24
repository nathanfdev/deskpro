<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Service\JIRA;
use Application\DeskPRO\Tickets\ExecutorContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JIRAWebhookController extends AbstractController
{
    /**
     * JIRA webhook endpoint
     * @param Request $request
     * @return Response
     */
	public function handleAction(Request $request)
    {
        $response = new Response();
        $content = $request->getContent();

	    /** @var JIRA $js */
	    $js = $this->get(JIRA::NAME);
	    if (!$js->isEnabled()) {
		    return $response;
	    }

        if (!$json = json_decode($content, 1)) {
            return $response;
        }

        if (!isset($json['webhookEvent'])) {
            return $response;
        }

        if (!isset($json['issue'])) {
            return $response;
        }

        $method = 'on' . Container::camelize(str_replace('jira:', '', $json['webhookEvent']));
        if (!method_exists($this, $method)) {
            return new $response;
        }

        $this->{$method}($json);

        return $response;
    }

	/**
	 * @param array $data
	 * @throws \Exception
	 */
    protected function onIssueUpdated(array $data)
    {
	    $manager = $this->container->getTicketManager();
	    $em = $this->em;
	    $issues = $em->getRepository('DeskPRO:JiraIssue')->findBy(array('issue_id' => $data['issue']['id']));
	    $meta = $this->get(JIRA::NAME)->getMeta();

	    foreach ($issues as $issue) {
		    /** @var $issue JiraIssue */

		    if (!$performer = $issue->ticket->agent) {
			    $performer = $em->getRepository('DeskPRO:Person')->findOneBy(array('is_agent' => true, 'is_deleted' => false));
		    }

		    $state = $issue->ticket->getStateChangeRecorder();
		    $context = $manager->createSystemExecutorContext(ExecutorContext::EVENT_UPDATE, ExecutorContext::METHOD_API);
		    $context->setPersonContext($performer);

		    if (isset($data['comment']) && $meta->getApiUsername() !== $data['comment']['author']['name']) {
			    $state->recordData('jira.comment', $data['comment']);
			    $context->getUserVars()->set('jira.comment', $data['comment']['body']);
		    }

		    if (isset($data['changelog'])) {
			    foreach ($data['changelog']['items'] as $change) {
				    // skip comments as handled above
				    if ('comment' === $change['field']) continue;

				    // store new status if exists
				    if ('status' === $change['field'] && $issue['status_id'] != $change['to']) {
					    $issue['status_id'] = $change['to'];
					    $em->flush($issue);
				    }

				    $state->recordData('jira.' . $change['field'], $change);
			    }
		    }

		    if (!$state->isTrivialChangeSet()) {
			    $manager->saveTicket($issue->ticket, $context);
		    }
	    }
    }

	/**
	 * @param array $data
	 * @throws \Exception
	 */
	protected function onIssueDeleted(array $data)
	{
		$manager = $this->container->getTicketManager();
		$em = $this->em;
		$issues = $em->getRepository('DeskPRO:JiraIssue')->findBy(array('issue_id' => $data['issue']['id']));

		foreach ($issues as $issue) {
			$ticket = $issue->ticket;
			if (!$performer = $ticket->agent) {
				$performer = $em->getRepository('DeskPRO:Person')->findOneBy(array('is_agent' => true, 'is_deleted' => false));
			}
			$em->remove($issue);
			$em->flush($issue);

			$context = $manager->createSystemExecutorContext(ExecutorContext::EVENT_UPDATE, ExecutorContext::METHOD_API);
			$context->setPersonContext($performer);
			$manager->saveTicket($ticket, $context);
		}
	}
}
