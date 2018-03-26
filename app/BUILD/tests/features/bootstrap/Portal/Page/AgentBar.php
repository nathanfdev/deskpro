<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal\Page;

class AgentBar extends BasePage
{
    protected $path       = '/';
    protected $parameters = ['base_url' => '/'];
    protected $elements   = [
        'Agent Bar' => ['css' => '#agent-bar'],
    ];

    public function isAgentBarOnPage()
    {
        try {
            return $this->getElement('Agent Bar')->isValid();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isAdminDropdownOnAgentBar()
    {
        try {
            return $this->getElement('Agent Bar')->find('css', '#admin-dropdown-arrow') !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
