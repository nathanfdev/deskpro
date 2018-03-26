<?php

namespace DeskPRO\Bundle\AppBundle\Logging\Formatter;

use Monolog\Formatter\LineFormatter;

/**
 * Similar to the default LineFormatter except we remove empty '[]' at the end of log lines.
 */
class CleanLineFormatter extends LineFormatter
{
    const SIMPLE_FORMAT = "[%datetime%] <%channel%.%level_name%> %message% %context% %extra%\n";
    const BASIC_FORMAT  = "[%datetime%] <%channel%.%level_name%> %message%\n";

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $output = $oldOutput = parent::format($record);

        do {
            $oldOutput = $output;
            $output    = preg_replace('/\s*\[\s*\]$/', '', rtrim($output, " \t"));
        } while ($oldOutput !== $output);

        return $output;
    }
}
