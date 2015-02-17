<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1423239825 extends AbstractBuild
{
    public function run()
    {
        $this->out("Mark old sendmail_queue blobs as temp so they are cleaned up");
        $this->execMutateSql("
            UPDATE blobs
            LEFT JOIN sendmail_queue ON blobs.id = sendmail_queue.blob_id
            SET blobs.is_temp = 1
            WHERE sendmail_queue.id IS NOT NULL
        ");

        $this->out("Moving queued messags in sendmail_queue to new sendmail_sources");
        while (($msg = $this->_getNext()) !== null) {
            if ($msg == -1) continue;
            $swift_message = $msg['message'];
            unset($msg['message']);
            try {
                $this->container->get('email.source_mapper')->createSourceForMessage($swift_message, 'pending');
            } catch (\Exception $e) {
                $msg['@error'] = $e->getMessage();
                $this->_saveDataBackup($msg, @serialize($swift_message));
            }
        }

        $this->out("Drop the old table");
        $this->execMutateSql("SET FOREIGN_KEY_CHECKS = 0");
        $this->execMutateSql("DROP TABLE IF EXISTS sendmail_queue");
        $this->execMutateSql("SET FOREIGN_KEY_CHECKS = 1");
    }

    private function _getNext()
    {
        $rec = $this->container->getDb()->fetchAssoc("
            SELECT * FROM sendmail_queue
            WHERE status IN ('pending') AND blob_id IS NOT NULL
            ORDER BY id ASC
            LIMIT 1
        ");

        if (!$rec) {
            return null;
        }

        $this->container->getDb()->delete('sendmail_queue', array('id' => $rec['id']));

        $blob = $this->container->getDb()->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($rec['blob_id']));
        if (!$blob) {
            $rec['@error'] = 'missing blob';
            $this->_saveDataBackup($rec);
            return -1;
        }

        try {
            $source = $this->container->getBlobStorage()->copyBlobRowToString($blob);
        } catch (\Exception $e) {
            $rec['@error'] = $e->getMessage();
            $this->_saveDataBackup($rec);
            return -1;
        }

        $message = @unserialize($source);
        if (!$message) {
            $rec['@error'] = "failed to deserialize";
            $this->_saveDataBackup($rec, $source);
            return 1;
        }

        $rec['message'] = $message;

        return $rec;
    }

    private function _saveDataBackup(array $info, $bin_data = null)
    {
        $name = "sendmail_queue." . $info['id'];
        file_put_contents(dp_get_backup_dir().'/'.$name, json_encode($info));

        if ($bin_data) {
            file_put_contents(dp_get_backup_dir() . '/' . $name . ".dat", $bin_data);
        }
    }
}