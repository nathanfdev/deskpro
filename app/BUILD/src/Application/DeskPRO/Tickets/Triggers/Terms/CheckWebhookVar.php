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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookExecutionContextVars;
use Orb\Util\CheckedOptionsArray;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Checks the value of a user var.
 *
 * @option string name  The name of the user var
 * @option string value Value to check for (not used for isset/notisset)
 */
class CheckWebhookVar extends AbstractTriggerTerm
{
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('name');
        $options->addValidNames('value');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $name = $options->get('name');
        if (!$name) {
            return false;
        }

        $payload = WebhookExecutionContextVars::getWebhookPayload($context);
        $valueExists = ! is_null($payload);
        $value = null;

        if ($payload) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $propertyPath = new PropertyPath($name);
            try {
                $value = $propertyAccessor->getValue($payload, $propertyPath);
            } catch (ExceptionInterface $e) {
                $valueExists = false;
            }
        }

        if ($this->getTermOperator() == 'not_isset') {
            return !$payload || !$valueExists;
        }

        if ($this->getTermOperator() == 'isset') {
            return $payload && $valueExists;
        }

        if (!$payload || !$valueExists) {
            return false;
        }

        return $this->isStringMatch($ticket, $context, TermValue::createWithValue($value), $options['value']);
    }
}
