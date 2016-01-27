<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataSerializer;

use Orb\Util\Strings;

/**
 * This knows how to find the type of an object.
 */
class DataTypeMap
{
    /**
     * @var array map
     */
    protected $map;

    /**
     * Constructor.
     *
     * $map is like:
     * <code>
     * $map = [
     *     'foo_bar' => [
     *         'classes' => [
     *             'DeskPRO\Bundle\AppBundle\Entity\FooBar',
     *             '\Proxies\__CG__\DeskPRO\Bundle\AppBundle\Entity\FooBar',
     *         ],
     *     ],
     * ];
     * </code>
     *
     * @param array|null $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Given some $data give me the object "type" or null if it can't be determined.
     *
     * @param mixed $data
     * @param bool  $null_on_none True to return null of no found type, otherwise an exception is raised
     *
     * @throws \Exception
     *
     * @return string
     */
    public function findType($data, $null_on_none = false)
    {
        $object_class = is_object($data) ? get_class($data) : null;

        if ($object_class && $type = $this->findTypeForClass($object_class)) {
            return $type;
        }

        if (is_array($data)) {
            $data = array_shift($data);
        } elseif ($data instanceof \Traversable) {
            $data = $data->current();
        }

        if (is_array($data) || $data instanceof \Traversable) {
            $object_class = is_object($data) ? get_class($data) : null;

            if ($object_class && $type = $this->findTypeForClass($object_class)) {
                return $type;
            }

            return ''; // it is still an array and we can't determine type now
        }

        $object_class = is_object($data) ? get_class($data) : null;

        if (!$object_class) {
            return '';
        } elseif ($type = $this->findTypeForClass($object_class)) {
            return $type;
        } else {
            if ($null_on_none) {
                return '';
            }
            throw new \Exception("Type for $object_class not found.");
        }
    }

    /**
     * Given a class name, give me the type.
     *
     * @param $object_class
     *
     * @return null|string
     */
    public function findTypeForClass($object_class)
    {
        $object_class = $this->normalizeNamespaceString($object_class);

        foreach ($this->map as $type => $checks) {
            if (isset($checks['classes'])) {
                foreach ($checks['classes'] as $class_name) {
                    $class_name = $this->normalizeNamespaceString($class_name);
                    if ($class_name == $object_class) {
                        return $type;
                    }
                }
            }
        }

        // we did not find an explicit type from the map, so we can imply a type:
        // take the non-qualified class name and go from camel -> underscore
        // e.g. DeskPRO\Bundle\AppBundle\Entity\FooBar => foo_bar
        $class_name_parts = explode('\\', $object_class);
        if (count($class_name_parts)) {
            $class_name = end($class_name_parts);

            return Strings::camelCaseToUnderscore($class_name);
        }

        return '';
    }

    /**
     * Make sure to remove any leading "\" from the FQNS.
     *
     * @param $object_class
     *
     * @return string
     */
    public function normalizeNamespaceString($object_class)
    {
        if (substr($object_class, 0, 1) === '\\') {
            return substr($object_class, 1);
        }

        return $object_class;
    }
}
