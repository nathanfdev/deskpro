<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator\Content;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Download;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;

class DownloadsLinkGenerator extends AbstractContentLinkGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof Download && $context === ObjectRouter::CONTEXT_PORTAL && $type == 'permalink';
    }

    /**
     * {@inheritdoc}
     */
    protected function getBrand(ContentAbstract $object)
    {
        /* @var Download $object */
        return $object->getCategory()->getBrand();
    }
}
