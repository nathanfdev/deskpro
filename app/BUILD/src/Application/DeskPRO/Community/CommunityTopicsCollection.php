<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\CustomFields\CommunityFieldManager;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CustomDataCommunityTopic;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class CommunityTopicsCollection
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CommunityTopic[]
     */
    protected $communityTopics;

    /**
     * @var CustomDataCommunityTopic[]
     */
    protected $communityTopicsData;

    /**
     * @var CommunityFieldManager
     */
    protected $communityFieldManager;

    /**
     * @var UserCategory[]
     */
    protected $user_cats = [];

    public function __construct(array $communityTopics, EntityManager $em, CommunityFieldManager $communityFieldManager)
    {
        $communityTopics = Arrays::keyFromData($communityTopics, 'id');

        $this->communityTopics       = $communityTopics;
        $this->em                    = $em;
        $this->communityFieldManager = $communityFieldManager;
    }

    /**
     * Get the full array of community topics.
     *
     * @return \Application\DeskPRO\Entity\CommunityTopic[]
     */
    public function getCommunityTopics()
    {
        return $this->communityTopics;
    }

    /**
     * Get an array of display data which includes community topics and all associated data with it.
     *
     * @return array
     */
    public function getDisplayArray()
    {
        $data = [];

        foreach ($this->communityTopics as $topic) {
            $data[$topic->getId()] = $this->getDisplayArrayForTopic($topic);
        }

        return $data;
    }

    /**
     * Get a display array for a community topics.
     *
     * @param CommunityTopic $communityTopic
     *
     * @return array
     */
    public function getDisplayArrayForTopic(CommunityTopic $communityTopic)
    {
        $customData   = $this->getDataForTopic($communityTopic);
        $userCategory = $this->getUserCategory($communityTopic);

        $data = [
            'topic'         => $communityTopic,
            'custom_data'   => $customData,
            'user_category' => $userCategory,
        ];

        return $data;
    }

    /**
     * Get an array of all custom data on community topics.
     *
     * @return CustomDataCommunityTopic[]
     */
    public function getCustomData()
    {
        if ($this->communityTopicsData !== null) {
            return $this->communityTopicsData;
        }

        $this->communityTopicsData = [];

        if ($this->communityTopics) {
            $ids = array_keys($this->communityTopics);

            $results = $this->em->createQuery('
                SELECT d
                FROM DeskPRO:CustomDataCommunityTopic d
                LEFT JOIN d.field AS field
                WHERE d.topic IN (?0)
            ')->setParameter(0, $ids)->execute();

            foreach ($results as $data) {
                if (!isset($this->communityTopicsData[$data->topic->getId()])) {
                    $this->communityTopicsData[$data->topic->getId()] = [];
                }

                $this->communityTopicsData[$data->topic->getId()][$data->field->getId()] = $data;
            }
        }

        return $this->communityTopicsData;
    }

    /**
     * Get data for a specific community topic.
     *
     * @param CommunityTopic $communityTopic
     *
     * @return CustomDataCommunityTopic
     */
    public function getDataForTopic(CommunityTopic $communityTopic)
    {
        $all_data = $this->getCustomData();

        return isset($all_data[$communityTopic->getId()]) ? $all_data[$communityTopic->getId()] : [];
    }

    /**
     * Get the user category title.
     *
     * @param CommunityTopic $communityTopic
     *
     * @return UserCategory|null
     */
    public function getUserCategory(CommunityTopic $communityTopic)
    {
        if (array_key_exists($communityTopic->getId(), $this->user_cats)) {
            return $this->user_cats[$communityTopic->getId()];
        }

        $cat_field = $this->communityFieldManager->getUserCategoryField();
        if (!$cat_field) {
            $this->user_cats[$communityTopic->getId()] = null;

            return;
        }

        $options = $this->communityFieldManager->getFieldChildren($cat_field);
        $options = Arrays::keyFromData($options, 'id');

        $customData = $this->getDataForTopic($communityTopic);
        $chosen     = null;
        foreach ($options as $opt) {
            if (isset($customData[$opt->getId()])) {
                $chosen = $opt;
            }
        }

        if (!$chosen) {
            $this->user_cats[$communityTopic->getId()] = null;

            return;
        }

        if ($chosen->getOption('parent_id')) {
            $chosen_parent                             = $options[$chosen->getOption('parent_id')];
            $this->user_cats[$communityTopic->getId()] = new UserCategory($chosen_parent, $chosen);
        } else {
            $this->user_cats[$communityTopic->getId()] = new UserCategory($chosen);
        }

        return $this->user_cats[$communityTopic->getId()];
    }
}
