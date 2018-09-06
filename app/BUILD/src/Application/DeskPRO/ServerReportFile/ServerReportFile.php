<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerReportFile;

use Application\DeskPRO\App;
use Application\DeskPRO\ORM\Util\Util;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Instructions\InstructionsGenerator;
use Doctrine\ORM\EntityManager;
use DpSys\License;
use Orb\Util\Files;
use Orb\Util\Strings;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class ServerReportFile
{
    /**
     * @var int
     */
    private $maxFileSize = 250000;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $tmpdir = '';

    /**
     * @var string
     */
    protected $fileName = 'deskpro-report.zip';

    /**
     * @var string
     */
    public $archiveFile = '';

    /**
     * @var array - this is mapping array between file name and method of this class that creates file content
     */
    public $filesAddedToArchive = [
        'phpinfo-web.html'      => '_createPhpInfoFile',
        'phpinfo-cli.txt'       => '_createCliInfoFile',
        'errorlog-deskpro.txt'  => '_createDeskPROErrorLog',
        'errorlog-web.txt'      => '_createWebErrorLog',
        'errorlog-cli.txt'      => '_createCliErrorLog',
        'upgrade-log.txt'       => '_createUpgradeLog',
        'fix-schema.log'        => '_createFixSchemaLog',
        'mysql-schema.sql'      => '_createMysqlSchema',
        'mysql-status.txt'      => '_createMysqlStatus',
        'mysql-vars.txt'        => '_createMysqlVariables',
        'misc.txt'              => '_createMisc',
        'mysql-schema-diff.sql' => '_createMysqlSchemaDiff',
        'cron-status.txt'       => '_createCronStatus',
        'license.txt'           => '_createLicense',
        'file-integrity.txt'    => '_createFileIntegrity',
        'templates.txt'         => '_createTemplates',
        'incidents'             => '_createIncidents',
        'jobs-statuses.txt'     => '_createJobsStatuses',
    ];

    /**
     * @var OutputInterface
     */
    protected $oi;

    /**
     * system entity manager.
     *
     * @var EntityManager|null
     */
    protected $sem;

    /**
     * @var InstructionsGenerator|null
     */
    protected $ig;

    /**
     * @var AppEnv
     */
    protected $appEnv;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, OutputInterface $output = null, AppEnv $appEnv)
    {
        $this->em = $em;
        $this->oi = $output;

        $this->tmpdir = dp_get_tmp_dir().DIRECTORY_SEPARATOR.uniqid('dpd', true);

        if (!mkdir($this->tmpdir, 0777, true)) {
            die('Could not create temp dir: '.$this->tmpdir);
        }

        $this->archiveFile = $this->tmpdir.'/deskpro-report.zip';
        $this->appEnv      = $appEnv;
    }

    /**
     * Saves results of integrity file checks in some temporary space
     * Later it will be used when generating resulting archive including report information.
     *
     * @param string $file_check_results - string with results of integrity file checks
     */
    public function saveFileCheckResults($file_check_results)
    {
        try {
            $this->_createFile(dp_get_tmp_dir().DIRECTORY_SEPARATOR.'file_check_results.txt', $file_check_results);
        } catch (IOException $e) {
        }
    }

    /**
     * Actually outputs the archive as downloadable attachment.
     */
    public function outputArchive()
    {
        header('Content-Type: application/zip');
        header('Content-Length: '.filesize($this->archiveFile));
        header('Content-Disposition: attachment; filename='.$this->fileName);

        ob_clean();
        flush();

        $fp = fopen($this->archiveFile, 'r');

        while (!feof($fp)) {
            echo fread($fp, 1024);
        }

        fclose($fp);

        unlink($this->archiveFile);

        $fs = new Filesystem();
        $fs->remove($this->tmpdir);

        exit;
    }

    /**
     * Creates archive with all needed files inside it.
     */
    public function createArchive()
    {
        $this->_addFilesToArchive();

        require_once DP_ROOT.'/vendor-src/pclzip/pclzip.lib.php';

        $archive = new \PclZip($this->archiveFile);

        $list = $archive->add(
            $this->tmpdir,
            \PCLZIP_OPT_REMOVE_ALL_PATH
        );

        if ($list == 0) {
            die('Error : '.$archive->errorInfo(true));
        }

        return $this->archiveFile;
    }

    /**
     * This methods iterates over all of the $this->files_added_to_archive and creates all the needed files.
     */
    protected function _addFilesToArchive()
    {
        foreach ($this->filesAddedToArchive as $fileName => $func) {
            $this->oi && $this->oi->writeln(sprintf('Generating "%s"', $fileName));
            if (false === $this->$func($fileName)) {
                $this->oi && $this->oi->writeln('');
            } else {
                $this->oi && $this->oi->writeln('Success');
            }
        }
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createPhpInfoFile($fileName)
    {
        /*
         * @var \Application\DeskPRO\ServerPhpInfo\ServerPhpInfo
         */
        $service = App::getSystemService('server_php_info');
        $info    = $service->getPhpInfo(true);

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $info['web_php']['phpinfo']);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createCliInfoFile($fileName)
    {
        /*
         * @var \Application\DeskPRO\ServerPhpInfo\ServerPhpInfo
         */
        $service = App::getSystemService('server_php_info');
        $info    = $service->getPhpInfo(true);

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $info['cli_php']['phpinfo']);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createDeskPROErrorLog($fileName)
    {
        $file = str_repeat('#', 72)."\n# error.log\n".str_repeat('#', 72)."\n\n";

        try {
            $file .= $this->_readFile(dp_get_log_dir().'/error.log');
        } catch (IOException $e) {
            $file = '';
        }

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $file);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createWebErrorLog($fileName)
    {
        $file = str_repeat('#', 72)."#\n server-phperr-web.log\n".str_repeat('#', 72)."\n\n";

        $logFilePath = @ini_get('error_log');

        if (!$logFilePath) {
            $logFilePath = dp_get_log_dir().'/server-phperr-web.log';
        }

        try {
            $file .= $this->_readFile($logFilePath);
        } catch (IOException $e) {
            $file = '';
        }

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $file);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createCliErrorLog($fileName)
    {
        $file = str_repeat('#', 72)."#\n cli-phperr.log\n".str_repeat('#', 72)."\n\n";

        try {
            $file .= $this->_readFile(dp_get_log_dir().'/cli-phperr.log');
        } catch (IOException $e) {
            $file = '';
        }

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $file);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param $fileName
     *
     * @return bool
     */
    protected function _createUpgradeLog($fileName)
    {
        $file = str_repeat('#', 72)."#\n upgrade.log\n".str_repeat('#', 72)."\n\n";

        try {
            $file .= $this->_readFile(dp_get_log_dir().'/upgrade.log');
        } catch (IOException $e) {
            $file = '';
        }
        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $file);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param $fileName
     *
     * @return bool
     */
    protected function _createFixSchemaLog($fileName)
    {
        $file = str_repeat('#', 72)."#\n fix-schema.log\n".str_repeat('#', 72)."\n\n";

        try {
            $file .= $this->_readFile(dp_get_log_dir().'/fix-schema.log');
        } catch (IOException $e) {
            $file = '';
        }
        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $file);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createMysqlSchema($fileName)
    {
        $sql = [];

        $sql[] = '### '.App::getContainer()->getBrandSetting('core.deskpro_url')."\n";
        $sql[] = '### DeskPRO Build: '.DP_BUILD_TIME."\n";
        $sql[] = '### Generated: '.date('Y-m-d H:i:s')."\n\n";

        $sql[] = 'SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;'."\n";
        $sql[] = 'SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;'."\n";
        $sql[] = 'SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'TRADITIONAL,ALLOW_INVALID_DATES\';'."\n\n";

        $tables = App::getDb()->fetchAllCol('SHOW TABLES');

        foreach ($tables as $table) {
            $sql[] = "### TABLE: $table\n";
            $sql[] = App::getDb()->fetchColumn("SHOW CREATE TABLE `$table`", [], 1);
            $sql[] = ";\n\n";
        }

        $sql[] = 'SET SQL_MODE=@OLD_SQL_MODE;'."\n";
        $sql[] = 'SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;'."\n";
        $sql[] = 'SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;'."\n\n";

        $sql = implode('', $sql);

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $sql);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createMysqlStatus($fileName)
    {
        $sections = [];

        try {
            $mysqlstatus              = App::getDb()->fetchAllKeyValue('SHOW STATUS', [], [], 0, 1);
            $sections['MySQL Status'] = Strings::keyValueAsciiTable($mysqlstatus);
        } catch (\Exception $e) {
        }

        $out = '';

        foreach ($sections as $title => $content) {
            $out .= "\n\n\n\n\n";
            $out .= str_repeat('#', 80)."\n";
            $out .= '# '.str_pad($title, 76).' #'."\n";
            $out .= str_repeat('#', 80)."\n";
            $out .= $content;
        }

        $out = trim($out);

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $out);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createMysqlVariables($fileName)
    {
        $sections = [];

        try {
            $mysqlinfo                   = App::getDb()->fetchAllKeyValue('SHOW VARIABLES', [], [], 0, 1);
            $sections['MySQL Variables'] = Strings::keyValueAsciiTable($mysqlinfo);
        } catch (\Exception $e) {
        }

        $out = '';

        foreach ($sections as $title => $content) {
            $out .= "\n\n\n\n\n";
            $out .= str_repeat('#', 80)."\n";
            $out .= '# '.str_pad($title, 76).' #'."\n";
            $out .= str_repeat('#', 80)."\n";
            $out .= $content;
        }

        $out = trim($out);

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $out);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createMisc($fileName)
    {
        /*
         * @var \Application\DeskPRO\ServerPhpInfo\ServerPhpInfo
         */
        $service = App::getSystemService('server_php_info');
        $vars    = $service->getPhpInfo(true);

        $items                         = array_merge($vars['binary_paths'], $vars['web_php']['php_config']);
        $items['ini_path']             = $vars['web_php']['ini_path'];
        $items['effective_max_upload'] = $vars['web_php']['effective_max_upload'];
        $items['cli_ini_path']         = isset($vars['cli_php']['ini_path']) ? $vars['cli_php']['ini_path'] : '';

        if (isset($vars['cli_php']['php_config'])) {
            foreach ($vars['cli_php']['php_config'] as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $subk => $subv) {
                        $items['cli_'.$k.'.'.$subk] = $subv;
                    }
                } else {
                    $items['cli_'.$k] = $v;
                }
            }
        }
        $items['has_apc']      = $vars['has_apc'] ? 'Yes' : 'No';
        $items['has_wincache'] = $vars['has_wincache'] ? 'Yes' : 'No';
        $items                 = array_merge($items, $vars['debug_settings']);

        $sections = [];

        $sections['Info'] = Strings::keyValueAsciiTable($items);
        $out              = '';

        foreach ($sections as $title => $content) {
            $out .= "\n\n\n\n\n";
            $out .= str_repeat('#', 80)."\n";
            $out .= '# '.str_pad($title, 76).' #'."\n";
            $out .= str_repeat('#', 80)."\n";
            $out .= "\n\n";
            $out .= $content;
        }

        $out = trim($out);

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $out);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    protected function _createTemplates()
    {
        $templates = App::getDb()->fetchAll('SELECT name, template_code, date_created, date_updated FROM templates');
        $out       = [];

        foreach ($templates as $t) {
            $out[] = ">>>>>>>>>>>>>>>>>>>> Template: {$t['name']} -- Created: {$t['date_created']} -- Updated: {$t['date_updated']} <<<<<<<<<<<<<<<<<<<<\n\n";
            $out[] = $t['template_code'];
            $out[] = "\n\n\n\n\n";
        }

        $out = trim(implode('', $out));

        try {
            $this->_createFile($this->tmpdir.'/'.'templates.txt', $out);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createMysqlSchemaDiff($fileName)
    {
        try {
            $schemaDiff = Util::getUpdateSchemaSql();

            if ($schemaDiff) {
                $schemaDiff = implode(";\n", $schemaDiff).';';
            }
        } catch (\Exception $e) {
            $schemaDiff = null;
        }

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $schemaDiff);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createCronStatus($fileName)
    {
        /*
         * @var \Application\DeskPRO\ServerCron\ServerCron $service
         */

        $content = '';

        $content .= '### '.App::getContainer()->getBrandSetting('core.deskpro_url')."\n";
        $content .= '### DeskPRO Build: '.DP_BUILD_TIME."\n";
        $content .= '### Generated: '.date('Y-m-d H:i:s')."\n\n";

        $content .= 'Task    Interval   Last Run   Last Complete   Next Run'."\n\n";

        $service = App::getSystemService('server_cron');
        $vars    = $service->getAllForApi();

        foreach ($vars as $job) {
            $content .= $job['title'].'       '.$job['interval_readable'].'       ';

            $lastStartDate = date('D, jS M Y g:ia', $job['last_start_date_ts']) ?
                date('D, jS M Y g:ia', $job['last_start_date_ts']) : 'N/A';
            $content .= $lastStartDate.'       ';

            $lastRunDate = date('D, jS M Y g:ia', $job['last_run_date_ts']) ?
                            date('D, jS M Y g:ia', $job['last_run_date_ts']) : 'N/A';
            $content .= $lastRunDate.'       ';

            $content .= $job['next_run_time'];
            $content .= "\n";
        }

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $content);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    protected function _createLicense($fileName)
    {
        $license = License::getLicense();

        $licenseExpiresIn = function () use ($license) {
            if ($license->getExpireDays() == 0 && $license->getExpireTime('hours') == 0) {
                return 'In '.$license->getExpireTime('mins').' minutes';
            } elseif ($license->getExpireDays() < 3) {
                return 'In '.$license->getExpireTime('hours').' hours';
            } else {
                return 'In '.$license->getExpireDays().' days';
            }
        };

        $content = '';

        $content .= '### '.App::getContainer()->getBrandSetting('core.deskpro_url')."\n";
        $content .= '### DeskPRO Build: '.$this->appEnv->getVersionName()." -- {$this->appEnv->getBuildId()}\n";
        $content .= '### Generated: '.date('Y-m-d H:i:s')."\n\n";

        $content .= 'License ID: '.$license->getLicenseId()."\n";
        $content .= 'Agents: '.$license->getMaxAgents()."\n";
        $content .= 'Expires: '.$licenseExpiresIn()."\n\n\n";

        $content .= 'Core.license: '.App::getSetting('core.license')."\n\n\n";
        $content .= 'Core.install_key: '.App::getSetting('core.install_key')."\n\n";
        $content .= 'Core.licenseopt: '.App::getSetting('core.licenseopt')."\n\n";

        try {
            $this->_createFile($this->tmpdir.'/'.$fileName, $content);
        } catch (IOException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $fileName
     */
    protected function _createFileIntegrity($fileName)
    {
        $fs = new Filesystem();

        if (file_exists(dp_get_tmp_dir().DIRECTORY_SEPARATOR.'file_check_results.txt')) {
            try {
                $fs->copy(
                    dp_get_tmp_dir().DIRECTORY_SEPARATOR.'file_check_results.txt',
                    $this->tmpdir.'/'.$fileName
                );
            } catch (IOException $e) {
                die(
                    'Could not create File Integrity file under this location - '.$this->tmpdir.'/'.$fileName.
                        '. More info:'.$e->getMessage()
                );
            }
        }
    }

    /**
     * Attempts to create file in specified location with specified content
     * Also acts as wrapper for throwing an exception in case of fail.
     *
     * @param string $fileName
     * @param string $content
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    protected function _createFile($fileName, $content)
    {
        if (@file_put_contents($fileName, $content) === false) {
            throw new IOException('Could not create file under location - '.$fileName);
        }
    }

    /**
     * Attempts to read a file from specified location
     * Also acts as wrapper for throwing an exception in case of fail.
     *
     * @param string $fileName
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return string
     */
    protected function _readFile($fileName)
    {
        if (!file_exists($fileName)) {
            return '';
        }

        try {
            $content = Files::readFromEnd($fileName, $this->maxFileSize);
        } catch (\Exception $e) {
            $content = false;
        }

        if ($content === false) {
            $content = '(failed to read file)';
        }

        return $content;
    }

    /**
     * @param EntityManager $em
     */
    public function setSystemEntityManager(EntityManager $em)
    {
        $this->sem = $em;
    }

    /**
     * @param InstructionsGenerator $ig
     */
    public function setInstructionGenerator(InstructionsGenerator $ig)
    {
        $this->ig = $ig;
    }

    /**
     * @return bool
     */
    protected function _createIncidents()
    {
        if (!$this->sem || !$this->ig) {
            return false;
        }

        try {
            $ig  = $this->ig;
            $dir = $this->tmpdir.'/incidents';
            if (!@mkdir($dir)) {
                throw new IOException('Could not create incidents directory');
            }

            $this->sem->transactional(function (EntityManager $em) use ($ig, $dir) {
                $incidents = [];
                $qb = $em->createQueryBuilder()
                    ->select('i, e')
                    ->from(AbstractIncident::class, 'i')
                    ->leftJoin('i.events', 'e');
                $entities = $qb->getQuery()->getResult();

                foreach ($entities as $entity) {
                    /* @var $entity AbstractIncident */
                    $incidents[$entity->getId()] = $ig->generate($entity);
                    $em->remove($entity);
                }

                foreach ($incidents as $id => $incident) {
                    $fileName = $dir.'/incident_'.$id.'.html';
                    if (@file_put_contents($fileName, $incident) === false) {
                        throw new IOException('Could not create file under location - '.$fileName);
                    }
                }
            });
        } catch (\Exception $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    protected function _createJobsStatuses($fileName)
    {
        $content = '';

        $sql = <<<'SQL'
SELECT `type`, `date_created`, `date_last_try`, `status`, `status_code`, `num_tries`, `data`, `log_summary`, `log`
FROM `jobs`
SQL;
        $rows = $this->em->getConnection()->query($sql)->fetchAll();
        foreach ($rows as $row) {
            $content .= "---------------------------------------------------------------------------------------------\r\n";
            foreach ($row as $key => $value) {
                if ($key === 'data') {
                    $value = var_export(json_decode($value, true), true);
                }
                $content .= "\r\n$key: ".$value."\r\n";
            }
            $content .= "\r\n";
        }
        $this->_createFile($this->tmpdir.'/'.$fileName, $content);
    }
}
