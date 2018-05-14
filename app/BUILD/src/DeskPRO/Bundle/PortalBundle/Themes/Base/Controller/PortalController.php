<?php

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\UseSectionVoter;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\Controller\SearchController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PortalController extends AbstractController
{
    /**
     * @Tag(name="page_top")
     */
    public function topBarAction(TagRequest $tag_request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager $language_manager */
        $language_manager = $this->get('language_manager');

        $view = $this->renderThemeView(
            'Theme:Portal:Header/top_bar.html.twig',
            [
                'enabled_languages' => $language_manager->getEnabledLanguages(),
                'current_language'  => $language_manager->getLanguageStack()->getActive(),
                'is_multi_language' => $language_manager->isMultiLanguagePortal(),
            ]
        );

        return $view;
    }

    /**
     * @Tag(name="nav_buttons", default_options={"style":"small"})
     * @Tag(name="nav_buttons_big", default_options={"style":"big"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "small",
     *      },
     *      allowed_values={
     *          "style": {"small", "big"}
     *      }
     * )
     */
    public function navButtonsAction(TagRequest $tag_request, array $options)
    {
        return $this->renderThemeView(
            sprintf('Theme:Portal:nav_buttons_%s.html.twig', $options['style'])
        );
    }

    /**
     * @Tag(name="sidebar", esi=true)
     */
    public function userSidebarAction(TagRequest $tag_request)
    {
        return $this->renderThemeView(
            'Theme:Portal:sidebar.html.twig'
        );
    }

    /**
     * @Tag(name="search_and_contact_bar", default_options={"include_contact_us":true}, esi=true)
     * @Tag(name="search_bar", default_options={"include_contact_us":false}, esi=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "include_contact_us": true
     *      }
     * )
     */
    public function searchBoxAction(TagRequest $tag_request, array $options)
    {
        if (isset($options['include_contact_us']) && $options['include_contact_us'] == true) {
            $brand       = $this->get('brand_stack')->getActive()->getBrand();
            $chatEnabled = $this->container->get('widget_settings_resolver')
                ->getWidgetOptions($brand)
                ->getGlobal()
                ->getChat()
                ->isEnabled()
            ;

            try {
                $canViewTicketsLink = $this->isGranted(UseSectionVoter::VIEW_TICKETS_LINK);
                $canUseChat         = $chatEnabled && $this->isGranted(UseSectionVoter::USE_CHAT);
                $canUseFeedback     = $this->isGranted(UseSectionVoter::USE_FEEDBACK);
            } catch (AuthenticationException $e) {
                $canViewTicketsLink = false;
                $canUseChat         = false;
                $canUseFeedback     = false;
            }

            $extendedOptions = [
                'can_view_tickets_link' => $canViewTicketsLink,
                'can_use_chat'          => $canUseChat,
                'can_use_feedback'      => $canUseFeedback,
            ];

            $extendedOptions = array_merge(
                $extendedOptions,
                [
                    'include_contact_us' => array_reduce(
                        $extendedOptions,
                        function ($carry, $item) {
                            return $carry || $item;
                        },
                        false
                    ),
                    'should_display_dropdown' => array_sum($extendedOptions) > 1,
                ]
            );

            $extendedOptions['first_link'] = '#';

            if ($extendedOptions['can_view_tickets_link']) {
                $extendedOptions['first_link'] = $this->get('router')->generate('portal_new_ticket');
            } elseif ($extendedOptions['can_use_feedback']) {
                $extendedOptions['first_link'] = $this->get('router')->generate('portal_feedback');
            } elseif ($extendedOptions['can_use_chat']) {
                // this is very, very dirty hack
                $extendedOptions['first_link'] = "javascript: window.dp_loader.postMessage({type: 'openWidget'}, '*');";
            }
        } else {
            $extendedOptions = ['include_contact_us' => false];
        }

        $masterRequest = $this->container->get('request_stack')->getMasterRequest();
        if ($masterRequest->attributes->has(SearchController::SEARCH_LOG_ID_VAR)) {
            $extendedOptions['search_log_id'] = $masterRequest->attributes->get(SearchController::SEARCH_LOG_ID_VAR);
        }

        return $this->renderThemeView(
            'Theme:Portal:Header/page_search_box.html.twig',
            array_replace($options, $extendedOptions)
        );
    }

    /**
     * @Tag(name="small_user_info", esi=true)
     */
    public function smallUserInfoAction(TagRequest $tag_request)
    {
        $user = $this->getUser();

        $auth_manager = $this->get('dp_authentication_manager.user');

        $page_vars = [
            'display_registration_link'     => $this->get('dp_authentication_manager.user')->isRegistrationFormVisible(),
            'chat_count'                    => $user ? $this->getChatDataService()->countUserChats($user, 'own') : 0,
            'ticket_count'                  => $user ? $this->getTicketsDataService()->getTicketCount($user, 'all') : 0,
            'ticket_count_org'              => $user ? $this->getTicketsDataService()->getOrganizationTicketCount($user, 'all') : 0,
            'user'                          => $user,
            'login_text_button_usersources' => $auth_manager->getLoginTextButtonUsersources(),
            'login_icon_usersources'        => $auth_manager->getLoginIconUsersources(),
            'show_forgot_password'          => $auth_manager->isForgotPasswordVisible(),
            'show_remember_me'              => $auth_manager->isRememberMeEnabled(),
            'show_login_form'               => $auth_manager->isLoginFormVisible(),
            'show_auth'                     => $auth_manager->isAuthVisible(),
        ];

        return $this->renderThemeView(
            'Theme:Portal:Header/small_user_info.html.twig',
            $page_vars
        );
    }
}
