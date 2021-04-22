<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Download as DownloadEntity;
use Application\DeskPRO\Entity\DownloadCategory;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Download.
 */
class Download extends ContentAbstract
{
    /**
     * Content category.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\DownloadCategory>")
     *
     * @var DownloadCategory
     */
    protected $category;

    /**
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob;

    /**
     * String array of labels associated with this article.
     *
     * @JMS\Expose()
     * @JMS\Type("ArrayCollection<Application\DeskPRO\Entity\LabelDownload>")
     *
     * @var ArrayCollection
     */
    protected $labels;

    /**
     * String array of labels associated with this download.
     *
     * @JMS\Expose()
     * @JMS\Type("ArrayCollection<Application\DeskPRO\Entity\CustomDataDownload>")
     *
     * @var ArrayCollection
     */
    protected $customData;

    /**
     * Constructor.
     *
     * @param DownloadEntity $entity
     */
    public function __construct(DownloadEntity $entity)
    {
        parent::__construct($entity);
        $this->category    = $entity->getCategory();
        $this->blob        = $entity->getBlob();
        $this->labels      = $entity->getLabels();
        $this->customData  = $entity->getCustomData();
    }
}
