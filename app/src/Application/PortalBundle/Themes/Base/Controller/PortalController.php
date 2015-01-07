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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\PortalBundle\Request\TagRequest;
use Symfony\Component\HttpFoundation\Request;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;

class PortalController extends AbstractController
{
    /**
     * @Tag(name="home")
     */
    public function homeAction(TagRequest $tag_request)
    {
        return $this->renderThemeView('Theme:Portal:Tag/home.html.twig');
    }

    /**
     * @Tag(name="page_top")
     */
    public function topBarAction(TagRequest $tag_request)
    {
        /** @var \Application\LanguageBundle\Language\LanguageManager $language_manager */
        $language_manager = $this->get('language_manager');

        return $this->renderThemeView(
            'Theme:Portal:Tag/top_bar.html.twig',
            array(
                'enabled_languages' => $language_manager->getEnabledLanguages(),
                'current_language' => $language_manager->getLanguageStack()->getActive(),
                'is_multi_language' => $language_manager->isMultiLanguagePortal(),
                'display_registration_link' => $this->get('dp_authentication_manager.user')->isRegistrationFormVisible(),
            )
        );
    }

    /**
     * @Tag(name="page_search_box")
     */
    public function topSearchAction(TagRequest $tag_request)
    {
        return $this->renderThemeView('Theme:Portal:Tag/top_search.html.twig');
    }

    /**
     * @Tag(name="page_tabs")
     */
    public function topTabsAction(TagRequest $tag_request)
    {
        $path_parts = explode('/', ltrim($tag_request->getPathInfo(), '/'));

        $tabs = $this->get('tabs_helper')->getTabsDisplay();

        return $this->renderThemeView(
            'Theme:Portal:Tag/top_tabs.html.twig',
            array(
                'url_starts_with' => isset($path_parts[0]) ? $path_parts[0] : null,
                'tabs' => $tabs
            )
        );
    }

    /**
     * @Tag(name="sidebar")
     */
    public function sidebarAction(TagRequest $tag_request)
    {
        return $this->renderThemeView('Theme:Portal:Tag/sidebar.html.twig');
    }

    /**
     * @Tag(name="login_sidebar")
     */
    public function loginSidebarAction(TagRequest $tag_request)
    {
        //TODO: dont pass the auth manager into the template...
        return $this->renderThemeView(
            'Theme:Portal:Tag/sidebar_login.html.twig',
            array(
                'auth_manager' => $this->get('dp_authentication_manager.user')
            )
        );
    }

    /**
     * @Tag(name="user_sidebar", esi=true)
     */
    public function userSidebarAction(TagRequest $tag_request)
    {
        $ticket_count = $this->getDoctrine()->getRepository('DeskPRO:Ticket')->getTicketCountForPerson($this->getUser());

        return $this->renderThemeView(
            'Theme:Portal:Tag/sidebar_user.html.twig',
            array(
                'has_tickets' => $ticket_count > 0
            )
        );
    }
}
