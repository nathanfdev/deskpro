<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal\Page;

class Home extends BasePage
{
    protected $path       = '/';
    protected $parameters = ['base_url' => '/'];

    public function sidebarLogin($username, $password)
    {
        $this->open();

        return $this->getElement('SidebarLogin')->login($username, $password);
    }
}
