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
use Application\DeskPRO\Entity\TaskComment;
use Application\AgentBundle\Form\Type\NewTask;

/**
 * Handles viewing and editing tasks
 */
class TaskController extends AbstractController {

    private $_entityManager;
    private $_currentUser;
    private $_task_repository;

    public function getSectionDataAction() {

        $this->_loadModels();
        $task_repository = $this->_task_repository;
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
                    'tasks' => array(
                        'all' => $all_tasks,
                        'person' => $person_tasks,
                        'teams' => $teams_tasks,
                        'delegated' => $delegated_tasks,
                        )));

        return $this->createJsonResponse(array(
            'section_html' => $section_html,
        ));
    }

    public function countPendingAction() {
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

        $data['section_html'] = $this->renderView('AgentBundle:Task:countPending.html.twig', array(
                    'tasks' => array(
                        'all' => $all_tasks,
                        'person' => $person_tasks,
                        'teams' => $teams_tasks,
                        'delegated' => $delegated_tasks,
                    )
                ));

        return $this->createJsonResponse($data);
    }

    /**
     * Renders an html list with all the pending task for the current user.
     */
    public function listPendingAction() {

    }

    /**
     * Render the new task form.
     * @return <type>
     */
    public function newAction() {
        $form = $this->get('form.factory')->create(new NewTask(), new Task())->createView();

        return $this->render('AgentBundle:Task:newtask.html.twig', array(
            'form' => $form
        ));
    }

    /**
     * Create action for the new task. Which pass the data and the from to the _process method to save it in DB.
     * @return json formated data
     */
    public function createAction() {
        $task = new Task();
        $form = $this->get('form.factory')->create(new NewTask(), $task);
        return $this->_process($form, $task);
    }

    /**
     * render the task
     * @param intiger $task_id
     * @return <type>
     */
    public function viewAction($task_id = null) {
        return $this->render('AgentBundle:Task:view.html.twig');
    }

    public function taskListAction($search_type = null, $search_categoty = null)
    {
        $this->_loadModels();
        $person = $this->person;
        
        if($search_type == 'own')
        {            
            $tasks = $this->_task_repository->filterPendingTasksForPerson($person, $search_categoty);
            
        }else if($search_type == 'team')
        {
            $tasks = $this->_task_repository->filterPendingTaksForPersonTeams($person, $search_categoty);
        }
        else if($search_type == 'delegate')
        {
            $tasks = $this->_task_repository->filterPendingDelegatedTasksForPerson($person, $search_categoty);
        }
        else if($search_type == 'all')
        {
            $tasks = $this->_task_repository->filterAllPendingTasks($search_categoty);
        }

        $tpl = 'AgentBundle:Task:task-list.html.twig';
        return $this->render($tpl, array(
            'tasks' => $tasks,
        ));        
    }

    /**
     * Process data for add or update the task.
     * @param NewTask $form
     * @param Task $task
     * @return json data for success true or false
     */
    private function _process($form, Task $task) {

        $this->_loadModels();
        $request = $this->get('request');

        $form->bindRequest($request);
        try {

            if ($request->getMethod() == 'POST') {

                if ($form->isValid()) {
                    $task->setPerson($this->_currentUser);
                    $this->_entityManager->persist($task);
                    $this->_entityManager->flush();

                    return $this->createJsonResponse(array(
                        'success' => true,
                        'task_id' => $task->getId()
                    ));
                } else {
                    return $this->createJsonResponse(array(
                        'success' => false
                    ));
                }
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            return $this->createJsonResponse(array(
                'success' => false
            ));
        }
    }

    private function _loadModels() {

        $this->_entityManager = $this->get('doctrine')->getEntityManager();
        $this->_currentUser = $user = App::getCurrentPerson();
        $this->_task_repository = App::getEntityRepository('DeskPRO:Task');        
    }

}
