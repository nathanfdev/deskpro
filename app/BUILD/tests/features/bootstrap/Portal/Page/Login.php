<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal\Page;

class Login extends BasePage
{
    protected $path       = '/login';
    protected $parameters = ['base_url' => '/'];
    protected $elements   = [
        'Login Form' => 'form#login',
    ];

    public function login($username, $password)
    {
        $this->open();

        $form = $this->getElement('Login Form');

        $form->fillField('username', $username);
        $form->fillField('password', $password);
        $form->checkField('remember_me');
        $form->submit();
    }
}
