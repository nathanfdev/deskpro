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
     * ApiUserContext constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (!isset($values['value']) || !in_array($values['value'], self::$availableContexts)) {
            throw new MissingConfigurationException();
        }

        if ($values['value'] === self::CONTEXT_OPEN) {
            $values['value'] = 'true';
        } else {
            $role            = sprintf('ROLE_%s', strtoupper($values['value']));
            $values['value'] = "is_granted('{$role}')";
        }
        parent::__construct($values);
    }
}
