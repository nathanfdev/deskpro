<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\Entity\ContentTemplate as ContentTemplateEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ContentTemplate.
 */
class ContentTemplate
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Content template type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * Person created this content first time.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var PersonEntity
     */
    protected $person = null;

    /**
     * Content template title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * DateTime when content template was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * DateTime when content template was updated last time.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateUpdated;

    /**
     * Template itself.
     *
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $template;

    /**
     * Constructor.
     *
     * @param ContentTemplateEntity $entity
     */
    public function __construct(ContentTemplateEntity $entity)
    {
        $this->id          = $entity->getId();
        $this->title       = $entity->getTitle();
        $this->dateCreated = $entity->getDateCreated();
        $this->dateUpdated = $entity->getDateUpdated();
        $this->template    = $entity->getTemplate();
        $this->person      = $entity->getPerson();
        $this->type        = $entity->getType();
    }
}
