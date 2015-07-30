<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer;

/**
 * This knows how to find the type of an object
 */
class DataTypeMap
{
    /**
     * @var array map
     */
    protected $map;

    public function __construct(array $map = null)
    {
        if ($map) {
            $this->map = $map;
        } else {
            // this is how we configure the map for now, the null check is for testing only
            // this config process will get simpler (probably a yml config file)
            // you can see how this map checks can be expanded beyond just object type lookups
            $this->map = [
                'sandbox_widget' => [
                    'classes' => [
                        'DeskPRO\Bundle\AppBundle\Entity\SandboxWidget'
                    ]
                ]
            ];
        }
    }

    /**
     * Given some $data give me the object "type" or null if it can't be determined.
     *
     * @param $data
     * @return string|null
     */
    public function findType($data)
    {
        $object_class = is_object($data) ? get_class($data) : null;

        foreach ($this->map as $type => $checks) {
            if ($object_class && isset($checks['classes'])) {
                foreach ($checks['classes'] as $class_name) {
                    if ($class_name == $object_class) {
                        return $type;
                    }
                }
            }
        }

        return null;
    }
}
