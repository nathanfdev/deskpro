<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal\Page\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class SidebarLogin extends Element
{
    protected $selector = 'form#login-sidebar';

    public function login($username, $password)
    {
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');
    }
}
