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
 *
 * @category Entities
 */
namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use DeskPRO\Bundle\PortalBundle\Form\Collection\CustomDataCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;

/**
 * @PortalLinkRoute("portal_feedback_view", route_param_map={"slug":"slug"})
 * @PortalLinkRoute("portal_feedback_view", route_param_map={"slug": "id"}, type="permalink")
 * @PortalLinkRoute("portal_feedback_toggle_subscription", route_param_map={"slug":"slug"}, type="toggle_subscription")
 * @PortalLinkRoute("portal_feedback_vote_up",       route_param_map={"slug":"slug"}, type="vote_up")
 * @PortalLinkRoute("portal_feedback_vote_down", route_param_map={"slug":"slug"}, type="vote_down")
 */
class Feedback extends ContentAbstract implements HighlightableModelInterface
{
    const CONTENT_TYPE = 'feedback';

    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    /**
     * Not public (e.g., waiting for review). But agents see it.
     */
    const STATUS_HIDDEN = 'hidden';

    /**
     * Has this feedback been reviewed by an agent?
     *
     * @var bool
     */
    protected $is_reviewed = false;

    /**
     * @var \Application\DeskPRO\Entity\FeedbackStatusCategory
     *                                                         SWG\Property(name="status_category",type="FeedbackStatusCategory")
     */
    protected $status_category = null;

    /**
     * @var string
     *             SWG\Property(name="hidden_status",type="string")
     */
    protected $hidden_status = null;

    /**
     * @var \Application\DeskPRO\Entity\FeedbackCategory
     *                                                   SWG\Property(name="category",type="array", items="$ref:FeedbackCategory")
     */
    protected $category;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *                                                   SWG\Property(name="revisions",type="array", items="$ref:FeedbackRevision")
     */
    protected $revisions;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *                                                   SWG\Property(name="labels",type="array", items="$ref:LabelFeedback")
     */
    protected $labels;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *                                                   SWG\Property(name="custom_data",type="array", items="$ref:CustomDataFeedback")
     */
    protected $custom_data;

    /**
     * Popularity (see recalculatePopularity).
     *
     * @var string
     *             SWG\Property(name="popularity",type="string")
     */
    protected $popularity = 0;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *                                                   SWG\Property(name="custom_data",type="array", items="$ref:FeedbackAttachment")
     */
    protected $attachments;

    /**
     * @var bool
     */
    protected $_is_new = false;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    /**
     * @var CustomDataCollection
     */
    protected $cdc;

