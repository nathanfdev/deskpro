<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
