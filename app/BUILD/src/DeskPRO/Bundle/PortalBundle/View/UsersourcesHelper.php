<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\PortalBundle\View;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;

class UsersourcesHelper
{
    /**
     * @var AuthenticationManager
     */
    private $auth_manager;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var array we only want to actually generate this array once per request, so we just store it here if we
     *            have calculated it before
     */
    private static $cached = false;

    public function __construct(AuthenticationManager $auth_manager, LanguageManager $language_manager)
    {
        $this->auth_manager     = $auth_manager;
        $this->language_manager = $language_manager;
    }

    /**
     * Get an array of usersource info that should be displayed to the user on the portal.
     *
     * @return array
     */
    public function createUsersourceViewList()
    {
        if (self::$cached) {
            return self::$cached;
        }

        $this->generateUsersourceViewList();

        return self::$cached;
    }

    /**
     * @return bool
     */
    public function hasLoginForm()
    {
        return $this->auth_manager->hasFormLoginCapability();
    }

    /**
     * @return array
     */
    protected function generateUsersourceViewList()
    {
        if (!$this->auth_manager->isAuthVisible()) {
            return [];
        }

        $usersources = [];

        /** @var \Application\DeskPRO\Entity\Usersource $us */
        foreach ($this->auth_manager->getLoginIconUsersources() as $us) {
            $info          = $this->getUsersourceInfo($us);
            $usersources[] = [
                'id'      => $us->getId(),
                'text'    => $info['text'],
                'icon'    => $info['icon'],
                'classes' => $info['css_classes'],
            ];
        }

        /** @var \Application\DeskPRO\Entity\Usersource $us */
        foreach ($this->auth_manager->getLoginTextButtonUsersources() as $us) {
            $info          = $this->getUsersourceInfo($us);
            $usersources[] = [
                'id'      => $us->getId(),
                'text'    => $info['text'],
                'icon'    => $info['icon'],
                'classes' => $info['css_classes'],
            ];
        }

        self::$cached = $usersources;
    }

    /**
     * @param Usersource $us
     *
     * @return array
     */
    protected function getUsersourceInfo(Usersource $us)
    {
        $type = strtolower($us->getTypeName());

        $info = [
            'icon'        => null, // a css string to use in an <i> element for font awesome icon (example: "fa fa-facebook").
            'text'        => '', // the full text that appears on the login button
            'css_classes' => ['button', 'auth'], // the deskpro css classes that should apply to the login button
        ];

        if ('facebook' === $type) {
            $info['icon']          = 'fa fa-facebook';
            $info['css_classes'][] = 'auth-facebook';
            $info['text']          = $this->usersourceBtnPhrase('Facebook');
        } elseif ('googleplus' === $type) {
            $info['icon']          = 'fa fa-google-plus';
            $info['css_classes'][] = 'auth-google';
            $info['text']          = $this->usersourceBtnPhrase('Google Plus');
        } elseif ('google' === $type) {
            $info['icon']          = 'fa fa-google';
            $info['css_classes'][] = 'auth-google';
            $info['text']          = $this->usersourceBtnPhrase('Google');
        } elseif ('twitter' === $type) {
            $info['icon']          = 'fa fa-twitter';
            $info['css_classes'][] = 'auth-twitter';
            $info['text']          = $this->usersourceBtnPhrase('Twitter');
        } else {
            $info['text']          = $us->getOption('login_custom_text', 'Login');
            $info['css_classes'][] = 'auth-one';
        }

        return $info;
    }

    protected function usersourceBtnPhrase($name)
    {
        return $this->language_manager->phrase('portal.account.login-btn-usersource', ['usersource' => $name]);
    }
}
