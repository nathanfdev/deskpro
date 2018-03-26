<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Data;

use Doctrine\ORM\EntityManager;

class GenerateSchema
{
    /**
     * @var array
     */
    protected $creates;

    /**
     * @var array
     */
    protected $alters;

    /**
     * @var array
     */
    protected $triggers;

    /**
     * @var array
     */
    protected $indexes;

    /**
     * @var array
     */
    protected $fks;

    /**
     * @var string
     */
    protected $php_file;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var bool
     */
    protected $is_master_schema;

    /**
     * @param EntityManager $em
     * @param bool          $is_master_schema
     */
    public function __construct(EntityManager $em, $is_master_schema = false)
    {
        $this->em               = $em;
        $this->is_master_schema = $is_master_schema;
    }

    /**
     * @return array
     */
    public function getCreates()
    {
        $this->load();

        return $this->creates;
    }

    /**
     * @return array
     */
    public function getAlters()
    {
        $this->load();

        return $this->alters;
    }

    /**
     * @return array
     */
    public function getTriggers()
    {
        $this->load();

        return $this->triggers;
    }

    /**
     * @return string
     */
    public function getPhpFile()
    {
        $this->load();

        return $this->php_file;
    }

    /**
     * Loads the schema.
     */
    protected function load()
    {
        if ($this->creates !== null) {
            return;
        }

        $this->creates = [];
        $this->alters  = [];

        //------------------------------
        // Load SQL
        //------------------------------

        $em = $this->em;
        /** @var $metadata \Doctrine\ORM\Mapping\ClassMetadata[] */
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool     = new \Doctrine\ORM\Tools\SchemaTool($em);
        $sm       = $this->em->getConnection()->getSchemaManager();
        $all_sql  = $tool->getCreateSchemaSql($metadata);

        //------------------------------
        // Non-entity tables
        //------------------------------

        if ($this->is_master_schema) {
            $all_sql[] = <<<'SQL'
CREATE TABLE `content_search` (
  `object_type` varchar(15) NOT NULL DEFAULT '',
  `object_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  PRIMARY KEY (`object_type`,`object_id`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM
SQL;
        }

        //------------------------------
        // Organise it
        //------------------------------

        $xa = 0;
        $xc = 0;
        $xt = 0;

        $php_creates  = [];
        $php_alters   = [];
        $php_triggers = [];

        foreach ($all_sql as $s) {
            $s = trim($s);

            // Trigger
            if (preg_match('#^CREATE TRIGGER#', $s)) {
                $s_ex = var_export($s, true);

                $this->triggers[] = $s;
                $php_triggers[]   = "\$queries['trigger'][$xt] = $s_ex;";
                ++$xt;

            // Alter
            } elseif (preg_match('#^ALTER#', $s)) {
                $s              = str_replace(["\r\n", "\n"], ' ', $s);
                $this->alters[] = $s;

            // Create
            } else {
                $s = str_replace(["\r\n", "\n"], ' ', $s);
                $s .= ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

                if (strpos($s, 'CREATE TABLE person2usergroups') !== false) {
                    $s = str_replace('INDEX IDX_356C969E217BBB47 (person_id), ', '', $s);
                }

                $s_ex = var_export($s, true);

                $this->creates[] = $s;
                $php_creates[]   = "\$queries['create'][$xc] = $s_ex;";
                ++$xc;
            }
        }

        $this->alters = self::combineAlters($this->alters);

        foreach ($this->alters as $s) {
            $s_ex         = var_export($s, true);
            $php_alters[] = "\$queries['alter'][$xa] = $s_ex;";
            ++$xa;
        }

        //------------------------------
        // Indexes and keys
        //------------------------------

        $this->indexes = [];
        $this->fks     = [];
        $php_indexes   = [];
        $php_fks       = [];

        $schema = $tool->getSchemaFromMetadata($metadata);
        /** @var $tables \Doctrine\DBAL\Schema\Table[] */
        $tables = $schema->getTables();
        foreach ($tables as $table) {
            $t = $table->getName();

            $this->indexes[$t] = [];
            $this->fks[$t]     = [];

            $indexes = $table->getIndexes();
            $fkeys   = $table->getForeignKeys();

            if (count($indexes) > 0) {
                $php_indexes[] = "\$queries['index']['$t'] = array(";
            }
            if (count($fkeys) > 0) {
                $php_fks[] = "\$queries['fk']['$t'] = array(";
            }

            foreach ($indexes as $idx) {
                $sql                                = $sm->getDatabasePlatform()->getCreateIndexSQL($idx, $t);
                $this->indexes[$t][$idx->getName()] = $sql;

                $php_indexes[] = "\t'{$idx->getName()}' => '".addslashes($sql)."',";
            }
            foreach ($fkeys as $fk) {
                $sql                           = $sm->getDatabasePlatform()->getCreateForeignKeySQL($fk, $t);
                $this->fks[$t][$fk->getName()] = $sql;

                $php_fks[] = "\t'{$fk->getName()}' => '".addslashes($sql)."',";
            }

            if (count($indexes) > 0) {
                $php_indexes[] = ');';
            }
            if (count($fkeys) > 0) {
                $php_fks[] = ');';
            }
        }

        //------------------------------
        // Create the PHP file
        //------------------------------

        $php = "<?php\n\n\$queries = array('create' => array(), 'alter' => array(), 'index' => array(), 'fk' => array(), 'trigger' => array());\n\n";
        $php .= implode("\n", $php_creates);
        $php .= "\n\n\n\n\n";
        $php .= implode("\n", $php_alters);
        $php .= "\n\n\n\n\n";
        $php .= implode("\n", $php_triggers);
        $php .= "\n\n\n\n\n";
        $php .= implode("\n", $php_indexes);
        $php .= "\n\n\n\n\n";
        $php .= implode("\n", $php_fks);
        $php .= "\n\n\n\n\nreturn \$queries;\n";

        $this->php_file = $php;
    }

    /**
     * Takes an array of ALTER queries and combines any alters that alter the same table.
     * For example, instead of 10 separate ALTER TABLE queries that add 10 separate FK's, there's only one.
     *
     * @param array $alters
     */
    public static function combineAlters(array $alters)
    {
        $segments = [];

        foreach ($alters as $sql) {
            $sql = str_replace(["\r\n", "\n", ' '], ' ', $sql);
            $sql = trim($sql, ' ;');

            $m = null;
            if (!preg_match('#^ALTER +TABLE +`?(.*?)`? (.*?)$#', $sql, $m)) {
                throw new \InvalidArgumentException("Invalid ALTER query: $sql");
            }

            $table     = $m[1];
            $alter_seg = trim($m[2], ' ,');

            if (!isset($segments[$table])) {
                $segments[$table] = [];
            }

            $segments[$table][] = $alter_seg;
        }

        $return = [];
        foreach ($segments as $table => $segs) {
            $return[] = 'ALTER TABLE '.$table.' '.implode(', ', $segs);
        }

        return $return;
    }
}
