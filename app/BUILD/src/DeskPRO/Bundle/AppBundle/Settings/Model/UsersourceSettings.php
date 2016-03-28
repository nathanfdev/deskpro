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

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class UsersourceSettings.
 */
class UsersourceSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $hasAgentLoginForm = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $hasUserLoginForm = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $regEnabled = false;

    /**
     * @return bool
     */
    public function isHasAgentLoginForm()
    {
        return $this->hasAgentLoginForm;
    }

    /**
     * @param bool $hasAgentLoginForm
     *
     * @return $this
     */
    public function setHasAgentLoginForm($hasAgentLoginForm)
    {
        $this->hasAgentLoginForm = $hasAgentLoginForm;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHasUserLoginForm()
    {
        return $this->hasUserLoginForm;
    }

    /**
     * @param bool $hasUserLoginForm
     *
     * @return $this
     */
    public function setHasUserLoginForm($hasUserLoginForm)
    {
        $this->hasUserLoginForm = $hasUserLoginForm;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRegEnabled()
    {
        return $this->regEnabled;
    }

    /**
     * @param bool $regEnabled
     *
     * @return $this
     */
    public function setRegEnabled($regEnabled)
    {
        $this->regEnabled = $regEnabled;

        return $this;
    }
}
