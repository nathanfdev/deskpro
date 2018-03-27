<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Search;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\PortalBundle\View\PortalIconFactory;

/**
 * There are very few serialization needs in the portal, so we just have a very simple class here that is capable
 * of serializing search results. This is not nearly as robust as the ApiBundle's serializer, and to use that here
 * is unnecessary.
 */
class SimplePortalEntitySerializer
{
    /**
     * @var ObjectRouter
     */
    private $object_router;

    /**
     * @var PortalIconFactory
     */
    private $icon_factory;

    public function __construct(ObjectRouter $object_router, PortalIconFactory $icon_factory)
    {
        $this->object_router = $object_router;
        $this->icon_factory  = $icon_factory;
    }

    /**
     * This will recursively travel through an array and replace specific entity objects with an array transformation.
     *
     * @param array $data
     *
     * @return array an array that can be transformed into JSON
     */
    public function serializeArray(array $data)
    {
        $result = [];

        foreach ($data as $key => $node) {
            $result[$key] = $this->transformNode($node);
        }

        return $result;
    }

    protected function transformNode($node)
    {
        if (is_object($node)) {
            $result = $this->transformObject($node);
        } elseif (is_array($node)) {
            $result = [];
            foreach ($node as $key => $val) {
                $result[$key] = $this->transformNode($val);
            }
        } else {
            $result = $node;
        }

        return $result;
    }

    protected function transformObject($object)
    {
        $result = [];

        if ($object instanceof Entity\Article
            || $object instanceof Entity\News
            || $object instanceof Entity\Download
            || $object instanceof Entity\Feedback
            || $object instanceof Entity\Topic
        ) {
            $result['id']        = $object->getId();
            $result['name']      = $object->getTranslatedTitle();
            $result['url']       = $this->object_router->getPortalUrl($object);
            $result['icon_html'] = $this->icon_factory->makeContentIcon($object);
            if ($object instanceof Entity\News) {
                if (!$date = $object->getDatePublished()) {
                    $date = $object->getDateCreated();
                }
                try {
                    $result['date'] = $date->format(\DateTime::ISO8601);
                } catch (\Exception $e) {
                }
            }
            if ($object instanceof Entity\Feedback) {
                if ($rating = $object->getTotalRating()) {
                    $result['rating'] = $rating;
                } else {
                    $result['rating'] = 0;
                }
            }
        } elseif ($object instanceof Entity\Ticket) {
            $result['id']   = $object->getId();
            $result['name'] = $object->getSubject();
            $result['url']  = $this->object_router->getPortalUrl($object);
        } elseif ($object instanceof Entity\Person) {
            $result['id']   = $object->getId();
            $result['name'] = $object->getDisplayName();
            $result['url']  = null;
        } elseif ($object instanceof Entity\ChatConversation) {
            $result['id']   = $object->getId();
            $result['name'] = $object->getSubjectLine();
            $result['url']  = $this->object_router->getPortalUrl($object);
        } else {
            $result = null;
        }

        return $result;
    }
}
