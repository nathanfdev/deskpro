<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource;

/**
 * Some useful constants/info on Usersources.
 */
class UsersourceInfo
{
    //----------------------------------------------------------------
    // USERSOURCE CAPABILITIES
    //----------------------------------------------------------------

    /**
     * Capable of single sign on (automatic/redirect method)
     * Most of these also implement sso_background.

     * Usersource adapters that claim to be capable of this, must implement getAgentLogoutRedirectUrl and
     * getUserLogoutRedirectUrl, and must return an SsoCapableInterface in getAuthAdapter.
     */
    const CAPABILITY_SSO = 'sso';

    /**
     * Single sign on method that is capable of attempting authentication via JS (in the background).
     */
    const CAPABILITY_SSO_JS = 'js_sso';

    /**
     * When a user submits a form, if the main dp db doesn't authenticate, it will try to authenticate with these
     * sources with the given form data.
     */
    const CAPABILITY_FORM_LOGIN = 'form_login';

    /**
     * Given just an email address or username it can return the right Identity object to you.
     */
    const CAPABILITY_FIND_IDENTITY = 'find_identity';

    /**
     * Given just an email address or username it can return the right Identity object to you.
     */
    const CAPABILITY_GET_USER_INFO = 'get_user_info';

    /**
     * A logo appears on the login screen, allowing you to click and login with that source (google/fb,etc).
     */
    const CAPABILITY_LOGIN_PULL_BTN = 'tpl_login_pull_btn';

    /**
     * A logo appears on the login screen, allowing you to click and login with that source (google, linkedin, azure,etc).
     * This is different than the existing usersources as it is using the oauth2 proxy
     */
    const CAPABILITY_SOCIAL_LOGIN = 'tpl_social_login_btn';

    /**
     * Create a block size text button.
     */
    const CAPABILITY_LOGIN_TEXT_BTN = 'tpl_login_text_btn';

    /**
     * Capable of logging in using a locally-available cookie (see the Session class for usage of EntityRepository
     * Usersource::getCookieInputUsersources).
     */
    const CAPABILITY_COOKIE_LOGIN = 'cookie_login';

    /**
     * Popups?
     */
    const CAPABILITY_WIDGET_OVERLAY_BTN = 'tpl_widget_overlay_btn';

    /**
     * Not sure at time of writing.
     */
    const CAPABILITY_NEW_COMMENT_TAB = 'tpl_newcomment_tab';

    /**
     * Not sure at time of writing.
     */
    const CAPABILITY_SHARE_SESSION = 'share_session';
}
