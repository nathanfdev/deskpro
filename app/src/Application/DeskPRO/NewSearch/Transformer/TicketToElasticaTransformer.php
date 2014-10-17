<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Elastica\Document;
use Application\DeskPRO\Entity\Ticket;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

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

		if ($object->organization) {
			$document->set('organization_id', $object->organization->getId());
		} else {
			$document->set('organization_id', 0);
		}

		if ($object->person) {
			$document->set('person_id', $object->person->getId());
		} else {
			$document->set('person_id', 0);
		}

		if ($object->labels) {
			$labels = Arrays::map(function ($l) { return $l->label; }, $object->labels);
			$document->set('labels', $labels);
		}

        $document->set('labels', $labels);

        $messages = array();
        foreach ($object->getMessages() as $message) {
            $messages[] = $message->getMessage();
        }

        $document->set('messages', $messages);

		$document->set('date_created', $object->date_created->format('Y-m-d H:i:s'));

		$dates = array($object->date_created, $object->date_status, $object->date_last_agent_reply, $object->date_last_user_reply);
		$dates = Arrays::removeFalsey($dates);
		$d = max($dates);
		$document->set('date_active', $d->format('Y-m-d H:i:s'));

        return $document;
    }
} 