<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer\AbstractDataSerializerTransformer;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;

class PersonTransformer extends AbstractDataSerializerTransformer
{
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
            'primary_email',
            'emails',
            'phone_numbers',
            'date_created',
            'date_last_login',
            'browser',
            'assigned_tasks',
        ];
    }

    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\Entity\TicketFilter $data */
        $data = $transformation_request->getDataToBeTransformed();
        return [];
    }
}
