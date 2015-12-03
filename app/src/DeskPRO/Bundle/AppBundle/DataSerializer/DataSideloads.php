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

use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;

/**
 * Keeps track of what data we are going to side-load, and helps make sure we only side-load each
 * type/id pair once (per view).
 */
class DataSideloads
{
    /**
     * @var array
     */
    private $ignore_data;

    /**
     * @var array
     */
    private $sideload_data;

    /**
     * @var array
     */
    private $sideload_collections;

    /**
     * @var array
     */
    private $deferred_properties;

    /**
     * @var DataTypeIdFinder
     */
    private $id_finder;

    /**
     * @var bool
     */
    private $needs_processing_since_last_sideload_get;

    public function __construct(DataTypeIdFinder $id_finder)
    {
        $this->deferred_properties                      = [];
        $this->sideload_collections                     = [];
        $this->sideload_data                            = [];
        $this->ignore_data                              = [];
        $this->id_finder                                = $id_finder;
        $this->needs_processing_since_last_sideload_get = false;
    }

    public function hasUnprocessed()
    {
        return $this->needs_processing_since_last_sideload_get;
    }

    public function addIgnoredData($type, $data)
    {
        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $the_data) {
                if (!$id = $this->id_finder->findDataId($the_data)) {
                    throw new \InvalidArgumentException('could not find ID for given data of type: '.$type);
                }

                $this->addIgnoredTypeId($type, $id);
            }
        } else {
            if (!$id = $this->id_finder->findDataId($data)) {
                throw new \InvalidArgumentException('could not find ID for given data of type: '.$type);
            }

            $this->addIgnoredTypeId($type, $id);
        }
    }

    public function addIgnoredTypeId($type, $id)
    {
        if (null === $id) {
            throw new \InvalidArgumentException('you cannot ignore "null" id for type: '.$type);
        }

        if (!array_key_exists($type, $this->ignore_data)) {
            $this->ignore_data[$type] = [];
        }

        $this->ignore_data[$type][] = $id;
    }

    public function addSideloadData($type, $data)
    {
        $this->addSideloadDataId($type, $this->id_finder->findDataId($data), $data);
    }

    public function addSideloadDataId($type, $id, $data)
    {
        if (array_key_exists($type, $this->ignore_data)) {
            if (in_array($id, $this->ignore_data[$type])) {
                return; // ignore this data, we dont want it to be side loaded
            }
        }

        if (null === $id) {
            throw new \InvalidArgumentException('you cannot add "null" id for data type: '.$type);
        }

        if (!array_key_exists($type, $this->sideload_data)) {
            $this->sideload_data[$type] = [];
        }

        $this->needs_processing_since_last_sideload_get = true;

        $this->sideload_data[$type][$id] = $data;
    }

    public function hasSideloadData($type, $id)
    {
        if (null === $id) {
            throw new \InvalidArgumentException('id cannot be "null"');
        }

        if (array_key_exists($type, $this->sideload_data)) {
            return isset($this->sideload_data[$type][$id]);
        }

        return false;
    }

    public function addDeferred($type, DeferredPropertyInterface $deferred)
    {
        if (!array_key_exists($type, $this->deferred_properties)) {
            $this->deferred_properties[$type] = [];
        }

        $this->needs_processing_since_last_sideload_get = true;

        $this->deferred_properties[$type][] = $deferred;
    }

    public function getAndClearDeferred()
    {
        $temp                      = $this->deferred_properties;
        $this->deferred_properties = [];

        return $temp;
    }

    public function addSideloadCollection($type, $array_of_data)
    {
        if (!array_key_exists($type, $this->sideload_collections)) {
            $this->sideload_collections[$type] = [];
        }

        $this->needs_processing_since_last_sideload_get = true;

        $this->sideload_collections[$type][] = $array_of_data;
    }

    public function processCollections()
    {
        foreach ($this->sideload_collections as $type => $collection) {
            foreach ($collection as $data_collection) {
                foreach ($data_collection as $data) {
                    $id = $this->id_finder->findDataId($data);
                    // this is making sure we are still only including one of each type/id pair
                    if (!$this->hasSideloadData($type, $id)) {
                        $this->addSideloadDataId($type, $id, $data);
                    }
                }
            }
        }

        $this->sideload_collections = [];
    }

    public function getSideloadData()
    {
        $this->needs_processing_since_last_sideload_get = false;

        return $this->sideload_data;
    }
}
