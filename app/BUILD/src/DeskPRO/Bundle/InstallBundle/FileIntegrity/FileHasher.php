<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\InstallBundle\FileIntegrity;

use Orb\Util\Strings;

class FileHasher
{
    /**
     * @param string $path
     *
     * @return string
     */
    public function hash($path)
    {
        $ext  = $this->getExt($path) ?: '';
        $file = null;

        // ext-less files might be executable files files
        if ($ext === 'bin') {
            $file = file_get_contents($path);
            if (strpos($file, '<?php') !== false) {
                $ext = 'php';
            } elseif (strpos($file, '#!/') === 0) {
                $ext = 'sh';
            }
        }

        switch ($ext) {
            case 'php':
            case 'sh':
            case 'bat':
            case 'md':
            case 'yml':
            case 'xml':
            case 'html':
            case 'js':
            case 'css':
            case 'scss':
            case 'json':
            case 'txt':
                // normalize txt files
                // to prevent false positives if someone opens
                // a file and their editor chagnes line endings

                if ($file === null) {
                    $file = file_get_contents($path);
                }
                $file = str_replace(["\r", "\r\n"], "\n", $file);
                $file = Strings::trimLines($file);

                return hash('crc32b', $file);
            default:
                return hash_file('crc32b', $path);
        }
    }

    private function getExt($path)
    {
        if (($pos = strrpos($path, '.')) !== false) {
            return substr($path, $pos + 1) ?: null;
        }

        return;
    }
}
