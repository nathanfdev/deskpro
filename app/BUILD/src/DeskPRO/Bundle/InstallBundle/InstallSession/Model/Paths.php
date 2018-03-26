<?php

namespace DeskPRO\Bundle\InstallBundle\InstallSession\Model;

class Paths
{
    public $php_path;
    public $mysql_path;
    public $mysqldump_path;

    public function hasAllPaths()
    {
        return $this->php_path !== null
            && $this->mysql_path !== null
            && $this->mysqldump_path !== null;
    }
}
