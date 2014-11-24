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
    const USER_EMAIL = 'email';
    const TICKET_FIELD = 'ticket_field';
    const USER_NAME = 'user_name';
    const USER_TIMEZONE = 'user_timezone';
    const USER_LANGUAGE = 'user_language';
}

//object(Application\DeskPRO\TicketLayout\LayoutField)[1754]
//  private 'field_type' => string 'department' (length = 10)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1755]
//  private 'field_type' => string 'subject' (length = 7)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1756]
//  private 'field_type' => string 'ticket_field' (length = 12)
//  private 'field_id' => string '2' (length = 1)
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1757]
//  private 'field_type' => string 'user_email' (length = 10)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1758]
//  private 'field_type' => string 'message' (length = 7)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1759]
//  private 'field_type' => string 'attach' (length = 6)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1760]
//  private 'field_type' => string 'cc' (length = 2)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1761]
//  private 'field_type' => string 'captcha' (length = 7)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1762]
//  private 'field_type' => string 'user_name' (length = 9)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1763]
//  private 'field_type' => string 'user_language' (length = 13)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1764]
//  private 'field_type' => string 'user_timezone' (length = 13)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null
//object(Application\DeskPRO\TicketLayout\LayoutField)[1765]
//  private 'field_type' => string 'priority' (length = 8)
//  private 'field_id' => null
//  private 'on_newticket' => boolean true
//  private 'on_viewticket' => boolean true
//  private 'on_viewticket_mode' => string 'value' (length = 5)
//  private 'on_editticket' => boolean true
//  private 'criteria' => null