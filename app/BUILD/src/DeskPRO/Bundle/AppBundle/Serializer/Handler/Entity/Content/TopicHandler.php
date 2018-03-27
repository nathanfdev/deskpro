<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Twig\Extension\TemplatingExtension;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Topic as TopicModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class TopicHandler.
 */
class TopicHandler extends AbstractEntityHandler
{
    /**
     * @var TemplatingExtension
     */
    protected $templatingExtension;

    /**
     * Constructor.
     *
     * @param TemplatingExtension $templatingExtension
     */
    public function __construct(TemplatingExtension $templatingExtension)
    {
        $this->templatingExtension = $templatingExtension;
    }

    /**
     * {@inheritdoc}
     *
     * @param Topic $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new TopicModel($entity, $this->templatingExtension);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Topic::class;
    }
}
