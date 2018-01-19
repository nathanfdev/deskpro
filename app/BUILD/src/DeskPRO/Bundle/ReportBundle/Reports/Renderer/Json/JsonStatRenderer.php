<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class JsonStatRenderer.
 */
class JsonStatRenderer extends AbstractJsonRenderer
{
    /**
     * Constructor.
     *
     * @param JsonValueRenderer $valueRenderer
     */
    public function __construct(JsonValueRenderer $valueRenderer)
    {
        $this->valueRenderer = $valueRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_STAT;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return;
        }

        return [
            'value'       => $this->renderValue($metadata, $rows),
            'description' => $this->renderDescription($metadata, $rows),
        ];
    }

    /**
     * Renders the header row (for a simple table).
     *
     * @param ResultMetadata $metadata
     * @param array          $rows
     *
     * @return string
     */
    protected function renderValue(ResultMetadata $metadata, array $rows)
    {
        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'stat_value') {
                return $this->valueRenderer->renderValue($rows[0][$column['resultId'] - 1], 'number');
            }
        }

        return 0;
    }

    /**
     * @param ResultMetadata $metadata
     * @param array          $rows
     *
     * @return string
     */
    protected function renderDescription(ResultMetadata $metadata, array $rows)
    {
        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'stat_description') {
                return $this->valueRenderer->renderValue($rows[0][$column['resultId'] - 1], 'string');
            }
        }

        return '';
    }
}
