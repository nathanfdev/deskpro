<?php

namespace Application\DeskPRO\Templating;

interface GlobalVariablesInterface
{
    public function setVariable($name, $value);

    public function getLicense();

    public function getVariable($name);

    public function getUser();

    public function getSetting($name);

    public function getSettingDefaultGroup($id);

    public function getRequest();

    public function isPortalEnabled();

    public function getJira();

    public function getSettingGroup($group);

    public function getConfig($name, $default = null);

    public function getSession();

    public function getLanguage();

    public function isDebug();

    public function isTesting();

    public function isDemo();

    /**
     * @deprecated
     */
    public function getStyle();

    public function getLogoBlob();

    public function getUsersourceManager();

    public function getAuthenticationManager();

    public function getTicketFieldManager();

    public function getPersonFieldManager();

    public function getOrgFieldManager();

    public function getEmailAccounts();

    /**
     * Used only for backwards comptat.
     *
     * @deprecated
     */
    public function getDataRepository($ent);

    public function getDataService($ent);

    public function getDepartments();

    public function getAgents();

    public function agent_teams();

    public function getAgentTeams();

    public function getUsersources();

    public function getUsergroups();

    public function getLanguages();

    public function getProducts();

    public function getMacros();

    public function getCustomFieldManager($type);

    public function get($name);

    public function getTimezoneList();

    public function getReturnUrl();

    public function isCloud();

    public function getBuildTime();

    public function isAppInstalled($name);

    public function getAppService($name);

    public function getFullAssetUrl();

    public function getFullWidgetUrl();

    /**
     * Returns the current app debug mode.
     *
     * @return bool The current debug mode
     */
    public function getDebug();

    public function isAppAllowed($name);

    public function canResetHelpdesk();
}
