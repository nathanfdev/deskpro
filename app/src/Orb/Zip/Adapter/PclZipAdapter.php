<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
 * @subpackage Zip
 */

namespace Orb\Zip\Adapter;

use Orb\Zip\ZipException;

class PclZipAdapter implements ZipAdapterInterface
{
    /**
     * Compress a file or directory of files
     *
     * @param  string $path The file or directory to ZIP
     * @param  string $to   Where to write the zip file to
     * @return void
     */
    public function compressPath($path, $to)
    {
        $z = new \PclZip($to);
        $z->create($path, PCLZIP_OPT_REMOVE_PATH, $path);
    }


    /**
     * Decompress a ZIP.
     *
     * @param  string $path The ZIP file to unzip
     * @param  string $to   The path to unzip to
     * @return void
     */
    public function decompressZip($path, $to)
    {
        if (!is_file($path)) {
            throw new ZipException("Invalid \$path", ZipException::NO_FILE);
        }

        if (!is_writable($to)) {
            throw new ZipException("\$to is not writable", ZipException::WRITE_ERROR);
        }

        $zip = new \PclZip($path);

        if (!is_array($zip->extract(
                \PCLZIP_OPT_PATH, $to,
                \PCLZIP_OPT_ADD_TEMP_FILE_ON,
                \PCLZIP_OPT_STOP_ON_ERROR
            ))) {
            switch ($zip->errorName()) {
                case 'PCLZIP_ERR_BAD_FORMAT':
                case 'PCLZIP_ERR_INVALID_ZIP':
                case 'PCLZIP_ERR_INVALID_ARCHIVE_ZIP':
                case 'PCLZIP_ERR_UNSUPPORTED_COMPRESSION':
                case 'PCLZIP_ERR_UNSUPPORTED_ENCRYPTION':
                    $code = ZipException::BAD_FORMAT;
                    break;
                default:
                    $code = ZipException::ZIP_ERROR;
            }
            throw new ZipException($zip->errorInfo(true), $code);
        }
    }
}
