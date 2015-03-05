<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * Orb
 *
 * @package  Orb
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * Similar to JsSsoInterface, but without the code smells of it. This enforces the use of a hidden iFrame load on first
 * page load, so if that is all you need then use this. URL should do redirects and ultimately return JS that refreshed
 * the parent window if auth succeeds. Auth errors/popups/forms, etc should be hidden and not shown to user.
 */
interface IframeSsoInterface extends SsoLoginActionInterface
{
    /**
     * array of parameters that are passed to the _sso_iframe.html.twig template
     * Note: iframe_url is required
     *
     * @param  bool  $is_first_page
     * @return array of twig vars
     */
    public function getIframeTemplateParams($is_first_page);
}
