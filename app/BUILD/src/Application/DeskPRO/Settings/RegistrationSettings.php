<?php

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
        $this->everyone_group_enabled = (bool) $this->everyone_group->is_enabled;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [
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
        $this->everyone_group->is_enabled = (bool) $this->everyone_group_enabled;
        $this->em->persist($this->everyone_group);

        $this->em->flush();
    }
}
