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

use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class FeedbackFixture extends DeskProAbstractFixture
{
    const NUM_FEEDBACK            = 100;
    const NUM_LABELS              = 30;
    const MIN_LABELS_PER_FEEDBACK = 0;
    const MAX_LABELS_PER_FEEDBACK = 5;

    /**
     * @var int
     */
    protected $fixtureOrder = 80;

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
    private $typeValues = ['Suggestion', 'Feature Request', 'Bug Report'];

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
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadTypes();
        $this->loadCustomDefFeedback();
        $this->loadStatusCategories();
        $this->manager->flush();

        $this->people         = $this->fetchRelatedEntitiesIds('id', self::TABLE_PEOPLE);
        $this->types          = $this->fetchRelatedEntitiesIds('id', self::TABLE_FEEDBACK_CATEGORIES);
        $this->languages      = $this->fetchRelatedEntitiesIds('id', self::TABLE_LANGUAGES);
        $this->activeStatuses = $this->fetchRelatedEntitiesIds(
            'id',
            self::TABLE_FEEDBACK_STATUS_CATEGORIES,
            [['field' => 'status_type', 'value' => Feedback::STATUS_ACTIVE]]
        );
        $this->closedStatuses = $this->fetchRelatedEntitiesIds(
            'id',
            self::TABLE_FEEDBACK_STATUS_CATEGORIES,
            [['field' => 'status_type', 'value' => Feedback::STATUS_CLOSED]]
        );

        $this->loadFeedback();
        $this->loadFeedbackCategories();
        $this->loadFeedbackLabels();
    }

    /**
     * @return array
     */
    private function loadTypes()
    {
        foreach ($this->typeValues as $title) {
            $cat        = new FeedbackCategory();
            $cat->title = $title;
            $this->manager->persist($cat);
        }
    }

    private function loadCustomDefFeedback()
    {
        $cat_field                = new CustomDefFeedback();
        $cat_field->sys_name      = 'cat';
        $cat_field->title         = 'Category';
        $cat_field->description   = 'e.g., maybe Windows, Mac, Linux.';
        $cat_field->handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';
        $this->manager->persist($cat_field);
    }

    private function loadStatusCategories()
    {
        foreach ($this->statusesCategories as $status => $titles) {
            foreach ($titles as $title) {
                $cat              = new FeedbackStatusCategory();
                $cat->status_type = $status;
                $cat->title       = $title;
                $this->manager->persist($cat);
            }
        }
    }

    private function loadFeedback()
    {
        $i     = 0;
        $batch = [];
        while ($i++ < self::NUM_FEEDBACK) {
            $dateCreated = $this->dateTimeBetween('-2 months', '-10 days');
            $values      = [
                'content'      => $this->faker->realText(300),
                'person_id'    => $this->randomArrayValue($this->people),
                'category_id'  => $this->randomArrayValue($this->types),
                'language_id'  => $this->randomArrayValue($this->languages),
                'date_created' => $dateCreated,
            ];
            $values  = $this->setStatus($values);
            $values  = $this->setTitleAndSlug($values);
            $values  = $this->setReviewed($values);
            $batch[] = $values;
        }
        $this->db->batchInsert(self::TABLE_FEEDBACK, $batch, true);
        $this->feedback = $this->fetchRelatedEntitiesIds('id', self::TABLE_FEEDBACK);
    }

    private function setStatus(array $values)
    {
        $values['status'] = $this->randomArrayValue($this->statuses);
        switch ($values['status']) {
            case Feedback::STATUS_ACTIVE:
                $values['hidden_status']      = null;
                $values['status_category_id'] = $this->randomArrayValue($this->activeStatuses);
                break;
            case Feedback::STATUS_CLOSED:
                $values['hidden_status']      = null;
                $values['status_category_id'] = $this->randomArrayValue($this->closedStatuses);
                break;
            case Feedback::STATUS_HIDDEN:
                $values['hidden_status']      = $this->randomArrayValue($this->hiddenStatuses);
                $values['status_category_id'] = null;
                break;
        }

        return $values;
    }

    private function setReviewed(array $values)
    {
        $date                  = $this->dateTimeBetween('-10 days', '-1 days');
        $isReviewed            = rand(0, 1);
        $values['is_reviewed'] = $isReviewed;
        if ($isReviewed) {
            $values['view_count']     = rand(0, 100);
            $values['num_comments']   = rand(0, 100);
            $values['num_ratings']    = rand(0, 20);
            $values['total_rating']   = rand(0, 20);
            $values['popularity']     = $this->calculatePopularity($values);
            $values['date_published'] = $date;
            $values['date_updated']   = $date;
        } else {
            $values['view_count']     = 0;
            $values['num_comments']   = 0;
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
        $batch         = [];
        $categoryDefId = $this->fetchRelatedEntitiesIds(
            'id',
            self::TABLE_CUSTOM_DEF_FEEDBACK,
            [['field' => 'title', 'value' => 'Category']]
        );
        foreach ($this->feedback as $feedbackId) {
            $values = [
                'feedback_id' => $feedbackId,
                'field_id'    => $categoryDefId[0],
                'value'       => 0,
                'input'       => $this->randomArrayValue($this->categories),
            ];
            $batch[] = $values;
        }
        $this->db->batchInsert(self::TABLE_CUSTOM_DATA_FEEDBACK, $batch, true);
    }

    private function loadFeedbackLabels()
    {
        $this->faker->unique(true);
        while (count($this->labels) < self::NUM_LABELS) {
            if ($label = strtolower($this->faker->unique()->company)) {
                $this->labels[] = $label;
            }
        }
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
}
