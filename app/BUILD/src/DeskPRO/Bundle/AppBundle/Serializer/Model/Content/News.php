<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\News as NewsEntity;
use Application\DeskPRO\Entity\NewsCategory;
use JMS\Serializer\Annotation as JMS;

/**
 * Class News.
 */
class News extends ContentAbstract
{
    /**
     * Content category.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\NewsCategory>")
     *
     * @var NewsCategory
     */
    protected $category;

    /**
     * Items attached to the content.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\NewsAttachment>>")
     *
     * @var TicketAttachment[]
     */
    protected $attachments;

    /**
     * Constructor.
     *
     * @param NewsEntity $entity
     */
    public function __construct(NewsEntity $entity)
    {
        parent::__construct($entity);
        $this->category    = $entity->getCategory();
        $this->attachments = $entity->getAttachments();
    }
}
