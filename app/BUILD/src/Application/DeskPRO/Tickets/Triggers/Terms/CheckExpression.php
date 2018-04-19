<?php

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Checks an expression.
 *
 * @option string subject
 */
class CheckExpression extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('expr');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        try {
            $e     = new ExpressionLanguage();
            $value = $e->evaluate($options->get('expr'), [
                'ticket'  => $ticket,
                'context' => $context,
            ]);
        } catch (\Exception $e) {
            $context->getLogger()->addError('Expression error: '.$e->getMessage());

            return false;
        }

        switch ($this->getTermOperator()) {
            case self::OP_IS:
                return (bool) $value;
            case self::OP_NOT:
                return !$value;
            default:
                return false;
        }
    }
}
