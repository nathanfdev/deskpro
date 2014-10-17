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

class ZipArchiveAdapter implements ZipAdapterInterface
{
	/**
	 * Compress a file or directory of files
	 *
	 * @param string $path The file or directory to ZIP
	 * @param string $to   Where to write the zip file to
	 * @return void
	 */
	public function compressPath($path, $to)
	{
		$pathInfo = pathInfo($path);
		$parentPath = $pathInfo['dirname'];
		$dirName = $pathInfo['basename'];

		$z = new \ZipArchive();
		$z->open($to, \ZipArchive::CREATE);
		$z->addEmptyDir($dirName);
		self::folderToZip($path, $z, strlen("$parentPath/"));
		$z->close();
	}


	/**
	 * Decompress a ZIP.
	 *
	 * @param string $path The ZIP file to unzip
	 * @param string $to   The path to unzip to
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

		$zip = new \ZipArchive();

		if (($code = $zip->open($path)) !== true) {
			throw new ZipException(sprintf("[%s/%s] %s %s", $zip->status, $zip->statusSys, $code, $zip->getStatusString()), ZipException::ZIP_ERROR);
		}

		if ($zip->extractTo($to) !== true) {
			throw new ZipException(sprintf("[%s/%s] %s %s", $zip->status, $zip->statusSys, $code, $zip->getStatusString()), ZipException::ZIP_ERROR);
		}

		$zip->close();
	}

	private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
		$handle = opendir($folder);
		while (false !== $f = readdir($handle)) {
			if ($f != '.' && $f != '..') {
				$filePath = "$folder/$f";
				// Remove prefix from file path before add to zip.
				$localPath = substr($filePath, $exclusiveLength);
				if (is_file($filePath)) {
					$zipFile->addFile($filePath, $localPath);
				} elseif (is_dir($filePath)) {
					// Add sub-directory.
					$zipFile->addEmptyDir($localPath);
					self::folderToZip($filePath, $zipFile, $exclusiveLength);
				}
			}
		}
		closedir($handle);
	}
}