    public function __construct()
    {
        parent::__construct();

        $this->_is_new     = true;
        $this->comments    = new ArrayCollection();
        $this->custom_data = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getTotalRating()
    {
        return $this->total_rating;
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param int $field_id
     *
     * @return CustomDataFeedback
     */
    public function getCustomDataForField($field_id)
    {
        if ($field_id instanceof CustomDefFeedback) {
            $field_id = $field_id['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id) {
                return $data;
            }
        }

        return;
    }

    /**
     * @param CustomDataFeedback $data
     */
    public function addCustomData(CustomDataFeedback $data)
    {
        $this->custom_data->add($data);
        $data['feedback'] = $this;
        $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
    }

    /**
     * Reset custom data
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetCustomData()
    {
        $this->custom_data->clear();

        return $this;
    }

    /**
     * @param $rating
     */
    public function addRating($rating)
    {
        parent::addRating($rating);
        $this->recalculatePopularity();
    }

    public function recalculatePopularity()
    {
        $days = (time() - $this->date_created->getTimestamp()) / 86400;
        if (!$days) {
            $days = 1;
        }

        $pop = ceil($this->total_rating / sqrt($days));

        $this->setModelField('popularity', $pop);
    }

    public function recalculateVoteStats(array $votes)
    {
        $this->num_ratings  = count($votes);
        $this->total_rating = 0;
        foreach ($votes as $v) {
            $this->total_rating += $v->getRating();
        }
        $this->recalculatePopularity();
    }

    public function getCategoryId()
    {
        return $this->category['id'];
    }

    /**
     * Set a category.
     *
     * @param FeedbackCategory $category
     *
     * @return $this
     */
    public function setCategory(FeedbackCategory $category = null)
    {
        if ($category) {
            $this->setModelField('category', $category);
        } else {
            $this->setModelField('category', null);
        }

        return $this;
    }

    public function setCategoryId($id)
    {
        $this->setModelField('category', App::getEntityRepository('DeskPRO:FeedbackCategory')->find($id));

        return $this;
    }

    public function getCategoryName()
    {
        return $this->category->getFullTitle();
    }

    /**
     * At the moment, there is only one custom_data set, this is just a quick way to access its value in twig.
     */
    public function getCustomDataSelection()
    {
        /* @var \Application\DeskPRO\Entity\CustomDataFeedback $data */
        if (!$data = $this->custom_data->last()) {
            return;
        }

        return $data->field->getChildById($data->getValue());
    }

    public function setIsReviewed($yesno)
    {
        $this->setModelField('is_reviewed', $yesno);
    }

    public function setStatus($status)
    {
        $last_status = $this->status;

        $this->_onPropertyChanged('status', $this->status, $status);
        $this->status = $status;

        // there is no STATUS_NEW anymore
        //if ($status == 'approve') {
        //    $status = self::STATUS_NEW;
        //}

        switch ($status) {
            case self::STATUS_ACTIVE:
            case self::STATUS_CLOSED:
                $this['hidden_status'] = null;
                break;

            case self::STATUS_HIDDEN:
                $this['status_category'] = null;
                break;
        }

        if ($this->status != 'hidden' && $last_status == 'hidden') {
            $this->date_published = new \DateTime();
        }
    }

    public function setStatusCode($status_code)
    {
        if (strpos($status_code, '.') !== false) {
            list($status, $sub_status) = explode('.', $status_code, 2);
        } else {
            $status     = $status_code;
            $sub_status = null;
        }

        switch ($status) {
            case self::STATUS_ACTIVE:
            case self::STATUS_CLOSED:
                $this['status'] = $status;
                if ($sub_status) {
                    $status_cat = App::findEntity('DeskPRO:FeedbackStatusCategory', $sub_status);
                    $this->setModelField('status_category', $status_cat);
                    $this->setModelField('date_updated', new \DateTime());
                } else {
                    $this->setModelField('status_category', null);
                    $this->setModelField('date_updated', new \DateTime());
                }
                break;

            case self::STATUS_HIDDEN:
                $this['status']        = $status;
                $this['hidden_status'] = $sub_status;
                break;
        }
    }

    /**
     * Can this be seen in the user portal?
     *
     * @return bool
     */
    public function isVisibleOnPortal()
    {
        return self::STATUS_HIDDEN !== $this->status;
    }

    public function getStatusCode()
    {
        if ($this->status == self::STATUS_ACTIVE or $this->status == self::STATUS_CLOSED) {
            if ($this->status_category) {
                return $this->status.'.'.$this->status_category->id;
            } else {
                return $this->status;
            }
        } elseif ($this->status == self::STATUS_HIDDEN) {
            return $this->status.'.'.$this->hidden_status;
        } else {
            return $this->status;
        }
    }

    public function getCategoryPath()
    {
        $path = array();

        $cat = $this->category;

        if ($cat) {
            $path[] = $cat;
            while ($cat['parent']) {
                $cat    = $cat['parent'];
                $path[] = $cat;
            }
        }

        return $path;
    }

    /**
     * Reset labels.
     *
     * @return $this
     */
    public function resetLabels()
    {
        foreach ($this->labels as $data) {
            $this->labels->removeElement($data);
        }

        $this->_onPropertyChanged('labels', null, $this->labels);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return LabelFeedback
     */
    public function addLabelByString($value)
    {
        if ($ret = $this->findLabelByString($value)) {
            return $ret;
        }
        $label           = new LabelFeedback();
        $label->label    = $value;
        $label->feedback = $this;
        $this->labels->add($label);
        $this->_onPropertyChanged('labels', null, $this->labels);

        return $label;
    }

    /**
     * @param string $value
     *
     * @return LabelTicket|null
     */
    public function findLabelByString($value)
    {
        $x        = new LabelTicket();
        $x->label = $value;

        foreach ($this->labels as $l) {
            if ($l->label === $x->label) {
                return $l;
            }
        }

        return;
    }

    /**
     * @param LabelFeedback $label
     *
     * @return $this
     */
    public function addLabel(LabelFeedback $label)
    {
        $label['feedback'] = $this;
        $this->labels->add($label);

        return $this;
    }

    /**
     * @return \Application\DeskPRO\Entity\LabelFeedback[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return array
     */
    public function getStringLabels()
    {
        return array_map(function (LabelFeedback $label) { return $label->getLabel(); }, $this->getLabels()->toArray());
    }

    /**
     * @return \Application\DeskPRO\Labels\LabelManager
     */
    public function getLabelManager()
    {
        if ($this->_label_manager === null) {
            $this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelFeedback');
        }

        return $this->_label_manager;
    }

    /**
     * Add an attachment.
     *
     * @param FeedbackAttachment $attach
     */
    public function addAttachment(FeedbackAttachment $attach)
    {
        $this->attachments->add($attach);
        $attach->feedback = $this;
    }

    public function _invalidatePageCache()
    {
        $cache = new \Application\DeskPRO\CacheInvalidator\UserPageCache();
        $cache->invalidateRegex('/_feedback(-|_)/');
    }

    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = array();
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }
        }

        return $data;
    }

    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     */
    public function setElasticHighlights(array $highlights)
    {
        if (!empty($highlights)) {
            $this->_search_highlights = $highlights;
        }
    }

    /**
     * Get Elasticsearch highlight data.
     *
     * @param null $field
     *
     * @return array|null
     */
    public function getElasticHighlights($field = null)
    {
        if (is_null($field)) {
            return $this->_search_highlights;
        } else {
            if (isset($this->_search_highlights[$field])) {
                return $this->_search_highlights[$field];
            } else {
                return;
            }
        }
    }

    /**
     * @return FeedbackStatusCategory
     */
    public function getStatusCategory()
    {
        return $this->status_category;
    }

