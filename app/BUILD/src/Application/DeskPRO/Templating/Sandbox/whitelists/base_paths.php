<?php

// A list of allowed URL base paths. Note that all templates under this base URL
// path will have sandboxed twig disabled

if (isset($GLOBALS['DP_ENV']) && $GLOBALS['DP_ENV']->isDebug()) {
    return [
        '/_wdt',
        '/_profiler',
    ];
}

return [];
