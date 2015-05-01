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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\DevTest\Controller;

use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    /**
     * @Tag(name="dev_test_portal", always_guest_inline=true)
     */
    public function testAction(TagRequest $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Request $main_request */
        $main_request = $request->attributes->get('mainRequest');

        $vars = array(
            'opts' => array(
                'show_news'                => $main_request->query->get('show_news', false),
                'show_kb'                  => $main_request->query->get('show_kb', false),
                'show_dl'                  => $main_request->query->get('show_dl', false),
                'show_sidebar'             => $main_request->query->get('show_sidebar', false),
                'show_sb_user'             => $main_request->query->get('show_sb_user', false),
                'show_sb_getintouch'       => $main_request->query->get('show_sb_getintouch', false),
                'show_sb_news'             => $main_request->query->get('show_sb_news', false),
                'show_sb_kb'               => $main_request->query->get('show_sb_kb', false),
                'show_sb_kb_cats'          => $main_request->query->get('show_sb_kb_cats', false),
                'show_sb_downloads'        => $main_request->query->get('show_sb_downloads', false),
                'show_sb_downloads_cats'   => $main_request->query->get('show_sb_downloads_cats', false),
                'show_sb_feedback'         => $main_request->query->get('show_sb_feedback', false),
            )
        );
        return $this->renderThemeView('Theme:Portal:Tag/dev_test_portal.html.twig', $vars);
    }
}
