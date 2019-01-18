<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\ObjectTranslatable;
use Application\DeskPRO\Entity\Labels\Label;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use DateTime;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableTrait;
use DeskPRO\Bundle\AppBundle\Helper\AttachmentHelper;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @PortalLinkRoute("portal_kb_view", route_param_map={"slug":"slug"})
 * @PortalLinkRoute("portal_kb_view", route_param_map={"slug": "id"}, type="permalink")
 * @PortalLinkRoute("portal_kb_article_toggle_subscription",
 *     route_param_map={"slug":"slug"}, type="toggle_subscription")
 * @PortalLinkRoute("portal_kb_article_vote_up", route_param_map={"slug":"slug"}, type="vote_up")
 * @PortalLinkRoute("portal_kb_article_vote_down", route_param_map={"slug":"slug"}, type="vote_down")
 */
class Article extends ContentAbstract implements HighlightableModelInterface, LabelsOwner, ObjectTranslatableInterface
{
    use ObjectTranslatableTrait;

    const CONTENT_TYPE = 'article';

    const END_ACTION_DELETE  = 'delete';
    const END_ACTION_ARCHIVE = 'archive';

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|ArticleCategory[]
     */
    protected $categories;

    /**
     * Revisions of this article.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $revisions;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @Assert\Valid()
     */
    protected $attachments;

    /**
     * @var \DateTime
     */
    protected $date_end;

    /**
     * @var string
     */
    protected $end_action = null;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|CustomDataArticle[]
     */
    protected $custom_data;

    /**
     * String array of labels associated with this article.
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     *
     * \Doctrine\Common\Collections\ArrayCollection.
     */
    protected $labels;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    /**
     * @var ArrayCollection|ObjectLang[]
     */
    protected $props_translations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->categories         = new ArrayCollection();
        $this->attachments        = new ArrayCollection();
        $this->custom_data        = new ArrayCollection();
        $this->labels             = new ArrayCollection();
        $this->props_translations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param DateTime $date_end
     *
     * @return $this
     */
    public function setDateEnd(DateTime $date_end = null)
    {
        $this->setModelField('date_end', $date_end);

        return $this;
    }

    /**
     * @param $end_action
     *
     * @return $this
     */
    public function setEndAction($end_action)
    {
        $this->setModelField('end_action', $end_action);

        return $this;
    }

    /**
     * @return string
     */
    public function getEndAction()
    {
        return $this->end_action;
    }

    /**
     * Set entity status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        if ($status == 'approve') {
            $status = self::STATUS_PUBLISHED;
        }

        parent::setStatus($status);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function getRevisions()
    {
        return $this->revisions;
    }

    /**
     * {@inheritdoc}
     */
    public function clearLabels()
    {
        foreach ($this->labels as $data) {
            $this->labels->removeElement($data);
        }

        $this->_onPropertyChanged('labels', null, $this->labels);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel(Label $label)
    {
        $this->labels->add($label);
        $label['article'] = $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', null, $this->labels);
        }
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param CustomDefArticle|int $fieldId
     *
     * @return CustomDataArticle
     */
    public function getCustomDataForField($fieldId)
    {
        if ($fieldId instanceof CustomDefArticle) {
            $fieldId = $fieldId['id'];
        }

        $return = [];

        foreach ($this->custom_data as $data) {
            if ($data->getField()->getId() == $fieldId || $data->getRootField()->getId() == $fieldId) {
                $return[] = $data;
            }
        }

        if (count($return) === 1 && $return[0]->getField()->getType() != 'file') {
            $return = array_pop($return);
        } elseif (empty($data)) {
            $return = null;
        }

        return $return;
    }

    /**
     * @param CustomDefArticle $field
     */
    public function removeCustomDataForField(CustomDefArticle $field)
    {
        $parentId = null;
        $fieldId  = $field->getId();
        if ($field->getParent()) {
            $parentId = $field->getParent()->getId();
        }

        $change = false;
        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $fieldId or $data['field_id'] == $parentId) {
                $change = true;
                $this->custom_data->removeElement($data);
            }
        }

