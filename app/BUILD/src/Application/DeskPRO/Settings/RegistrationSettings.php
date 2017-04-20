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

namespace Application\DeskPRO\Settings;

use Doctrine\ORM\EntityManager;

class RegistrationSettings
{
    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup
     */
    private $everyone_group;

    /**
     * @var bool
     */
    public $reg_enabled;
    /**
     * @var bool
     */
    public $everyone_group_enabled;

    /**
     * @param Settings      $settings
     * @param EntityManager $em
     */
    public function __construct(Settings $settings, EntityManager $em)
    {
        $this->settings = $settings;
        $this->em       = $em;

        $this->everyone_group = $this->em->getRepository('DeskPRO:Usergroup')->findOneBy(['sys_name' => 'everyone']);

        $this->resetSettings();
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        $this->reg_enabled            = (bool) $this->settings->get('core.reg_enabled');
        $this->everyone_group_enabled = (bool) $this->everyone_group->is_enabled;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [
            'reg_enabled'            => $this->reg_enabled,
            'everyone_group_enabled' => $this->everyone_group_enabled,
        ];

        return $export_settings;
    }

    /**
     * @param array $set_settings
     */
    public function setArray(array $set_settings)
    {
        foreach ($set_settings as $s => $val) {
            if (property_exists($this, $s)) {
                $this->$s = $val;
            }
        }
    }

    /**
     * Persists settings.
     */
    public function saveSettings()
    {
        if ($this->reg_enabled) {
            $this->settings->setSetting('core.reg_enabled', 1);
        } else {
            $this->settings->setSetting('core.reg_enabled', 0);
        }

        $this->everyone_group->is_enabled = (bool) $this->everyone_group_enabled;
        $this->em->persist($this->everyone_group);

        $this->em->flush();
    }
}
