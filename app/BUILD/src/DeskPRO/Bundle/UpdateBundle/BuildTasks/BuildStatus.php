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
