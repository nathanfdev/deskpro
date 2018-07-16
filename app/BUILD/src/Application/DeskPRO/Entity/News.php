<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Entity\Labels\Label;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use DateTime;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @PortalLinkRoute("portal_news_view", route_param_map={"slug": "slug"})
 * @PortalLinkRoute("portal_news_view", route_param_map={"slug": "id"}, type="permalink")
 * @PortalLinkRoute("portal_news_post_toggle_subscription", route_param_map={"slug":"slug"}, type="toggle_subscription")
 * @PortalLinkRoute("portal_news_post_vote_up", route_param_map={"slug":"slug"}, type="vote_up")
 * @PortalLinkRoute("portal_news_post_vote_down", route_param_map={"slug":"slug"}, type="vote_down")
 */
class News extends ContentAbstract implements HighlightableModelInterface, LabelsOwner
{
    const CONTENT_TYPE = 'news';

    /**
     * @var NewsCategory
     */
    protected $category;

    /**
     * Revisions of this news.
     *
     * @var ArrayCollection
     */
    protected $revisions;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @Assert\Valid()
     */
    protected $attachments;

    /**
     * String array of labels associated with this news.
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     *
     * @var ArrayCollection|LabelNews[]
     */
    protected $labels;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    /**
     * @var DateTime
     */
    protected $date_end;

    /**
     * @var string
     */
    protected $end_action = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->attachments = new ArrayCollection();
    }

    public function getContentHtml()
    {
        $content = $this->getContent();

        // Remove the intro separator
        $content = RegexUtils::safePregReplace('#[\r\n]+\-{3,}[\r\n]+#', "\n", $content);

        return $content;
    }

    public function getExcerptHtml()
    {
        $content = Strings::html2Text($this->getContent());
        if ($pos = strpos($content, '![more]')) {
            $excerpt = substr($content, $pos);
        } elseif ($pos = strpos($content, "\n\n")) {
            $excerpt = substr($content, 0, $pos);
        } else {
            $excerpt = $content;
        }

        if (str_word_count($excerpt) > 50) {
            $words   = str_word_count($excerpt, 2);
            $pos     = Arrays::getNthKey($words, 50);
            $excerpt = substr($excerpt, 0, $pos);
            $excerpt = RegexUtils::safePregReplace('#[^a-zA-Z0-9]$#', '', $excerpt);
            $excerpt .= '...';
        }

        return $excerpt;
    }

    public function getCountWordsAfterExcerpt()
    {
        $content = strip_tags($this->getContentHtml());
        $exceprt = strip_tags($this->getExcerptHtml());

        $diff = str_word_count($content) - str_word_count($exceprt);

        return $diff;
    }

    /**
     * Set a category.
     *
     * @param NewsCategory $category
     *
     * @return $this
     */
    public function setCategory(NewsCategory $category = null)
    {
        $this->setModelField('category', $category);

        return $this;
    }

    public function getCategoryId()
    {
        return $this->category['id'];
    }

    public function getCategoryPath()
    {
        $path = [];

        $cat    = $this->category;
        $path[] = $cat;
        while ($cat['parent']) {
            $cat    = $cat['parent'];
            $path[] = $cat;
        }

        return $path;
    }

    /**
     * @return ArrayCollection|NewsAttachment[]
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
     * @param NewsAttachment $attach
     */
    public function addAttachment(NewsAttachment $attach)
    {
        $this->attachments->add($attach);
        $attach['news'] = $this;
    }

    /**
     * Reset labels.
     *
     * @return $this
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
     * @param Label $label
     *
     * @return $this
     */
    public function addLabel(Label $label)
    {
        $label['news'] = $this;
        $this->labels->add($label);
        $this->_onPropertyChanged('labels', null, $this->labels);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', $this->labels, $this->labels);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\LabelNews[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
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

    public function getCategory()
    {
        return $this->category;
    }

    protected function addSlugHistory($oldSlug)
    {
        $history = new NewsSlugHistory($this, $oldSlug);
        $this->slug_history->add($history);

        return $history;
    }

    /**
     * @return string
     */
    public function getEndAction()
    {
        return $this->end_action;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\News';
        $metadata->setPrimaryTable(
            [
                'name'    => 'news',
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
        $metadata->mapField(
            ['fieldName' => 'date_end', 'type' => 'datetime', 'nullable' => true, 'columnName' => 'date_end']
        );
        $metadata->mapField(
            [
                'fieldName'  => 'end_action',
                'type'       => 'string',
                'length'     => 10,
                'nullable'   => true,
                'columnName' => 'end_action',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'category',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\NewsCategory',
                'mappedBy'     => null,
                'inversedBy'   => 'articles',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'category_id',
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
                'targetEntity' => 'Application\\DeskPRO\\Entity\\NewsRevision',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'news',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'attachments',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\NewsAttachment',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'news',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'labels',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\LabelNews',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'news',
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
                'targetEntity' => 'Application\DeskPRO\Entity\NewsSlugHistory',
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'news',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'comments',
                'targetEntity' => NewsComment::class,
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'news',
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );

        $metadata->addLifecycleCallback('_preUpdate', 'preUpdate');
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
