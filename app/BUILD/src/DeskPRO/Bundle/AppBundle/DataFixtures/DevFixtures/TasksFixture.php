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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TasksFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
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
     * @var int[]
     */
    private $blob_ids = [];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 100;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->db      = $this->container->get('database_connection');

        $this->team_ids  = $this->fetchIds(self::TABLE_AGENT_TEAMS);
        $this->blob_ids  = $this->fetchIds(self::TABLE_BLOBS);
        $this->agent_ids = $this->fetchIds(
            self::TABLE_PEOPLE,
            [['field' => 'is_agent', 'value' => 1]]
        );
        $this->department_ids = $this->fetchIds(
            self::TABLE_DEPARTMENTS,
            [['field' => 'is_tickets_enabled', 'value' => 1]]
        );

        $this->loadProjects();
        $this->loadLists();
        $this->loadTasks();
        $this->loadLabels();
        $this->loadAssignments();
        $this->loadSubtasks();
        $this->loadComments();
        $this->loadAttachments();
        $this->loadLinks();

        $this->manager->flush();

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

        $this->db->batchInsert(self::TABLE_TASK_PROJECTS, $batch, true);
        $this->project_ids = $this->fetchIds(self::TABLE_TASK_PROJECTS);
    }

    private function loadLists()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_lists) {
            $batch[] = [
                'title'         => $this->faker->realText(70),
                'display_order' => $i,
                'project_id'    => $this->faker->randomElement($this->project_ids),
            ];
        }

        $this->db->batchInsert(self::TABLE_TASK_LISTS, $batch, true);
        $this->list_ids = $this->fetchIds(self::TABLE_TASK_LISTS);
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
                'creator_person_id' => $this->faker->randomElement($this->agent_ids),
                'project_id'        => $this->faker->randomElement($this->project_ids),
                'list_id'           => $this->faker->randomElement($this->list_ids),
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

        $this->db->batchInsert(self::TABLE_TASKS_NEW, $batch, true);
        $this->task_ids = $this->db->fetchAllCol('SELECT id FROM '.self::TABLE_TASKS_NEW.' ORDER BY id');
    }

    private function loadAssignments()
    {
        $batch = [];
        foreach ($this->task_ids as $id) {
            $agent      = null;
            $department = null;
            $team       = null;

            if (rand(1, 100) < 25 && $this->department_ids) {
                $department = $this->faker->randomElement($this->department_ids);
            } elseif (rand(1, 100) < 25 && $this->team_ids) {
                $team = $this->faker->randomElement($this->team_ids);
            } else {
                $agent = $this->faker->randomElement($this->agent_ids);
            }

            $batch[] = [
                'task_id'       => $id,
                'person_id'     => $agent,
                'team_id'       => $team,
                'department_id' => $department,
            ];
        }

        $this->db->batchInsert(self::TABLE_TASK_ASSIGNMENTS, $batch, true);
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
                'creator_id'     => $this->faker->randomElement($this->agent_ids),
                'is_done'        => $isDone,
                'date_created'   => $dateCreated,
                'date_completed' => $dateDone,
                'display_order'  => $i,
                'task_id'        => $this->faker->randomElement($this->task_ids),
            ];
        }

        $this->db->batchInsert(self::TABLE_TASK_SUBTASK, $batch, true);
    }

    public function loadComments()
    {
        $i     = 0;
        $batch = [];

        while ($i++ < $this->num_comments) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');

            $batch[] = [
                'person_id'    => $this->faker->randomElement($this->agent_ids),
                'task_id'      => $this->faker->randomElement($this->task_ids),
                'comment'      => $this->faker->realText(300),
                'date_created' => $dateCreated,
            ];
        }

        $this->db->batchInsert(self::TABLE_TASK_COMMENTS_NEW, $batch, true);
    }

    public function loadAttachments()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < $this->num_attachments) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');

            $batch[] = [
                'person_id'    => $this->faker->randomElement($this->agent_ids),
                'date_created' => $dateCreated,
                'task_id'      => $this->faker->randomElement($this->task_ids),
                'blob_id'      => $this->blob_ids[0],
            ];
        }

        $this->db->batchInsert(self::TABLE_TASK_ATTACHMENTS, $batch, true);
    }

    public function loadLinks()
    {
        $articles = $this->fetchIds(self::TABLE_ARTICLES);
        $chats    = $this->fetchIds(self::TABLE_CHAT_CONVERSATIONS);
        $tickets  = $this->fetchIds(self::TABLE_TICKETS);
        $i        = 0;
        while ($i++ < $this->num_links) {
            /** @var \DeskPRO\Bundle\AppBundle\Entity\Task $task */
            $task = $this->manager->getRepository(Task::class)->find($this->faker->randomElement($this->task_ids));
            if (rand(1, 100) < 25) {
                /* @var \Application\DeskPRO\Entity\Article $article */
                $articleId     = $this->faker->randomElement($articles);
                $article       = $this->manager->getRepository('DeskPRO:Article')->find($articleId);
                $articles      = $this->removeId($articles, $articleId);
                $linkedArticle = new TaskLinkedArticle();
                $linkedArticle->setTask($task)->setArticle($article);
                $this->manager->persist($linkedArticle);
            } elseif (rand(1, 100) < 40) {
                /* @var \Application\DeskPRO\Entity\ChatConversation $chat */
                $chatId         = $this->faker->randomElement($chats);
                $chat           = $this->manager->getRepository('DeskPRO:ChatConversation')->find($chatId);
                $this->task_ids = $this->removeId($this->task_ids, $task->getId());
                $linkedChat     = new TaskLinkedChat();
                $linkedChat->setTask($task)->setChat($chat);
                $this->manager->persist($linkedChat);
            } else {
                /* @var \Application\DeskPRO\Entity\Ticket $ticket */
                $ticketId     = $this->faker->randomElement($tickets);
                $ticket       = $this->manager->getRepository('DeskPRO:Ticket')->find($ticketId);
                $tickets      = $this->removeId($tickets, $ticketId);
                $linkedTicket = new TaskLinkedTicket();
                $linkedTicket->setTask($task)->setTicket($ticket);
                $this->manager->persist($linkedTicket);
            }
        }
    }

    private function removeId(array $array, $id)
    {
        return array_diff($array, [$id]);
    }
}
