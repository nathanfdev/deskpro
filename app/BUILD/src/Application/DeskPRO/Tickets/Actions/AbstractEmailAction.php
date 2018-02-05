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

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\ChangeEmailLog;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmail;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use Application\DeskPRO\Twig\Extension\TemplatingExtension;
use DeskPRO\Bundle\AppBundle\Templating\EmailTemplatesDesc;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;

abstract class AbstractEmailAction extends AbstractContainerAwareAction implements ActionInterface, NoopableInterface
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\Entity\EmailAccount
     */
    protected function getFromEmailAccountOption(Ticket $ticket, ExecutorContextInterface $context)
    {
        $fromAccount = $this->getActionOption('from_account') ?: null;
        if ($fromAccount) {
            if (Numbers::isInteger($fromAccount)) {
                $fromAccountId = $fromAccount;
                try {
                    $fromAccount = $this->getContainer()->getEmailAccountManager()->getAccount($fromAccountId);
                } catch (\OutOfBoundsException $e) {
                    $context->getLogger()->debug("[AbstractEmailAction] Invalid account: $fromAccountId");
                    throw new \InvalidArgumentException('invalid_account');
                }
            }

            $context->getLogger()->debug("[AbstractEmailAction] Sending with email account: $fromAccount");

            if (!$fromAccount->is_enabled) {
                $context->getLogger()->warn('[AbstractEmailAction] Email account is not enabled');
                throw new \InvalidArgumentException('account_disabled');
            }

            if (!$fromAccount->outgoing_account) {
                $context->getLogger()->warn('[AbstractEmailAction] Email account is not an outgoing account');
                throw new \InvalidArgumentException('account_not_outgoing');
            }
        }

        return $fromAccount;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param bool                     $allowBlank
     *
     * @throws \InvalidArgumentException
     *
     * @return string|null
     */
    protected function getEmailTemplateOption(Ticket $ticket, ExecutorContextInterface $context, $allowBlank = false)
    {
        $template = $this->getActionOption('template');
        if (!$template) {
            if ($allowBlank) {
                return;
            }

            $context->getLogger()->warn('[AbstractEmailAction] No template specified');
            throw new \InvalidArgumentException('no_template_specified');
        }

        $context->getLogger()->debug("[AbstractEmailAction] Using template: $template");
        if (!$this->getContainer()->get('templating.email')->exists($template)) {
            $context->getLogger()->warn('[AbstractEmailAction] Template does not exist');
            throw new \InvalidArgumentException('invalid_template');
        }

        return $template;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $mode    'user' or 'agent'
     *
     * @return array
     */
    protected function getStandardEmailVars(Ticket $ticket, ExecutorContextInterface $context, $mode)
    {
        //------------------------------
        // Build up some type flags
        //------------------------------

        $state = $ticket->getStateChangeRecorder();
        if ($state->isNewTicket()) {
            $type = 'newticket';
        } elseif ($state->hasChangedField('message')) {
            $type = 'newreply';
        } else {
            $type = 'updated';
        }

        $context->getLogger()->info("[AbstractEmailAction] Type: $type");
        $context->getLogger()->info(sprintf('[AbstractEmailAction] Performer: %s', $context->getEventPerformer()));

        //------------------------------
        // Set reply flags
        //------------------------------

        $newReplies      = $state->getNewReplies();
        $isNewTicket     = $state->isNewTicket();
        $isNewAgentReply = false;
        $isNewAgentNote  = false;
        $isNewUserReply  = false;

        foreach ($newReplies as $message) {
            if ($message->is_agent_note) {
                $isNewAgentNote = true;
            } elseif ($message->person->is_agent) {
                $isNewAgentReply = true;
            } else {
                $isNewUserReply = true;
            }
        }

        // In user mode, never show notes
        if ($mode == 'user') {
            $newReplies = array_filter($newReplies, function ($r) {
                return !$r->is_agent_note;
            });
            $ticketLogs = null;

            // Agent mode - include ticket logs
        } else {
            $ticketLogGenerator = new TicketLogGenerator($ticket, $context);
            $ticketLogs         = $ticketLogGenerator->getLogEntries();
        }

        //------------------------------
        // Build map of mentions
        //------------------------------

        $vars = [
            'type'               => $type,
            'user_mode'          => $mode,
            'performer_type'     => $context->getEventPerformer(),
            'is_new_ticket'      => $isNewTicket,
            'is_new_agent_reply' => $isNewAgentReply,
            'is_new_agent_note'  => $isNewAgentNote,
            'is_new_user_reply'  => $isNewUserReply,
            'is_status_change'   => $state->hasChangedField('status'),
            'action_performer'   => $context->getPersonContext(),
            'new_message'        => Arrays::getFirstItem($newReplies),
            'new_messages'       => $newReplies,
            'ticket_logs'        => $ticketLogs,
            'user_vars'          => $context->getUserVars(),
        ];

        return $vars;
    }

    /**
     * @param TicketEmail              $ticketEmail
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    protected function recordEmailTicketLog(TicketEmail $ticketEmail, Ticket $ticket, ExecutorContextInterface $context)
    {
        $state = $ticket->getStateChangeRecorder();

        $change = new ChangeEmailLog(
            'ticket_email',
            $ticketEmail->getUserMode(),
            $ticketEmail->getSentToName(),
            $ticketEmail->getSentToEmail(),
            $ticketEmail->getSentWithCcs(),
            $ticketEmail->getFromName(),
            $ticketEmail->getFromEmailAccount()->getUseEmailAddress(),
            $ticketEmail->getTemplateName(),
            $ticketEmail->getSendmailSourceId()
        );

        $state->recordChange($change);
    }

    /**
     * @param string                   $name
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $email_mode 'user' or 'agent'
     *
     * @return string
     */
    protected function renderFromName($name, Ticket $ticket, ExecutorContextInterface $context, $email_mode)
    {
        if (!$name) {
            return '';
        }

        switch ($name) {
            case 'performer':
                $person = $context->getPersonContext();
                if (!$person) {
                    return '';
                }

                if ($email_mode == 'agent') {
                    return $person->getDisplayName();
                } else {
                    return $person->getDisplayNameUser();
                }
            case 'helpdesk_name':
                /* Todo ensure we stacked the right brand here */
                return $this->getContainer()->getBrandSetting('core.deskpro_name');
            case 'site_name':
                /* Todo ensure we stacked the right brand here */
                return $this->getContainer()->getBrandSetting('core.site_name');
            default:
                try {
                    $name = $this->renderStringTemplate($name, $ticket, $context);

                    return trim(Strings::collapseWhitespace(Strings::removeLineBreaks($name)));
                } catch (\Exception $e) {
                    $context->getLogger()->warn('Invalid name pattern syntax: '.$name.'. Exception: '.$e->getMessage(), ['exception' => $e]);

                    return '';
                }
        }
    }

    /**
     * @param string                   $string
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param array                    $extra_vars
     *
     * @return string
     */
    protected function renderStringTemplate($string, Ticket $ticket, ExecutorContextInterface $context, array $extra_vars = null)
    {
        /** @var TemplatingExtension $renderer */
        $renderer = $this->getContainer()->getTwig()->getExtension('deskpro_templating');
        $actionVars = ActionVars::getContextVars($context);
        $extraRendererVars = empty($extra_vars) ? $actionVars : array_merge($actionVars, $extra_vars);

        return $renderer->renderTicketTemplate($string, $ticket, $context, $extraRendererVars);
    }

    /**
     * @param array                    $rawHeaders
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return array
     */
    protected function processHeaders(array $rawHeaders, Ticket $ticket, ExecutorContextInterface $context)
    {
        $headers = [];
        foreach ($rawHeaders as $h) {
            if (empty($h['name'])) {
                continue;
            }
            if (empty($h['value'])) {
                $h['value'] = '';
            }
            $headers[] = [
                'name'  => $this->renderStringTemplate($h['name'], $ticket, $context),
                'value' => $this->renderStringTemplate($h['value'], $ticket, $context),
            ];
        }

        return $headers;
    }

    /**
     * @param $template
     * @param $arguments
     * @param $context
     *
     * @throws \Exception
     *
     * @return bool|EmailBaseType
     */
    protected function createViewModelFromTemplate($template, $arguments, $context)
    {
        if (strpos($template, 'SendmailBundle:emails_custom:') === 0) {
            $factory   = $this->getContainer()->get('email.custom_viewmodel_factory');
            $viewModel = call_user_func_array([$factory, 'createCustomTemplateModel'], $arguments);
            $viewModel->setTemplateFile($template);

            return $viewModel;
        }
        $templatesDesc = new EmailTemplatesDesc();
        $manifest      = $templatesDesc->getManifest();
        $viewModel     = false;
        foreach ($manifest as $t) {
            if (isset($t['newTemplate']) && $t['newTemplate'] === $template) {
                if ($t['viewModel']) {
                    $viewModel = $t['viewModel'];
                }
                break;
            }
        }
        if (!$viewModel) {
            $context->getLogger()->info('Unknown template: '.$template);

            return false;
        }
        $factory = $this->getContainer()->get('email.user_viewmodel_factory');
        $action  = 'create'.$viewModel.'Model';

        if (!is_callable([$factory, $action])) {
            $context->getLogger()->info('Missing method '.$action.' in factory');

            return false;
        }

        return call_user_func_array([$factory, $action], $arguments);
    }
}
