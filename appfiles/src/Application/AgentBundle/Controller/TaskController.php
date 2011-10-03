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
use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Task;
use Application\AgentBundle\Form\Type\NewTask;

/**
 * Handles viewing and editing tasks
 */
class TaskController extends AbstractController
{

	/**
	 * Renders a JSON with the count of all the pending task for the current user.
	 */
	public function countPendingAction()
	{
		$task_repository = App::getEntityRepository('DeskPRO:Task');
		$person = $this->person;

		$all_tasks = array(
			'total' => $task_repository->countPendingTasks(),
			'overdue' => $task_repository->countOverdueTasks($person['timezone']),
			'due_today' => $task_repository->countDueTodayTasks($person['timezone']),
		);

		$person_tasks = array(
			'total' => $task_repository->countPendingTasksForPerson($person),
			'overdue' => $task_repository->countOverdueTasksForPerson($person),
			'due_today' => $task_repository->countDueTodayTasksForPerson($person),
		);

		$teams_tasks = array(
			'total' => $task_repository->countPendingTaksForPersonTeams($person),
			'overdue' => $task_repository->countOverdueTasksForPersonTeams($person),
			'due_today' => $task_repository->countDueTodayTasksForPersonTeams($person),
		);

		$delegated_tasks = array(
			'total' => $task_repository->countPendingDelegatedTasksForPerson($person),
			'overdue' => $task_repository->countOverdueDelegatedTasksForPerson($person),
			'due_today' => $task_repository->countDueTodayDelegatedTasksForPerson($person),
		);

		return $this->renderJson('AgentBundle:Task:countPending.html.twig', array(
			'tasks' => array(
				'all' => $all_tasks,
				'person' => $person_tasks,
				'teams'  => $teams_tasks,
				'delegated'  => $delegated_tasks,
			)
		));
	}

	/**
	 * Renders an html list with all the pending task for the current user.
	 */
	public function listPendingAction()
	{

	}

        /**
         * Render the new task form.
         * @return <type>
         */

	public function newAction()
	{
            $form = $this->get('form.factory')->create(new NewTask(), new Task())->createView();

            return $this->render('AgentBundle:Task:newtask.html.twig', array(
                'form' => $form
            ));
	}

<<<<<<< HEAD
=======
        public function createAction()
        {
            $task = new Task(); 
            $form = $this->get('form.factory')->create(new NewTask(), $task); 
            return $this->_process($form, $task);
        }

        private function _process($form, $task)
        {
            $request = $this->get('request');
            $form->bindRequest($request); 
<<<<<<< HEAD
=======
            $this->_entityManager->persist($task);
            $this->_entityManager->flush();
>>>>>>> e969e50... task info now saved in DB
            return new Response('ok');
        }

>>>>>>> e0d18f6... edit the task controller to remobe print method
}
