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
