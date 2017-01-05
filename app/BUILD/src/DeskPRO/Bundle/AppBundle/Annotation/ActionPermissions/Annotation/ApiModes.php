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
