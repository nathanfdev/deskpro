<?php

namespace DeskPRO\Bundle\UpdateBundle\DbBackup;

class DbBackupException extends \Exception
{
    const NO_MYSQLDUMP             = 100;
    const FILE_EXISTS              = 200;
    const DISK_SPACE_UNKNOWN       = 210;
    const DISK_SPACE_INSUFFICIENT  = 220;
    const DUMP_ERROR               = 300;
    const DUMP_ERROR_NOTFOUND      = 301;
    const DUMP_ERROR_TOOSMALL      = 302;
    const DUMP_ERROR_OPEN_FAILED   = 303;
    const DUMP_ERROR_READ_FAILED   = 304;
    const DUMP_ERROR_MISSING_TABLE = 304;
    const NO_MYSQL                 = 400;
    const BAD_ZIP                  = 500;
    const RESTORE_ERROR            = 600;
    const EXTRACT_ERROR            = 700;
}
