<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
