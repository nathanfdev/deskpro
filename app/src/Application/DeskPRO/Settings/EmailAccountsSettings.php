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

namespace Application\DeskPRO\Settings;


class EmailAccountsSettings
{
    const PREFIX = 'core.emails';

    /**
     * @var Settings
     */
    protected $settings;

    /** @var array  */
    protected $values = array(
        'attach_agent_maxsize'   => 26214400,
        'attach_agent_must_exts' => array(),
        'attach_agent_not_exts'  => array(),
        'attach_user_maxsize'    => 26214400,
        'attach_user_must_exts'  => array(),
        'attach_user_not_exts'   => array(),
        'sendemail_attach_maxsize' => 7340032,
    );

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function toArray()
    {
        $data = array();
        foreach ($this->values as $k => $v) {
            if ($k == 'sendemail_attach_maxsize') {
                $storedValue = $this->settings->get('core.sendemail_attach_maxsize');
            } else {
                $storedValue = $this->settings->get(self::PREFIX . '.' . $k, $v);
            }

            if (is_int($v)) {
                $storedValue = (int) $storedValue;
            } elseif (is_array($v)) {
                $storedValue = $storedValue ? explode(',', $storedValue) : $v;
            }

            $data[$k] = $this->values[$k] = $storedValue ?: $v;
        }

        return $data;
    }

    public function fromArray(array $data = array())
    {
        foreach ($data as $k => $v) {
            if (!array_key_exists($k, $this->values)) {
                continue;
            }

            $storeValue = $v;
            if (is_int($this->values[$k])) {
                $storeValue = $v = (int) $v;
            } elseif (is_array($this->values[$k])) {
                $v = (array) $v;
                $storeValue = implode(',', $v);
            }
            $this->values[$k] = $v;

            if ($k == 'sendemail_attach_maxsize') {
                $this->settings->setSetting('core.sendemail_attach_maxsize', (int)$storeValue ?: null);
            } else {
                $this->settings->setSetting(self::PREFIX . '.' . $k, $storeValue);
            }
        }
    }
}
