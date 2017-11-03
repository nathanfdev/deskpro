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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\UpdateBundle\Command\Dev;

use DeskPRO\Component\Doctrine\ORM\Tools\SchemaTool;
use DeskPRO\Component\Util\EnvUtils;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenBuildScriptCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    private $buffer = [];

    /**
     * @var bool
     */
    private $toStdout = false;

    /**
     * @var int
     */
    private $buildTime;

    protected function configure()
    {
        $this
            ->setName('dp:update:dev:gen-build-script')->setAliases(['dpdev:gen-build-class'])
            ->addOption('output', null, InputOption::VALUE_NONE, 'Output to stdout instead of writing it')
            ->addOption('blocking', null, InputOption::VALUE_NONE, 'Force use of BlockingBuildInterface')
            ->addOption('skip-manifest', null, InputOption::VALUE_NONE, 'Do not update manifest (always skipped if --out is being used)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('output')) {
            echo "Generating new migration script\n";
        }

        $this->buildTime = time();
        $this->toStdout  = $input->getOption('output');

        $this->appendBuffer($this->getFileHeader());
        $skipPostBuild = false;
        $isOnline      = true;

        if ($input->getOption('blocking')) {
            $isOnline = false;
        }

        // Use list-changed-files to determine if we've modified any PostBuild type files
        if (!EnvUtils::isWindows()) {
            global $DP_ENV;
            $script = $DP_ENV->getDpRoot().'/dev/bin/list-changed-files';
            if (is_file($script) && is_executable($script)) {
                $output = $return = null;
                @exec($script.' origin', $output, $return);

                if (!$return && !empty($output)) {
                    $files = ListUtils::filter($output, function ($path) {
                        $path = trim($path);
                        if (!$path) {
                            return false;
                        }
                        if (strpos($path, 'languages/manifest.json') !== false) {
                            return true;
                        }
                        if (strpos($path, 'InstallBundle/Data/DefaultData') !== false) {
                            return true;
                        }
                        if (strpos($path, 'apps/deskpro_') !== false) {
                            return true;
                        }
                        if (strpos($path, 'DeskPRO/Bundle/PortalBundle/Themes') !== false) {
                            return true;
                        }
                        if (strpos($path, 'pub/src/DeskPRO/Bundle/PortalBundle/Resources') !== false) {
                            return true;
                        }

                        return false;
                    });

                    if (empty($files)) {
                        $skipPostBuild = true;
                    }
                }
            }
        }

        $this->appendBuffer($this->getClassStart($skipPostBuild));

        $newTableQueries = [];
        $bcAlterQueries  = [];
        $alterQueries    = [];

        foreach ([
            'default' => $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            'sys' => $this->getContainer()->get('doctrine.orm.system_entity_manager'),
            'audit' => $this->getContainer()->get('doctrine.orm.audit_entity_manager'),
        ] as $dbId => $em) {
            $metadata = $em->getMetadataFactory()->getAllMetadata();
            $tool     = new SchemaTool($em);
            $platform = $tool->getPlatform();

            $diff = $tool->getSchemaDiff($metadata);

            if (!empty($diff->newTables)) {
                $newFksLines = [];
                foreach ($diff->newTables as $t) {
                    $sqls = $platform->getCreateTableSQL($t, AbstractPlatform::CREATE_INDEXES);
                    foreach ($sqls as $sql) {
                        $newTableQueries[] = [$dbId, $sql];
                    }

                    foreach ($t->getForeignKeys() as $fk) {
                        $sql           = $platform->getCreateForeignKeySQL($fk, $t);
                        $newFksLines[] = [$dbId, $sql];
                    }
                }

                if ($newFksLines) {
                    $newTableQueries = array_merge($newTableQueries, $newFksLines);
                }
            }

            if (!empty($diff->changedTables)) {
                foreach ($diff->changedTables as $tableDiff) {
                    $parts = $platform->getAlterTableSQL($tableDiff);
                    $parts = array_map(function ($p) use ($dbId) {
                        return [$dbId, $p];
                    }, $parts);
                    if ($tool->isTableDiffBackwardsCompatible($tableDiff)) {
                        $bcAlterQueries = array_merge($bcAlterQueries, $parts);
                    } else {
                        $alterQueries = array_merge($alterQueries, $parts);
                    }
                }
            }
        }

        if ($newTableQueries) {
            $this->appendBuffer("\n");
            $this->appendBuffer($this->getMethodLines('addNewTables', ListUtils::map($newTableQueries, function ($sql) {
                return '$this->execDbQuery(\''.$sql[0].'\', \''.addslashes($sql[1]).'\');';
            })));
        } else {
            $this->appendBuffer($this->getMethodLines('addNewTables', []));
        }

        if ($alterQueries) {
            // for some reason this always comes up as wrong
            $alterQueries = array_filter($alterQueries, function ($q) {
                if ($q[1] === 'ALTER TABLE email_uids CHANGE id id VARCHAR(100) NOT NULL') {
                    return false;
                }

                return true;
            });
        }

        if ($bcAlterQueries || $alterQueries) {
            $this->appendBuffer("\n");
            $this->appendBuffer($this->getMethodLines('runAlters', ListUtils::map(array_merge($bcAlterQueries, $alterQueries), function ($sql) {
                return '$this->execDbQuery(\''.$sql[0].'\', \''.addslashes($sql[1]).'\');';
            })));

            if ($alterQueries) {
                $isOnline = false;
            }
        } else {
            $this->appendBuffer($this->getMethodLines('runAlters', []));
        }

        $this->appendBuffer($this->getMethodLines('run', []));

        $this->appendBuffer($this->getClassEnd());

        $buffer     = implode('', $this->buffer);
        $interfaces = [];
        $prelines   = [''];
        if ($isOnline) {
            $interfaces[] = 'OnlineBuildInterface';
            $prelines[]   = '// NOTE: I used the OnlineBuildInterface interface because';
            $prelines[]   = '//       it looks like your schema changes ARE backwards compatible with the previous version.';
            $prelines[]   = '//       You should double-check this yourself though. If there are breaking changes, use BlockingBuildInterface instead.';
            $prelines[]   = '';
        } else {
            $interfaces[] = 'BlockingBuildInterface';
            $prelines[]   = '// NOTE: I used the BlockingBuildInterface interface because';
            $prelines[]   = '//       it looks like your schema changes are NOT backwards compatible with the previous version.';
            $prelines[]   = '//       You should double-check this yourself though. If they are backwards compatible, use OnlineBuildInterface instead.';
            $prelines[]   = '';
        }
        if ($skipPostBuild) {
            $interfaces[] = 'SkipPostBuildInterface';
            $prelines[]   = '// NOTE: I have added the SkipPostBuildInterface interface because';
            $prelines[]   = '//       it looks like you do not have any changes that require PostBuild to run.';
            $prelines[]   = '//       You should double-check this yourself though. Remove the SkipPostBuildInterface interface if necessary.';
            $prelines[]   = '';
        }

        $prelines[] = '// Please remove these NOTE comments after you have checked the code.';
        $prelines[] = '';

        $buffer = str_replace('__INTERFACE_TYPE__', implode(', ', $interfaces), $buffer);
        $buffer = str_replace('__PRE_CLASS_LINES__', implode("\n", $prelines), $buffer);

        if (!$this->toStdout) {
            echo "  .. done\n";

            $className    = 'Build'.$this->buildTime;
            $baseBuildDir = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';
            $buildDir     = $baseBuildDir.'/'.date('Y/m', $this->buildTime);
            $filePath     = $buildDir.'/'.$className.'.php';
            $manifestPath = $baseBuildDir.'/build-manifest.php';
            $timePath     = $DP_ENV->getAppDir().'/sys/config/build-time.txt';

            if (!is_dir($buildDir)) {
                mkdir($buildDir, 0744, true);
            }

            echo "Generating buildscript:    $filePath\n";
            file_put_contents($filePath, $buffer);
            echo " .. done\n";

            echo "Setting buildtime:         $timePath\n";
            file_put_contents($timePath, $this->buildTime);
            echo " .. done\n";

            echo "Generating manifest:       $manifestPath\n";

            $reader       = $this->getContainer()->get('dp.build_tasks.manifest_reader');
            $gen          = $this->getContainer()->get('dp.build_tasks.manifest_gen');
            $manifestPath = $reader->getManifestPath();

            $file = $gen->getContents();
            file_put_contents($manifestPath, $file);

            echo " .. done\n";
        } else {
            echo $buffer;
            echo "\n";
        }

        return 0;
    }

    /**
     * @param string $content
     */
    private function appendBuffer($content)
    {
        $this->buffer[] = $content;
    }

    /**
     * @param string $methodName
     * @param array  $lines
     *
     * @return string
     */
    private function getMethodLines($methodName, array $lines)
    {
        $indent = '    ';
        $result = "\n"
            ."{$indent}public function {$methodName}()\n"
            ."{$indent}{\n";

        foreach ($lines as $l) {
            $result .= "{$indent}{$indent}{$l}\n";
        }

        $result .= "{$indent}}\n";

        return $result;
    }

    /**
     * @return string
     */
    private function getClassStart()
    {
        $time = $this->buildTime;

        $content = <<<CONTENT
namespace Application\InstallBundle\Upgrade\Build;
__PRE_CLASS_LINES__
class Build$time extends AbstractBuild implements __INTERFACE_TYPE__
{
CONTENT;

        return $content;
    }

    private function getClassEnd()
    {
        return "}\n";
    }

    /**
     * @return string
     */
    private function getFileHeader()
    {
        $year   = date('Y');
        $header = <<<HEADER
<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) $year, DeskPRO Ltd.
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

HEADER;

        return $header;
    }
}
