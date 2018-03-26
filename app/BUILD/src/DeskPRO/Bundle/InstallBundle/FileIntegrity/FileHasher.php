<?php

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
        $ext  = $this->getExt($path) ?: 'bin';
        $file = null;

        // ext-less files might be executable files files
        if ($ext === 'bin' && filesize($path) < 20000) {
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
        $name = @basename($path) ?: '';

        if (($pos = strrpos($name, '.')) !== false) {
            return substr($name, $pos + 1) ?: null;
        }

        return;
    }
}
