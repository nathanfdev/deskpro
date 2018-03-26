<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation;

/**
 * @Annotation
 * Class ApiModes
 */
class ApiModes
{
    /**
     * @var array
     */
    protected $modes = [];

    /**
     * @var array
     */
    protected $mode_alias = [
        'key'      => ['key'],
        'session'  => ['session'],
        'token'    => ['token'],
        'standard' => ['session', 'token'],
        'all'      => ['session', 'token', 'key'],
    ];

    /**
     * @param array $modes
     */
    public function __construct(array $modes)
    {
        if (isset($modes['value']) && is_array($modes['value'])) {
            $modes = $modes['value'];
        }

        foreach ($modes as $mode) {
            if (!isset($this->mode_alias[$mode])) {
                throw new \InvalidArgumentException();
            }

            $this->modes = array_merge($this->modes, $this->mode_alias[$mode]);
        }
        $this->modes = array_unique($this->modes);
    }

    /**
     * @return array
     */
    public function getModes()
    {
        return $this->modes;
    }
}