    /**
     * @param FeedbackStatusCategory $status_category
     */
    public function setStatusCategory(FeedbackStatusCategory $status_category = null)
    {
        $this->setModelField('status_category', $status_category);
        $this->setModelField('date_updated', new \DateTime());
    }

    /**
     * @param $field
     */
    public function removeCustomDataForField($field)
    {
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        $change = false;
        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id or $data['field_id'] == $parent_id) {
                $change = true;
                $this->custom_data->removeElement($data);

                if ($parent_id) {
                    $this->getStateChangeRecorder()->record("custom_data.$parent_id", $data, null, true);
                } else {
                    $this->getStateChangeRecorder()->record("custom_data.$field_id", $data, null, true);
                }
            }
        }
    }

    public function getCustomDataCollection()
    {
        return $this->cdc = $this->cdc ?: new CustomDataCollection($this->custom_data, $this);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    public function getCategory()
    {
        return $this->category;
    }

    protected function addSlugHistory($old_slug)
    {
        $history = new FeedbackSlugHistory($this, $old_slug);
        $this->slug_history->add($history);

        return $history;
    }

    /**
     * @param string $value
     *
     * @return Feedback $this
     */
    public function setHiddenStatus($value)
    {
        $this->hidden_status = $value;

        return $this;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Feedback';
        $metadata->setPrimaryTable(
            array(
                'name'    => 'feedback',
                'indexes' => array(
                    'date_published_idx'    => array('columns' => array(0 => 'date_published')),
                    'date_updated_idx'      => array('columns' => array('date_updated')),
                    'date_last_comment_idx' => array('columns' => array('date_last_comment')),
                    'status_idx'            => array('columns' => array('status')),
                ),
            )
        );
        $metadata->addLifecycleCallback('_invalidatePageCache', 'preFlush');
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            array(
                'fieldName'  => 'hidden_status',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'hidden_status',
            )
        );
        $metadata->mapField(array('fieldName' => 'is_reviewed', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_reviewed'));
        $metadata->mapField(
            array(
                'fieldName'  => 'popularity',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'popularity',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'slug',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'slug',
                'unique'     => true,
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'content',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'content',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'view_count',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'view_count',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'total_rating',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'total_rating',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'num_comments',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'num_comments',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'num_ratings',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'num_ratings',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'status',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'status',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'date_published',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_published',
            )
        )
        ;
        $metadata->mapField(
            array(
                'fieldName'  => 'date_updated',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_updated',
            )
        );
        $metadata->mapField(
            array(
                'fieldName'  => 'date_last_comment',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_last_comment',
            )
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'status_category',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\FeedbackStatusCategory',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => array(
                    0 => array(
                        'name'                 => 'status_category_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ),
                ),
                'dpApi' => true,
            )
        );
        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'category',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\FeedbackCategory',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => array(0 => array('name' => 'category_id', 'referencedColumnName' => 'id')),
                'dpApi'        => true,
            )
        );
        $metadata->mapOneToMany(
            array(
                'fieldName'    => 'revisions',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\FeedbackRevision',
                'cascade'      => array(0 => 'remove', 1 => 'persist', 3 => 'merge'),
                'mappedBy'     => 'feedback',
            )
        );
        $metadata->mapOneToMany(
            array(
                'fieldName'    => 'comments',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\FeedbackComment',
                'cascade'      => array(0 => 'remove', 1 => 'persist', 3 => 'merge'),
                'mappedBy'     => 'feedback',
            )
        );
        $metadata->mapOneToMany(
            array(
                'fieldName'     => 'labels',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\LabelFeedback',
                'cascade'       => array(0 => 'remove', 1 => 'persist', 3 => 'merge'),
                'mappedBy'      => 'feedback',
                'orphanRemoval' => true,
            )
        );
        $metadata->mapOneToMany(
            array(
                'fieldName'     => 'custom_data',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\CustomDataFeedback',
                'cascade'       => array(0 => 'remove', 1 => 'persist', 3 => 'merge'),
                'mappedBy'      => 'feedback',
                'orphanRemoval' => true,
                'dpApi'         => true,
            )
        );
        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => array(
                    0 => array(
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ),
                ),
                'dpApi' => true,
            )
        );
        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'language',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Language',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => array(
                    0 => array(
                        'name'                 => 'language_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ),
                ),
                'dpApi' => true,
            )
        );
        $metadata->mapOneToMany(
            array(
                'fieldName'    => 'attachments',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\FeedbackAttachment',
                'cascade'      => array(0 => 'remove', 1 => 'persist', 3 => 'merge'),
                'mappedBy'     => 'feedback',
                'dpApi'        => true,
                'dpApiDeep'    => true,
                'dpApiPrimary' => true,
            )
        );
        $metadata->mapOneToMany(
            array(
                'fieldName'    => 'slug_history',
                'targetEntity' => 'Application\DeskPRO\Entity\FeedbackSlugHistory',
                'cascade'      => array(0 => 'remove', 1 => 'persist', 3 => 'merge'),
                'mappedBy'     => 'feedback',
            )
        );
    }
}
