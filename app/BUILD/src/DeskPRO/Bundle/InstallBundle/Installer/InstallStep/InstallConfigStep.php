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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class InstallConfigStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Installing configuration');

        $fs = new Filesystem();

        $new_config_path = $this->getContext()->getDpEnv()->getAppDir().'/config_new';

        if ($this->getSession()->getSource() === 'dev') {
            $new_config_path = DP_DIR.'/dev/config_dev_new';
        }

        $dir = Finder::create()
            ->files()
            ->in($new_config_path);

        $config_path = $this->getConfigPath();

        if (file_exists($config_path.DIRECTORY_SEPARATOR.'config.database.php')) {
            $backup_dir = $this->backupConfig();
            $this->writeln('<info>Configuration files already exist</info>');
            $this->writeln("We have MOVED configuration files into a sub-directory:\n<info>$backup_dir</info>");
            $this->writeln('');
        } else {
            // might be non database files as well, just backup anyway
            $this->backupConfig();
        }

        $this->writeln('Installing configuration files to:');
        $this->writeln('<info>'.$config_path.'</info>');

        /** @var \SplFileInfo $f */
        foreach ($dir as $f) {
            $path    = str_replace('\\', '/', $f->getRealPath());
            $relPath = str_replace(str_replace('\\', '/', $new_config_path), '', $path);
            $relPath = str_replace('/', DIRECTORY_SEPARATOR, $relPath);

            $fs->copy(
                $f->getRealPath(),
                $config_path.$relPath
            );
        }

        $this->writeVars('config.database.php', 'DB_CONFIG', [
            'host'     => $this->getSession()->getDbInfo()->host,
            'user'     => $this->getSession()->getDbInfo()->user,
            'password' => $this->getSession()->getDbInfo()->password,
            'dbname'   => $this->getSession()->getDbInfo()->dbname,
        ]);

        $this->writeVars('config.paths.php', 'PATHS_CONFIG', [
            'php_path'       => $this->getSession()->getPaths()->php_path,
            'mysqldump_path' => $this->getSession()->getPaths()->mysqldump_path,
            'mysql_path'     => $this->getSession()->getPaths()->mysql_path,
        ]);

        $this->writeln('Done!');
        $this->getSession()->disableFlag('reset_config');
    }

    private function writeVars($file, $varname, array $vars)
    {
        $full = $this->getContext()->getDpEnv()->getDpRoot()
            .DIRECTORY_SEPARATOR
            .'config'
            .DIRECTORY_SEPARATOR
            .$file;

        $content = file_get_contents($full);

        foreach ($vars as $k => $v) {
            $content = $this->writeInContent($k, $v, $content, $varname);
        }

        file_put_contents($full, $content);
    }

    private function writeInContent($name, $value, $content, $varname)
    {
        $key = preg_quote($name, '#');

        return preg_replace(
            '#^\$'.$varname.'\[\''.$key.'\']\s*=\s*.*?;$#m',
            '$'.$varname.'[\''.$name.'\'] = '.var_export($value, true).';',
            $content
        );
    }

    /**
     * Example: /path/to/deskpro/config.
     *
     * @return string
     */
    private function getConfigPath()
    {
        return $this->getContext()->getDpEnv()->getDpRoot().DIRECTORY_SEPARATOR.'config';
    }

    private function backupConfig()
    {
        $fs          = new Filesystem();
        $config_path = $this->getConfigPath();

        $backup_basedir = $this->getContext()->getDpEnv()->getUserBackupsDir().DIRECTORY_SEPARATOR.'config_backup';
        $backup_dir     = $backup_basedir.DIRECTORY_SEPARATOR.date('Ymd_His').'_'.mt_rand(1000, 9999);
        $any            = false;

        $fs->mkdir($backup_dir);

        $finder = Finder::create()
            ->notName('.gitkeep')
            ->depth(0)
            ->in($config_path);

        /** @var \SplFileInfo $f */
        foreach ($finder as $f) {
            $fs->rename($f->getRealPath(), $backup_dir.DIRECTORY_SEPARATOR.$f->getBasename());
            $any = true;
        }

        if (!$any) {
            $fs->remove($backup_dir);
        }

        return $backup_dir;
    }

    public function isComplete()
    {
        return !$this->getSession()->hasFlag('reset_config')
            && file_exists(
                $this->getContext()->getDpEnv()->getDpRoot()
                .DIRECTORY_SEPARATOR.'config'
                .DIRECTORY_SEPARATOR.'config.database.php'
            )
        ;
    }
}
