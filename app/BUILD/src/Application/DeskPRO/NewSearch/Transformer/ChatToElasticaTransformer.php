<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\Entity\ChatConversation;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * Class ChatToElasticaTransformer.
 */
class ChatToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param ChatConversation $object
     */
    public function transform($object, array $fields)
    {
        $document = new Document();
        $document->setId($object->id);

        $document->set('subject', $object->getSubjectLine());
        $document->set('department', $object->getDepartmentId());
        $document->set('agent', $object->getAgentId());
        $document->set('is_agent', $object->is_agent);

        if ($object->labels) {
            $labels = Arrays::map(function ($l) {
                return $l->label;
            }, $object->labels);
            $document->set('labels', $labels);
        }

        $messages = [];
        foreach ($object->messages as $message) {
            if (!$message->is_sys) {
                $content = $message->content;
                if ($message->is_html) {
                    $content = Strings::stripTags($content);
                }
                $messages[] = $content;
            }
        }

        $document->set('messages', $messages);

        $document->set('date_created', $object->date_created->format('Y-m-d H:i:s'));
        $document->set('date_active', date('Y-m-d H:i:s'));

        return $document;
    }
}
