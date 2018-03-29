<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Download as DownloadEntity;
use Application\DeskPRO\Entity\DownloadCategory;
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
     * Constructor.
     *
     * @param DownloadEntity $entity
     */
    public function __construct(DownloadEntity $entity)
    {
        parent::__construct($entity);
        $this->category = $entity->getCategory();
        $this->blob     = $entity->getBlob();
    }
}
