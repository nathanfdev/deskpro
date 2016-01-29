<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace Application\DeskPRO\Templating;

use Application\DeskPRO\App;
use Application\DeskPRO\Service\JIRA;
use DpSys\License;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables as BaseGlobalVariables;

class GlobalVariables extends BaseGlobalVariables implements GlobalVariablesInterface
{
    /** @var array */
    protected $variables = array();

    /** @var array simple cache of isAppAllowed() multiple calls */
    protected $app_allowed_checks = array();

    public function setVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function getLicense()
    {
        return \DpSys\License::getLicense();
    }

    public function getVariable($name)
    {
        return isset($this->variables[$name]) ? $this->variables[$name] : null;
    }

    public function getSetting($name, $default = null)
    {
        // be caerful, not all kernels have a brand stack (only portal)
        if ($this->container->has('brand_stack')) {
            if ($brand = $this->container->get('brand_stack')->getActive()) {
                return $brand->getSetting($name, $default);
            }
        }

        // default to globals for others
        return $this->container->get('settings_resolver')->getGlobalSettings()->get($name, $default);
    }

    public function getUser()
    {
        $u = parent::getUser();
        if (!$u) {
            $u = App::getCurrentPerson();
        }

        return $u;
    }

    public function getSession()
    {
        $s = parent::getSession();
        if (!$s) {
            $s = App::getSession();
        }

        return $s;
    }

    public function getSettingDefaultGroup($id)
    {
        return App::get('deskpro.core.settings')->getDefaultGroup($id);
    }

    public function getRequest()
    {
        return App::$container->getRequest();
    }

    public function isPortalEnabled()
    {
        return App::$container->getSetting('user.portal_enabled');
    }

    public function getJira()
    {
        return App::$container->get(JIRA::NAME);
    }

    public function getSettingGroup($group)
    {
        return [];
    }

    public function getConfig($name, $default = null)
    {
        return App::getConfig($name, $default);
    }

    public function getLanguage()
    {
        return App::getLanguage();
    }

    public function isDebug()
    {
        return $this->getDebug();
    }

    public function isTesting()
    {
        return isset($GLOBALS['DP_USING_TESTING_CONFIG']) && $GLOBALS['DP_USING_TESTING_CONFIG'];
    }

    public function isDemo()
    {
        return License::getLicense()->isDemo();
    }

    /**
     * @deprecated
     */
    public function getStyle()
    {
        return;
    }

    public function getLogoBlob()
    {
        return;
    }

    public function getUsersourceManager()
    {
        return App::getSystemService('UsersourceManager');
    }

    public function getAuthenticationManager()
    {
        return App::getSystemService('authentication_manager');
    }

    public function getTicketFieldManager()
    {
        return App::getSystemService('TicketFieldsManager');
    }

    public function getPersonFieldManager()
    {
        return App::getSystemService('PersonFieldsManager');
    }

    public function getOrgFieldManager()
    {
        return App::getSystemService('OrgFieldsManager');
    }

    public function getEmailAccounts()
    {
        static $accounts;

        if ($accounts === null) {
            $accounts = array_map(function ($a) {
                return array(
                    'id'                    => $a->id,
                    'address'               => $a->address,
                    'other_addresses'       => $a->other_addresses,
                    'all_addresses'         => $a->getAllAddresses(),
                    'incoming_account_type' => $a->getIncomingAccountType(),
                    'outgoing_account_type' => $a->getOutgoingAccountType(),
                );
            }, App::$container->getEmailAccountManager()
                ->getAllAccounts());
        }

        return $accounts;
    }

    /**
     * Used only for backwards comptat.
     *
     * @deprecated
     */
    public function getDataRepository($ent)
    {
        return App::getSystemService("{$ent}Data");
    }

    public function getDataService($ent)
    {
        return App::getDataService($ent);
    }

    public function getDepartments()
    {
        return App::getDataService('Department');
    }

    public function getAgents()
    {
        return App::getDataService('Agent');
    }

    public function agent_teams()
    {
        return App::getDataService('AgentTeam');
    }
    public function getAgentTeams()
    {
        return App::getDataService('AgentTeam');
    }

