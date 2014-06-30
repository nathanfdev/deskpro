<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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


/**
 * DeskPRO AgentBubdle's Task Controller
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskComment;
use Orb\Util\Arrays;
use Orb\Util\Dates;
use Orb\Util\Numbers;

/**
 * Handles viewing and editing tasks
 */
class TaskController extends AbstractController
{
    public function getSectionDataAction()
	{
        $task_repository = $this->em->getRepository('DeskPRO:Task');
        $person = $this->person;

        $all_tasks = array(
            'total' => $task_repository->countPendingTasks($person),
            'overdue' => $task_repository->countOverdueTasks($person),
            'due_today' => $task_repository->countDueTodayTasks($person),
            'due_future' => $task_repository->countDueFutureTasks($person),
        );

        $person_tasks = array(
            'total' => $task_repository->countPendingTasksForPerson($person),
            'overdue' => $task_repository->countOverdueTasksForPerson($person),
            'due_today' => $task_repository->countDueTodayTasksForPerson($person),
            'due_future' => $task_repository->countDueFutureTasksForPerson($person),
        );

        $teams_tasks = array(
            'total' => $task_repository->countPendingTaksForPersonTeams($person),
            'overdue' => $task_repository->countOverdueTasksForPersonTeams($person),
            'due_today' => $task_repository->countDueTodayTasksForPersonTeams($person),
            'due_future' => $task_repository->countDueFutureTasksForPersonTeams($person),
        );

        $delegated_tasks = array(
            'total' => $task_repository->countPendingDelegatedTasksForPerson($person),
            'overdue' => $task_repository->countOverdueDelegatedTasksForPerson($person),
            'due_today' => $task_repository->countDueTodayDelegatedTasksForPerson($person),
            'due_future' => $task_repository->countDueFutureDelegatedTasksForPerson($person),
        );

        $section_html = $this->renderView('AgentBundle:Task:window-section.html.twig', array(
			'counts' => array(
				'all' => $all_tasks,
				'person' => $person_tasks,
				'teams' => $teams_tasks,
				'delegated' => $delegated_tasks,
			)
		));

        return $this->createJsonResponse(array(
            'section_html' => $section_html,
        ));
    }


    /**
     * Render the new task form.
     * @return html
     */
    public function newAction()
	{
		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

        return $this->render('AgentBundle:Task:newtask.html.twig', array(
			'agents' => $agents,
			'agent_teams' => $agent_teams,
        ));
    }

    /**
     * Create action for the new task. Which pass the data and the from to the _process method to save it in DB.
     * @return json formated data
     */
    public function createAction()
	{
        $all_task_data = $this->in->getCleanValueArray('newtask', 'raw', 'discard');

		$tasks = array();

		foreach ($all_task_data as $task_data) {

			$task_data['title'] = trim($task_data['title']);
			if (empty($task_data['title'])) {
				continue;
			}

			$task = new Task();
			$task->title = $task_data['title'];
			$task->person = $this->person;

			if (!empty($task_data['assigned_agent'])) {
				list ($type, $id) = explode(':', $task_data['assigned_agent']);
				if ($type == 'agent') {
					$task->setAsignedAgentId($id);
				} else {
					$task->setAsignedAgentTeamId($id);
				}
			}

			if (!empty($task_data['ticket_id'])) {
				$ticket = $this->em->find('DeskPRO:Ticket', $task_data['ticket_id']);

				$assoc = new \Application\DeskPRO\Entity\TaskAssociatedTicket();
				$assoc->ticket = $ticket;
				$assoc->task   = $task;

				$task->task_associations->add($assoc);
			}

			/*
            if (!empty($task_data['deal_id'])) {
				$deal = $this->em->find('DeskPRO:Deal', $task_data['deal_id']);

				$assoc = new \Application\DeskPRO\Entity\TaskAssociatedDeal();
				$assoc->deal = $deal;
				$assoc->task   = $task;

				$task->task_associations->add($assoc);
			}
			*/

			$task->setVisibility($task_data['visibility']);
			if (!empty($task_data['date_due'])) {
				try {
					$date_due = new \DateTime($task_data['date_due'], $this->person->getDateTimezone());

					if (!empty($task_data['time_due']) && strpos($task_data['time_due'], ':') !== 0) {
						list ($hour, $min) = explode(':', $task_data['time_due']);
						$hour = (int)$hour;
						$min = (int)$min;
						if (Numbers::inRange($hour, 0, 23) && Numbers::inRange($min, 0, 59)) {
							$date_due->setTime($hour, $min, 59);
						} else {
							$date_due->setTime(23, 59, 59);
						}
					} else {
						$date_due->setTime(23, 59, 59);
					}
				} catch (\Exception $e) {
					$date_due = null;
				}

				if ($date_due) {
					$task->date_due = Dates::convertToUtcDateTime($date_due);
				} else {
					$task->date_due = null;
				}
			} else {
				$task->date_due = null;
			}

			$tasks[] = $task;
		}

		$this->db->beginTransaction();
		try {
			foreach ($tasks as $t) {
				$this->em->persist($t);
			}

			$this->em->flush();

			foreach ($tasks as $t) {
				$notify = new \Application\DeskPRO\Notifications\TaskAssignNotification($t);
				$notify->send();
			}
			$this->em->flush();

			$this->db->commit();

		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		$task_data = array();

		foreach ($tasks as $t) {
			$d = false;
			if ($t->date_due) {
				$d = clone $t->date_due;
				$d->setTimezone($this->person->getDateTimezone());
				$d = $d->format($this->container->getSetting('core.date_day'));
			}
			$task_data[] = array(
				'id' => $t->getId(),
				'title' => $t->title,
				'date_due' => $d,
				'row_html' => $this->renderView('AgentBundle:Task:task-list-row.html.twig', array('task' => $t, 'noShowLinked' => true))
			);
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'tasks' => $task_data,
		));
    }

