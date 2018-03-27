<?php

/**
 * Orb.
 */

namespace Orb\Zip;

class ZipException extends \Exception
{
    const ZIP_ERROR      = 1;
    const NO_FILE        = 100;
    const TMP_DIR_FAILED = 200;
    const WRITE_ERROR    = 300;
    const BAD_FORMAT     = 400;
}
