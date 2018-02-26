<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * Deskpro.
 *
 * @category Entities
 */

namespace deskpro_clickatell_sms;

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
            'app_id'      => $context->getApp()->getId(),
            'action_name' => $action_name,
            'def_class'   => 'deskpro_clickatell_sms\\Ticket\\Actions\\ActionDef\\SmsClickatellActionDef',
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
        return 'SmsClickatellAction'.$context->getApp()->getId();
    }
}
