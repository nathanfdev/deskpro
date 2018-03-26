<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\LabelDef;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FeedbackFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    const NUM_FEEDBACK            = 100;
    const MIN_FEEDBACK_COMMENTS   = 1;
    const MAX_FEEDBACK_COMMENTS   = 5;
    const NUM_LABELS              = 30;
    const MIN_LABELS_PER_FEEDBACK = 0;
    const MAX_LABELS_PER_FEEDBACK = 5;

    /**
     * @var int[]
     */
    private $people = [];

    /**
     * @var int[]
     */
    private $types = [];

    /**
     * @var int[]
     */
    private $languages = [];

    /**
     * @var int[]
     */
    private $feedback = [];

    /**
     * @var string[]
     */
    private $statuses = [Feedback::STATUS_ACTIVE, Feedback::STATUS_CLOSED, Feedback::STATUS_HIDDEN];

    /**
     * @var array
     */
    private $statusesCategories = [
        Feedback::STATUS_ACTIVE => ['Gathering Feedback', 'Planning', 'Started', 'Under Review'],
        Feedback::STATUS_CLOSED => ['Completed', 'Duplicate', 'Declined'],
    ];

    /**
     * @var string[]
     */
    private $hiddenStatuses = [
        Feedback::HIDDEN_STATUS_DELETED,
        Feedback::HIDDEN_STATUS_UNPUBLISHED,
        Feedback::HIDDEN_STATUS_SPAM,
        Feedback::HIDDEN_STATUS_DRAFT,
    ];

    /**
     * @var string[]
     */
    private $categories = ['Windows', 'Linux', 'Mac'];

    /**
     * @var string[]
     */
    private $labels = [];

    /**
     * @var int[]
     */
    private $activeStatuses = [];

    /**
     * @var int[]
     */
    private $closedStatuses = [];

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
        $this->loadFeedbackChoices();
        $this->loadStatusCategories();
        $this->loadLabels();
        $this->manager->flush();

        $this->people         = $this->fetchIds(self::TABLE_PEOPLE);
        $this->types          = $this->fetchIds(self::TABLE_FEEDBACK_CATEGORIES);
        $this->languages      = $this->fetchIds(self::TABLE_LANGUAGES);
        $this->activeStatuses = $this->fetchIds(
            self::TABLE_FEEDBACK_STATUS_CATEGORIES,
            [['field' => 'status_type', 'value' => Feedback::STATUS_ACTIVE]]
        );
        $this->closedStatuses = $this->fetchIds(
            self::TABLE_FEEDBACK_STATUS_CATEGORIES,
            [['field' => 'status_type', 'value' => Feedback::STATUS_CLOSED]]
        );

        $this->loadFeedback();
        $this->loadFeedbackCategories();
        $this->loadFeedbackLabels();
        $this->loadFeedbackComments();
    }

    private function loadFeedbackChoices()
    {
        $customCatDef = $this->manager->getRepository(CustomDefFeedback::class)->findOneBy([
            'sys_name' => 'cat',
        ]);

        foreach ($this->categories as $order => $title) {
            $customCatChoice = new CustomDefFeedback();
            $customCatChoice
                ->setParent($customCatDef)
                ->setTitle($title)
                ->setDescription('')
                ->setIsUserEnabled(true)
                ->setIsEnabled(true)
                ->setDisplayOreder($order)
                ->setOption('parent_id', 0)
            ;

            $customCatDef->addChild($customCatChoice);
        }

        $this->manager->flush();
    }

    private function loadStatusCategories()
    {
        foreach ($this->statusesCategories as $status => $titles) {
            $i = 0;
            foreach ($titles as $title) {
                $i += 10;
                $cat = new FeedbackStatusCategory();
                $cat
                    ->setStatusType($status)
                    ->setTitle($title)
                    ->setDisplayOrder($i);

                $this->manager->persist($cat);
            }
        }
    }

    private function loadLabels()
    {
        $label_type = LabelDef::TYPE_FEEDBACK;
        $this->faker->unique(true);

        $batch = [];

        for ($i = 0; $i < self::NUM_LABELS; ++$i) {
            $l = str_replace(',', '', $this->faker->unique()->company);
            if ($l) {
                $l       = strtolower($l);
                $batch[] = [
                    'label_type' => $label_type,
                    'label'      => $l,
                    'color'      => $this->faker->hexColor,
                    'total'      => 0,
                ];
            }
        }

        $this->db->batchInsert('label_defs', $batch, true);

        $this->labels = $this->db->fetchAllCol('SELECT label FROM label_defs WHERE label_type = ?', [$label_type]);
    }

    private function loadFeedback()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < self::NUM_FEEDBACK) {
            $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');
            $values      = [
                'content'      => $this->faker->realText(300),
                'person_id'    => $this->faker->randomElement($this->people),
                'category_id'  => $this->faker->randomElement($this->types),
                'language_id'  => $this->faker->randomElement($this->languages),
                'date_created' => $dateCreated,
            ];
            $values  = $this->setReviewed($values);
            $values  = $this->setStatus($values);
            $values  = $this->setTitleAndSlug($values);
            $batch[] = $values;
        }
        $this->db->batchInsert(self::TABLE_FEEDBACK, $batch, true);
        $this->feedback = $this->fetchIds(self::TABLE_FEEDBACK);
    }

    private function setStatus(array $values)
    {
        $values['status'] = $this->faker->randomElement($this->statuses);
        switch ($values['status']) {
            case Feedback::STATUS_ACTIVE:
                $values['hidden_status']      = null;
                $values['status_category_id'] = $this->faker->randomElement($this->activeStatuses);
                break;
            case Feedback::STATUS_CLOSED:
                $values['hidden_status']      = null;
                $values['status_category_id'] = $this->faker->randomElement($this->closedStatuses);
                break;
            case Feedback::STATUS_HIDDEN:
                $values['hidden_status']      = $this->faker->randomElement($this->hiddenStatuses);
                $values['status_category_id'] = null;
                break;
        }

        return $values;
    }

    private function setReviewed(array $values)
    {
        $date                   = $this->faker->dateTimeBetween('-10 days', '-1 days')->format('Y-m-d H:i:s');
        $isReviewed             = rand(0, 1);
        $values['is_reviewed']  = $isReviewed;
        $values['num_comments'] = 0;
        if ($isReviewed) {
            $values['view_count']     = rand(0, 100);
            $values['num_ratings']    = rand(0, 20);
            $values['total_rating']   = rand(0, 20);
            $values['popularity']     = $this->calculatePopularity($values);
            $values['date_published'] = $date;
            $values['date_updated']   = $date;
        } else {
            $values['view_count']     = 0;
            $values['num_ratings']    = 0;
            $values['total_rating']   = 0;
            $values['popularity']     = 0;
            $values['date_published'] = null;
            $values['date_updated']   = null;
        }

        return $values;
    }

    private function calculatePopularity(array $values)
    {
        $date = new \DateTime($values['date_created']);
        $days = (time() - $date->getTimestamp()) / 86400;
        if (!$days) {
            $days = 1;
        }

        return ceil($values['total_rating'] / sqrt($days));
    }

    private function loadFeedbackCategories()
    {
        $customCatDef = $this->manager->getRepository(CustomDefFeedback::class)->findOneBy([
            'sys_name' => 'cat',
        ]);

        $batch = [];
        $ids   = $customCatDef->getChoiceIds();

        foreach ($this->feedback as $feedbackId) {
            $batch[] = [
                'feedback_id'   => $feedbackId,
                'root_field_id' => $customCatDef->getId(),
                'field_id'      => $ids[array_rand($ids)],
                'value'         => 1,
            ];
        }

        $this->db->batchInsert(self::TABLE_CUSTOM_DATA_FEEDBACK, $batch, true);
    }

    private function loadFeedbackLabels()
    {
        $batch = [];
        foreach ($this->feedback as $id) {
            $num = rand(self::MIN_LABELS_PER_FEEDBACK, self::MAX_LABELS_PER_FEEDBACK);
            if ($num) {
                $labels = (array) array_rand($this->labels, $num);
                foreach ($labels as $key) {
                    $batch[] = [
                        'feedback_id' => $id,
                        'label'       => $this->labels[$key],
                    ];
                }
            }
        }

        $this->db->batchInsert(self::TABLE_LABELS_FEEDBACK, $batch, true);
    }

    private function loadFeedbackComments()
    {
        $batch = [];
        foreach ($this->feedback as $id) {
            $num_comments = rand(self::MIN_FEEDBACK_COMMENTS, self::MAX_FEEDBACK_COMMENTS);
            /** @var Feedback $feedback */
            $feedback = $this->manager->getRepository('DeskPRO:Feedback')->find($id);
            if (!$feedback->isReviewed()) {
                continue;
            }
            $feedback->setNumComments($num_comments);
            $this->manager->persist($feedback);

            $i = 0;
            while ($i++ < $num_comments) {
                $dateCreated = $this->faker->dateTimeBetween('-2 months', '-10 days')->format('Y-m-d H:i:s');
                $values      = [
                    'content'     => $this->faker->realText(300),
                    'feedback_id' => $id,
                    'person_id'   => $this->faker->randomElement($this->people),
                    'ip_address'  => $this->faker->ipv4,
                    'status'      => $this->faker->randomElement(
                        [
                            FeedbackComment::STATUS_VISIBLE,
                            FeedbackComment::STATUS_HIDDEN,
                            FeedbackComment::STATUS_DELETED,
                        ]
                    ),
                    'date_created' => $dateCreated,
                ];
                $values  = $this->setCommentReviewed($values);
                $batch[] = $values;
            }
        }
        $this->db->batchInsert(self::TABLE_FEEDBACK_COMMENTS, $batch, true);
        $this->manager->flush();
    }

    private function setCommentReviewed(array $values)
    {
        $values['is_reviewed'] = $values['status'] === FeedbackComment::STATUS_HIDDEN ? 0 : 1;

        return $values;
    }
}
