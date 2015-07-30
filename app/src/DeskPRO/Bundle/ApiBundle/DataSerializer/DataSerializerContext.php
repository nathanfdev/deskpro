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
 * The DataSerializer package is mostly stateless services, however there is a lot of state to
 * keep track of during the serialization process. An instance of this class tracks all of that
 * state, and it is passed around to the various DataSerializer services during the serialization process.
 */
class DataSerializerContext
{
    /**
     * @var mixed the main data we are serializing
     */
    protected $main_data;

    /**
     * @var string the object type of the main data
     */
    protected $main_type;

    /**
     * @var string|null the view that we should transform the $main_data on (this is simply passed to the transformer)
     */
    protected $main_view;

    /**
     * @var array the transformed data (this is not always ready to use, because the context can be in a "processing" state)
     */
    protected $main_transformed;

    /**
     * @var array an array of "types" that we want to include (side-load) if we ever encounter them during transformation
     */
    protected $requested_includes;

    /**
     * @var array a multi-dimensional array, the root keys are an object "type" ("person") and the value contains the
     *            ids (or SerializeDeferredPropertyInterface's that represent ids) of the entities of this type that
     *            we need to include (side-load) that have not been side-loaded yet
     */
    protected $includes_to_process;

    /**
     * @var array similar to $includes_to_process, a multi-dimensional array with the root key being the object "type" and
     *            the value being an array of actual ids (no objects) that have already been side-loaded.
     */
    protected $includes_processed;

    public function __construct($main_data, array $requested_includes = array(), $main_view = null, $main_type = null)
    {
        $this->main_data = $main_data;
        $this->requested_includes = $requested_includes;
        $this->main_type = $main_type;
        $this->main_view = $main_view;
    }

    /**
     * This is an alternate way to create the context where you can use a string instead of an array for the includes,
     * and it will be parsed into the array for you.
     *
     * @param $main_data
     * @param null $requested_includes_string
     * @param null $main_view
     * @param null $main_type
     * @return DataSerializerContext
     */
    public static function create($main_data, $requested_includes_string = null, $main_view = null, $main_type = null)
    {
        return new self($main_data, self::parseIncludes($requested_includes_string), $main_view, $main_type);
    }

    public static function parseIncludes($requested_includes_string)
    {
        if (null === $requested_includes_string) {
            return [];
        }

        $exploded = explode(',', $requested_includes_string);

        $cleaned = [];

        foreach ($exploded as $type) {
            $type = trim($type);

            if (!empty($type)) {
                $cleaned[] = $type;
            }
        }

        return $cleaned;
    }

    /**
     * @return array
     */
    public function getMainTransformed()
    {
        return $this->main_transformed;
    }

    /**
     * @param array $main_transformed
     */
    public function setMainTransformed($main_transformed)
    {
        $this->main_transformed = $main_transformed;
    }

    /**
     * @return mixed
     */
    public function getMainData()
    {
        return $this->main_data;
    }

    /**
     * @return string
     */
    public function getMainType()
    {
        return $this->main_type;
    }

    /**
     * @return null|string
     */
    public function getMainView()
    {
        return $this->main_view;
    }

    /**
     * @return array
     */
    public function getRequestedIncludes()
    {
        return $this->requested_includes;
    }
}
