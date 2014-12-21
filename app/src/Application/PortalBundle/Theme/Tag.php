<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Theme;

/**
 * Contains the configuration for a theme tag
 */
class Tag implements \Serializable
{
    protected $name;
    protected $controller_name;
    protected $esi;
    protected $default_options;
    protected $defined_options;

    /**
     * @param string $name            the tag's name
     * @param string $controller_name Theme:Portal:index
     * @param array  $defined_options An indexed array of options names (if not in this list, an exception thrown)
     * @param array  $default_options A map of pre-determined default values for some or all options (can override)
     * @param bool   $esi             true if this should be an edge side include
     */
    public function __construct($name, $controller_name, $defined_options = array(), $default_options = array(), $esi = false)
    {
        $this->name = $name;
        $this->controller_name = $controller_name;
        $this->defined_options = $defined_options;
        $this->default_options = $default_options;
        $this->esi = $esi;
    }

    public function serialize()
    {
        return serialize(
            array(
                'name' => $this->name,
                'controller_name' => $this->controller_name,
                'defined_options' => $this->defined_options,
                'default_options' => $this->default_options,
                'esi' => $this->esi
            )
        );
    }

    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this->name = $unserialized['name'];
        $this->controller_name = $unserialized['controller_name'];
        $this->defined_options = $unserialized['defined_options'];
        $this->default_options = $unserialized['default_options'];
        $this->esi = $unserialized['esi'];
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
     * @return array a map of pre-determined default values for some or all options
     */
    public function getDefaultOptions()
    {
        return $this->default_options;
    }

    /**
     * @return array an indexed array of defined option names
     */
    public function getDefinedOptions()
    {
        return $this->defined_options;
    }

    /**
     * @return boolean
     */
    public function isEsi()
    {
        return (bool) $this->esi;
    }
}
