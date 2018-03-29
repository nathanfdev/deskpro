<?php

namespace DeskPRO\Bundle\PortalBundle\Theme;

/**
 * Contains the configuration for a theme tag.
 */
class Tag implements \Serializable
{
    protected $name;
    protected $controller_name;
    protected $esi;
    protected $default_options;
    protected $defined_options;
    protected $allow_route_params;
    protected $always_guest_inline;

    /**
     * @param string $name                The tag's name
     * @param string $controller_name     Theme:Portal:index
     * @param array  $defined_options     An indexed array of options names (if not in this list, an exception thrown)
     * @param array  $default_options     A map of pre-determined default values for some or all options (can override)
     * @param bool   $esi                 True if this should be an edge side include
     * @param bool   $always_guest_inline Ignore ESI=true if the user is a guest
     * @param bool   $allow_route_params  True if the /_proxy call will include _route and _route_params
     */
    public function __construct(
        $name,
        $controller_name,
        $defined_options = [],
        $default_options = [],
        $esi = false,
        $always_guest_inline = true,
        $allow_route_params = false
    ) {
        $this->name                = $name;
        $this->controller_name     = $controller_name;
        $this->defined_options     = $defined_options;
        $this->default_options     = $default_options;
        $this->esi                 = $esi;
        $this->always_guest_inline = $always_guest_inline;
        $this->allow_route_params  = $allow_route_params;
    }

    public function serialize()
    {
        return serialize(
            [
                'name'                => $this->name,
                'controller_name'     => $this->controller_name,
                'defined_options'     => $this->defined_options,
                'default_options'     => $this->default_options,
                'esi'                 => $this->esi,
                'always_guest_inline' => $this->always_guest_inline,
                'allow_route_params'  => $this->allow_route_params,
            ]
        );
    }

    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this->name                = $unserialized['name'];
        $this->controller_name     = $unserialized['controller_name'];
        $this->defined_options     = $unserialized['defined_options'];
        $this->default_options     = $unserialized['default_options'];
        $this->esi                 = $unserialized['esi'];
        $this->always_guest_inline = $unserialized['always_guest_inline'];
        $this->allow_route_params  = $unserialized['allow_route_params'];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getControllerName()
    {
        return $this->controller_name;
    }

    /**
     * @return array an indexed array of defined option names
     */
    public function getDefinedOptions()
    {
        return $this->defined_options;
    }

    /**
     * @return array a map of pre-determined default values for some or all options
     */
    public function getDefaultOptions()
    {
        return $this->default_options;
    }

    /**
     * @param bool $isGuest
     *
     * @return bool
     */
    public function isEsi($isGuest = false)
    {
        // not an esi tag, no more processing needed
        if (!$this->esi) {
            return false;
        }

        // is an esi... if not a guest request, then yes
        if (!$isGuest) {
            return true;
        }

        // its an esi tag and a guest... only if we don't inline for guests
        return !$this->isAlwaysGuestInline();
    }

    public function allowRouteParams()
    {
        return (bool) $this->allow_route_params;
    }

    public function isAlwaysGuestInline()
    {
        return (bool) $this->always_guest_inline;
    }
}
