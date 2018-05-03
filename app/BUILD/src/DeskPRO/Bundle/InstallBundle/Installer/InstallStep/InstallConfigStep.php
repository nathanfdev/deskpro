<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class InstallConfigStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Installing configuration');

        $fs = new Filesystem();

        $newConfigPath = $this->getContext()->getDpEnv()->getAppDir().'/config_new';

        if ($this->getSession()->getSource() === 'dev') {
            $newConfigPath = DP_DIR.'/dev/config_dev_new';
        }

        $dir = Finder::create()
            ->files()
            ->in($newConfigPath);

        $configPath = $this->getConfigPath();

        if (file_exists($configPath.DIRECTORY_SEPARATOR.'config.database.php')) {
            $backupDir = $this->backupConfig();
            $this->writeln('<info>Configuration files already exist</info>');
            $this->writeln("We have MOVED configuration files into a sub-directory:\n<info>$backupDir</info>");
            $this->writeln('');
        } else {
            // might be non database files as well, just backup anyway
            $this->backupConfig();
        }

        $this->writeln('Installing configuration files to:');
        $this->writeln('<info>'.$configPath.'</info>');

        /** @var \SplFileInfo $f */
        foreach ($dir as $f) {
            $path    = str_replace('\\', '/', $f->getRealPath());
            $relPath = str_replace(str_replace('\\', '/', $newConfigPath), '', $path);
            $relPath = str_replace('/', DIRECTORY_SEPARATOR, $relPath);

            $fs->copy(
                $f->getRealPath(),
                $configPath.$relPath
            );
        }

        $this->writeVars('config.database.php', 'DB_CONFIG', [
            'host'     => $this->getSession()->getDbInfo()->host,
            'user'     => $this->getSession()->getDbInfo()->user,
            'password' => $this->getSession()->getDbInfo()->password,
            'dbname'   => $this->getSession()->getDbInfo()->dbname,
        ]);

        if ($this->getSession()->getDbInfo() !== $this->getSession()->getSystemDbInfo()) {
            $this->writeVars('advanced/config.database_advanced.php', 'DB_CONFIG\[\'system\'\]', [
                'host'     => $this->getSession()->getSystemDbInfo()->host,
                'user'     => $this->getSession()->getSystemDbInfo()->user,
                'password' => $this->getSession()->getSystemDbInfo()->password,
                'dbname'   => $this->getSession()->getSystemDbInfo()->dbname,
            ]);
        }

        if ($this->getSession()->getDbInfo() !== $this->getSession()->getAuditDbInfo()) {
            $this->writeVars('advanced/config.database_advanced.php', 'DB_CONFIG\[\'audit\'\]', [
                'host'     => $this->getSession()->getAuditDbInfo()->host,
                'user'     => $this->getSession()->getAuditDbInfo()->user,
                'password' => $this->getSession()->getAuditDbInfo()->password,
                'dbname'   => $this->getSession()->getAuditDbInfo()->dbname,
            ]);
        }

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
            '#^(//)?\$'.$varname.'\[\''.$key.'\']\s*=\s*.*?;$#m',
            '$'.str_replace('\\', '', $varname).'[\''.$name.'\'] = '.var_export($value, true).';',
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
        $path = $this->getContext()->getDpEnv()->getDpRoot().DIRECTORY_SEPARATOR.'config';

        if (!is_dir($path)) {
            @mkdir($path);
        }

        return $path;
    }

    private function backupConfig()
    {
        $fs         = new Filesystem();
        $configPath = $this->getConfigPath();

        if (!is_dir($configPath)) {
            return;
        }

        $backupBasedir = $this->getContext()->getDpEnv()->getUserBackupsDir().DIRECTORY_SEPARATOR.'config_backup';
        $backupDir     = $backupBasedir.DIRECTORY_SEPARATOR.date('Ymd_His').'_'.mt_rand(1000, 9999);
        $any           = false;

        $fs->mkdir($backupDir);

        $finder = Finder::create()
            ->notName('.gitkeep')
            ->depth(0)
            ->in($configPath);

        /** @var \SplFileInfo $f */
        foreach ($finder as $f) {
            $fs->rename($f->getRealPath(), $backupDir.DIRECTORY_SEPARATOR.$f->getBasename());
            $any = true;
        }

        if (!$any) {
            $fs->remove($backupDir);
        }

        return $backupDir;
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
