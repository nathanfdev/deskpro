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

namespace DeskPRO\Bundle\DevBundle\Language;

use Onesky\Api\Client as OneSkyClient;

class OneSky extends OneSkyClient
{
    const PROJECT_PORTAL = 'portal';
    const PROJECT_AGENT  = 'agent';
    const PROJECT_OTHER  = 'other';

    /**
     * Array of name => projectId.
     *
     * @var array
     */
    private $projects;

    public function __construct($apiKey, $apiSecret, array $projects)
    {
        parent::__construct();
        $this->setApiKey($apiKey)->setSecret($apiSecret);
        $this->projects = $projects;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getProjectId($name)
    {
        return $this->projects[$name];
    }
}
