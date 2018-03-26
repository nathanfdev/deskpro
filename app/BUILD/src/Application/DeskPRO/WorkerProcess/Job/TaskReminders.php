<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\Task;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Orb\Util\Dates;

/**
 * Will send daily task due reminders.
 */
class TaskReminders extends AbstractJob
{
    const DEFAULT_INTERVAL = 900;

    public function run()
    {
        //------------------------------
        // The time of day reminders are sent
        //------------------------------

        $sendTime = App::getSetting('core.task_reminder_time');
        if (!$sendTime || strpos($sendTime, ':') === false) {
            $sendTime = '09:00';
        }

        list($hour, $min) = explode(':', $sendTime);
        $hour             = (int) $hour;
        $min              = (int) $min;

        //------------------------------
        // Figure out which agents the current time is for
        //------------------------------

        $forAgents = [];
        foreach (App::getDataService('Agent')->getAgents() as $agent) {
            /* @var $agent \Application\DeskPRO\Entity\Person */

            $t       = $agent->getDateTime();
            $hourNow = (int) $t->format('G');
            $minNow  = (int) $t->format('i');

            if ($hourNow == $hour && $minNow >= $min) {
                $this->getLogger()->logInfo(sprintf(
                    'Running for agent %d %s (Local time %s is in range of %s)',
                    $agent->getId(),
                    $agent->getDisplayName(),
                    $t->format('H:i'),
                    $sendTime
                ));
                $forAgents[$agent->getId()] = $agent;
            }
        }

        if (!$forAgents) {
            return;
        }

        $onlineIds = App::getEntityRepository(Session::class)->getAvailableAgentIds();
        $emails    = 0;
        $alerts    = 0;

        foreach ($forAgents as $agent) {
            $agent->loadHelper('Agent');
            $teamIds = $agent->Agent->getTeamIds();
            if (!$teamIds) {
                $teamIds = [0];
            }

            $today = $agent->getDateTime();
            $today->setTime(0, 0, 0);

            $todayEnd = $agent->getDateTime();
            $todayEnd->setTime(23, 59, 59);

            $todayUtc    = Dates::convertToUtcDateTime($today);
            $todayEndUtc = Dates::convertToUtcDateTime($todayEnd);

            $taskIds = App::getDb()->fetchAllCol('
                SELECT tasks.id
                FROM tasks
                LEFT JOIN task_reminder_logs ON (task_reminder_logs.task_id = tasks.id)
                WHERE
                    tasks.is_completed = 0
                    AND (tasks.assigned_agent_id = ? OR tasks.assigned_agent_team_id IN (?) OR (tasks.assigned_agent_id IS NULL AND tasks.person_id = ?))
                    AND tasks.date_due >= ? AND tasks.date_due <= ?
                    AND task_reminder_logs.id IS NULL
            ',
                [$agent['id'], $teamIds, $agent['id'], $todayUtc->format('Y-m-d H:i:s'), $todayEndUtc->format('Y-m-d H:i:s')],
                [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY, \PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_STR]);

            if (!$taskIds) {
                continue;
            }

            $tasks = App::getEntityRepository(Task::class)->getByIds($taskIds);

            /** @var Task $task */
            foreach ($tasks as $task) {
                if (in_array($agent->id, $onlineIds) && $agent->getPref('agent_notif.task_due.alert')) {
                    $tplLine = App::getTemplating()->render('AgentBundle:Task:notify-row-reminder.html.twig', [
                        'task'   => $task,
                        'person' => $agent,
                    ]);

                    App::get('event_dispatcher')->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent('agent-notify.tasks', [
                            'row'    => $tplLine,
                            'target' => $agent->getId(),

                        ]));

                    ++$alerts;
                }

                // task is the thing that only agents see - so no matter which brand to use, pick default one
                $emailAccounts = App::$container->getEmailAccountManager();
                $out           = $emailAccounts->getDefaultOutAccountWithFallback();
                $fromEmail     = $out->getUseEmailAddress();

                if ($fromEmail && $agent->getPref('agent_notif.task_due.email')) {
                    if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                        $viewModel = App::$container->get('email.agent_viewmodel_factory')
                            ->createAgentTaskDueReminderModel($task);
                        App::$container->get('email.email_sender')
                            ->send($viewModel, ['to' => $agent]);
                    } else {
                        $message = App::getMailer()->createMessage();
                        $message->setTemplate(
                            'DeskPRO:emails_agent:task-due-reminder.html.twig',
                            [
                                'task'   => $task,
                                'person' => $agent,
                            ]
                        );
                        $message->setToPerson($agent);
                        $message->setFrom($fromEmail, App::getContainer()->getBrandSetting('core.deskpro_name'));
                        App::getMailer()->send($message);
                    }

                    ++$emails;
                }

                App::getDb()->insert('task_reminder_logs', [
                    'task_id'   => $task->getId(),
                    'person_id' => $agent->getId(),
                    'date_sent' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($emails || $alerts) {
            $this->getLogger()->logInfo("Reminders sent (alerts: $alerts, emails: $emails)");
        }
    }
}
