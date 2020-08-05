<?php

namespace DeskPRO\Bundle\PortalBundle\View;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;

class UsersourcesHelper
{
    /**
     * @var AuthenticationManager
     */
    private $authManager;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var array we only want to actually generate this array once per request, so we just store it here if we
     *            have calculated it before
     */
    private static $cached = false;

    /**
     * Constructor.
     *
     * @param AuthenticationManager $authManager
     * @param LanguageManager       $languageManager
     */
    public function __construct(AuthenticationManager $authManager, LanguageManager $languageManager)
    {
        $this->authManager     = $authManager;
        $this->languageManager = $languageManager;
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
        return $this->authManager->hasFormLoginCapability();
    }

    /**
     * @return array
     */
    protected function generateUsersourceViewList()
    {
        if (!$this->authManager->isAuthVisible()) {
            return [];
        }

        $usersources = [];

        /** @var \Application\DeskPRO\Entity\Usersource $us */
        foreach ($this->authManager->getLoginIconUsersources() as $us) {
            $info          = $this->getUsersourceInfo($us);
            $usersources[] = [
                'id'      => $us->getId(),
                'text'    => $info['text'],
                'icon'    => $info['icon'],
                'classes' => $info['css_classes'],
            ];
        }

        /** @var \Application\DeskPRO\Entity\Usersource $us */
        foreach ($this->authManager->getLoginTextButtonUsersources() as $us) {
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
            'icon'        => null, // a css string to use in an <i> element for font awesome icon (example: "fab fa-facebook-f").
            'text'        => '', // the full text that appears on the login button
            'css_classes' => ['button', 'auth'], // the deskpro css classes that should apply to the login button
        ];

        if ('facebook' === $type) {
            $info['icon']          = 'fab fa-facebook-f';
            $info['css_classes'][] = 'auth-facebook';
            $info['text']          = $this->usersourceBtnPhrase('Facebook');
        } elseif ('googleplus' === $type) {
            $text                  = $us->getOption('login_custom_text', $this->usersourceBtnPhrase('Google'));
            $info['icon']          = 'fab fa-google';
            $info['css_classes'][] = 'auth-google';
            $info['text']          = $text ? $text : $this->usersourceBtnPhrase('Google');
        } elseif ('google' === $type) {
            $info['icon']          = 'fab fa-google';
            $info['css_classes'][] = 'auth-google';
            $info['text']          = $this->usersourceBtnPhrase('Google');
        } elseif ('twitter' === $type) {
            $info['icon']          = 'fab fa-twitter';
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
        return $this->languageManager->phrase(
            ['helpcenter.account.login-btn-usersource', 'portal.account.login-btn-usersource'],
            ['usersource' => $name]
        );
    }
}
