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

/**
 * DeskPRO.
 */

namespace DpBehat\System\AntiAbuse;

use Application\DeskPRO\Entity\RateLimitLog;
use Application\DeskPRO\Entity\Setting;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\BaseContext;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DpBehat\KernelAwareTrait;

/**
 * Class EventsContext.
 */
class AntiAbuseContext extends BaseContext implements KernelAwareContext
{
    use KernelAwareTrait;

    /**
     * @Given I disable anti-abuse rate limiting
     */
    public function iDisableAntiAbuseRateLimiting()
    {
        $setting        = $this->getSetting(AntiAbuse::SETTING_RATE_LIMIT_IS_DISABLED);
        $setting->value = true;
        $this->persistAndFlush($setting);
    }

    /**
     * @Given I set :which rate limit to :limit attempt(s) within :time minute(s) with :response response and :lockoutTime minute(s) lockout time
     * @Given I set :which rate limit to :limit attempt(s) within :time minute(s) with :response response
     * @Given I set :which rate limit to :limit attempt(s) within :time minute(s) with :response response and :lockoutTime minute(s) lockout time for :role
     * @Given I set :which rate limit to :limit attempt(s) within :time minute(s) with :response response for :role
     *
     * @param string $which
     * @param int    $limit
     * @param int    $time
     * @param string $response
     * @param int    $lockoutTime
     * @param string $role
     */
    public function setRateLimits($which, $limit, $time, $response, $lockoutTime = 0, $role = '')
    {
        $settingPrefix = 'rate_limit.'.$which;
        if ($role) {
            $settingPrefix .= '.'.$role;
        }
        $this->persistSetting($settingPrefix.'.enabled', true);
        $this->persistSetting($settingPrefix.'.limit', $limit);
        $this->persistSetting($settingPrefix.'.response', $response);
        $this->persistSetting($settingPrefix.'.time', $time * 60);
        if ($lockoutTime) {
            $this->persistSetting($settingPrefix.'.lockout_time', $lockoutTime * 60);
        }

        $this->em()->flush();
    }

    /**
     * @Given rate limit table is empty
     */
    public function rateLimitTableIsEmpty()
    {
        $cmd        = $this->em()->getClassMetadata(RateLimitLog::class);
        $connection = $this->em()->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSQL($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @param $name
     *
     * @return Setting
     */
    private function getSetting($name)
    {
        $repository          = $this->repository(Setting::class);
        $setting             = $repository->findOneBy(['name' => $name]);
        $setting or $setting = new Setting();
        $setting->name       = $name;

        return $setting;
    }

    /**
     * @param $name
     * @param $value
     */
    private function persistSetting($name, $value)
    {
        $setting        = $this->getSetting($name);
        $setting->value = $value;
        $this->em()->persist($setting);
    }
}
