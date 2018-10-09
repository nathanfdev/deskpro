<?php

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
        if (!isset($this->projects[$name])) {
            throw new \InvalidArgumentException();
        }

        return $this->projects[$name];
    }
}
