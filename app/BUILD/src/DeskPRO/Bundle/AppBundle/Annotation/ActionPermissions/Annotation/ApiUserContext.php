<?php

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
