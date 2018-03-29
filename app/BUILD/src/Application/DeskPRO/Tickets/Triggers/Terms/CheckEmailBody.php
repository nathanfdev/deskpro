<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks body of an email for a string.
 *
 * The check is done against all of these:
 * - text
 * - html
 * - plaintext with whitespace removed
 * - html with tags and whitespace removed
 *
 * @option string body
 */
class CheckEmailBody extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('body');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$context->hasEmailContext()) {
            $context->getLogger()->debug('Not email context');

            return false;
        }

        $options = $this->getTermOptions();

        $reader  = $context->getEmailContext();
        $strings = [];

        if ($html = $reader->getBodyHtml()->getBodyUtf8()) {
            $strings[] = $html;
            $strings[] = trim(preg_replace('#\s+#', ' ', strip_tags($html)));
        }
        if ($txt = $reader->getBodyText()->getBodyUtf8()) {
            $strings[] = $txt;
            $strings[] = trim(preg_replace('#\s+#', ' ', $txt));
        }

        $strings = array_unique($strings);

        $value = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['body']);
    }
}
