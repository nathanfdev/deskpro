<?php

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
        $document->setId($object->getId());

        $document->set('subject', $object->getSubjectLine());
        $document->set('department', $object->getDepartmentId());
        $document->set('person', $object->getPerson() ? $object->getPerson()->getId() : null);
        $document->set('agent', $object->getAgentId());
        $document->set('participants', $object->getParticipantIds());
        $document->set('is_agent', $object->isAgentChat());

        if ($object->getLabels()) {
            $labels = Arrays::map(function ($l) {
                return $l->label;
            }, $object->getLabels());
            $document->set('labels', array_values($labels));
        }

        $messages = [];
        foreach ($object->getMessages() as $message) {
            if (!$message->getIsSys()) {
                $content = $message->getContent();
                if ($message->isHtml()) {
                    $content = Strings::stripTags($content);
                }

                $messages[] = $content;
            }
        }

        $document->set('messages', $messages);
        $document->set('date_created', $object->getDateCreated()->format('Y-m-d H:i:s'));
        $document->set('date_active', date('Y-m-d H:i:s'));

        return $document;
    }
}
