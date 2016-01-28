<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\DBAL\Connection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TasksFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $num_tasks       = 100;
    private $num_links       = 60;
    private $num_subtasks    = 30;
    private $num_comments    = 30;
    private $num_attachments = 30;
    private $num_projects    = 5;
    private $num_labels      = 30;
    private $num_lists       = 3;

    private $min_labels_per_task = 1;
    private $max_labels_per_task = 5;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var string[]
     */
    private $labels = [];

    /**
     * @var int[]
     */
    private $agent_ids = [];

    /**
     * @var int[]
     */
    private $project_ids = [];

    /**
     * @var int[]
     */
    private $task_ids = [];

    /**
     * @var int[]
     */
    private $list_ids = [];

    /**
     * @var int[]
     */
    private $team_ids = [];

    /**
     * @var int[]
     */
    private $department_ids = [];

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * DpFixture constructor.
     */
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 80;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->db      = $this->container->get('database_connection');

        $this->agent_ids      = $this->db->fetchAllCol('SELECT id FROM people WHERE is_agent = 1');
        $this->department_ids = $this->db->fetchAllCol('SELECT id FROM departments where is_tickets_enabled = 1');
        $this->team_ids       = $this->db->fetchAllCol('SELECT id FROM agent_teams');

        if (!$this->agent_ids) {
            throw new \RuntimeException('Please import agents first');
        }

        $this->loadProjects();
        $this->loadLists();
        $this->loadTasks();
        $this->loadLabels();
        $this->loadAssignments();
        $this->loadSubtasks();
        $this->loadComments();
        $this->loadAttachments();
        $this->loadLinks();

        $this->labels         = [];
        $this->agent_ids      = [];
        $this->department_ids = [];
        $this->team_ids       = [];
        $this->task_ids       = [];
        $this->list_ids       = [];
        $this->project_ids    = [];
    }

    private function loadLabels()
    {
        $this->faker->unique(true);

        while (count($this->labels) < $this->num_labels) {
            if ($label = strtolower($this->faker->unique()->company)) {
                $this->labels[] = $label;
            }
        }

        $batch = [];
        foreach ($this->task_ids as $id) {
            $num    = rand($this->min_labels_per_task, $this->max_labels_per_task);
            $labels = (array) array_rand($this->labels, $num);
            foreach ($labels as $key) {
                $batch[] = [
                    'task_id' => $id,
                    'label'   => $this->labels[$key],
                ];
            }
        }

        $this->db->batchInsert('task_labels', $batch, true);
    }

    private function loadProjects()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_projects) {
            $batch[] = [
                'title' => $this->faker->realText(70),
            ];
        }

        $table = 'task_projects';
        $this->db->batchInsert($table, $batch, true);
        $this->project_ids = $this->db->fetchAllCol('SELECT id FROM '.$table);
    }

    private function loadLists()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_lists) {
            $batch[] = [
                'title'         => $this->faker->realText(70),
                'display_order' => $i,
                'project_id'    => $this->project_ids[array_rand($this->project_ids)],
            ];
        }

        $table = 'task_lists';
        $this->db->batchInsert($table, $batch, true);
        $this->list_ids = $this->db->fetchAllCol('SELECT id FROM '.$table);
    }

    private function loadTasks()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_tasks) {
            $dateDone    = null;
            $percentDone = rand(0, 99);
            if ($isDone = rand(0, 1)) {
                $dateDone    = $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s');
                $percentDone = 100;
            }

            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');

            $dateDue = rand(0, 1)
                ? $this->faker->dateTimeBetween('+5 days', '+2 months')->format('Y-m-d H:i:s')
                : null;

            $batch[] = [
                'title'             => $this->faker->realText(100),
                'creator_person_id' => $this->agent_ids[array_rand($this->agent_ids)],
                'project_id'        => $this->project_ids[array_rand($this->project_ids)],
                'list_id'           => $this->list_ids[array_rand($this->list_ids)],
                'is_done'           => $isDone,
                'percent_complete'  => $percentDone,
                'date_created'      => $dateCreated,
                'date_due'          => $dateDue,
                'date_done'         => $dateDone,
                'urgency'           => rand(1, 5),
                'task_type'         => 'task',
                'display_order'     => $i,
            ];
        }

        $table = 'tasks_new';
        $this->db->batchInsert($table, $batch, true);
        $this->task_ids = $this->db->fetchAllCol('SELECT id FROM '.$table.' order by id');
    }

    private function loadAssignments()
    {
        $batch = [];
        foreach ($this->task_ids as $id) {
            $agent      = null;
            $department = null;
            $team       = null;

            if (rand(1, 100) < 25 && $this->department_ids) {
                $department = $this->department_ids[array_rand($this->department_ids)];
            } elseif (rand(1, 100) < 25 && $this->team_ids) {
                $team = $this->team_ids[array_rand($this->team_ids)];
            } else {
                $agent = $this->agent_ids[array_rand($this->agent_ids)];
            }

            $batch[] = [
                'task_id'       => $id,
                'person_id'     => $agent,
                'team_id'       => $team,
                'department_id' => $department,
            ];
        }

        $this->db->batchInsert('task_assignments', $batch, true);
    }

    private function loadSubtasks()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_subtasks) {
            $dateDone = null;
            if ($isDone = rand(0, 1)) {
                $dateDone = $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s');
            }

            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');

            $batch[] = [
                'title'          => $this->faker->realText(100),
                'creator_id'     => $this->agent_ids[array_rand($this->agent_ids)],
                'is_done'        => $isDone,
                'date_created'   => $dateCreated,
                'date_completed' => $dateDone,
                'display_order'  => $i,
                'task_id'        => $this->task_ids[array_rand($this->task_ids)],
            ];
        }

        $this->db->batchInsert('task_subtask', $batch, true);
    }

    public function loadComments()
    {
        $i     = 0;
        $batch = [];

        while ($i++ < $this->num_comments) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');

            $batch[] = [
                'person_id'    => $this->agent_ids[array_rand($this->agent_ids)],
                'task_id'      => $this->task_ids[array_rand($this->task_ids)],
                'comment'      => $this->faker->realText(300),
                'date_created' => $dateCreated,
            ];
        }

        $this->db->batchInsert('task_comments_new', $batch, true);
    }

    public function loadAttachments()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_attachments) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');

            $batch[] = [
                'person_id'    => $this->agent_ids[array_rand($this->agent_ids)],
                'date_created' => $dateCreated,
                'task_id'      => $this->task_ids[array_rand($this->task_ids)],
                'blob_id'      => 1,
            ];
        }

        $this->db->batchInsert('task_attachments', $batch, true);
    }

    public function loadLinks()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_links) {
            $ticket  = null;
            $chat    = null;
            $article = null;

            if (rand(1, 100) < 25) {
                $article = 1;
            } elseif (rand(1, 100) < 25) {
                $chat = 1;
            } else {
                $ticket = 1;
            }

            $batch[] = [
                'task_id'    => $this->task_ids[array_rand($this->task_ids)],
                'ticket_id'  => $ticket,
                'chat_id'    => $chat,
                'article_id' => $article,
            ];
        }

        $this->db->batchInsert('task_links', $batch, true);
    }
}
