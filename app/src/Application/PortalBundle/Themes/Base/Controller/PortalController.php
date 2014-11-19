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


use Symfony\Component\HttpFoundation\Request;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PortalController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('Theme:Portal:index.html.twig');
    }


    public function getInTouchAction()
    {
        return $this->render('Theme:Portal:get_in_touch.html.twig');
    }


    public function alertsAction()
    {
        return $this->render('Theme:Portal:alerts.html.twig');
    }


    public function topBarAction()
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


    public function topSearchAction()
    {
        return $this->render('Theme:Portal:top_search.html.twig');
    }


    public function sidebarAction()
    {
        return $this->render('Theme:Portal:sidebar.html.twig');
    }


    public function topTabsAction(Request $request)
    {
        $path_parts = explode('/', ltrim($request->getPathInfo(), '/'));

        return $this->render(
            'Theme:Portal:top_tabs.html.twig',
            array(
                'url_starts_with' => isset($path_parts[0]) ? $path_parts[0] : null
            )
        );
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

    public function loginSidebarAction()
    {
        return $this->render(
            'Theme:Portal:login_sidebar.html.twig',
            array(
                'auth_manager' => $this->get('dp_authentication_manager.user')
            )
        );
    }

    public function userSidebarAction()
    {
        return $this->render('Theme:Portal:user_sidebar.html.twig');
    }
}
