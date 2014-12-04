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
 * @subpackage
 */

namespace Application\FormBundle;


class FormFields
{
    const DEPARTMENT = 'department';
    const SUBJECT = 'subject';
    const MESSAGE = 'message';
    const USER_EMAIL = 'user_email';
    const USER_NAME = 'user_name';
    const USER_TIMEZONE = 'user_timezone';
    const USER_LANGUAGE = 'user_language';
    const USER_FIELD = 'user_field';
    const TICKET_FIELD = 'ticket_field';
    const CUSTOM_FIELD = 'custom_field';
    const CATEGORY = 'category';
    const PRIORITY = 'priority';
    const WORKFLOW = 'workflow';
    const PRODUCT = 'product';
    const CAPTCHA = 'captcha';
    const CC = 'cc';
    const ATTACH = 'attach';
}
