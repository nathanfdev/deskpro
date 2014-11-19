<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Elastica\Document;
use Application\DeskPRO\Entity\ChatConversation;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class ChatToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * Transform
     *
     * @param ChatConversation $object
     * @param array            $fields
     *
     * @return Document
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
            $labels = Arrays::map(function ($l) { return $l->label; }, $object->labels);
            $document->set('labels', $labels);
        }

        $messages = array();
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
