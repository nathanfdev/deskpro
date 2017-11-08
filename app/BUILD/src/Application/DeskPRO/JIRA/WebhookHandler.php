<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\JIRA;

use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Service\JIRA;
use Application\DeskPRO\Tickets\StateChangeRecorder;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\JiraApiExceptionEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebhookHandler
{
    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function handle(array $data)
    {
        if (!isset($data['webhookEvent'])) {
            return false;
        }

        if (!isset($data['issue'])) {
            return false;
        }

        $method = 'on'.Container::camelize(str_replace('jira:', '', $data['webhookEvent']));
        if (!method_exists($this, $method)) {
            return false;
        }

        try {
            $this->{$method}($data);

            return true;
        } catch (\Exception $e) {
            /* @var EventLogger logger */
            $logger = $this->container->get('dp_sys.alerts.event_logger');
            $logger->log(new JiraApiExceptionEvent($e));

            return false;
        }
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function onIssueUpdated(array $data)
    {
        if (!$app = $this->container->get('deskpro.apps.manager')->getPackageApp('deskpro_jira')) {
            throw new NotFoundHttpException();
        }

        /** @var TicketManager $manager */
        $manager = $this->container->get('ticket_manager');
        $em      = $this->container->get('doctrine.orm.entity_manager');
        $issues  = $em->getRepository('DeskPRO:JiraIssue')->findBy(['issue_id' => $data['issue']['id']]);
        /** @var Meta $meta */
        $meta = $this->container->get(JIRA::NAME)->getMeta();

        foreach ($issues as $issue) {
            /* @var $issue JiraIssue */
            /** @var StateChangeRecorder $state */
            $state   = $issue->ticket->getStateChangeRecorder();
            $context = $manager->createAppExecutorContext($app, 'issue_update');

            if (isset($data['comment']) && $meta->getApiUsername() === $data['comment']['author']['name']) {
                $state->recordData('jira.comment', $data['comment']);
                $context->getUserVars()->set('jira.comment', $data['comment']['body']);
            }

            if (isset($data['changelog'])) {
                foreach ($data['changelog']['items'] as $change) {
                    // skip comments as handled above
                    if ('comment' === $change['field']) {
                        continue;
                    }

                    // store new status if exists
                    if ('status' === $change['field'] && $issue['status_id'] != $change['to']) {
                        $issue['status_id'] = $change['to'];
                        $em->flush($issue);
                    }

                    $state->recordData('jira.'.$change['field'], $change);
                }
            }

            if (!$state->isTrivialChangeSet() || isset($data['comment'])) {
                $manager->markAsManaged($issue->ticket);
                $manager->saveTicket($issue->ticket, $context);
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function onIssueDeleted(array $data)
    {
        if (!$app = $this->container->get('deskpro.apps.manager')->getPackageApp('deskpro_jira')) {
            throw new NotFoundHttpException();
        }

        /** @var TicketManager $manager */
        $manager = $this->container->get('ticket_manager');
        $em      = $this->container->get('doctrine.orm.entity_manager');
        $issues  = $em->getRepository('DeskPRO:JiraIssue')->findBy(['issue_id' => $data['issue']['id']]);

        foreach ($issues as $issue) {
            $ticket = $issue->ticket;
            $manager->markAsManaged($issue->ticket);

            $em->remove($issue);
            $em->flush($issue);

            $context = $manager->createAppExecutorContext($app, 'issue_delete');
            $manager->saveTicket($ticket, $context);
        }
    }
}
