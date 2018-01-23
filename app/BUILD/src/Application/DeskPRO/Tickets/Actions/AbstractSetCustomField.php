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
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Util;

abstract class AbstractSetCustomField extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('field_id', 'value');
        $options->addValidNames(['op', 'with_formatter']);
        $options->setAliases('field_id', ['field']);

        return $options;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return \Application\DeskPRO\CustomFields\FieldManager
     */
    abstract public function getFieldManager(Ticket $ticket, ExecutorContextInterface $context);

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return mixed
     */
    abstract public function getApplicableObject(Ticket $ticket, ExecutorContextInterface $context);

    /**
     * Parse the action options and return the field id
     *
     * @return string
     */
    public function resolveFieldId()
    {
        $fieldId = $this->getActionOption('field_id');
        if (empty($fieldId)) {
            return $this->getActionOption('field');
        }

        return "field_{$fieldId}";
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $fm  = $this->getFieldManager($ticket, $context);
        $obj = $this->getApplicableObject($ticket, $context);

        if (!$fm || !$obj) {
            return;
        }

        $operator = $this->getActionOption('op');

        switch ($operator) {
            case 'unset':
                $this->applyUnsetOperator($ticket, $context);
                break;
            case 'unset-list':
                $this->applyUnsetListOperator($ticket, $context);
                break;
            default:
                $this->applySetOperator($ticket, $context);
        }
    }

    private function applyUnsetOperator(Ticket $ticket, ExecutorContextInterface $context)
    {
        $fm  = $this->getFieldManager($ticket, $context);
        $obj = $this->getApplicableObject($ticket, $context);

        $fieldId = $this->resolveFieldId();

        $form_array = [$fieldId => null];
        $fm->saveFormToObject($form_array, $obj, true);
    }

    private function applySetOperator(Ticket $ticket, ExecutorContextInterface $context)
    {
        $fm  = $this->getFieldManager($ticket, $context);
        $obj = $this->getApplicableObject($ticket, $context);
        $value   = $this->getActionOption('value');

        if (is_string($value) && $this->getActionOption('with_formatter')) {
            $extraVars = ActionVars::getContextVars($context);
            $renderer = $this->getContainer()->get('twig_template_renderer');
            $value    = $renderer->renderTicketTemplate($value, $ticket, $context, $extraVars);
        }

        $fieldId = $this->resolveFieldId();
        $form_array = [$fieldId => $value];

        $fm->saveFormToObject($form_array, $obj, true);
    }

    private function applyUnsetListOperator(Ticket $ticket, ExecutorContextInterface $context)
    {
        $fm  = $this->getFieldManager($ticket, $context);
        $obj = $this->getApplicableObject($ticket, $context);

        $fieldId = $this->resolveFieldId();
        $fieldDef = $fm->getFieldFromId($fieldId);
        if (empty($fieldDef)) {
            throw new \RuntimeException(sprintf('could not find field with id: %s', $fieldId));
        }

        $value   = $this->getActionOption('value');
        if (!is_numeric($value) && empty($value)) {
            throw new \DomainException('the value option must be set');
        }

        if (is_string($value) && $this->getActionOption('with_formatter')) {
            $extraVars = ActionVars::getContextVars($context);
            $renderer = $this->getContainer()->get('twig_template_renderer');

            $value    = $renderer->renderTicketTemplate($value, $ticket, $context, $extraVars);
        }

        $fm->removeSomeCustomDataOnObjectAndFlushChanges(
            $obj,
            $fieldDef,
            function( CustomDataAbstract $customData) use ($value) {
                return $customData->getData() === $value;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return ['fields'];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return Util::getBaseClassname($this).$this->getActionOption('field_id');
    }
}
