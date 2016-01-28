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

namespace Application\ImportBundle\Entity;

use Application\DeskPRO;
use DateTime;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting article entity.
 *
 * Class Article
 */
final class Article extends AbstractContentEntity implements PersonAwareInterface, LabelAwareInterface
{
    /**
     * @var string
     */
    private $person_email;

    /**
     * @var string
     */
    private $end_action;

    /**
     * @var DateTime
     */
    private $date_end;

    /**
     * @var DateTime
     */
    private $date_updated;

    /**
     * @var array
     */
    private $categories = array();

    /**
     * @var array
     */
    private $labels = array();

    /**
     * @var CustomField[]
     */
    private $custom_fields;

    /**
     * @var Attachment[]
     */
    private $attachments;

    /**
     * @var ArticleComment[]
     */
    private $comments;

    /**
     * @var ObjectLang[]
     */
    private $translations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->custom_fields = new Collection();
        $this->attachments   = new Collection();
        $this->comments      = new Collection();
        $this->translations  = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = $person_email;

        return $this;
    }

    /**
     * End action.
     *
     * @return string
     */
    public function getEndAction()
    {
        return $this->end_action;
    }

    /**
     * Set end action.
     *
     * @param string $end_action
     *
     * @return $this
     */
    public function setEndAction($end_action)
    {
        $this->end_action = $end_action;

        return $this;
    }

    /**
     * Checks if end action is valid.
     *
     * @return bool
     */
    public function isEndActionValid()
    {
        if ($this->end_action) {
            $actions = array(
                DeskPRO\Entity\Article::END_ACTION_ARCHIVE,
                DeskPRO\Entity\Article::END_ACTION_DELETE,
            );

            return in_array($this->end_action, $actions, true);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        if ($this->date_end) {
            return DeskPRO\Entity\ContentAbstract::STATUS_ARCHIVED;
        }

        return parent::getStatus();
    }

    /**
     * Returns date end of publishing.
     *
     * @return DateTime
     */
    public function getDateEnd()
    {
        return $this->date_end;
    }

    /**
     * Set date end of publishing.
     *
     * @param DateTime $date_end
     *
     * @return $this
     */
    public function setDateEnd(DateTime $date_end = null)
    {
        $this->date_end = $date_end;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param DateTime $date_updated
     *
     * @return $this
     */
    public function setDateUpdated(DateTime $date_updated = null)
    {
        $this->date_updated = $date_updated;

        return $this;
    }

    /**
     * Returns article categories.
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add a new category.
     *
     * @param string $category
     *
     * @return $this
     */
    public function addCategory($category)
    {
        $this->categories[] = (string) $category;

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
    public function addLabel($label)
    {
        $this->labels[] = (string) $label;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    /**
     * @param CustomField $custom_field
     *
     * @return $this
     */
    public function addCustomField(CustomField $custom_field)
    {
        $this->custom_fields->attach($custom_field);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments->attach($attachment);

        return $this;
    }

    /**
     * Returns article comments.
     *
     * @return ArticleComment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add an article comment.
     *
     * @param ArticleComment $comment
     *
     * @return $this
     */
    public function addComment(ArticleComment $comment)
    {
        $this->comments->attach($comment);

        return $this;
    }

    /**
     * Returns article translations.
     *
     * @return ObjectLang[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Returns article translations grouped by language.
     *
     * @return ObjectLang[]
     */
    public function getUniqueTranslations()
    {
        return ObjectLang::getUniqueCollection($this->translations);
    }

    /**
     * Add an article property translation.
     *
     * @param ObjectLang $translation
     *
     * @return $this
     */
    public function addTranslation(ObjectLang $translation)
    {
        $this->translations->attach($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (!$this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        return array(
            'oid'            => $this->oid,
            'import_map_key' => $this->import_map_key,
            'person'         => $this->person_email,
            'title'          => $this->title,
            'content'        => $this->content,
            'language'       => $this->language,
            'end_action'     => $this->end_action,
            'slug'           => $this->slug,
            'total_rating'   => $this->total_rating,
            'num_comments'   => $this->num_comments,
            'num_ratings'    => $this->num_ratings,
            'view_count'     => $this->view_count,
            'status'         => $this->status,
            'date_created'   => $this->date_created->format('Y-m-d H:i:s'),
            'date_updated'   => $this->date_updated ? $this->date_updated->format('Y-m-d H:i:s') : null,
            'date_published' => $this->date_published ? $this->date_published->format('Y-m-d H:i:s') : null,
            'date_end'       => $this->date_end ? $this->date_end->format('Y-m-d H:i:s') : null,
            'categories'     => $this->categories,
            'labels'         => $this->labels,
            'custom_fields'  => $this->custom_fields->entitiesToArray(),
            'attachments'    => $this->attachments->entitiesToArray(),
            'comments'       => $this->comments->entitiesToArray(),
            'translations'   => $this->translations->entitiesToArray(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractContentEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('person_email', new Constraints\NotBlank())
            ->addPropertyConstraint('custom_fields', new Constraints\Valid())
            ->addPropertyConstraint('comments', new Constraints\Valid())
            ->addPropertyConstraint('attachments', new Constraints\Valid())
            ->addPropertyConstraint('translations', new Constraints\Valid())
        ;
    }
}
