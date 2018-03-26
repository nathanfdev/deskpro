<?php

/**
 * DeskPRO AgentBubdle's Task Controller.
 *
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskComment;
use Application\DeskPRO\Form\Type\TaskType;
use Application\LegacyApiBundle\Controller\TasksController;
use Orb\Util\Arrays;
use Orb\Util\Dates;
use Orb\Util\Numbers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles viewing and editing tasks.
 */
class TaskController extends AbstractController
{
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if (!$this->settings->get(TasksController::KEY_ENABLED, 0)) {
            throw new NotFoundHttpException();
        }

        if (!$this->person->hasPerm('agent_tasks.use')) {
            throw new NotFoundHttpException();
        }

        parent::preActionHandler($request, $action, $arguments);
    }

    public function getSectionDataAction()
    {
        /** @var \Application\DeskPRO\EntityRepository\Task $taskRepository */
        $taskRepository = $this->em->getRepository('DeskPRO:Task');
        $person         = $this->person;

        $allTasks = [
            'total'      => $taskRepository->countPendingTasks($person),
            'overdue'    => $taskRepository->countOverdueTasks($person),
            'due_today'  => $taskRepository->countDueTodayTasks($person),
            'due_future' => $taskRepository->countDueFutureTasks($person),
        ];

        $personTasks = [
            'total'      => $taskRepository->countPendingTasksForPerson($person),
            'overdue'    => $taskRepository->countOverdueTasksForPerson($person),
            'due_today'  => $taskRepository->countDueTodayTasksForPerson($person),
            'due_future' => $taskRepository->countDueFutureTasksForPerson($person),
        ];

        $teamsTasks = [
            'total'      => $taskRepository->countPendingTaksForPersonTeams($person),
            'overdue'    => $taskRepository->countOverdueTasksForPersonTeams($person),
            'due_today'  => $taskRepository->countDueTodayTasksForPersonTeams($person),
            'due_future' => $taskRepository->countDueFutureTasksForPersonTeams($person),
        ];

        $delegatedTasks = [
            'total'      => $taskRepository->countPendingDelegatedTasksForPerson($person),
            'overdue'    => $taskRepository->countOverdueDelegatedTasksForPerson($person),
            'due_today'  => $taskRepository->countDueTodayDelegatedTasksForPerson($person),
            'due_future' => $taskRepository->countDueFutureDelegatedTasksForPerson($person),
        ];

        $sectionHtml = $this->renderView('AgentBundle:Task:window-section.html.twig', [
            'counts' => [
                'all'       => $allTasks,
                'person'    => $personTasks,
                'teams'     => $teamsTasks,
                'delegated' => $delegatedTasks,
            ],
        ]);

        return $this->createJsonResponse([
            'section_html' => $sectionHtml,
        ]);
    }

    /**
     * Render the new task form.
     *
     * @return Response html
     */
    public function newAction()
    {
        $agents     = $this->em->getRepository('DeskPRO:Person')->getAgents();
        $agentTeams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

        return $this->render('AgentBundle:Task:newtask.html.twig', [
            'agents'      => $agents,
            'agent_teams' => $agentTeams,
        ]);
    }

    /**
     * Create action for the new task. Which pass the data and the from to the _process method to save it in DB.
     *
     * @return Response json formated data
     */
    public function createAction()
    {
        $allTaskData = $this->in->getCleanValueArray('newtask', 'raw', 'discard');
        $tasks       = [];

        foreach ($allTaskData as $taskData) {
            $task = new Task();
            $form = $this->createForm(new TaskType(), $task, [
                'person' => $this->person,
            ]);

            if (!empty($taskData['ticket_id'])) {
                $taskData['ticket'] = $taskData['ticket_id'];
            }
            if (!empty($taskData['date_due'])) {
                if (!empty($taskData['time_due'])) {
                    $taskData['date_due'] .= ' '.$taskData['time_due'];
                } else {
                    $taskData['date_due'] .= ' 23:59:59';
                }
            }

            // remove extra
            $taskData = array_intersect_key($taskData, $form->all());
            $form->submit($taskData);

            if ($form->isValid()) {
                $this->em->persist($task);
                $tasks[] = $task;
            }
        }
        $this->em->flush();

        // todo postPersist event
        foreach ($tasks as $t) {
            $notify = new \Application\DeskPRO\Notifications\TaskAssignNotification($t);
            $notify->send();
        }

        $taskData = [];
        foreach ($tasks as $t) {
            $d = false;
            if ($t->date_due) {
                $d = clone $t->date_due;
                $d->setTimezone($this->person->getDateTimezone());
                $d = $d->format($this->container->getSetting('core.date_day'));
            }
            $taskData[] = [
                'id'       => $t->getId(),
                'title'    => $t->title,
                'date_due' => $d,
                'row_html' => $this->renderView('AgentBundle:Task:task-list-row.html.twig', ['task' => $t, 'noShowLinked' => true]),
            ];
        }

        return $this->createJsonResponse([
            'success' => true,
            'tasks'   => $taskData,
        ]);
    }

    /**
     * render the task list.
     *
     * @param string $search_type
     * @param string $search_category
     *
     * @return string view of the task list
     */
    public function taskListAction($search_type = null, $search_category = null)
    {
        $taskType = false;

        $perPage         = 100;
        $page            = $this->in->getUInt('page') ?: 1;
        $completedPage   = $this->in->getUInt('completed_page') ?: 1;
        $offset          = ($page - 1) * $perPage;
        $completedOffset = ($completedPage - 1) * $perPage;

        $hasNext          = false;
        $hasNextCompleted = false;
        $hasPrev          = $offset != 0;
        $hasPrevCompleted = $completedOffset != 0;

        /** @var \Application\DeskPRO\EntityRepository\Task $taskRepos */
        $taskRepos = $this->em->getRepository('DeskPRO:Task');

        switch ($search_type) {
            case 'own':
                $filterMethod = 'filterTasksForPerson';
                break;

            case 'team':
                $filterMethod = 'filterTaksForPersonTeams';
                break;

            case 'delegate':
                $filterMethod = 'filterDelegatedTasksForPerson';
                break;

            case 'all':
                $filterMethod = 'filterAllPendingTasks';
                break;
        }

        $tasks          = $taskRepos->$filterMethod($this->person, $search_category, $perPage + 1, $offset, 'incomplete');
        $completedTasks = $taskRepos->$filterMethod($this->person, $search_category, $perPage + 1, $completedOffset, 'complete');

        if (count($tasks) == $perPage + 1) {
            array_pop($tasks);
            $hasNext = true;
        }
        if (count($completedTasks) == $perPage + 1) {
            array_pop($completedTasks);
            $hasNextCompleted = true;
        }

        $agents     = $this->em->getRepository('DeskPRO:Person')->getAgents();
        $agentTeams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

        $tasksGrouped = null;
        $groupBy      = $this->in->getString('group_by');
        if ($groupBy == 'assigned') {
            $tasksGrouped = [];
            foreach ($tasks as $t) {
                /** @var Task $t */
                if ($t->getAssignedAgent()) {
                    $key   = 'agent:'.$t->getAssignedAgent()->getId();
                    $title = $t->getAssignedAgent()->getDisplayName();
                } elseif ($t->getAssignedAgentTeam()) {
                    $key   = 'agent_team:'.$t->getAssignedAgentTeam()->getId();
                    $title = $t->getAssignedAgentTeam()->getName();
                } elseif ($t->getPerson()) {
                    $key   = 'agent:'.$t->getPerson()->getId();
                    $title = $t->getPerson()->getDisplayName();
                } else {
                    $key   = '';
                    $title = 'Unassigned';
                }

                if (!isset($tasksGrouped[$key])) {
                    $tasksGrouped[$key] = ['title' => $title, 'tasks' => []];
                }

                $tasksGrouped[$key]['tasks'][] = $t;
            }

            $key = 'agent:'.$this->person->id;
            if (isset($tasksGrouped[$key])) {
                $tmp          = $tasksGrouped[$key];
                $tmp['title'] = 'Me';
                unset($tasksGrouped[$key]);
                Arrays::unshiftAssoc($tasksGrouped, $key, $tmp);
            }
        } elseif ($groupBy == 'creator') {
            $tasksGrouped = [];
            foreach ($tasks as $t) {
                /** @var Task $t */
                if ($t->getPerson()) {
                    $key   = 'agent:'.$t->getPerson()->getId();
                    $title = $t->getPerson()->getDisplayName();
                } else {
                    $key   = '';
                    $title = 'Unassigned';
                }

                if (!isset($tasksGrouped[$key])) {
                    $tasksGrouped[$key] = ['title' => $title, 'tasks' => []];
                }

                $tasksGrouped[$key]['tasks'][] = $t;
            }

            if (isset($tasksGrouped[$this->person->id])) {
                $tmp          = $tasksGrouped[$this->person->id];
                $tmp['title'] = 'Me';
                unset($tasksGrouped[$this->person->id]);
                Arrays::unshiftAssoc($tasksGrouped, $this->person->id, $tmp);
            }
        } else {
            $groupBy = 'date';

            $now = $this->person->getDateTime();

            $todayStart = clone $now;
            $todayStart->setTime(0, 0, 0);
            $todayStart = Dates::convertToUtcDateTime($todayStart);

            $today = clone $now;
            $today->setTime(23, 59, 59);
            $today = Dates::convertToUtcDateTime($today);

            $overdue = clone $now;
            $overdue = Dates::convertToUtcDateTime($overdue);

            $week = clone $now;
            $week->modify('-'.$now->format('w').' days');
            $week->modify('+7 days');
            $week->setTime(23, 59, 59);
            $week = Dates::convertToUtcDateTime($week);

            $month = clone $now;
            $month->setDate($now->format('Y'), $now->format('n'), 1);
            $month->modify('+1 month');
            $month->modify('-1 day');
            $month->setTime(23, 59, 59);
            $month = Dates::convertToUtcDateTime($month);

            $translator = $this->get('language_manager')->getTranslator();

            $tasksGrouped = [
                'overdue' => [
                    'title' => $translator->phrase('agent.tasks.overdue'),
                    'tasks' => [],
                ],
                'overdue_today' => [
                    'title' => $translator->phrase('agent.tasks.overdue_today'),
                    'tasks' => [],
                ],
                'today' => [
                    'title' => $translator->phrase('agent.time.today'),
                    'tasks' => [],
                ],
                'week' => [
                    'title' => $translator->phrase('agent.time.this_week'),
                    'tasks' => [],
                ],
                'month' => [
                    'title' => $translator->phrase('agent.time.this_month'),
                    'tasks' => [],
                ],
                'future' => [
                    'title' => $translator->phrase('agent.time.future'),
                    'tasks' => [],
                ],
            ];

            foreach ($tasks as $t) {
                if (!$t->date_due) {
                    $key = 'today';
                } elseif ($t->date_due >= $todayStart && $t->date_due < $overdue) {
                    $key = 'overdue_today';
                } elseif ($t->date_due <= $overdue) {
                    $key = 'overdue';
                } elseif ($t->date_due <= $today) {
                    $key = 'today';
                } elseif ($t->date_due <= $week) {
                    $key = 'week';
                } elseif ($t->date_due <= $month) {
                    $key = 'month';
                } else {
                    $key = 'future';
                }

                $tasksGrouped[$key]['tasks'][] = $t;
            }
        }

        $tpl = 'AgentBundle:Task:task-list.html.twig';

        return $this->render($tpl, [
            'agents'          => $agents,
            'agent_teams'     => $agentTeams,
            'tasks'           => $tasks,
            'completed_tasks' => $completedTasks,
            'tasks_grouped'   => $tasksGrouped,
            'task_type'       => $taskType,
            'search_type'     => $search_type,
            'search_category' => $search_category,
            'group_by'        => $groupBy,

            'page'               => $page,
            'has_next'           => $hasNext,
            'has_prev'           => $hasPrev,
            'completed_page'     => $completedPage,
            'has_next_completed' => $hasNextCompleted,
            'has_prev_completed' => $hasPrevCompleted,
        ]);
    }

    /**
     * Save labels for tasks.
     *
     * @param int $task_id
     *
     * @return Response json
     */
    public function ajaxSaveLabelsAction($task_id)
    {
        $task   = $this->getTaskOr404($task_id);
        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');
        $task->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($task);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    /**
     * Save the comment for tasks.
     *
     * @param int $task_id
     *
     * @return Response comment list in li format
     */
    public function ajaxSaveCommentAction($task_id)
    {
        $task = $this->getTaskOr404($task_id);

        $commentTxt = $this->in->getString('comment');

        if (!$commentTxt || !$task) {
            return $this->createJsonResponse([
                'error'      => true,
                'error_code' => 'no_message',
            ]);
        }

        $comment            = new TaskComment($this->person, $commentTxt);
        $comment['person']  = $this->person;
        $comment['task']    = $task;
        $comment['content'] = $commentTxt;

        $this->em->persist($comment);
        $this->em->flush();

        return $this->createJsonResponse([
            'success'         => true,
            'task_id'         => $task_id,
            'comment_li_html' => $this->renderView('AgentBundle:Task:comment-li.html.twig', ['comment' => $comment]),
        ]);
    }

    public function ajaxSaveAction($task_id)
    {
        $task = $this->getTaskOr404($task_id);

        switch ($this->in->getString('action')) {
            case 'title':
                $val = $this->in->getString('value');
                if (!$val || $task->getPersonId() !== $this->person['id']) {
                    break;
                }

                $task['title'] = trim($val);
                $this->em->flush();

                break;
            case 'comment':
                if (!$arr = json_decode($this->in->getString('value'), 1)) {
                    break;
                }
                if (empty($arr['value']) || empty($arr['id'])) {
                    break;
                }
                if (!$comment = $this->em->find('DeskPRO:TaskComment', $arr['id'])) {
                    break;
                }
                if ($comment->getPersonId() !== $this->person['id']) {
                    break;
                }

                $comment['content'] = trim($arr['value']);
                $this->em->flush();

                break;
            case 'date_due':
                if ($this->in->getString('value')) {
                    try {
                        $dateDue = new \DateTime($this->in->getString('value'), $this->person->getDateTimezone());
                        $dateDue->setTime(23, 59, 59);
                    } catch (\Exception $e) {
                        $dateDue = null;
                    }

                    if ($dateDue) {
                        $task->setDateDue(Dates::convertToUtcDateTime($dateDue));
                    } else {
                        $task->setDateDue(null);
                    }
                } else {
                    $task->setDateDue(null);
                }
                $this->em->flush();

                break;

            case 'time_due':
                if ($task->getDateDue()) {
                    $timeDue = $this->in->getString('value');
                    if (!empty($timeDue) && strpos($timeDue, ':') !== 0) {
                        list($hour, $min) = explode(':', $timeDue);
                        $hour             = (int) $hour;
                        $min              = (int) $min;
                        if (Numbers::inRange($hour, 0, 23) && Numbers::inRange($min, 0, 59)) {
                            $date = clone $task->getDateDue();
                            $date->setTimezone($this->person->getDateTimezone());
                            $date->setTime($hour, $min, 59);

                            $task->setDateDue(Dates::convertToUtcDateTime($date));
                        } else {
                            $date = clone $task->getDateDue();
                            $date->setTime(23, 59, 59);
                            $task->setDateDue($date);
                        }
                    } else {
                        $date = clone $task->getDateDue();
                        $date->setTime(23, 59, 59);
                        $task->setDateDue($date);
                    }
                }
                break;

            case 'visibility':
                $val = $this->in->getString('value');

                $task->setAssignedDepartment(null);
                $task->setVisibility(Task::PRIVATE_VISIBILITY);

                if ($val && is_string($val) && strpos($val, ':') !== false) {
                    list($type, $id) = explode(':', $val);
                    if ($type === 'department') {
                        $department = $this->getDoctrine()->getManager()->getRepository(Department::class)->find($id);
                        $task->setAssignedDepartment($department);
                    }
                } else {
                    $task->setVisibility($val);
                }

                break;

            case 'completed':
                $task->setCompleted($this->in->getBool('value'));

                if ($this->in->getBool('value')) {
                    $notify = new \Application\DeskPRO\Notifications\TaskCompleteNotification($task);
                    $notify->send();
                }
                break;

            case 'assigned':
                $val = $this->in->getString('value');

                $task->setAssignedAgent(null);
                $task->setAssignedAgentTeam(null);
                $task->setAssignedDepartment(null);

                if ($val && is_string($val) && strpos($val, ':') !== false) {
                    list($type, $id) = explode(':', $val);
                    if ($type == 'agent') {
                        $task->setAsignedAgentId($id);
                    } elseif ($type === 'agent_team') {
                        $task->setAsignedAgentTeamId($id);
                    }
                }

                $notify = new \Application\DeskPRO\Notifications\TaskAssignNotification($task);
                $notify->send();

                break;
        }

        $this->db->beginTransaction();
        try {
            $this->em->persist($task);
            $this->em->flush();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function printAssociativeTaskAction($assoc = null)
    {
        if (method_exists($assoc, 'getDeal') && $assoc->getDeal()) {
            return $this->render('AgentBundle:Task:dealAssoc.html.twig', ['assoc' => $assoc]);
        } elseif (method_exists($assoc, 'getTicket') && $assoc->getTicket() != null) {
            return $this->render('AgentBundle:Task:ticketAssoc.html.twig', ['assoc' => $assoc]);
        }
    }

    public function deleteTaskAction($task_id)
    {
        $task = $this->getTaskOr404($task_id);

        if ($task->person->getId() != $this->person->getId()) {
            throw $this->createNotFoundException();
        }

        $this->db->beginTransaction();
        try {
            $this->em->remove($task);
            $this->em->flush();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createJsonResponse(['success' => true]);
    }

    /**
     * @return \Application\DeskPRO\Entity\Task
     */
    protected function getTaskOr404($task_id)
    {
        $task = $this->em->find('DeskPRO:Task', $task_id);
        if (!$task) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no task with ID $task_id");
        }

        return $task;
    }

    public function iCalAction($id, $authcode, $filter)
    {
        /** @var Person $person */
        $person = Person::getRepository()->find($id);

        if (!$person) {
            throw $this->createNotFoundException('Invalid authcode');
        }

        $generatedAuthCode = sha1($person->secret_string.$person->password);

        if ($generatedAuthCode !== $authcode) {
            throw $this->createNotFoundException('Invalid authcode');
        }

        $tasks = [];

        switch ($filter) {
            case 'all':
                $tasks = $this->em->getRepository(Task::class)->filterAllPendingTasks($person);
                break;

            case 'assigned':
                $tasks = $this->em->getRepository(Task::class)->filterTasksForPerson($person);
                break;

            case 'delegated':
                $tasks = $this->em->getRepository(Task::class)->filterDelegatedTasksForPerson($person);
                break;

            default:
                break;
        }

        $language    = substr($person->getLanguage()->getLocale(), 0, 2);
        $deskProName = $this->container->getBrandSetting('core.deskpro_name');
        $vCalendar   = new \Eluceo\iCal\Component\Calendar('-//DeskPRO//'.$deskProName.'//'.strtoupper($language));

        foreach ($tasks as $task) {
            $vEvent = new \Eluceo\iCal\Component\Event();

            $vEvent
                ->setDtStart($task->date_due)
                ->setDtEnd($task->date_due)
                ->setNoTime(true)
                ->setSummary($task->title);

            $vCalendar->addEvent($vEvent);
        }

        $response = new \Symfony\Component\HttpFoundation\Response($vCalendar->render());

        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filter.'.ics"');

        return $response;
    }
}
