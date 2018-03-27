<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\ContentAbstract as ContentAbstractEntity;
use Application\DeskPRO\Entity\Download as DownloadEntity;
use Application\DeskPRO\Entity\News as NewsEntity;
use JMS\Serializer\Annotation as JMS;

class ContentCsv extends ContentAbstract
{
    /**
     * Person created this content first time.
     *
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * Person created this content first time.
     *
     * @JMS\Type("string")
     */
    protected $language;

    /**
     * Content category.
     *
     * @JMS\Type("string")
     */
    protected $category;

    /**
     * Constructor.
     *
     * @param ContentAbstractEntity $entity
     */
    public function __construct(ContentAbstractEntity $entity)
    {
        parent::__construct($entity);

        $this->person   = $entity->getPerson() ? $entity->getPerson()->getName() : '';
        $this->language = $entity->getLanguage() ? $entity->getLanguage()->getTitle() : '';
        $this->content  = mb_substr($entity->getContentPlain(), 0, 50);
        $this->category = $this->getCategory($entity);
    }

    /**
     * @param ContentAbstract|NewsEntity|DownloadEntity $entity
     *
     * @return string
     */
    protected function getCategory($entity)
    {
        return $entity->getCategory() ? $entity->getCategory()->getTitle() : '';
    }
}