    /**
     * render the task list
     *
     * @param string $search_type
     * @param string $search_categoty
     * @return string view of the task list
     */
    public function taskListAction($search_type = null, $search_categoty = null)
    {
        $task_type = false;

		$per_page         = 50;
		$page             = $this->in->getUInt('page') ?: 1;
		$completed_page   = $this->in->getUInt('completed_page') ?: 1;
		$offset           = ($page - 1) * $per_page;
		$completed_offset = ($completed_page - 1) * $per_page;

		$has_next           = false;
		$has_next_completed = false;
		$has_prev           = $offset != 0;
		$has_prev_completed = $completed_offset != 0;

		/** @var \Application\DeskPRO\EntityRepository\Task $task_repos */
		$task_repos = $this->em->getRepository('DeskPRO:Task');

		switch ($search_type) {
			case 'own':
				$filter_method = 'filterTasksForPerson';
				break;

			case 'team':
				$filter_method = 'filterTaksForPersonTeams';
				break;

			case 'delegate':
				$filter_method = 'filterDelegatedTasksForPerson';
				break;

			case 'all':
				$filter_method = 'filterAllPendingTasks';
				break;
		}

		$tasks           = $task_repos->$filter_method($this->person, $search_categoty, $per_page+1, $offset, 'incomplete');
		$completed_tasks = $task_repos->$filter_method($this->person, $search_categoty, $per_page+1, $completed_offset, 'complete');

        $sort_fn = function($a, $b) {
			$a_time = $a->date_due ? $a->date_due->getTimestamp() : 0;
			$b_time = $b->date_due ? $b->date_due->getTimestamp() : 0;

			if ($a_time == $b_time) {
				return 0;
			}

			return ($a_time < $b_time) ? -1 : 1;
		};

		usort($tasks, $sort_fn);
		usort($completed_tasks, $sort_fn);

		if (count($tasks) == $per_page+1) {
			array_pop($tasks);
			$has_next = true;
		}
		if (count($completed_tasks) == $per_page+1) {
			array_pop($completed_tasks);
			$has_next_completed = true;
		}

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

		$tasks_grouped = null;
		$group_by = $this->in->getString('group_by');
		if ($group_by == 'assigned') {
			$tasks_grouped = array();
			foreach ($tasks as $t) {
				if ($t->assigned_agent) {
					$key = 'agent:' . $t->assigned_agent->id;
					$title = $t->assigned_agent->getDisplayName();
				} elseif ($t->assigned_agent_team) {
					$key = 'agent_team:' . $t->assigned_agent_team->id;
					$title = $t->assigned_agent_team->getName();
				} else {
					$key = 'agent:' . $t->person->id;
					$title = $t->person->getDisplayName();
				}

				if (!isset($tasks_grouped[$key])) {
					$tasks_grouped[$key] = array('title' => $title, 'tasks' => array());
				}

				$tasks_grouped[$key]['tasks'][] = $t;
			}

			uasort($tasks_grouped, function($a, $b) {
				return strcmp($a['title'], $b['title']);
			});

			$key = 'agent:' . $this->person->id;
			if (isset($tasks_grouped[$key])) {
				$tmp = $tasks_grouped[$key];
				$tmp['title'] = 'Me';
				unset($tasks_grouped[$key]);
				Arrays::unshiftAssoc($tasks_grouped, $key, $tmp);
			}
		} else if ($group_by == 'creator') {
			$tasks_grouped = array();
			foreach ($tasks as $t) {
				$key = 'agent:' . $t->person->id;
				$title = $t->person->getDisplayName();

				if (!isset($tasks_grouped[$key])) {
					$tasks_grouped[$key] = array('title' => $title, 'tasks' => array());
				}

				$tasks_grouped[$key]['tasks'][] = $t;
			}

			uasort($tasks_grouped, function($a, $b) {
				return strcmp($a['title'], $b['title']);
			});

			if (isset($tasks_grouped[$this->person->id])) {
				$tmp = $tasks_grouped[$this->person->id];
				$tmp['title'] = 'Me';
				unset($tasks_grouped[$this->person->id]);
				Arrays::unshiftAssoc($tasks_grouped, $this->person->id, $tmp);
			}
		} else {
			$group_by = 'date';

			$now = new \DateTime();
			$today = clone $now;
			$today->setTime(23, 59, 59);

			$yesterday = clone $today;
			$yesterday->modify('-1 day');

			$week = clone $now;
			$week->modify("-" . $now->format('w') . ' days');
			$week->modify('+7 days');

			$month = clone $now;
			$month->setDate($now->format('Y'), $now->format('n'), 1);
			$month->modify('+1 month');
			$month->modify('-1 day');

			$tasks_grouped = array(
				'overdue' => array(
					'title' => 'Overdue',
					'tasks' => array()
				),
				'today' => array(
					'title' => 'Today',
					'tasks' => array()
				),
				'week' => array(
					'title' => 'This Week',
					'tasks' => array()
				),
				'month' => array(
					'title' => 'This Month',
					'tasks' => array()
				),
				'future' => array(
					'title' => 'Future',
					'tasks' => array()
				)
			);

			foreach ($tasks as $t) {
				if (!$t->date_due) {
					$key = 'today';
				} else if ($t->date_due < $yesterday) {
					$key = 'overdue';
				} else if ($t->date_due < $today) {
					$key = 'today';
				} else if ($t->date_due < $week) {
					$key = 'week';
				} else if ($t->date_due < $month) {
					$key = 'month';
				} else {
					$key = 'future';
				}

				$tasks_grouped[$key]['tasks'][] = $t;
			}
		}

        $tpl = 'AgentBundle:Task:task-list.html.twig';
        return $this->render($tpl, array(
			'agents'          => $agents,
			'agent_teams'     => $agent_teams,
            'tasks'           => $tasks,
            'completed_tasks' => $completed_tasks,
			'tasks_grouped'   => $tasks_grouped,
        	'task_type'       => $task_type,
			'search_type'     => $search_type,
			'search_category' => $search_categoty,
			'group_by'        => $group_by,

			'page'               => $page,
			'has_next'           => $has_next,
			'has_prev'           => $has_prev,
			'completed_page'     => $completed_page,
			'has_next_completed' => $has_next_completed,
			'has_prev_completed' => $has_prev_completed
        ));
    }


