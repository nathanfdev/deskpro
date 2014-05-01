<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Elastica\Document;
use Application\DeskPRO\Entity\Ticket;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

/**
 * Ticket To Elastica Transformer
 *
 * Transforms a Ticket entity to Elasticsearch document with the right
 * field mappings. Need this instead of the standard mapping to handle
 * nested properties (participants, messages) using our preferred format.
 */
class TicketToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * Transform
     *
     * @param Ticket $object
     * @param array $fields
     *
     * @return Document
     */
    public function transform($object, array $fields)
    {
        $document = new Document();

        $document->setId($object->getId());

        $document->set('subject', $object->getSubject());
        $document->set('ref', $object->getRef());
        $document->set('department', $object->getDepartmentId());
        $document->set('agent', $object->getAgentId());
        $document->set('agent_team', $object->getAgentTeamId());
        $document->set('participants', $object->getParticipantPeopleIds());

        $labels = array();
        foreach ($object->getLabels() as $label) {
            $labels[] = $label->getLabel();
        }

        $document->set('labels', $labels);

        $messages = array();
        foreach ($object->getMessages() as $message) {
            $messages[] = $message->getMessage();
        }

        $document->set('messages', $messages);

        return $document;
    }
} 