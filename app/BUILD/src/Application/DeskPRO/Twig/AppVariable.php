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

namespace Application\DeskPRO\Twig;

use Application\DeskPRO\App;
use Application\DeskPRO\Templating\GlobalVariablesInterface;
use DeskPRO\Bundle\AppBundle\Server\PhpInfo;
use DpSys\Features;
use Symfony\Bridge\Twig\AppVariable as BaseAppVariable;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppVariable extends BaseAppVariable implements GlobalVariablesInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @deprecated since version 2.7, to be removed in 3.0
     */
    public function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);
        $this->container = $container;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container->get('templating.globals'), $name], $arguments);
    }

    public function __get($name)
    {
        return call_user_func_array([$this->container->get('templating.globals'), '__get'], [$name]);
    }

    public function __isset($name)
    {
        return call_user_func_array([$this->container->get('templating.globals'), '__isset'], [$name]);
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

    public function setVariable($name, $value)
    {
        return $this->container->get('templating.globals')->setVariable($name, $value);
    }

    public function getLicense()
    {
        return $this->container->get('templating.globals')->getLicense();
    }

    public function getVariable($name)
    {
        return $this->container->get('templating.globals')->getVariable($name);
    }

    public function getSetting($name)
    {
        if (!isset($this->cache[__METHOD__][$name])) {
            $this->cache[__METHOD__][$name] = $this->container->get('templating.globals')->getSetting($name);
        }

        return $this->cache[__METHOD__][$name];
    }

    public function getSettingDefaultGroup($id)
    {
        return $this->container->get('templating.globals')->getSettingDefaultGroup($id);
    }

    public function isPortalEnabled()
    {
        return $this->container->get('templating.globals')->isPortalEnabled();
    }

    public function getJira()
    {
        return $this->container->get('templating.globals')->getJira();
    }

    public function getSettingGroup($group)
    {
        return $this->container->get('templating.globals')->getSettingGroup($group);
    }

    public function getConfig($name, $default = null)
    {
        return $this->container->get('templating.globals')->getConfig($name, $default);
    }

    public function getLanguage()
    {
        return $this->container->get('templating.globals')->getLanguage();
    }

    public function isDebug()
    {
        return $this->container->get('templating.globals')->isDebug();
    }

    public function isTesting()
    {
        return $this->container->get('templating.globals')->isTesting();
    }

    public function isDemo()
    {
        return $this->container->get('templating.globals')->isDemo();
    }

    /**
     * @deprecated
     */
    public function getStyle()
    {
        return $this->container->get('templating.globals')->getStyle();
    }

    public function getLogoBlob()
    {
        return $this->container->get('templating.globals')->getLogoBlob();
    }

    public function getUsersourceManager()
    {
        return $this->container->get('templating.globals')->getUsersourceManager();
    }

    public function getAuthenticationManager()
    {
        return $this->container->get('templating.globals')->getAuthenticationManager();
    }

    public function getTicketFieldManager()
    {
        return $this->container->get('templating.globals')->getTicketFieldManager();
    }

    public function getPersonFieldManager()
    {
        return $this->container->get('templating.globals')->getPersonFieldManager();
    }

    public function getOrgFieldManager()
    {
        return $this->container->get('templating.globals')->getOrgFieldManager();
    }

    public function getEmailAccounts()
    {
        return $this->container->get('templating.globals')->getEmailAccounts();
    }

    /**
     * Used only for backwards comptat.
     *
     * @deprecated
     */
    public function getDataRepository($ent)
    {
        return $this->container->get('templating.globals')->getDataRepository($ent);
    }

    public function getDataService($ent)
    {
        return $this->container->get('templating.globals')->getDataService($ent);
    }

    public function getDepartments()
    {
        return $this->container->get('templating.globals')->getDepartments();
    }

    public function getAgents()
    {
        return $this->container->get('templating.globals')->getAgents();
    }

    public function agent_teams()
    {
        return $this->container->get('templating.globals')->agent_teams();
    }

    public function getAgentTeams()
    {
        return $this->container->get('templating.globals')->getAgentTeams();
    }

    public function getUsersources()
    {
        return $this->container->get('templating.globals')->getUsersources();
    }

    public function getUsergroups()
    {
        return $this->container->get('templating.globals')->getUsergroups();
    }

    public function getLanguages()
    {
        return $this->container->get('templating.globals')->getLanguages();
    }

    public function getProducts()
    {
        return $this->container->get('templating.globals')->getProducts();
    }

    public function getMacros()
    {
        return $this->container->get('templating.globals')->getMacros();
    }

    public function getCustomFieldManager($type)
    {
        return $this->container->get('templating.globals')->getCustomFieldManager($type);
    }

    public function get($name)
    {
        return $this->container->get('templating.globals')->get($name);
    }

    public function getTimezoneList()
    {
        return $this->container->get('templating.globals')->getTimezoneList();
    }

    public function getReturnUrl()
    {
        return $this->container->get('templating.globals')->getReturnUrl();
    }

    public function isCloud()
    {
        return $this->container->get('templating.globals')->isCloud();
    }

    public function getBuildTime()
    {
        return $this->container->get('templating.globals')->getBuildTime();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function isAppInstalled($name)
    {
        if (!isset($this->cache[__METHOD__][$name])) {
            $this->cache[__METHOD__][$name] = $this->container->get('templating.globals')->isAppInstalled($name);
        }

        return $this->cache[__METHOD__][$name];
    }

    public function getAppService($name)
    {
        return $this->container->get('templating.globals')->getAppService($name);
    }

    public function getFullAssetUrl()
    {
        return $this->container->get('templating.globals')->getFullAssetUrl();
    }

    public function getFullWidgetUrl()
    {
        return $this->container->get('templating.globals')->getFullWidgetUrl();
    }

    public function isAppAllowed($name)
    {
        return $this->container->get('templating.globals')->isAppAllowed($name);
    }

    public function canResetHelpdesk()
    {
        return $this->container->get('templating.globals')->canResetHelpdesk();
    }

    /**
     * @return bool
     */
    public function hasAccelerator()
    {
        return PhpInfo::hasAccelerator();
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Agent\AgentData
     */
    public function getAgentData()
    {
        return $this->container->get('agent_data');
    }

    /**
     * @return bool
     */
    public function isDevEnv()
    {
        return $this->container->get('kernel')->getEnvironment() !== 'prod';
    }

    /**
     * @return bool
     */
    public function hasVoice()
    {
        return $this->hasFeature(Features::VOICE);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasFeature($id)
    {
        return $this->container->get('deskpro.feature_flags')->hasFeature($id);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasBeta($id)
    {
        return $this->container->get('deskpro.feature_flags')->hasBeta($id);
    }

    public function getVersionName()
    {
        return $this->container->get('deskpro.app_env')->getVersionName();
    }
}
