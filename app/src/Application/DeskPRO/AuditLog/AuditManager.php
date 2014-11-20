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
 */

namespace Application\DeskPRO\AuditLog;

use Application\DeskPRO\AuditLog\AuditWriter\AuditWriterInterface;
use Application\DeskPRO\Entity\AuditLog;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\CompositeCaller;

class AuditManager
{
    /**
     * @var \Orb\Util\CompositeCaller
     */
    protected $writers;

    /**
     * @var \Application\DeskPRO\Entity\AuditLog[]
     */
    protected $pending_logs = array();

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $default_performer = null;

    /**
     * @var bool
     */
    protected $disabled = false;

    public function __construct()
    {
        $this->writers = new CompositeCaller();
    }

    /**
     * Disable the audit manager
     */
    public function disable()
    {
        $this->disabled = true;
    }

    /**
     * Enable the audit manager
     */
    public function enable()
    {
        $this->disabled = false;
    }

    /**
     * Check if the audit manager is enabled
     */
    public function isEnabled()
    {
        return !$this->disabled;
    }

    /**
     * @param Person $person
     */
    public function setDefaultPerformer(Person $person = null)
    {
        $this->default_performer = $person;
    }

    /**
     * Add a writer
     *
     * @param AuditWriterInterface $writer
     */
    public function addWriter(AuditWriterInterface $writer)
    {
        $this->writers->addObject($writer);
    }

    /**
     * @param  mixed    $object
     * @param  string   $field_id
     * @param  mixed    $old_val
     * @param  mixed    $new_val
     * @return AuditLog
     */
    public function recordChange($object, $field_id, $old_val, $new_val)
    {
        if ($this->disabled) {
            return null;
        }

        $name = AuditLog::getObjectNameFromVar($object);

        if (isset($this->pending_logs[$name])) {
            $audit_log = $this->pending_logs[$name];
        } else {
            $audit_log = new AuditLog(AuditLog::UPDATE, $object);
            if ($this->default_performer) {
                $audit_log->setPerson($this->default_performer);
            }
            $this->pending_logs[$name] = $audit_log;
        }

        // A create/delete doesnt set change values
        if ($audit_log->op == AuditLog::CREATE || $audit_log->op == AuditLog::DELETE) {
            return;
        }

        $audit_log->addChangeData($field_id, $old_val, $new_val);

        return $audit_log;
    }

    /**
     * @param  mixed    $object
     * @return AuditLog
     */
    public function recordCreated($object)
    {
        if ($this->disabled) {
            return null;
        }

        $name                      = AuditLog::getObjectNameFromVar($object);
        $audit_log                 = new AuditLog(AuditLog::CREATE, $object);
        $this->pending_logs[$name] = $audit_log;

        if ($this->default_performer) {
            $audit_log->setPerson($this->default_performer);
        }

        return $audit_log;
    }

    /**
     * @param  mixed    $object
     * @return AuditLog
     */
    public function recordDelete($object)
    {
        if ($this->disabled) {
            return null;
        }

        $name                      = AuditLog::getObjectNameFromVar($object);
        $audit_log                 = new AuditLog(AuditLog::DELETE, $object);
        $this->pending_logs[$name] = $audit_log;

        if ($this->default_performer) {
            $audit_log->setPerson($this->default_performer);
        }

        return $audit_log;
    }

    /**
     * Write logs
     *
     * @return void
     */
    public function flushLogs()
    {
        if ($this->disabled) {
            return;
        }

        $ret = $this->writers->callMethod('writeLogs', array($this->pending_logs), null, true);

        foreach ($ret as $r) {
            if ($r['exception']) {
                KernelErrorHandler::logException($r['exception'], false);
            }
        }
    }
}
