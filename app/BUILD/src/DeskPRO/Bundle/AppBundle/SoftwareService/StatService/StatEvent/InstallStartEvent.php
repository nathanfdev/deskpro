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

namespace DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent;

class InstallStartEvent
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $build;

    /**
     * @var string
     */
    private $user_name;

    /**
     * @var string
     */
    private $user_email;

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param string $build
     *
     * @return $this
     */
    public function setBuild($build)
    {
        $this->build = $build;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * @param string $user_name
     *
     * @return $this
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        return $this->user_email;
    }

    /**
     * @param string $user_email
     *
     * @return $this
     */
    public function setUserEmail($user_email)
    {
        $this->user_email = $user_email;

        return $this;
    }
}
