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

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;

/**
 * Class ChatConversationTransformer.
 */
class ChatConversationTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var AvatarResolver
     */
    private $avatar_resolver;

    /**
     * @param AvatarResolver $avatar_resolver
     */
    public function __construct(AvatarResolver $avatar_resolver)
    {
        $this->avatar_resolver = $avatar_resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return ['id', 'subject_line', 'department_id', 'date_created', 'date_ended', 'ended_by', 'status', 'subject'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var ChatConversation $data */
        $data       = $transformation_request->getDataToBeTransformed();
        $person     = $data->getPerson();
        $agent      = $data->getAgent();
        $department = $data->getDepartment();

        return [
            'conversation_id'        => $data->getId(),
            'author_id'              => $person ? $person->getId() : 0,
            'author_name'            => $person ? $person->getDisplayName() : $data->getPersonName(),
            'author_email'           => $person ? $person->getPrimaryEmailAddress() : $data->getPersonEmail(),
            'author_type'            => $person && $person->isAgent() ? 'agent' : 'user',
            'agent_id'               => $agent ? $agent->getId() : 0,
            'agent_name'             => $agent ? $agent->getDisplayName() : '',
            'agent_avatar'           => $agent ? $this->avatar_resolver->getAvatarModel($agent)->getUrl(150) : '',
            'agent_last_typing_time' => new \DateTime(),
            'department_name'        => $department ? $department->getFullTitle() : '',
        ];
    }
}
