<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Strings;

/**
 * Execute a web hook.
 *
 * @option string url
 * @option string username
 * @option string password
 * @option string method
 * @option string custom_data
 * @option string headers
 * @option int    timeout
 */
class DeleteAttachments extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames(
            'must_match',
            'must_not_match',
            'at_least',
            'skip_inline'
        );

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getActionOptions();
        $log     = ['deleted' => []];

        foreach ($ticket->attachments as $attachment) {
            /* @var TicketAttachment $attachment */
            $blob           = $attachment->getBlob();
            $must_match     = $options->get('must_match');
            $must_not_match = $options->get('must_not_match');
            $at_least       = $options->get('at_least');
            $skip_inline    = $options->get('skip_inline');

            if ($skip_inline && $attachment->is_inline) {
                continue;
            }

            if ($regexp = Strings::getInputRegexPattern($must_match)) {
                if (!preg_match($regexp, $blob->filename)) {
                    continue;
                }
            }

            if ($regexp = Strings::getInputRegexPattern($must_not_match)) {
                if (preg_match($regexp, $blob->filename)) {
                    continue;
                }
            }

            if ($at_least) {
                if ((int) $at_least >= $blob->filesize / 1024) {
                    continue;
                }
            }

            $this->delete($ticket, $attachment, $log);
        }

        if ($log['deleted']) {
            $ticket->getStateChangeRecorder()->recordData('deleted_attachments', $log);
        }
    }

    protected function delete(Ticket $ticket, TicketAttachment $attachment, &$log)
    {
        $log['deleted'][] = [
            'filename' => $attachment->blob->filename,
            'filesize' => $attachment->blob->getReadableFilesize(),
            'message'  => $attachment->message->id,
        ];

        /* @var TicketMessage $message */
        $ticket->removeAttachment($attachment);
        $this->getContainer()->getEm()->remove($attachment);
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
