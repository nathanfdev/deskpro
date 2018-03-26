<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator\Content;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;

class GuidesLinkGenerator extends AbstractContentLinkGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof Topic && $context === ObjectRouter::CONTEXT_PORTAL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBrand(ContentAbstract $object)
    {
        /* @var Topic $object */

        /** @var Guide $guide */
        $guide = $object->getGuide();

        return $guide ? $guide->getBrand() : null;
    }
}
