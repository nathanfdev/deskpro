<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Settings;

use Orb\Util\Numbers;

class LoginRateLimitSettings
{
    const KEY = 'login_rate_limit';

    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;
    /** @var string */
    private $context;

    /** @var bool */
    public $enabled;
    /** @var int */
    public $attempts;
    /** @var int */
    public $attempts_time;
    /** @var int */
    public $lock_time;


    /**
     * @param Settings $settings
     * @param string $context
     * @throws \Exception
     */
    public function __construct(Settings $settings, $context = 'user')
    {
        if ('user' !== $context && 'agent' !== $context) {
            throw new \Exception(sprintf('Wrong context "%s"', $context));
        }
        $this->settings = $settings;
        $this->context = $context;

        $this->resetSettings();
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        $this->enabled = (bool) $this->settings->get($this->context . '.' . self::KEY . '.enabled');
        $this->attempts = (int) $this->settings->get($this->context . '.' . self::KEY . '.attempts');
        $this->attempts_time = (int) $this->settings->get($this->context . '.' . self::KEY . '.attempts_time');
        $this->lock_time = (int) $this->settings->get($this->context . '.' . self::KEY . '.lock_time');
    }


    /**
     * @return array
     */
    public function toArray()
    {
        $ret = array();

        foreach (array(
                     'enabled',
                     'attempts',
                     'attempts_time',
                     'lock_time',
                 ) as $opt) {
            $ret[$opt] = $this->$opt;
        }

        return $ret;
    }


    /**
     * @param array $set_settings
     */
    public function setArray(array $set_settings)
    {
        $this->enabled        = (bool) $set_settings['enabled'];
        $this->attempts       = (int) $set_settings['attempts'];
        $this->attempts_time  = (int) $set_settings['attempts_time'];
        $this->lock_time      = (int) $set_settings['lock_time'];
    }


    /**
     * Persists settings
     */
    public function saveSettings()
    {
        foreach (array(
                     'enabled',
                     'attempts',
                     'attempts_time',
                     'lock_time',
                 ) as $opt) {
            $this->settings->setSetting($this->context . '.' . self::KEY . '.' . $opt, $this->$opt);
        }
    }
}
