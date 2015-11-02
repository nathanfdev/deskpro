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
namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;

/**
 * Class PersonTransformer.
 */
class PersonTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var AvatarResolver
     */
    private $avatar_resolver;

    /** @var AgentDataService  */
    private $agent_data_service;

    /**
     * @param AvatarResolver   $avatar_resolver
     * @param AgentDataService $agent_data_service
     */
    public function __construct(AvatarResolver $avatar_resolver, AgentDataService $agent_data_service)
    {
        $this->avatar_resolver    = $avatar_resolver;
        $this->agent_data_service = $agent_data_service;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'picture_blob',
            'disable_picture',
            'gravatar_url',
            'is_contact',
            'is_user',
            'is_agent',
            'was_agent',
            'can_agent',
            'can_admin',
            'can_billing',
            'is_vacation_mode',
            'disable_autoresponses',
            'disable_autoresponses_log',
            'is_confirmed',
            'is_agent_confirmed',
            'is_deleted',
            'is_disabled',
            'importance',
            'creation_system',
            'name',
            'first_name',
            'last_name',
            'title_prefix',
            'override_display_name',
            'summary',
            'language',
            'organization',
            'organization_position',
            'organization_manager',
            'timezone',
            'phone_numbers',
            'date_created',
            'date_last_login',
            'browser',
            'assigned_tasks', // TODO: shouldnt be here
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $transformation_request->getDataToBeTransformed();

        #------------------------------
        # Email addresses
        #------------------------------

        $ret = [
            'emails'            => [],
            'validating_emails' => [],
            'primary_email'     => [],
        ];

        foreach ($person->getEmails() as $email) {
            if ($email->is_validated) {
                $ret['emails'][] = $email->getEmail();
            } else {
                $ret['validating_emails'][] = $email->getEmail();
            }
        }

        if ($email = $person->getPrimaryEmail()) {
            $ret['primary_email'] = $email->getEmail();
        }

        #------------------------------
        # Avatar
        #------------------------------
        $ret['avatar'] = $this->avatar_resolver->getAvatarModel($person);
        $ret['online'] = $this->agent_data_service->isAgentOnline($person);
        $last_seen     = $this->agent_data_service->getLastSeen($person);
        if ($last_seen) {
            $last_seen        = new \DateTime($last_seen);
            $ret['last_seen'] = $last_seen->format(\DateTime::ISO8601);
        } else {
            $ret['last_seen'] = false;
        }

        return $ret;
    }
}
