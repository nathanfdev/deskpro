<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_twilio_sms;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractInstallerHandler;
use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;

class InstallerHandler extends AbstractInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    public function install(InstallerContext $context)
    {
        $this->refreshTriggerAction($context);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(InstallerContext $context)
    {
        $action_name = $this->getActionName($context);
        $context->getDb()->executeUpdate('DELETE FROM ticket_actions_def WHERE action_name = ?', [$action_name]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSettings(InstallerContext $context)
    {
        $this->refreshTriggerAction($context);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackage(InstallerContext $context)
    {
        $this->refreshTriggerAction($context);
    }

    /**
     * @param InstallerContext $context
     */
    private function refreshTriggerAction(InstallerContext $context)
    {
        $action_name = $this->getActionName($context);

        $rec = [
            'app_id'      => $context->getApp()->id,
            'action_name' => $action_name,
            'def_class'   => 'deskpro_twilio_sms\\Ticket\\Actions\\ActionDef\\SmsTwilioActionDef',
            'settings'    => null,
        ];

        $exist_id = $context->getDb()->fetchColumn(
            'SELECT id FROM ticket_actions_def WHERE action_name = ?',
            [$action_name]
        );
        if ($exist_id) {
            $context->getDb()->update('ticket_actions_def', $rec, ['id' => $exist_id]);
        } else {
            $context->getDb()->insert('ticket_actions_def', $rec);
        }
    }

    /**
     * @param InstallerContext $context
     *
     * @return string
     */
    private function getActionName(InstallerContext $context)
    {
        return 'SmsTwilioAction'.$context->getApp()->id;
    }
}
