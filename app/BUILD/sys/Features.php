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

namespace DpSys;

/**
 * This class handles feature flags for both licensed features as well as experimental features.
 */
final class Features
{
    const VOICE = 'voice';

    /**
     * @var Features
     */
    private static $inst;

    private function __construct()
    {
    }

    /**
     * @return Features
     */
    public static function getInstance()
    {
        if (!self::$inst) {
            self::$inst = new self();
        }

        return self::$inst;
    }

    /**
     * Do we want to enable an experimental feature?
     *
     * @param string $id The id of the feature (should be a constant above)
     *
     * @return bool
     */
    private function hasExperimental($id)
    {
        return $this->getDpEnv()->getConfig('settings.enable_experimental.all')
            || $this->getDpEnv()->getConfig('settings.enable_experimental.'.$id);
    }

    /**
     * Check if a certain feature is enabeld for the current license / config.
     *
     * @param string $id the id of the feature
     *
     * @return bool
     */
    public function hasFeature($id)
    {
        switch ($id) {
            case self::VOICE:
                return $this->hasVoice();
            default:
                // assume it's an arbitrary experimental flag
                return $this->hasExperimental($id);
        }
    }

    /**
     * Can we use voice? Voice is only enabled if the license has voice or if we're in dev mode.
     *
     * @return bool
     */
    public function hasVoice()
    {
        return ($this->getLicense()->hasFlag('has_voice') || $this->getLicense()->hasFlag('is_dev'))
            && $this->hasExperimental('voice');
    }

    /**
     * @return License
     */
    private function getLicense()
    {
        return License::getLicense();
    }

    /**
     * @return \DpRun\DpEnv
     */
    private function getDpEnv()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        return $DP_ENV;
    }
}
