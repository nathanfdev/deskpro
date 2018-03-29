<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator\Content;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;

class NewsLinkGenerator extends AbstractContentLinkGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof News && $context === ObjectRouter::CONTEXT_PORTAL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBrand(ContentAbstract $object)
    {
        /* @var News $object */
        return $object->getCategory()->getBrand();
    }
}
