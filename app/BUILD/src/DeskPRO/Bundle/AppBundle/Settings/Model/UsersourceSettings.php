<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class UsersourceSettings.
 */
class UsersourceSettings
{
    /**
     * True if usersource has agent form for login.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $hasAgentLoginForm = false;

    /**
     * True if usersource has user form for login.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $hasUserLoginForm = false;

    /**
     * True if registration is enabled for usersource.
     *
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
