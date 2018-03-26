<?php

namespace Application\InstallBundle\Upgrade\Build;

use DpSys\LowError\SystemErrorHandler;

class Build1493119962 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        if (defined('DPC_IS_CLOUD')) {
            return;
        }

        global $DP_ENV;
        $htaccessFile = $DP_ENV->getWwwRoot().DIRECTORY_SEPARATOR.'.htaccess';

        $this->out('Patching htaccess file: '.$htaccessFile);

        if (!is_file($htaccessFile)) {
            $this->out('No htaccess file found, not patching');

            return;
        }

        if ($this->needsPatch($htaccessFile)) {
            try {
                $this->patchFile($htaccessFile);
            } catch (\Exception $e) {
                $this->out('Failed to patch: '.$e->getMessage());
                SystemErrorHandler::logException($e);
            }
        } else {
            $this->out('Doesnt need patching');
        }
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    private function needsPatch($file)
    {
        $contents = file_get_contents($file);

        return !preg_match('#SetEnvIf\s+Authorization#i', $contents);
    }

    /**
     * @param string $file
     */
    private function patchFile($file)
    {
        if (!is_writable($file)) {
            throw new \RuntimeException('The upgrader does not have permission to write the file');
        }

        $str = "\n\nSetEnvIf Authorization .+ HTTP_AUTHORIZATION=\$0\n";

        if (!file_put_contents($file, $str, \FILE_APPEND)) {
            throw new \RuntimeException('Failed to write line');
        }
    }
}
