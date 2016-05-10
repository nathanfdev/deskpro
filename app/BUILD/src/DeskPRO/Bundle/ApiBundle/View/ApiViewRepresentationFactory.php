<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\View;

use DeskPRO\Bundle\ApiBundle\View\Representation\StandardRepresentation;
use Symfony\Component\Form\FormInterface;

class ApiViewRepresentationFactory
{
    /** @const DATATYPE_STANDARD Standard and sort of unknown datatype. */
    const DATATYPE_STANDARD = 1;
    /** @const DATATYPE_GROUPED_COUNT Provided data is an array resulting from a grouped count query. */
    const DATATYPE_GROUPED_COUNT = 2;
    /** @const DATATYPE_COUNT_ONLY Standard datatype, but we only want the total results */
    const DATATYPE_COUNT_ONLY = 3;

    /**
     * Format a set of responses for batch response.
     *
     * @param array $responses
     *
     * @return array
     */
    public function createBatchRepresentation(array $responses = array())
    {
        return [
            'responses' => $responses,
        ];
    }

    /**
     * @param $status
     * @param $code
     * @param $message
     * @param array|FormInterface $errors_data
     *
     * @return array
     */
    public function createErrorRepresentation($status, $code, $message, $errors_data = array())
    {
        return [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'errors'  => count($errors_data) ? $errors_data : null,
        ];
    }

    /**
     * Serializes a grouped count array.
     *
     * @param mixed $data is the grouped count's data.
     *
     * @return StandardRepresentation an ordered and ready-to-eat representation of the grouped count.
     */
    protected function serializeGroupedCount($data)
    {
        $repr = $this->serializeSingleGroupedCount($data);

        return new StandardRepresentation($repr['data'], array(
            'count'       => $repr['total'],
            'total_count' => $repr['total'],
        ));
    }

    protected function serializeSingleGroupedCount($data, $group = null, array $group_values = null)
    {
        if (!$data) {
            return;
        }

        // Let's inspect the headers.
        $groups = array_keys($data[0]);
        array_shift($groups); // Removing the count column.
        $group_id = array_search($group, $groups);

        if (!$group) {
            $group_id = 0;
            $group    = $groups[$group_id];
        }

        $total         = 0;
        $grouped_count = array();
        foreach ($data as $count_row) {
            if ($group_values) {
                foreach ($group_values as $key => $value) {
                    if (!array_key_exists($key, $count_row) || $count_row[$key] != $value) {
                        continue 2;
                    }
                }
            }

            if (!isset($grouped_count[$count_row[$group]])) {
                $grouped_count[$count_row[$group]] = array(
                    'count' => $count_row['count'],
                    $group  => $count_row[$group],
                );
                if (count($groups) > $group_id + 1) {
                    $my_group_values         = $group_values;
                    $my_group_values[$group] = $count_row[$group];

                    $sub_counts                                  = $this->serializeSingleGroupedCount($data, $groups[$group_id + 1], $my_group_values);
                    $grouped_count[$count_row[$group]]['groups'] = $sub_counts['data'];
                    $grouped_count[$count_row[$group]]['count']  = $sub_counts['total'];
                    $total += $sub_counts['total'];
                } else {
                    $total += $count_row['count'];
                }
            } else {
                continue; // We've already processed this one.
            }
        }

        return array(
            'total' => $total,
            'data'  => array_values($grouped_count),
        );
    }
}