    public function getUsersources()
    {
        return App::getDataService('Usersource');
    }

    public function getUsergroups()
    {
        return App::getDataService('Usergroup');
    }

    public function getLanguages()
    {
        return App::getDataService('Language');
    }

    public function getProducts()
    {
        return App::getDataService('Product');
    }

    public function getCustomFieldManager($type)
    {
        switch ($type) {
            case 'tickets':
                return App::getSystemService('ticket_fields_manager');
            case 'people':
                return App::getSystemService('person_fields_manager');
        }

        return;
    }

    public function get($name)
    {
        return $this->__get($name);
    }

    public function __get($name)
    {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }

        if (method_exists($this, $name)) {
            return $this->$name;
        }
        if (method_exists($this, "get$name")) {
            return $this->{"get$name"};
        }

        if ($ent = \Orb\Util\Strings::extractRegexMatch('#^(.*?)Data$#', $name, 1)) {
            return App::getContainer()->getSystemService(ucfirst($ent).'Data');
        }

        return;
    }

    public function __call($method, $args)
    {
        if ($var = \Orb\Util\Strings::extractRegexMatch('#^get(.*?)$#', $method, 1)) {
            return $this->__get(ucfirst($method));
        }

        return;
    }

    public function __isset($name)
    {
        return isset($this->variables[$name]);
    }

    public function getLastException()
    {
        if (!App::has('deskpro.exception_logger')) {
            return;
        }

        $logger = App::get('deskpro.exception_logger');

        return $logger->getLastException();
    }

    public function getTimezoneList()
    {
        static $tz = null;

        if ($tz === null) {
            $tz = array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers());
        }

        return $tz;
    }

    public function getReturnUrl()
    {
        $request = App::getRequest();

        return $request->getReturnParam() ?: $request->getRequestUri();
    }

    public function isCloud()
    {
        return defined('DPC_IS_CLOUD');
    }

    public function getBuildTime()
    {
        return defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0;
    }

    public function isAppInstalled($name)
    {
        return App::getContainer()->getAppManager()->isPackageInstalled($name);
    }

    public function isAppAllowed($name)
    {
        $person = $this->getUser();
        $k      = sha1($name.'|'.$person['id']);

        if (isset($this->app_allowed_checks[$k])) {
            return $this->app_allowed_checks[$k];
        }

        if (!$this->isAppInstalled($name)) {
            return $this->app_allowed_checks[$k] = false;
        }

        $cont = App::getContainer();

        if (!$app = $cont->getAppManager()->getPackageApp($name)) {
            return $this->app_allowed_checks[$k] = false;
        }

        if ('set' !== $app->perm_type) {
            return $this->app_allowed_checks[$k] = true;
        }

        $perms = $cont->getAppPerms();

        return $this->app_allowed_checks[$k] = $perms->checkPersonPermission($app, $person);
    }

    public function getAppService($name)
    {
        return App::getContainer()->getAppManager()->getService($name);
    }

    public function getFullAssetUrl()
    {
        if (defined('DPC_SITE_DOMAIN')) {
            return '//'.DPC_SITE_DOMAIN.'/web/';
        } else {
            $asset_url = $this->container->getSetting('core.deskpro_url');
            $asset_url = trim(str_replace('/index.php', '', $asset_url), '/');
            $asset_url .= '/web/';
            $asset_url = preg_replace('#^https?://#', '//', $asset_url);

            return $asset_url;
        }
    }

    public function getFullWidgetUrl()
    {
        if (defined('DPC_SITE_DOMAIN')) {
            return '//'.DPC_SITE_DOMAIN.'/';
        } else {
            $helpdesk_url = trim(str_replace('/index.php', '', $this->container->getSetting('core.deskpro_url')), '/').'/';
            $deskpro_url  = $helpdesk_url;

            $widget_url = $deskpro_url;
            $widget_url = preg_replace('#^https?://#', '//', $widget_url);

            return $widget_url;
        }
    }

    public function __toString()
    {
        return '[app]';
    }

    public function canResetDemo()
    {
        return true;
    }
}
