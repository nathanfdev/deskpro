<?php

namespace DeskPRO\Bundle\UpdateBundle\DbBackup;

interface CmdBuilderInterface
{
    /**
     * @param string $filename
     * @param array  $dbInfo   DB info from (e.g. from DpRun\LowUtil::getMysqlInfoFromConfigArray)
     * @param array  $options
     *
     * @return string
     */
    public function getDumpCmd($filename, array $dbInfo, array $options);
}
