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

use Application\DeskPRO\Entity\CategoryAbstract;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting download entity.
 *
 * Class Download
 */
final class Download extends AbstractContentEntity implements PersonAwareInterface, LabelAwareInterface
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var Attachment
     */
    private $attachment;

    /**
     * @var string
     */
    private $person_email;

    /**
     * @var int
     */
    private $num_downloads = 0;

    /**
     * @var array
     */
    private $labels = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_DOWNLOAD;
    }

    /**
     * Download category.
     *
     * @return CategoryAbstract
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set a download category.
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
     * Download attachment.
     *
     * @return Attachment
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * Set a download attachment.
     *
     * @param Attachment $attachment
     *
     * @return $this
     */
    public function setAttachment(Attachment $attachment = null)
    {
        $this->attachment = $attachment;

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
     * Number of downloads.
     *
     * @return int
     */
    public function getNumDownloads()
    {
        return $this->num_downloads;
    }

    /**
     * Set a number of downloads.
     *
     * @param int $num_downloads
     *
     * @return $this
     */
    public function setNumDownloads($num_downloads)
    {
        $this->num_downloads = (int) $num_downloads;

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
    public function toArray()
    {
        if (!$this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        return array(
            'oid'            => $this->oid,
            'person'         => $this->person_email,
            'import_map_key' => $this->import_map_key,
            'title'          => $this->title,
            'content'        => $this->content,
            'language'       => $this->language,
            'slug'           => $this->slug,
            'total_rating'   => $this->total_rating,
            'num_comments'   => $this->num_comments,
            'num_ratings'    => $this->num_ratings,
            'num_downloads'  => $this->num_downloads,
            'view_count'     => $this->view_count,
            'category'       => $this->category,
            'status'         => $this->status,
            'attachment'     => $this->attachment ? $this->attachment->toArray() : null,
            'date_created'   => $this->date_created->format('Y-m-d H:i:s'),
            'date_published' => $this->date_published ? $this->date_published->format('Y-m-d H:i:s') : null,
            'labels'         => $this->labels,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractContentEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('attachment', new Constraints\Valid())
        ;
    }
}
