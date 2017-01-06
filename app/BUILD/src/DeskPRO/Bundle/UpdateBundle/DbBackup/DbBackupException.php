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
