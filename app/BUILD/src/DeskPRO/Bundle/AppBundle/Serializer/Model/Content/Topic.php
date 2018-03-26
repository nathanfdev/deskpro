<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\Topic as TopicEntity;
use Application\DeskPRO\Twig\Extension\TemplatingExtension;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Topic.
 */
class Topic extends ContentAbstract
{
    /**
     * Display order.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("detail")
     *
     * @var int
     */
    protected $displayOrder = 0;

    /**
     * Topic's parent.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Topic>")
     *
     * @var Topic
     */
    protected $parent;

    /**
     * Topic's children.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Topic>>")
     *
     * @var Topic[]
     */
    protected $children;

    /**
     * Display order.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("detail")
     *
     * @var int
     */
    protected $calcNumComments = 0;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("collection<Application\DeskPRO\Entity\TopicComment>")
     */
    protected $comments;

    /**
     * Constructor.
     *
     * @param TopicEntity         $entity
     * @param TemplatingExtension $templatingExtension
     */
    public function __construct(TopicEntity $entity, $templatingExtension)
    {
        parent::__construct($entity);

        $this->person          = null;
        $this->children        = $entity->getChildren();
        $this->displayOrder    = $entity->getDisplayOrder();
        $this->parent          = $entity->getParent();
        $this->calcNumComments = $entity->getCalcNumComments();
        $this->comments        = $entity->getComments();
        $this->content         = $templatingExtension->replaceContent($this->content);
    }
}
