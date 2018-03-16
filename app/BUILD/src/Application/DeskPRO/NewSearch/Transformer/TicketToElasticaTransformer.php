<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use Application\DeskPRO\ApacheTika\ClientManager as ApacheTikaManager;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

/**
 * Ticket To Elastica Transformer.
 *
 * Transforms a Ticket entity to Elasticsearch document with the right
 * field mappings. Need this instead of the standard mapping to handle
 * nested properties (participants, messages) using our preferred format.
 */
class TicketToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * @var ApacheTikaManager
     */
    private $apache_tika;

    /**
     * @return ApacheTikaManager
     */
    public function getApacheTika()
    {
        return $this->apache_tika;
    }

    /**
     * @param ApacheTikaManager $apache_tika
     */
    public function setApacheTika($apache_tika)
    {
        $this->apache_tika = $apache_tika;
    }

    /**
     * Transform.
     *
     * @param Ticket $object
     * @param array  $fields
     *
     * @return Document
     */
    public function transform($object, array $fields)
    {
        $document = new Document();

        $document->setId($object->getId());

        $document->set('subject', $object->getSubject());
        $document->set('ref', $object->getRef());
        $document->set('brand', $object->getBrandId());
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
            $labels = Arrays::map(function ($l) {
                return $l->label;
            }, $object->labels);
            $document->set('labels', array_values($labels));
        } else {
            $document->set('labels', []);
        }

        $messages = [];
        foreach ($object->getMessages() as $message) {
            $messages[] = $message->getMessage();
        }

        $document->set('messages', $messages);
        $document->set('date_created', $object->getDateCreated()->format('Y-m-d H:i:s'));

        $dates = [$object->getDateCreated(), $object->getDateStatus(), $object->getDateLastAgentReply(), $object->getDateLastUserReply()];
        $dates = Arrays::removeFalsey($dates);
        $d     = max($dates);
        $document->set('date_active', $d->format('Y-m-d H:i:s'));

        if ($object->hasAttachments()) {
            $attachments = [];
            foreach ($object->getAttachments() as $attachment) {
                $attachments[] = $attachment->getBlob()->getFilename();
            }

            $document->set('attachments', $attachments);

            if ($this->getApacheTika()->isEnabled()) {
                $attachmentData = [];
                try {
                    /** @var ApacheTikaManager $client */
                    $client = $this->getApacheTika()->getClient();
                    /** @var TicketAttachment $attachment */
                    foreach ($attachments as $attachment) {
                        $blob = $attachment->getBlob();
                        if (!$blob->isImage()) {
                            $attachmentData[] = [
                                'filename' => $blob->getFilenameSafe(),
                                'content'  => $client->getText($blob->getDownloadUrl(true)),
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // TODO log error
                    $error = $e->getMessage();
                }

                $document->set('attachment', $attachmentData);
            }
        } else {
            $document->set('attachments', []);
            $document->set('attachment', []);
        }

        return $document;
    }
}
