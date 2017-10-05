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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

/**
 * Parser / recognizer for various identifiers.
 */
class IdentifierParser
{
    /**
     * @param string $raw
     *
     * @return ApplicationRef|null
     */
    public function parseApplicationRef($raw)
    {
        if ($this->recognizeApplicationInstanceId($raw)) {
            return null;
        }

        $appId = $this->parseApplicationId($raw);
        if (!is_null($appId)) {
            return new ApplicationRef($appId, false);
        }

        //let's consider it an application name
        return new ApplicationRef($raw, true);
    }

    /**
     * @param $raw
     *
     * @return string|null
     */
    public function parseApplicationId($raw)
    {
        if (1 === preg_match('#^app:(\d+)$#', $raw, $matches)) {
            return $matches[1][0];
        }

        return null;
    }

    /**
     * @param string $raw
     *
     * @return bool
     */
    public function recognizeApplicationInstanceId($raw)
    {
        //TODO: implement application instance id recognition
        return (bool) preg_match('#^(\d+)$#', $raw);
    }
}
