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
namespace DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer;

use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;

/**
 * Class ChatMessageTransformer.
 */
class ChatMessageTransformer extends AbstractDataSerializerTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return ['id', 'author', 'content', 'is_html', 'is_sys', 'date_created', 'date_received'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var ChatMessage $data */
        $data = $transformation_request->getDataToBeTransformed();

        return [
            'metadata' => $data->getMetadata(),

            // Back compatibility to work with old agent
            'message_id'      => $data->getId(),
            'conversation_id' => $data->getConversation()->getId(),
            'author_id'       => $this->getAuthorId($data),
            'author_type'     => $this->getAuthorType($data),
        ];
    }

    /**
     * @param ChatMessage $message
     *
     * @return int
     */
    private function getAuthorId(ChatMessage $message)
    {
        return $message->getAuthor() ? $message->getAuthor()->getId() : 0;
    }

    /**
     * @param ChatMessage $message
     *
     * @return string
     */
    private function getAuthorType(ChatMessage $message)
    {
        if ($message->getIsSys()) {
            return 'sys';
        }

        $author      = $message->getAuthor();
        $author_type = $author && $author->is_agent ? 'agent' : 'user';
        $metadata    = $message->getMetadata();

        // Handle the case where the author is an agent in the user interface
        if ($author_type === 'agent' && isset($metadata['is_user_message'])) {
            $author_type = 'user';
        }

        return $author_type;
    }
}
