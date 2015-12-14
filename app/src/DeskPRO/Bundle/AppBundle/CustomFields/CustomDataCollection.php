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

namespace DeskPRO\Bundle\AppBundle\CustomFields;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\Common\Collections\Collection;

class CustomDataCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var CustomDataAbstract[]
     */
    private $custom_data;

    /**
     * @var CustomDataItem[]
     */
    private $data;

    /**
     * CustomDataCollection constructor.
     *
     * @param CustomDataAbstract[] $custom_data
     */
    public function __construct($custom_data)
    {
        if ($custom_data instanceof Collection) {
            $custom_data = $custom_data->toArray();
        } elseif (!$custom_data) {
            $custom_data = [];
        }

        if (!is_array($custom_data)) {
            throw new \InvalidArgumentException('Expected a Collection or array, got: '.TypeUtils::getVarType($custom_data));
        }

        $this->custom_data = $custom_data;

        $data = [];

        foreach ($custom_data as $d) {
            $field = $d->field->parent ? $d->field->parent : $d->field;
            $fid   = $field->getId();

            if (!isset($data[$fid])) {
                $data[$fid] = ['field' => $field, 'data' => []];
            }

            $data[$fid]['data'][] = $d;
        }

        $this->data = [];
        foreach ($data as $fid => $info) {
            $this->data[$fid] = new CustomDataItem($info['field'], $info['data']);
        }
    }

    /**
     * @param int|CustomDefAbstract $field
     *
     * @return bool
     */
    public function hasDataFor($field)
    {
        return $this->getDataFor($field) === null;
    }

    /**
     * @param int|CustomDefAbstract $field
     *
     * @return CustomDataItem|null
     */
    public function getDataFor($field)
    {
        if ($field instanceof CustomDefAbstract) {
            $fid = $field->getId();
        } else {
            $fid = $field;
        }

        return isset($this->data[$fid]) ? $this->data[$fid] : null;
    }

    /**
     * @return CustomDataItem[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }
}
