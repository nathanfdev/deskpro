<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting feedback entity.
 *
 * Class Feedback
 */
final class Feedback extends AbstractContentEntity
    implements PersonAwareInterface, LabelAwareInterface, AttachmentsAwareInterface
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $person_email;

    /**
     * @var int
     */
    private $popularity = 0;

    /**
     * @var array
     */
    private $labels = [];

    /**
     * @var Collection
     */
    private $attachments;

    /**
     * @var Collection
     */
    private $custom_fields;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attachments   = new Collection();
        $this->custom_fields = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_FEEDBACK;
    }

    /**
     * Feedback category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set feedback category.
     *
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
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
     * Feedback popularity.
     *
     * @return int
     */
    public function getPopularity()
    {
        return $this->popularity;
    }

    /**
     * Set feedback popularity.
     *
     * @param int $popularity
     *
     * @return $this
     */
    public function setPopularity($popularity)
    {
        $this->popularity = (int) $popularity;

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
        $this->labels[] = $label;

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
    public function isStatusValid()
    {
        $feedback_statuses = [
            DeskPRO\Entity\Feedback::STATUS_ACTIVE,
            DeskPRO\Entity\Feedback::STATUS_CLOSED,
            DeskPRO\Entity\Feedback::STATUS_HIDDEN,
        ];

        return in_array($this->status, $feedback_statuses, true) || parent::isStatusValid();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (!$this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        return [
            'oid'            => $this->oid,
            'import_map_key' => $this->import_map_key,
            'person'         => $this->person_email,
            'language'       => $this->language,
            'title'          => $this->title,
            'content'        => $this->content,
            'popularity'     => $this->popularity,
            'status'         => $this->status,
            'total_rating'   => $this->total_rating,
            'num_comments'   => $this->num_comments,
            'num_ratings'    => $this->num_ratings,
            'view_count'     => $this->view_count,
            'category'       => $this->category,
            'labels'         => $this->labels,
            'date_created'   => $this->date_created->format('Y-m-d H:i:s'),
            'date_published' => $this->date_published ? $this->date_published->format('Y-m-d H:i:s') : null,
            'attachments'    => $this->attachments->entitiesToArray(),
            'custom_fields'  => $this->custom_fields->entitiesToArray(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractContentEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('attachments', new Constraints\Valid())
            ->addPropertyConstraint('custom_fields', new Constraints\Valid())
        ;
    }
}
