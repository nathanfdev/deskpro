<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\Entity\Usersource;

/**
 * Used to filter results down to what you want
 * UsersourceManager returns instances of this offering you a flexible filtering API
 * Instead of doing direct queries for usersources, we can centralize and keep dynamic the logic of usersource selection.
 */
class UsersourceCollection extends \ArrayObject
{
    /**
     * @return UsersourceCollection
     */
    public function forInterface($interface, $includeDisabled = false)
    {
        switch ($interface) {
            case 'user':
                return $this->configuredForUsers($includeDisabled);
            case 'agent':
            case 'admin':
            case 'reports':
            case 'billing':
                return $this->configuredForAgents($includeDisabled);
        }

        throw new \InvalidArgumentException("Unknown interface '$interface'");
    }

    /**
     * Filter out any usersources that are not enabled.
     *
     * @return UsersourceCollection
     */
    public function mustBeEnabled()
    {
        $filtered = array_filter(
            (array) $this, function (Usersource $us) {
                return (bool) $us->is_enabled;
            }
        );

        return new static($filtered);
    }

    /**
     * Filter out any usersources that are not enabled.
     *
     *
     * @param int $id id
     *
     * @return UsersourceCollection
     */
    public function mustHaveSyncEnabled()
    {
        $filtered = array_filter(
            (array) $this, function (Usersource $us) {
                return (bool) $us->isSyncEnabled();
            }
        );

        return new static($filtered);
    }

    /**
     * Limits to this ID only, still allowing other filters to fit your criteria.
     *
     * @param int $id id
     *
     * @return UsersourceCollection
     */
    public function mustHaveId($id)
    {
        $filtered = array_filter(
            (array) $this, function (Usersource $us) use ($id) {
                return $us->id == $id;
            }
        );

        return new static($filtered);
    }

    public function withAppId($app_id)
    {
        $filtered = array_filter(
            (array) $this,
            function (Usersource $us) use ($app_id) {
                if ($app = $us->getApp()) {
                    return $app->getId() == $app_id;
                }

                return false;
            }
        );

        return new static($filtered);
    }

    /**
     * @return UsersourceCollection
     */
    public function configuredForAgents($includeDisabled = false)
    {
        // select only agent enabled usersources
        $filtered = array_filter(
            (array) $this, function (Usersource $us) use ($includeDisabled) {
                return $us->type == Usersource::TYPE_AGENT && ($includeDisabled ?: $us->is_enabled);
            }
        );

        // order them
        usort($filtered, function ($us1, $us2) {
            if ($us1->display_order == $us2->display_order) {
                return 0;
            }

            if ($us1->display_order > $us2->display_order) {
                return 1;
            }

            return -1;
        });

        return new static($filtered);
    }

    /**
     * @return UsersourceCollection
     */
    public function configuredForUsers($includeDisabled = false)
    {
        // select only user enabled usersources
        $filtered = array_filter(
            (array) $this, function (Usersource $us) use ($includeDisabled) {
                return $us->type === Usersource::TYPE_USER && ($includeDisabled ?: $us->is_enabled);
            }
        );

        // order them
        usort($filtered, function ($us1, $us2) {
            if ($us1->display_order == $us2->display_order) {
                return 0;
            }

            if ($us1->display_order > $us2->display_order) {
                return 1;
            }

            return -1;
        });

        return new static($filtered);
    }

    /**
     * @param array|string $capability a string with a single capability, or an array of strings
     *
     * @return UsersourceCollection with usersources that have at least one of the passed capabilities
     */
    public function withCapability($capability)
    {
        $filtered = array_filter(
            (array) $this, function (Usersource $us) use ($capability) {
                if (is_array($capability)) {
                    foreach ($capability as $cap) {
                        if ($us->getAdapter()->isCapable($cap)) {
                            return true;
                        }
                    }
                } else {
                    return $us->getAdapter()->isCapable($capability);
                }

                return false;
            }
        );

        return new static($filtered);
    }

    /**
     * @return UsersourceCollection
     */
    public function ofType($type)
    {
        $type = strtolower($type);

        $filtered = array_filter(
            (array) $this, function (Usersource $us) use ($type) {
                return strtolower($us->source_type) == $type;
            }
        );

        return new static($filtered);
    }

    /**
     * @return UsersourceCollection
     */
    public function withBackgroundSso()
    {
        $filtered = array_filter(
            (array) $this, function (Usersource $us) {
                return $us->is_sso_background;
            }
        );

        return new static($filtered);
    }

    /**
     * @return UsersourceCollection
     */
    public function withAutoSso()
    {
        $filtered = array_filter(
            (array) $this, function (Usersource $us) {
                return $us->is_sso_auto;
            }
        );

        return new static($filtered);
    }

    /**
     * @return UsersourceCollection
     */
    public function withNoApp()
    {
        $filtered = array_filter(
            (array) $this, function (Usersource $us) {
                return !$us->app;
            }
        );

        return new static($filtered);
    }

    /**
     * @return \Application\DeskPRO\Entity\Usersource|null
     */
    public function getFirstOrNull()
    {
        $arr = (array) $this;

        return array_pop($arr);
    }

    public function contains(Usersource $usersource)
    {
        $arr = (array) $this;
        foreach ($arr as $us) {
            if ($us->id == $usersource->id) {
                return true;
            }
        }

        return false;
    }
}
