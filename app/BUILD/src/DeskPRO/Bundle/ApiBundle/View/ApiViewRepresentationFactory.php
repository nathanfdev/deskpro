<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\View;

use Symfony\Component\Form\FormInterface;

/**
 * Class ApiViewRepresentationFactory.
 */
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
    public function createBatchRepresentation(array $responses = [])
    {
        return [
            'responses' => $responses,
        ];
    }

    /**
     * @param $status
     * @param $code
     * @param $message
     * @param array|FormInterface $errorsData
     *
     * @return array
     */
    public function createErrorRepresentation($status, $code, $message, $errorsData = [])
    {
        return [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'errors'  => count($errorsData) ? $errorsData : null,
        ];
    }
}
