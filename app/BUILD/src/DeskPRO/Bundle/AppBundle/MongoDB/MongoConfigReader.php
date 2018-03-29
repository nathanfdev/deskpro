<?php

namespace DeskPRO\Bundle\AppBundle\MongoDB;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DpRun\LowUtil;

class MongoConfigReader
{
    const DEFAULT_ID = 'default';

    /**
     * @var \DpRun\DpEnv
     */
    private $appEnv;

    /**
     * DbConfigReader constructor.
     *
     * @param AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * Given a connection ID, get params from config.
     *
     * @param string $id
     * @param string $type
     *
     * @return array
     */
    public function getParams($id, $type = 'server')
    {
        switch ($id) {
            case self::DEFAULT_ID:
            default:
                $conf_array_raw = $this->appEnv->getConfig('mongo');
                break;
        }

        return LowUtil::getMongoConfigFromArray($conf_array_raw)[$type];
    }
}
