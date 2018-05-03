<?php

namespace DeskPRO\Bundle\UpdateBundle\DbBackup;

class CmdBuilder implements CmdBuilderInterface
{
    /**
     * @var string
     */
    private $mysqldumpPath;

    /**
     * @var string
     */
    private $mysqlPath;

    /**
     * CmdBuilder constructor.
     *
     * @param string $mysqlPath
     * @param string $mysqldumpPath
     */
    public function __construct($mysqlPath, $mysqldumpPath)
    {
        $this->mysqlPath     = $mysqlPath;
        $this->mysqldumpPath = $mysqldumpPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getDumpCmd($filename, array $dbInfo, array $options = [])
    {
        $cmd = [
            $this->escapeArgument($this->mysqldumpPath),
        ];

        if ($dbInfo['unix_socket']) {
            $cmd[] = '--protocol=socket';
            $cmd[] = '-S '.$this->escapeArgument($dbInfo['unix_socket']);
        } else {
            $cmd[] = '-h '.$dbInfo['host'];
            $cmd[] = '--port '.$dbInfo['port'];
        }

        $password = $this->escapeArgument($dbInfo['password']);

        if ((isset($options['with_gzip']) && $options['with_gzip']) && !$this->isWindowsMode()) {
            $cmd = array_merge($cmd, [
                '-u '.$this->escapeArgument($dbInfo['user']),
                '-p'.$password,
                '--opt', '-Q', '--hex-blob', '--lock-tables=false', '--single-transaction',
                $this->escapeArgument($dbInfo['dbname']),
                '|', 'gzip',
                '>', $this->escapeArgument($filename),
            ]);
        } else {
            $cmd = array_merge($cmd, [
                '-u '.$this->escapeArgument($dbInfo['user']),
                '-p'.$password,
                '--opt', '-Q', '--hex-blob', '--lock-tables=false', '--single-transaction',
                $this->escapeArgument($dbInfo['dbname']),
                '>', $this->escapeArgument($filename),
            ]);
        }

        return implode(' ', $cmd);
    }

    /**
     * Escapes a string to be used as a shell argument.
     *
     * Copy pasted from
     * https://github.com/symfony/process/blob/v3.3.13/Process.php
     *
     * @param string $argument
     *
     * @return string
     */
    private function escapeArgument($argument)
    {
        // we modified next if condition
        // originally, DIRECTORY_SEPARATOR was used to determinate WIN mode
        if (!$this->isWindowsMode()) {
            return "'".str_replace("'", "'\\''", $argument)."'";
        }
        if ('' === $argument = (string) $argument) {
            return '""';
        }
        if (false !== strpos($argument, "\0")) {
            $argument = str_replace("\0", '?', $argument);
        }
        if (!preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }
        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);

        return '"'.str_replace(['"', '^', '%', '!', "\n"], ['""', '"^^"', '"^%"', '"^!"', '!LF!'], $argument).'"';
    }

    /**
     * Check if run under windows env
     * Used to avoid hardcoded dependencies in test.
     *
     * @return bool
     */
    protected function isWindowsMode()
    {
        return defined('PHP_WINDOWS_VERSION_BUILD');
    }
}
