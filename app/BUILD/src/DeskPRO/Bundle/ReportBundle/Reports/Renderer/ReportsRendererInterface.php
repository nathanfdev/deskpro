<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

use DeskPRO\Bundle\ReportBundle\Reports\Results;

/**
 * Interface ReportsRendererInterface.
 */
interface ReportsRendererInterface
{
    const TYPE_TABLE  = 'table';
    const TYPE_LINE   = 'line';
    const TYPE_PIE    = 'pie';
    const TYPE_BAR    = 'bar';
    const TYPE_AREA   = 'area';
    const TYPE_GAUGE  = 'gauge';
    const TYPE_BUBBLE = 'bubble';
    const TYPE_STAT   = 'stat';

    /**
     * @return string
     */
    public static function getOutputFormat();

    /**
     * Gets the MIME content type for this type of output.
     *
     * @return string
     */
    public static function getContentType();

    /**
     * Gets the file extension for this type of output.
     *
     * @return string
     */
    public static function getExtension();

    /**
     * Render to the specified format and type.
     *
     * @param Results $results
     * @param array   $options
     *
     * @return string|array
     */
    public function render(Results $results, array $options = []);

    /**
     * Merge layered results.
     *
     * @param array $results
     * @param array $options
     *
     * @return mixed
     */
    public function mergeResults(array $results, array $options);
}
