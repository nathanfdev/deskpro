<?php

namespace Application\DeskPRO\Templating;

interface GlobalVariablesInterface
{
    public function setVariable($name, $value);

    public function getLicense();

    public function getVariable($name);

    public function getUser();

    /**
     * @deprecated please add a specific templating extension for whatever setting you need
     *            because this is limited to AdvancedSettings::getAcceptableSettingIds
     */
    public function getSetting($name);

    /**
     * @deprecated
     */
    public function getSettingDefaultGroup($id);

    public function getRequest();

    public function isPortalEnabled();

    /**
     * @deprecated
     */
    public function getJira();


    /**
     * @deprecated
     */
    public function getSettingGroup($group);

    /**
     * @deprecated
     */
    public function getConfig($name, $default = null);

    public function getSession();

    public function getLanguage();

    public function isDebug();

    public function isTesting();

    public function isQa();

    public function isDemo();

    /**
     * @deprecated
     */
    public function getStyle();

    public function getLogoBlob();

    public function getTicketFieldManager();

    public function getPersonFieldManager();

    public function getOrgFieldManager();

    public function getEmailAccounts();

    /**
     * @deprecated
     */
    public function getDataRepository($ent);

    /**
     * @deprecated
     */
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

    /**
     * @deprecated
     */
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
