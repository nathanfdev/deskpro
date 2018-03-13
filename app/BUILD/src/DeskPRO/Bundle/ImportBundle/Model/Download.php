<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ImportBundle\Model;

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
    use LabelAwareTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * @var Attachment
     *
     * @JMS\Type("DeskPRO\Bundle\ImportBundle\Model\Blob")
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    private $blob;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $num_downloads = 0;

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
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * Set a download attachment.
     *
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob(Blob $blob = null)
    {
        $this->blob = $blob;

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
    public function setPerson($person)
    {
        $this->person = $person;

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
}
