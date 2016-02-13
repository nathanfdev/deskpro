<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Serializer;

use Hateoas\Serializer\JsonSerializerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class HateoasJsonApiSerializer implements JsonSerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serializeLinks(array $links, JsonSerializationVisitor $visitor, SerializationContext $context)
    {
        $serializedLinks         = array();
        $topLevelSerializedLinks = array();
        foreach ($links as $link) {
            $serializedLink = array_merge(array(
                'href' => $link->getHref(),
            ), $link->getAttributes());
            if (isset($serializedLink['topLevel']) && true === $serializedLink['topLevel']) {
                unset($serializedLink['topLevel']);
                $topLevelSerializedLinks[$link->getRel()] = $serializedLink;
                continue;
            }
            if (!isset($serializedLinks[$link->getRel()])) {
                $serializedLinks[$link->getRel()] = $serializedLink['href'];
            } else {
                $serializedLinks[$link->getRel()][] = $serializedLink['href'];
            }
        }
        $visitor->addData('links', $serializedLinks);
        //$visitor->setRoot(array('links' => $topLevelSerializedLinks));
    }

    /**
     * {@inheritdoc}
     */
    public function serializeEmbeddeds(array $embeds, JsonSerializationVisitor $visitor, SerializationContext $context)
    {
        $serializedEmbeds = array();
        foreach ($embeds as $embed) {
            $serializedEmbeds[$embed->getRel()] = $context->accept($embed->getData());
        }
        $visitor->setRoot(array('linked' => $serializedEmbeds));
    }
}
