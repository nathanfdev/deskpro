<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage Serializer
 */

namespace Application\DeskPRO\Serializer;

use Orb\Serializer\SerializerRegistry as BaseSerializerRegistry;

class SerializerRegistry extends BaseSerializerRegistry
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, $view = 'default', $format = 'array')
    {
        if ('array' !== $format) {
            throw new \LogicException('deskpro serializer can only output array format, currently');
        }

        return parent::serialize($data, $view, $format);
    }

    /**
     * Takes an array of arbitrary data and iterates through the array, serializing each element and returning
     * the result array. The is only 1 level deep, and is meant to make working with an array of API data (lists, etc)
     * a bit easier.
     *
     * @param  array $array an array of data to be serialized
     * @return array an array of serialized data
     */
    public function serializeArray(array $array)
    {
        $result = array();

        foreach ($array as $data) {
            $result[] = $this->serialize($data);
        }

        return $result;
    }
}
