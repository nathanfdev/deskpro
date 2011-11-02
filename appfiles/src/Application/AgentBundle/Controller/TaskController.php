<?php

/**
 * DeskPRO AgentBubdle's Task Controller
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\AgentBundle\Controller;

use Orb\Util\Arrays;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\PersonNote;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskComment;
use Application\AgentBundle\Form\Type\NewTask;

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
            'total' => $task_repository->countPendingTasks(),
            'overdue' => $task_repository->countOverdueTasks($person['timezone']),
            'due_today' => $task_repository->countDueTodayTasks($person['timezone']),
            'due_future' => $task_repository->countDueFutureTasks($person['timezone']),
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

			$task->setVisibility($task_data['visibility']);
			if (!empty($task_data['due_date'])) {
				$task->setDueDate($task_data['due_date']);
			}

			$tasks[] = $task;
		}

		$this->db->beginTransaction();
		try {
			foreach ($tasks as $t) {
				$this->em->persist($t);
			}

			$this->em->flush();
			$this->db->commit();

		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => true));
    }

    /**
     * render the task
     * @param intiger $task_id
     * @return <type>
     */
    public function viewAction($task_id = null)
	{
        return $this->render('AgentBundle:Task:view.html.twig');
    }

    /**
     * render the task list
     *
     * @param string $search_type
     * @param string $search_categoty
     * @return html view of the task list
     */
    public function taskListAction($search_type = null, $search_categoty = null)
    {
        $person = $this->person;
        $task_type = false;

        if ($search_type == 'own') {
            $tasks = $this->em->getRepository('DeskPRO:Task')->filterPendingTasksForPerson($person, $search_categoty);
        } else if ($search_type == 'team') {
            $tasks = $this->em->getRepository('DeskPRO:Task')->filterPendingTaksForPersonTeams($person, $search_categoty);
        } else if ($search_type == 'delegate') {
            $tasks = $this->em->getRepository('DeskPRO:Task')->filterPendingDelegatedTasksForPerson($person, $search_categoty);
        } else if ($search_type == 'all') {
            $tasks = $this->em->getRepository('DeskPRO:Task')->filterAllPendingTasks($search_categoty);
        } else if($search_type == 'complete'){
            $tasks = $this->em->getRepository('DeskPRO:Task')->allCompleteTasks();
            $task_type = true;
        }

        $tpl = 'AgentBundle:Task:task-list.html.twig';
        return $this->render($tpl, array(
            'tasks' => $tasks,
            'total_complete_task' => $this->em->getRepository('DeskPRO:Task')->countCompleteTasks(),
             'task_type' => $task_type
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

	// TODO error checking

    /**
     * Save the comment for tasks
     *
     * @param intiger $task_id
     * @return comment list in li format
     */
    public function ajaxSaveCommentAction($task_id = null)
    {
        if ($task_id) {
                $task = $this->getTaskOr404($task_id);
        } else {
                $task = new Task();
        }

        $comment_txt = $this->in->getString('comment');

        $comment = new TaskComment($this->person, $comment_txt);
        $comment['person'] = $this->person;
        $comment['task'] = $task;
        $comment['content'] = $comment_txt;

        $this->em->persist($task);
        $this->em->flush();

        return $this->createJsonResponse(array(
                'success' => true,
                'task_id' => $task_id,
                'comment_li_html' => $this->renderView('AgentBundle:Task:comment-li.html.twig', array('comment' => $comment))
        ));
    }

    /**
     * Update the due date for tasks.
     *
     * @param intiger $task_id
     * @return json
     */
    public function ajaxSaveDueDateAction($task_id = null)
    {

        $task = $this->getTaskOr404($task_id);

        $date_due = $this->in->getString('date_due');
        $task->setDueDate($date_due);
        $this->em->persist($task);
        $this->em->flush();
        return $this->createJsonResponse(array('success' => 1));
    }

    /**
     * Update the task visbility from public to private or vice versa
     *
     * @param intiger $task_id
     * @param intiger 0/2 $visibility
     * @return json
     */
    public function setVisibilityAction($task_id, $visibility)
    {

        $task = $this->getTaskOr404($task_id);
        $task->setVisibility($visibility);

        $this->em->persist($task);
        $this->em->flush();

        return $this->createJsonResponse(array('success' => 1));
    }


    public function draftsMassActionsAction($action)
    {

        $data = $this->in->getCleanValueArray('ids', 'array', 'string');

        $action = ($action == 'complete') ? true : false;
        foreach ($data as $value) {
            $task = $this->getTaskOr404($value[0]);
            $task->setIsCompleted($action);
            $this->em->persist($task);
        }
        $this->em->flush();

        return $this->createJsonResponse(array(
			'success' => true,
			'total_complete_task' => $this->em->getRepository('DeskPRO:Task')->countCompleteTasks()
		));
    }


    public function checkDueDateAction($due_date = null)
    {
        $person = $this->person;
        $time_zone = new \DateTimeZone($person['timezone']);
        $today = new \DateTime('today', $time_zone);
        $over_due = 0;
        if($due_date < $today)
        {
            $over_due = 1;
        }

        $days_ago = (time() - $due_date->format('m-d-y'))/86400;

        return $this->render('AgentBundle:Task:task-due-date.html.twig', array(
            'new-due_date' => $days_ago,
            'over_due' => $over_due
        ));
    }

    /**
	 * @return Application\DeskPRO\Entity\Task
	 */
	protected function getTaskOr404($task_id)
	{
		try {
			$task = $this->em->find('DeskPRO:Task', $task_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no task with ID $task_id");
		}

		return $task;
	}
}
