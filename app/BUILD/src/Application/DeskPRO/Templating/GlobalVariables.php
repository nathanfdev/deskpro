<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Templating;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\HttpFoundation\LegacyRequestUtils;
use Application\DeskPRO\Service\JIRA;
use DpSys\License;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables as BaseGlobalVariables;

class GlobalVariables extends BaseGlobalVariables implements GlobalVariablesInterface
{
    /** @var array */
    protected $variables = [];

    /** @var array simple cache of isAppAllowed() multiple calls */
    protected $app_allowed_checks = [];

    public function setVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function getLicense()
    {
        return License::getLicense();
    }

    public function getVariable($name)
    {
        return isset($this->variables[$name]) ? $this->variables[$name] : null;
    }

    public function getUser()
    {
        return App::getCurrentPerson();
    }

    public function getSetting($name)
    {
        return App::getSetting($name);
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
        return App::$container->getBrandSetting('core.iface_portal');
    }

    public function getJira()
    {
        return App::$container->get(JIRA::NAME);
    }

    public function getSettingGroup($group)
    {
        $group_vars = App::get('deskpro.core.settings')->getGroup($group);

        return $group_vars;
    }

    public function getConfig($name, $default = null)
    {
        return App::getConfig($name, $default);
    }

    public function getSession()
    {
        return App::getSession();
    }

    public function getLanguage()
    {
        return App::getLanguage();
    }

    public function isDebug()
    {
        return $this->container->isDebug();
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
        return App::getSystemService('logo_blob');
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
                return [
                    'id'                    => $a->id,
                    'address'               => $a->address,
                    'other_addresses'       => $a->other_addresses,
                    'all_addresses'         => $a->getAllAddresses(),
                    'incoming_account_type' => $a->getIncomingAccountType(),
                    'outgoing_account_type' => $a->getOutgoingAccountType(),
                ];
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

    public function getMacros()
    {
        return App::$container->get('doctrine.orm.default_entity_manager')->getRepository(TicketMacro::class)->findAll();
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

        if ($ent = Strings::extractRegexMatch('#^(.*?)Data$#', $name, 1)) {
            return App::getContainer()->getSystemService(ucfirst($ent).'Data');
        }

        return;
    }

    public function __call($method, $args)
    {
        if ($var = Strings::extractRegexMatch('#^get(.*?)$#', $method, 1)) {
            return $this->__get(ucfirst($method));
        }

        return;
    }

    public function __isset($name)
    {
        return isset($this->variables[$name]);
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

        return LegacyRequestUtils::readReturnParam($request) ?: $request->getRequestUri();
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
        $person = App::getSession()->getPerson();
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
            $asset_url = $this->container->getBrandSetting('core.deskpro_url');
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
            $helpdesk_url = trim(str_replace('/index.php', '', $this->container->getBrandSetting('core.deskpro_url')), '/').'/';
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

    public function canResetHelpdesk()
    {
        return $this->daysToResetLeft() > 0;
    }

    private function daysToResetLeft()
    {
        $settingsResolver = $this->container->get('settings_resolver');
        $installTime      = $settingsResolver->getGlobalSettings()->get('core.install_timestamp');

        return 90 - (time() - $installTime) / (60 * 60 * 24);
    }

    public function formatDaysToResetLeft()
    {
        $date = new \DateTime('+'.floor($this->daysToResetLeft()).' days');

        return $date->format('Y-m-d');
    }

    public function brandDefaultDepartment($type)
    {
        return App::get('brand_form_helper')->getDefaultDepartment($type);
    }

    public function isRelativeTimesDisabled()
    {
        return App::getContainer()->get('settings_resolver')->getGlobalSettings()->getBool('core.disable_relative_times', false);
    }
}