    /**
     * Save labels for tasks.
     *
     * @param intiger $task_id
     * @return json
     */
    public function ajaxSaveLabelsAction($task_id)
    {
        $task = $this->getTaskOr404($task_id);
        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');
        $task->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($task);
        $this->em->flush();

        return $this->createJsonResponse(array('success' => 1));
    }

    /**
     * Save the comment for tasks
     *
     * @param intiger $task_id
     * @return comment list in li format
     */
    public function ajaxSaveCommentAction($task_id)
    {
        $task = $this->getTaskOr404($task_id);

        $comment_txt = $this->in->getString('comment');

		if (!$comment_txt) {
			return $this->createJsonResponse(array(
				'error' => true,
				'error_code' => 'no_message'
			));
		}

        $comment = new TaskComment($this->person, $comment_txt);
        $comment['person'] = $this->person;
        $comment['task'] = $task;
        $comment['content'] = $comment_txt;

        $this->em->persist($comment);
        $this->em->flush();

        return $this->createJsonResponse(array(
                'success' => true,
                'task_id' => $task_id,
                'comment_li_html' => $this->renderView('AgentBundle:Task:comment-li.html.twig', array('comment' => $comment))
        ));
    }

	public function ajaxSaveAction($task_id)
	{
		$task = $this->getTaskOr404($task_id);

		switch ($this->in->getString('action')) {
			case 'date_due':
				if ($this->in->getString('value')) {
					try {
						$date_due = new \DateTime($this->in->getString('value'), $this->person->getDateTimezone());
						$date_due->setTime(23, 59, 59);
					} catch (\Exception $e) {
						$date_due = null;
					}

					if ($date_due) {
						$task->date_due = Dates::convertToUtcDateTime($date_due);
					} else {
						$task->date_due = null;
					}
				} else {
					$task->date_due = null;
				}
				break;

			case 'time_due':
				if ($task->date_due) {
					$time_due = $this->in->getString('value');
					if (!empty($time_due) && strpos($time_due, ':') !== 0) {
						list ($hour, $min) = explode(':', $time_due);
						$hour = (int)$hour;
						$min = (int)$min;
						if (Numbers::inRange($hour, 0, 23) && Numbers::inRange($min, 0, 59)) {
							$date = clone $task->date_due;
							$date->setTimezone($this->person->getDateTimezone());
							$date->setTime($hour, $min, 59);

							$task->date_due = Dates::convertToUtcDateTime($date);
						} else {
							$date = clone $task->date_due;
							$date->setTime(23, 59, 59);
							$task->date_due = $date;
						}
					} else {
						$date = clone $task->date_due;
						$date->setTime(23, 59, 59);
						$task->date_due = $date;
					}
				}
				break;

			case 'visibility':
				$task->setVisibility($this->in->getString('value'));
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

				$task->agent = null;
				$task->agent_team = null;

				if ($val) {
					list ($type, $id) = explode(':', $val);
					if ($type == 'agent') {
						$task->setAsignedAgentId($id);
					} else {
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

		return $this->createJsonResponse(array('success' => true));
	}

	public function printAssociativeTaskAction($assoc = null)
	{
		if(method_exists($assoc, 'getDeal') && $assoc->getDeal())
		{
			return $this->render('AgentBundle:Task:dealAssoc.html.twig', array('assoc' => $assoc));
		} else if(method_exists($assoc, 'getTicket') && $assoc->getTicket() != null){
			return $this->render('AgentBundle:Task:ticketAssoc.html.twig', array('assoc' => $assoc));
		}
	}


	public function deleteTaskAction($task_id)
	{
		$task = $this->getTaskOr404($task_id);

		if ($task->person->getId() != $this->person->getId()) {
			return $this->createNotFoundException();
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

		return $this->createJsonResponse(array('success' => true));
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
            $person = Person::getRepository()->find($id);
            
            if (!$person) {
                throw $this->createNotFoundException("Invalid authcode");
            }
            
            $generatedAuthCode = sha1($person->secret_string . $person->password);
            
            if ($generatedAuthCode !== $authcode) {
                throw $this->createNotFoundException("Invalid authcode");
            }
            
            switch ($filter) {
                case 'all':
                    $tasks = $this->em->getRepository('DeskPRO:Task')->filterAllPendingTasks($person);
                    break;
                
                case 'assigned':
                    $tasks = $this->em->getRepository('DeskPRO:Task')->filterTasksForPerson($person);
                    break;
                
                case 'delegated':
                    $tasks = $this->em->getRepository('DeskPRO:Task')->filterDelegatedTasksForPerson($person);
                    break;

                default:
                    break;
            }
            
            $vCalendar = new \Eluceo\iCal\Component\Calendar('www.example.com');
            
            foreach ($tasks as $task) {
                $vEvent = new \Eluceo\iCal\Component\Event();
            
                $vEvent
                    ->setDtStart($task->date_due)
                    ->setDtEnd($task->date_due)
                    ->setNoTime(true)
                    //->setTitle($task->title)
                    ->setSummary($task->title)
                ;

                $vCalendar->addEvent($vEvent);
            }
            
            $response = new \Symfony\Component\HttpFoundation\Response($vCalendar->render());
            
            $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filter . '.ics"');
            
            return $response;
        }
}
