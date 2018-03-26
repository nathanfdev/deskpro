<?php

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