        if ($change) {
            $this->_onPropertyChanged('custom_data', null, $this->custom_data);
        }
    }

    /**
     * Check if this article has a specific custom field.
     *
     * @param int $fieldId
     *
     * @return bool
     */
    public function hasCustomField($fieldId)
    {
        foreach ($this->custom_data as $data) {
            if ($data->getField()->getId() == $fieldId) {
                return true;
            }
        }

        foreach ($this->custom_data as $data) {
            if ($data->getField()->getParent() and $data->getField()->getParent()->getId() == $fieldId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ArrayCollection|CustomDataArticle[]
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }

    /**
     * Add custom data.
     *
     * @param CustomDataArticle $data
     */
    public function addCustomData(CustomDataArticle $data)
    {
        $this->custom_data->add($data);
        $data['article'] = $this;
        $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
    }

    /**
     * Reset custom data.
     *
     * @return $this
     */
    public function resetCustomData()
    {
        $this->custom_data->clear();

        return $this;
    }

    /**
     * Reset custom data.
     *
     * @return $this
     */
    public function resetCategories()
    {
        $this->categories->clear();
        $this->_onPropertyChanged('categories', null, $this->categories);

        return $this;
    }

    /**
     * @param ArticleCategory $cat
     *
     * @return bool
     */
    public function isInCategory(ArticleCategory $cat)
    {
        return $this->categories->contains($cat);
    }

    /**
     * @param ArticleCategory $cat
     *
     * @return $this
     */
    public function addToCategory(ArticleCategory $cat)
    {
        $this->categories->add($cat);

        return $this;
    }

    /**
     * @param ArticleCategory $cat
     *
     * @return $this
     */
    public function removeFromCategory(ArticleCategory $cat)
    {
        $this->categories->removeElement($cat);

        return $this;
    }

    /**
     * @param array $cats
     *
     * @return $this
     */
    public function setCategories(array $cats)
    {
        $helper = new \Application\DeskPRO\ORM\CollectionHelper($this, 'categories');
        $helper->setCollection($cats);

        return $this;
    }

    /**
     * @param string $sep
     * @param bool   $full
     *
     * @return mixed
     */
    public function getCategoryNames($sep = ', ', $full = true)
    {
        $cats = [];
        foreach ($this->categories as $cat) {
            if ($full) {
                if ($full !== true) {
                    // If its not a boolean, then its a string separator
                    $cats[] = $cat->getFullTitle($full);
                } else {
                    $cats[] = $cat->getFullTitle();
                }
            } else {
                $cats[] = $cat['title'];
            }
        }

        return implode($sep, $cats);
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        $ids = [];

        foreach ($this->categories as $cat) {
            /* @var ArticleCategory $cat */
            $ids[] = $cat->getId();
        }

        return $ids;
    }

    /**
     * @param int $index
     *
     * @return array
     */
    public function getCategoryPath($index = 0)
    {
        $path = [];

        $cat    = $this->categories[$index];
        $path[] = $cat;
        while ($cat['parent']) {
            $cat    = $cat['parent'];
            $path[] = $cat;
        }

        return $path;
    }

    /**
     * @return ArticleCategory|mixed|null|void
     */
    public function getPrimaryCategory()
    {
        if ($this->categories->isEmpty()) {
            return;
        }

        foreach ($this->categories as $c) {
            return $c;
        }
    }

    /**
     * @return ArrayCollection|ArticleCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return ArrayCollection|ArticleAttachment[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Reset attachments.
     *
     * @return $this
     */
    public function resetAttachments()
    {
        $this->attachments->clear();
        $this->_onPropertyChanged('attachments', null, $this->attachments);

        return $this;
    }

    /**
     * @param ArticleAttachment $attach
     */
    public function addAttachment(ArticleAttachment $attach)
    {
        $this->attachments->add($attach);
        $attach['article'] = $this;
    }

    /**
     * @param bool  $primary
     * @param bool  $deep
     * @param array $visited
     *
     * @return array
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = [];
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
     * @param $oldSlug
     *
     * @return ArticleSlugHistory
     */
    protected function addSlugHistory($oldSlug)
    {
        $history = new ArticleSlugHistory($this, $oldSlug);
        $this->slug_history->add($history);

        return $history;
    }

    /**
     * @return Collection|ObjectLang[]
     *
     * @Assert\Valid()
     */
    public function getTitleTranslations()
    {
        return $this->getObjectPropTranslations('title');
    }

    /**
     * @return Collection|ObjectLang[]
     *
     * @Assert\Valid()
     */
    public function getContentTranslations()
    {
        return $this->getObjectPropTranslations('content');
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @return ObjectTranslatable
     */
    public function getObjectTranslatable()
    {
        return ObjectTranslatable::loadObjectTranslatable($this);
    }

    /**
     * @return array
     */
    public static function loadObjectTranslatableMetadata()
    {
        return [
            'with_lang_prop' => 'language',
            'fields'         => ['title', 'content'],
        ];
    }

    /**
     * @param ClassMetadata $metadata
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Article';
        $metadata->setPrimaryTable(
            [
                'name'    => 'articles',
                'indexes' => [
                    'date_published_idx'    => ['columns' => ['date_published']],
                    'date_updated_idx'      => ['columns' => ['date_updated']],
                    'date_last_comment_idx' => ['columns' => ['date_last_comment']],
                    'status_idx'            => ['columns' => ['status']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'date_end',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_end',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'end_action',
                'type'       => 'string',
                'length'     => 10,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'end_action',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'slug',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'slug',
                'unique'     => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'content',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'content',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'content_input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'content_input',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'content_input_type',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'content_input_type',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'view_count',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'view_count',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'total_rating',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'total_rating',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'num_comments',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'num_comments',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'num_ratings',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'num_ratings',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'status',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'status',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'hidden_status',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'hidden_status',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_published',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_published',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_updated',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_updated',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_last_comment',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_last_comment',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'categories',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ArticleCategory',
                'cascade'      => ['persist', 'merge'],
                'inversedBy'   => 'articles',
                'joinTable'    => [
                    'name'        => 'article_to_categories',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'article_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'category_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                ],
                'dpApi' => true,
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'    => 'revisions',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ArticleRevision',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'article',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'attachments',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ArticleAttachment',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'article',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'custom_data',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\CustomDataArticle',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'article',
                'orphanRemoval' => true,
                'dpApi'         => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'labels',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\LabelArticle',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'article',
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'language',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Language',
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'language_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'CASCADE',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'slug_history',
                'targetEntity' => 'Application\DeskPRO\Entity\ArticleSlugHistory',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'article',
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'    => 'comments',
                'targetEntity' => ArticleComment::class,
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'article',
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );

        ObjectTranslatable::loadEntityMetadata($metadata);
        $metadata->addLifecycleCallback('_preUpdate', 'preUpdate');
        $metadata->addEntityListener(Events::postPersist, AttachmentHelper::class, 'verifyBlobs');
        $metadata->addEntityListener(Events::postUpdate, AttachmentHelper::class, 'verifyBlobs');
    }

    /**
     * @return array
     */
    protected function getUpdateFields()
    {
        $fields   = parent::getUpdateFields();
        $fields[] = 'attachments';

        return $fields;
    }
}
