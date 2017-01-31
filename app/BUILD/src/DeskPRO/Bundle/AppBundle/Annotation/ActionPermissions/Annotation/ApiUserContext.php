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

use Application\DeskPRO\Exception\MissingConfigurationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class ApiUserContext.
 *
 * @Annotation
 */
class ApiUserContext extends Security
{
    const CONTEXT_OPEN  = 'open';
    const CONTEXT_USER  = 'user';
    const CONTEXT_AGENT = 'agent';
    const CONTEXT_ADMIN = 'admin';

    /**
     * @var array
     */
    protected static $availableContexts = [
        self::CONTEXT_OPEN,
        self::CONTEXT_USER,
        self::CONTEXT_AGENT,
        self::CONTEXT_ADMIN,
    ];

    /**
     * Constructor.
     *
     * @param array $values
     *
     * @throws \Exception
     */
    public function __construct(array $values)
    {
        if (!isset($values['value']) || !in_array($values['value'], self::$availableContexts)) {
            throw new MissingConfigurationException();
        }

        $defaultRole = $values['value'];
        unset($values['value']);

        $overrides = [];
        foreach ($values as $role => $actions) {
            if (is_string($actions)) {
                $actions = (array) $actions;
            }
            if (!is_array($actions)) {
                throw new \Exception('Role actions should be a string or array value');
            }

            foreach ($actions as $action) {
                $overrides[$action] = $role;
            }
        }

        // collect role permission overrides
        $expressions = [];
        foreach ($overrides as $action => $role) {
            $expression = ["request.attributes.get('_controller') matches '/::{$action}Action$/'"];
            if ($role !== self::CONTEXT_OPEN) {
                $role         = sprintf('ROLE_%s', strtoupper($role));
                $expression[] = "is_granted('{$role}')";
            }

            $expressions[] = implode(' and ', $expression);
        }

        // set default role permission
        $expression = [];
        if (!empty($overrides)) {
            $customActions = array_map(function ($action) {
                return '::'.$action.'Action';
            }, array_keys($overrides));

            $expression[] = "request.attributes.get('_controller') matches '/^((?!(".implode('|', $customActions).")).)*$/'";
        }

        if ($defaultRole !== self::CONTEXT_OPEN) {
            $role         = sprintf('ROLE_%s', strtoupper($defaultRole));
            $expression[] = "is_granted('{$role}')";
        }

        $expressions[] = $expression ? implode(' and ', $expression) : 'true';

        parent::__construct([
            'value' => implode(' or ', $expressions),
        ]);
    }
}
