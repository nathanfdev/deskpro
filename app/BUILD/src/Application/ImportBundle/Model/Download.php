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

namespace Application\ImportBundle\Model;

use Application\DeskPRO\Entity\CategoryAbstract;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting download entity.
 *
 * Class Download
 */
class Download extends AbstractContentModel implements PersonAwareInterface, LabelAwareModelInterface
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * @var Attachment
     *
     * @JMS\Type("Application\ImportBundle\Model\Attachment")
     *
     * @Assert\Valid()
     */
    private $attachment;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Email(strict="true")
     */
    private $person;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $num_downloads = 0;

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $labels = [];

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
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * {@inheritdoc}
     */
    public function setPerson($person_email)
    {
        $this->person = $person_email;

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
}
