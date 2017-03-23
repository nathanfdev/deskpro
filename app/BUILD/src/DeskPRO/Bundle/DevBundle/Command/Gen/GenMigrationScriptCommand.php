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

namespace DeskPRO\Bundle\DevBundle\Command\Gen;

use Application\InstallBundle\Util\GenBuildManifest;
use DeskPRO\Component\Doctrine\ORM\Tools\SchemaTool;
use DeskPRO\Component\Util\EnvUtils;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenMigrationScriptCommand extends ContainerAwareCommand
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

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:gen:migration-script');
        $this->addOption('out', null, InputOption::VALUE_NONE, 'Output to stdout instead of writing it');
        $this->addOption('skip-manifest', null, InputOption::VALUE_NONE, 'Do not update manifest (always skipped if --out is being used)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('out')) {
            echo "Generating new migration script\n";
        }

        $this->buildTime = time();
        $this->toStdout  = $input->getOption('out');

        $this->appendBuffer($this->getFileHeader());
        $this->appendBuffer($this->getClassStart());

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
                        $this->appendBuffer("\n");
                        $this->appendBuffer("    // By comparing the modified files in this branch, I have guessed\n");
                        $this->appendBuffer("    // that the PostBuild routines dont need to run. You should check though (remove this comment when you have).\n");
                        $this->appendBuffer("    public static \$skipPostBuild = true;\n");
                    }
                }
            }
        }

        $newTableQueries = [];
        $bcAlterQueries  = [];
        $alterQueries    = [];

        foreach ([
            'default' => $this->getContainer()->get('doctrine.orm.default_entity_manager'),
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
                        $newTableQueries[] = $sql;
                    }

                    foreach ($t->getForeignKeys() as $fk) {
                        $sql           = $platform->getCreateForeignKeySQL($fk, $t);
                        $newFksLines[] = $sql;
                    }
                }

                if ($newFksLines) {
                    $newTableQueries = array_merge($newTableQueries, $newFksLines);
                }
            }

            if (!empty($diff->changedTables)) {
                foreach ($diff->changedTables as $tableDiff) {
                    $parts = $platform->getAlterTableSQL($tableDiff);
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
                return '$this->execDbQuery("'.addslashes($sql).'");';
            })));
        }

        if ($bcAlterQueries) {
            $this->appendBuffer("\n");
            $this->appendBuffer($this->getMethodLines('runBcAlters', ListUtils::map($bcAlterQueries, function ($sql) {
                return '$this->execDbQuery("'.addslashes($sql).'");';
            })));
        }

        if ($alterQueries) {
            $this->appendBuffer("\n");
            $this->appendBuffer($this->getMethodLines('runAlters', ListUtils::map($alterQueries, function ($sql) {
                return '$this->execDbQuery("'.addslashes($sql).'");';
            })));
        }

        $this->appendBuffer($this->getClassEnd());

        if (!$input->getOption('out')) {
            echo "  .. done\n";

            $className    = 'Build'.$this->buildTime;
            $baseBuildDir = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';
            $buildDir     = $baseBuildDir.'/'.date('Y/m', $this->buildTime);
            $filePath     = $buildDir.'/'.$className.'.php';
            $manifestPath = $baseBuildDir.'/build-manifest.php';

            if (!is_dir($buildDir)) {
                mkdir($buildDir, 0744, true);
            }

            echo "Writing $filePath\n";
            file_put_contents($filePath, $this->buffer);
            echo " .. done\n";

            echo "Generating manifest to $manifestPath\n";
            $gen = new GenBuildManifest($baseBuildDir);
            file_put_contents($manifestPath, $gen->getContents());
            echo " .. done\n";
        }

        return 0;
    }

    /**
     * @param string $content
     */
    private function appendBuffer($content)
    {
        $this->buffer[] = $content;

        if ($this->toStdout) {
            echo $content;
        }
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
        $result = "{$indent}public function {$methodName}()\n"
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

class Build$time extends AbstractImprovedBuild
{
    public static \$title = 'Enter a title/summary here';

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
