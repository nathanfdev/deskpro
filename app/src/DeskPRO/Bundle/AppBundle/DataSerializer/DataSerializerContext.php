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

/**
 * The DataSerializer package is mostly stateless services, however there is a lot of state to
 * keep track of during the serialization process. An instance of this class tracks all of that
 * state, and it is passed around to the various DataSerializer services during the serialization process.
 */
class DataSerializerContext
{
    /**
     * @var mixed the input data that we want to serialize (could be a Pager for example)
     */
    protected $source_data;

    /**
     * @var mixed the main data we are serializing (never a Pager object for example)
     */
    protected $main_data;

    /**
     * @var string the object type of the main data
     */
    protected $main_type;

    /**
     * @var string|null the view that we should transform the on (this is simply passed to the transformer)
     */
    protected $main_view;

    /**
     * @var array the final result of serialization
     */
    protected $serialized_array;

    /**
     * @var array the transformed data (this is not always ready to use, because the context can be in a
     *            "processing" state). this is an intermediary state to get into $serialized_array.
     */
    protected $main_transformed;

    /**
     * @var array an array of "types" that we want to include (side-load) if we ever encounter them during
     *            transformation
     */
    protected $requested_includes;

    /**
     * @var DataSideloads
     */
    protected $sideloads;

    /**
     * @var DataTypeIdFinder
     */
    private $id_finder;

    /**
     * Constructor.
     *
     * @param mixed            $source_data
     * @param array            $requested_includes
     * @param string           $main_view
     * @param string           $main_type
     * @param DataTypeIdFinder $id_finder
     */
    public function __construct($source_data, array $requested_includes = [], $main_view = null, $main_type = null, DataTypeIdFinder $id_finder)
    {
        $this->source_data        = $source_data;
        $this->main_data          = $source_data; // main data starts the same as source data, but event listeners can change this
        $this->requested_includes = $requested_includes;
        $this->main_type          = $main_type;
        $this->includes           = [];
        $this->main_view          = $main_view;
        $this->serialized_array   = [];
        $this->sideloads          = new DataSideloads($id_finder);
        $this->id_finder          = $id_finder;
    }

    /**
     * This is an alternate way to create the context where you can use a string instead of an array for the includes,
     * and it will be parsed into the array for you.
     *
     * @param $source_data
     * @param null             $requested_includes_string
     * @param null             $main_view
     * @param null             $main_type
     * @param DataTypeIdFinder $id_finder
     *
     * @return DataSerializerContext
     */
    public static function create($source_data, $requested_includes_string = null, $main_view = null, $main_type = null, DataTypeIdFinder $id_finder)
    {
        return new self($source_data, self::parseIncludes($requested_includes_string), $main_view, $main_type, $id_finder);
    }

    /**
     * @param string|null $requested_includes_string
     *
     * @return array
     */
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
     * True if we need to include this type.
     *
     * @param $type
     *
     * @return bool
     */
    public function isTypeIncluded($type)
    {
        return in_array($type, $this->requested_includes);
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
     * @var mixed
     */
    public function setMainData($main_data)
    {
        if ($type = $this->getMainType()) {
            $this->sideloads->addIgnoredData($type, $main_data);
        }

        $this->main_data = $main_data;
    }

    /**
     * @return mixed
     */
    public function getMainData()
    {
        return $this->main_data;
    }

    /**
     * @param string $type
     */
    public function setMainType($type)
    {
        $this->main_type = $type;
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

    /**
     * @return mixed
     */
    public function getSourceData()
    {
        return $this->source_data;
    }

    /**
     * @return array
     */
    public function getSerializedArray()
    {
        return $this->serialized_array;
    }

    /**
     * @param array $serialized_array
     */
    public function setSerializedArray(array $serialized_array)
    {
        $this->serialized_array = $serialized_array;
    }

    /**
     * @return DataSideloads
     */
    public function getSideloads()
    {
        return $this->sideloads;
    }
}
