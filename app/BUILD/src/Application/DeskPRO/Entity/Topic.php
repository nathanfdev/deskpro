<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\Topic as TopicRepository;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @PortalLinkRoute("portal_guides_topic_view", route_param_map={"slug":"slug", "guide_slug":"guide_slug", "parents_slug":"parents_slug"})
 * @PortalLinkRoute("portal_guides_topic_permalink", route_param_map={"slug": "id"}, type="permalink")
 */
class Topic extends ContentAbstract implements HighlightableModelInterface
{
    const CONTENT_TYPE = 'topic';

    /**
     * @var Guide
     */
    protected $guide;

    /**
     * Revisions of this Manual Topic.
     *
     * @var ArrayCollection
     */
    protected $revisions;

    /**
     * Display order.
     *
     * @var int
     */
    protected $display_order = 0;

    /**
     * Topic's parent.
     *
     * @var Topic
     */
    protected $parent;

    /**
     * Topic's children.
     *
     * @var Topic[]|ArrayCollection
     */
    protected $children;

    /**
     * Topic without content used for structure.
     *
     * @var bool
     */
    protected $no_content = false;

    /**
     * The main content originally input type, markdown or HTML.
     *
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $content_input_type = self::CONTENT_TYPE_MARKDOWN;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    protected function addSlugHistory($oldSlug)
    {
        $history = new TopicSlugHistory($this, $oldSlug);
        $this->slug_history->add($history);

        return $history;
    }

    /**
     * @return Guide
     */
    public function getGuide()
    {
        return $this->guide;
    }

    /**
     * @param Guide $guide
     *
     * @return Topic
     */
    public function setGuide($guide)
    {
        $this->setModelField('guide', $guide);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRevisions()
    {
        return $this->revisions;
    }

    /**
     * @param ArrayCollection $revisions
     *
     * @return Topic
     */
    public function setRevisions($revisions)
    {
        $this->setModelField('revisions', $revisions);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     *
     * @return Topic
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', $display_order);

        return $this;
    }

    /**
     * @return Topic
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Topic $parent
     *
     * @return Topic
     */
    public function setParent($parent)
    {
        $this->setModelField('parent', $parent);

        return $this;
    }

    /**
     * @return Topic[]|ArrayCollection
     */
    public function getChildren()
    {
        if ($this->children) {
            return $this->children->filter(function ($topic) {
                /* @var Topic $topic */
                return $topic->getStatus() !== Topic::STATUS_HIDDEN;
            });
        }

        return [];
    }

    /**
     * @param Topic[]|ArrayCollection $children
     *
     * @return Topic
     */
    public function setChildren($children)
    {
        $this->setModelField('children', $children);

        return $this;
    }

    /**
     * @return bool
     */
    public function isNoContent()
    {
        return $this->no_content;
    }

    /**
     * @param bool $no_content
     *
     * @return Topic
     */
    public function setNoContent($no_content)
    {
        $this->setModelField('no_content', $no_content);

        return $this;
    }

    public function getGuideSlug()
    {
        return $this->getGuide()->getSlug();
    }

    public function getParentsSlug()
    {
        if (!$this->getParent()) {
            return '';
        }
        $slug   = '';
        $parent = $this->getParent();
        $i      = 0;
        while ($parent) {
            // Protect infinite loop
            if ($i++ > 100) {
                break;
            }
            $slug   = '/'.$parent->getSlug().$slug;
            $parent = $parent->getParent();
        }

        return $slug;
    }

    /**
     * Not yet implemented.
     *
     * @return array
     */
    public function getLabels()
    {
        return [];
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

    public function getContentFull()
    {
        return preg_replace_callback(['|<(h[1-6])>(.+?)<\/h[1-6]>|'],
            function ($match) {
                $id = '';
                if ($match[1] == 'h1') {
                    $id = strtolower($match[2]);
                    $id = str_replace(['[', '(', ')', ':', ']'], '', $id);
                    $id = str_replace(' ', '-', $id);
                    $id = $this->getSlug().'_'.$id;
                }

                return '<span class="'.$match[1].'" id="'.$id.'">'.$match[2].'</span>';
            },
            $this['content']
        );
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = TopicRepository::class;
        $metadata->setPrimaryTable(
            [
                'name'    => 'topics',
                'indexes' => [
                    'date_published_idx'    => ['columns' => [0 => 'date_published']],
                    'date_updated_idx'      => ['columns' => ['date_updated']],
                    'date_last_comment_idx' => ['columns' => ['date_last_comment']],
                    'status_idx'            => ['columns' => ['status']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
                'fieldName'  => 'no_content',
                'type'       => 'boolean',
                'columnName' => 'no_content',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'display_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'display_order',
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
                'fieldName'  => 'date_last_comment',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_last_comment',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'parent',
                'targetEntity' => self::class,
                'mappedBy'     => null,
                'inversedBy'   => 'children',
                'joinColumns'  => [
                    [
                        'name'                 => 'parent_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'children',
                'targetEntity' => self::class,
                'mappedBy'     => 'parent',
                'orderBy'      => ['display_order' => 'ASC'],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'guide',
                'targetEntity' => Guide::class,
                'mappedBy'     => null,
                'inversedBy'   => 'topics',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'guide_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'revisions',
                'targetEntity' => TopicRevision::class,
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'topic',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => Person::class,
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
                'targetEntity' => Language::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'language_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'slug_history',
                'targetEntity' => TopicSlugHistory::class,
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'topic',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'comments',
                'targetEntity' => TopicComment::class,
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'topic',
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );

        $metadata->addLifecycleCallback('_preUpdate', 'preUpdate');
    }
}
