<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildTasks;

use Doctrine\DBAL\Connection;

class BuildStatus
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * BuildStatus constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return int
     */
    public function getSchemaBuild()
    {
        $dbVersion = $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build'");

        return $dbVersion;
    }

    /**
     * @param int $buildId
     */
    public function setSchemaBuild($buildId)
    {
        $this->db->beginTransaction();
        $this->db->delete('settings', ['name' => 'core.deskpro_build']);
        $this->db->insert('settings', ['name' => 'core.deskpro_build', 'value' => $buildId]);
        $this->db->commit();
    }

    /**
     * @param int $buildId
     *
     * @return string
     */
    public function formatBuildId($buildId)
    {
        return date('Y-m-d', $buildId);
    }

    /**
     * @param int $buildId
     *
     * @return bool
     */
    public function hasBuildRun($buildId)
    {
        return $this->db->fetchColumn('
            SELECT COUNT(*)
            FROM install_data
            WHERE build = ? AND name = ?
        ', [$buildId, 'has_run']) >= 1;
    }

    /**
     * @param int $buildId
     */
    public function markBuildHasRun($buildId)
    {
        $this->db->beginTransaction();
        $this->db->delete('install_data', ['build' => $buildId, 'name' => 'has_run']);
        $this->db->insert('install_data', ['build' => $buildId, 'name' => 'has_run', 'data' => 1]);
        $this->db->commit();
    }
}
