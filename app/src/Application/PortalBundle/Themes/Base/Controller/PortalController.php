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
    public function indexAction(Request $request)
    {
        return $this->render('Theme:Portal:index.html.twig');
    }

    public function loginAction(Request $request)
    {
        return $this->render(
            'Theme:Portal:login.html.twig',
            array(
                'auth_manager' => $this->get('dp_authentication_manager.user'),
                'login_error' => $request->get('retry') == 'auth'
            )
        );
    }

    /**
     * @Tag(name="home")
     */
    public function homeAction(TagRequest $tag_request)
    {
        return $this->render('Theme:Portal:home.html.twig');
    }

    /**
     * @Tag(name="get_in_touch")
     */
    public function getInTouchAction(TagRequest $tag_request)
    {
        return $this->render('Theme:Portal:get_in_touch.html.twig');
    }

    /**
     * @Tag(name="alerts")
     */
    public function alertsAction(TagRequest $tag_request)
    {
        return $this->render('Theme:Portal:alerts.html.twig');
    }

    /**
     * @Tag(name="flashes")
     */
    public function flashesAction(TagRequest $tag_request)
    {
        $flashes = array();
        $session = $tag_request->getSession();
        if (null !== $session && $session->isStarted()) {
            $flashes = $session->getFlashBag()->all();
        }

        return $this->render('Theme:Portal:flashes.html.twig', array(
            'flashes' => $flashes
        ));
    }

    /**
     * @Tag(name="page_top")
     */
    public function topBarAction(TagRequest $tag_request)
    {
        /** @var \Application\LanguageBundle\Language\LanguageManager $language_manager */
        $language_manager = $this->get('language_manager');

        return $this->render('Theme:Portal:top_bar.html.twig',
            array(
                'enabled_languages' => $language_manager->getEnabledLanguages(),
                'current_language' => $language_manager->getLanguageStack()->getActive(),
                'is_multi_language' => $language_manager->isMultiLanguagePortal(),
                'display_registration_link' => $this->get('dp_authentication_manager.user')->isRegistrationFormVisible(),
            )
        );
    }

    /**
     * @Tag(name="pager")
     *
     * @TagOptions(
     *      required={"pager"},
     *      defaults={
     *          "show_pagination": true
     *      },
     *      allowed_types={
     *          "pager":"Pagerfanta\Pagerfanta",
     *          "show_pagination":"bool"
     *      }
     * )
     */
    public function pagerAction(TagRequest $request, array $options)
    {
        return $this->render('Theme:Portal:pager.html.twig', array(
            'pager' => $options['pager'],
            'show_pagination' => $options['show_pagination']
        ));
    }

    /**
     * @Tag(name="page_search_box")
     */
    public function topSearchAction(TagRequest $tag_request)
    {
        return $this->render('Theme:Portal:top_search.html.twig');
    }

    /**
     * @Tag(name="page_tabs")
     */
    public function topTabsAction(TagRequest $tag_request)
    {
        $path_parts = explode('/', ltrim($tag_request->getPathInfo(), '/'));

        return $this->render(
            'Theme:Portal:top_tabs.html.twig',
            array(
                'url_starts_with' => isset($path_parts[0]) ? $path_parts[0] : null
            )
        );
    }

    /**
     * @Tag(name="sidebar")
     */
    public function sidebarAction(TagRequest $tag_request)
    {
        return $this->render('Theme:Portal:sidebar.html.twig');
    }

    /**
     * @Tag(name="login_sidebar")
     */
    public function loginSidebarAction(TagRequest $tag_request)
    {
        return $this->render(
            'Theme:Portal:login_sidebar.html.twig',
            array(
                'auth_manager' => $this->get('dp_authentication_manager.user')
            )
        );
    }

    /**
     * @Tag(name="user_sidebar")
     */
    public function userSidebarAction(TagRequest $tag_request)
    {
        $ticket_count = $this->getDoctrine()->getRepository('DeskPRO:Ticket')->getTicketCountForPerson($this->getUser());

        return $this->render('Theme:Portal:user_sidebar.html.twig', array(
            'has_tickets' => $ticket_count > 0
        ));
    }
}
