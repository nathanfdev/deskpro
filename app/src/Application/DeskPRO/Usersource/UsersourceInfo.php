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

namespace Application\DeskPRO\Usersource;

/**
 * Some useful constants/info on Usersources
 */
class UsersourceInfo
{
	//----------------------------------------------------------------
	// USERSOURCE CAPABILITIES
	//----------------------------------------------------------------

	/**
	 * Capable of single sign on (automatic/redirect method)
	 * Most of these also implement sso_background

	 * Usersource adapters that claim to be capable of this, must implement getAgentLogoutRedirectUrl and
	 * getUserLogoutRedirectUrl, and must return an SsoCapableInterface in getAuthAdapter
	 */
	const CAPABILITY_SSO = 'sso';

	/**
	 * Single sign on method that is capable of attempting authentication via JS (in the background)
	 */
	const CAPABILITY_SSO_JS = 'js_sso';

	/**
	 * When a user submits a form, if the main dp db doesn't authenticate, it will try to authenticate with these
	 * sources with the given form data.
	 */
	const CAPABILITY_FORM_LOGIN = 'form_login';

	/**
	 * Given just an email address or username it can return the right Identity object to you
	 */
	const CAPABILITY_FIND_IDENTITY = 'find_identity';

	/**
	 * Given just an email address or username it can return the right Identity object to you
	 */
	const CAPABILITY_GET_USER_INFO = 'get_user_info';

	/**
	 * A logo appears on the login screen, allowing you to click and login with that source (google/fb,etc)
	 */
	const CAPABILITY_LOGIN_PULL_BTN = 'tpl_login_pull_btn';

	/**
	 * Create a block size text button
	 */
	const CAPABILITY_LOGIN_TEXT_BTN = 'tpl_login_text_btn';

	/**
	 * Capable of logging in using a locally-available cookie (see the Session class for usage of EntityRepository
	 * Usersource::getCookieInputUsersources)
	 */
	const CAPABILITY_COOKIE_LOGIN = 'cookie_login';

	/**
	 * Popups?
	 */
	const CAPABILITY_WIDGET_OVERLAY_BTN = 'tpl_widget_overlay_btn';

	/**
	 * Not sure at time of writing
	 */
	const CAPABILITY_NEW_COMMENT_TAB = 'tpl_newcomment_tab';

	/**
	 * Not sure at time of writing
	 */
	const CAPABILITY_SHARE_SESSION = 'share_session';
}
